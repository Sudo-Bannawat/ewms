<?php
/**
 * @filesource modules/tasks/models/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Settings;

use Gcms\Login;
use Kotchasan\Config;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ตั้งค่าโมดูล (settings.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = [];
        // session, token, can_config, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_config')) {
                try {
                    // รับค่าจากการ POST
                    // แจ้งเตือนซ้ำ (ทุกกี่วัน)
                    $reminder_dates = [];
                    $reminder = $request->post('task_reminder_dates', [])->password();
                    $reminder = is_array($reminder) ? $reminder : [$reminder];
                    foreach ($reminder as $days) {
                        if (in_array((int) $days, [1, 3, 7, 14], true)) {
                            $reminder_dates[] = (int) $days;
                        }
                    }
                    // โหลด config
                    $config = Config::load(ROOT_PATH.'settings/config.php');
                    $config->task_reminder_days = $request->post('task_reminder_days')->toInt();
                    $config->task_reminder_dates = $reminder_dates;
                    $config->task_user_permission = $request->post('task_user_permission', [])->password();
                    $secret = $request->post('cron_secret')->topic();
                    if (strlen($secret) < 6) {
                        // รหัสลับสำหรับเรียก cron สั้นเกินไป
                        $ret['ret_cron_secret'] = 'this';
                    } else {
                        $config->cron_secret = $secret;
                    }
                    if (empty($ret)) {
                        // save config
                        if (Config::save($config, ROOT_PATH.'settings/config.php')) {
                            // log
                            \Index\Log\Model::add(0, 'tasks', 'Save', '{LNG_Task settings}', $login['id']);
                            // คืนค่า
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = 'reload';
                            // เคลียร์
                            $request->removeToken();
                        } else {
                            // ไม่สามารถบันทึก config ได้
                            $ret['alert'] = Language::replace('File %s cannot be created or is read-only.', 'settings/config.php');
                        }
                    }
                } catch (\Kotchasan\InputItemException $e) {
                    $ret['alert'] = $e->getMessage();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}