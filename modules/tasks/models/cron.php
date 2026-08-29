<?php
/**
 * @filesource modules/tasks/models/cron.php
 *
 * @copyright 2026 Bannawat
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Cron;

use Kotchasan\Date;

/**
 * งานค้าง : ตรวจสอบกำหนดส่งและแจ้งเตือนผ่าน Telegram (เรียกผ่าน cron_tasks.php)
 *
 * @author Bannawat
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * เรียกใช้จาก cron_tasks.php
     *
     * @return array
     */
    public static function run()
    {
        return [
            'overdue_marked' => static::markOverdueTasks(),
            'notifications' => static::sendDeadlineAlerts()
        ];
    }

    /**
     * เปลี่ยนสถานะงานที่เลยกำหนดส่งจาก pending เป็น overdue
     *
     * @return int จำนวนงานที่เปลี่ยนสถานะ
     */
    public static function markOverdueTasks()
    {
        $now = date('Y-m-d H:i:s');
        // นับจำนวนงานที่ค้างเกินกำหนด
        $count = static::createQuery()
            ->from('tasks A')
            ->where([['A.status', 'pending'], ['A.deadline', '<', $now]])
            ->count();
        if ($count > 0) {
            // อัปเดตสถานะ
            static::createQuery()
                ->update('tasks A')
                ->set(['status' => 'overdue', 'updated_at' => $now])
                ->where([['A.status', 'pending'], ['A.deadline', '<', $now]])
                ->execute();
        }
        return $count;
    }

    /**
     * แจ้งเตือนงานที่ใกล้ถึงกำหนดส่งผ่าน Telegram
     * แจ้งซ้ำได้ไม่เกิน 1 ครั้ง ต่อ 24 ชั่วโมง
     *
     * @return array
     */
    public static function sendDeadlineAlerts()
    {
        $result = ['sent' => 0, 'failed' => 0, 'errors' => []];
        if (empty(self::$cfg->telegram_bot_token)) {
            $result['errors'][] = 'Telegram bot token is not configured';
            return $result;
        }
        // จำนวนวันก่อนถึงกำหนดส่งที่จะเริ่มแจ้งเตือน
        $days = empty(self::$cfg->task_reminder_days) ? 1 : (int) self::$cfg->task_reminder_days;
        if ($days < 1) {
            $days = 1;
        }
        $now = date('Y-m-d H:i:s');
        $query = static::createQuery()
            ->from('tasks A')
            ->join('user U', 'LEFT', ['U.id', 'A.user_id'])
            ->where([
                ['A.status', 'pending'],
                ['A.deadline', '>', $now],
                ['A.deadline', '<=', date('Y-m-d H:i:s', strtotime('+'.$days.' days'))]
            ])
            ->notExists('task_notifications', [
                ['task_id', 'A.id'],
                ['notification_type', 'telegram'],
                ['status', 'sent'],
                ['sent_at', '>', date('Y-m-d H:i:s', strtotime('-24 hours'))]
            ])
            ->select('A.id', 'A.title', 'A.deadline', 'U.id user_id', 'U.telegram_chat_id', 'U.name')
            ->order('A.deadline ASC');
        foreach ($query->execute() as $task) {
            if (empty($task->telegram_chat_id)) {
                // ผู้รับผิดชอบยังไม่ได้ตั้งค่า Telegram Chat ID
                $result['failed']++;
                continue;
            }
            // ส่งข้อความ
            $err = \Gcms\Telegram::sendTo($task->telegram_chat_id, self::buildMessage($task));
            if ($err == '') {
                // ส่งสำเร็จ
                $result['sent']++;
                $status = 'sent';
                $response = '';
            } else {
                // ส่งไม่สำเร็จ
                $result['failed']++;
                $result['errors'][] = 'Task #'.$task->id.': '.$err;
                $status = 'failed';
                $response = $err;
            }
            // บันทึกการแจ้งเตือน
            static::createQuery()
                ->insert('task_notifications', [
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'notification_type' => 'telegram',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'status' => $status,
                    'response' => $response
                ])
                ->execute();
        }
        return $result;
    }

    /**
     * สร้างข้อความแจ้งเตือน
     *
     * @param object $task
     *
     * @return string
     */
    private static function buildMessage($task)
    {
        $text = 'งานค้างที่ใกล้ถึงกำหนดส่ง'."\n";
        $text .= '===================='."\n";
        $text .= 'งาน: '.$task->title."\n";
        $text .= 'ผู้รับผิดชอบ: '.$task->name."\n";
        $text .= 'กำหนดส่ง: '.Date::format($task->deadline, 'd/m/Y H:i')."\n";
        $text .= 'โปรดตรวจสอบและดำเนินการให้ทันเวลา';
        return $text;
    }
}