<?php
/**
 * @filesource modules/tasks/models/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Write;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลรายการที่เลือก
     * ถ้า $id = 0 หมายถึงรายการใหม่
     *
     * @param int   $id    ID
     * @param array $login
     *
     * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
     */
    public static function get($id, $login)
    {
        if (empty($id)) {
            // ใหม่
            return (object) [
                'id' => 0,
                'user_id' => $login['id'],
                'title' => '',
                'description' => '',
                'deadline' => '',
                'status' => 'pending',
                'priority' => 'medium'
            ];
        } else {
            // แก้ไข อ่านรายการที่เลือก
            return static::createQuery()
                ->from('tasks E')
                ->where(['E.id', $id])
                ->first('E.*');
        }
    }

    /**
     * บันทึกข้อมูลที่ส่งมาจากฟอร์ม (write.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = [];
        // session, token, สมาชิก และสามารถเพิ่ม/แก้ไขงานได้
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::checkPermission($login, 'can_add_tasks')) {
                try {
                    // ค่าที่ส่งมา
                    $save = [
                        'title' => $request->post('task_title')->topic(),
                        'description' => $request->post('task_description')->textarea(),
                        'deadline' => $request->post('task_deadline')->date(true),
                        'priority' => $request->post('task_priority')->filter('a-z'),
                        'status' => $request->post('task_status')->filter('a-z')
                    ];
                    // แอดมิน/ผู้จัดการงาน สามารถเลือกผู้รับผิดชอบได้
                    $can_manage = $login['status'] == 1 || Login::checkPermission($login, 'can_manage_tasks');
                    if ($can_manage) {
                        $save['user_id'] = $request->post('task_user_id')->toInt();
                    } else {
                        $save['user_id'] = $login['id'];
                    }
                    // ตรวจสอบค่าที่ส่งมา
                    if ($save['title'] == '') {
                        // ไม่ได้กรอก ชื่องาน
                        $ret['ret_task_title'] = 'Please fill in';
                    }
                    if (empty($save['deadline']) || strtotime($save['deadline']) <= time()) {
                        // กำหนดส่งต้องเป็นวันเวลาในอนาคต
                        $ret['ret_task_deadline'] = Language::get('Please enter a future date and time');
                    }
                    if (!in_array($save['priority'], ['low', 'medium', 'high'], true)) {
                        $save['priority'] = 'medium';
                    }
                    if (!in_array($save['status'], ['pending', 'done', 'overdue'], true)) {
                        $save['status'] = 'pending';
                    }
                    if ($can_manage && $save['user_id'] == 0) {
                        // ไม่ได้เลือกผู้รับผิดชอบ
                        $ret['ret_task_user_id'] = 'Please select';
                    }
                    if (empty($ret)) {
                        // อ่านรายการที่เลือก
                        $index = self::get($request->post('id')->toInt(), $login);
                        if ($index) {
                            if ($index->id == 0) {
                                // ใหม่
                                $save['created_at'] = date('Y-m-d H:i:s');
                                $save['updated_at'] = $save['created_at'];
                                $id = $this->db()->insert($this->getTableName('tasks'), $save);
                            } else {
                                if ($login['status'] != 1 && !Login::checkPermission($login, 'can_manage_tasks') && $index->user_id != $login['id']) {
                                    // แก้ไขเฉพาะงานของตัวเอง
                                    $ret['alert'] = Language::get('Can not be performed this request');
                                } else {
                                    // แก้ไข ผู้จัดการเท่านั้นแก้ไขผู้รับผิดชอบได้
                                    if (!$can_manage) {
                                        unset($save['user_id']);
                                    }
                                    $save['updated_at'] = date('Y-m-d H:i:s');
                                    $this->db()->update($this->getTableName('tasks'), $index->id, $save);
                                    $id = $index->id;
                                }
                            }
                            if (empty($ret)) {
                                // log
                                \Index\Log\Model::add($id, 'tasks', 'Save', '{LNG_Task} ID : '.$id, $login['id']);
                                // คืนค่า
                                $ret['alert'] = Language::get('Saved successfully');
                                $ret['location'] = $request->getUri()->postBack('index.php', ['module' => 'tasks']);
                                // เคลียร์
                                $request->removeToken();
                            }
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