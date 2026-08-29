<?php
/**
 * @filesource modules/tasks/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Index;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     * @param array $login
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params, $login)
    {
        $where = [];
        if ($login['status'] != 1 && !Login::checkPermission($login, 'can_manage_tasks')) {
            // สมาชิก ดูได้เฉพาะงานของตัวเอง
            $where[] = ['A.user_id', $login['id']];
        }
        if (!empty($params['status']) && in_array($params['status'], ['pending', 'done', 'overdue'])) {
            $where[] = ['A.status', $params['status']];
        }
        if (!empty($params['user_id'])) {
            $where[] = ['A.user_id', $params['user_id']];
        }
        return static::createQuery()
            ->from('tasks A')
            ->join('user U', 'LEFT', ['U.id', 'A.user_id'])
            ->select('A.id', 'A.user_id', 'A.title', 'A.deadline', 'A.status', 'A.priority', 'U.name assignee')
            ->where($where);
    }

    /**
     * รายชื่อสมาชิก สำหรับสร้างตัวกรองผู้รับผิดชอบ
     *
     * @return array
     */
    public static function getUsers()
    {
        $result = [];
        foreach (static::createQuery()
            ->from('user')
            ->select('id', 'name')
            ->order('id ASC')
            ->toArray()
            ->execute() as $item) {
            $result[$item['id']] = $item['name'] == '' ? 'ID : '.$item['id'] : $item['name'];
        }
        return $result;
    }

    /**
     * รับค่าจาก action (index.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = [];
        // session, referer, member
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::checkPermission($login, 'can_view_tasks')) {
                // ค่าที่ส่งมา
                $action = $request->post('action')->toString();
                $id = $request->post('id')->filter('0-9,');
                if (preg_match_all('/,?([0-9]+),?/', $id, $match)) {
                    // สิทธิ์ในการจัดการงาน
                    $can_manage = $login['status'] == 1 || Login::checkPermission($login, 'can_manage_tasks');
                    $can_delete = $can_manage || Login::checkPermission($login, 'can_delete_tasks');
                    if ($action == 'delete') {
                        // ลบ (1 รายการหรือหลายรายการ)
                        $where = [['id', $match[1]]];
                        if (!$can_delete) {
                            // ลบเฉพาะงานของตัวเอง
                            $where[] = ['user_id', $login['id']];
                        }
                        $this->db()->delete($this->getTableName('tasks'), $where, 0);
                        $ret['location'] = 'reload';
                    } elseif ($action == 'done') {
                        // เครื่องหมายทำงานเสร็จแล้ว (1 รายการ)
                        $id = (int) $match[1][0];
                        if ($id > 0) {
                            // อ่านรายการที่เลือก
                            $result = static::createQuery()
                                ->from('tasks A')
                                ->where(['A.id', $id])
                                ->first('A.id', 'A.user_id');
                            if ($result && ($can_manage || $result->user_id == $login['id'])) {
                                $this->db()->update($this->getTableName('tasks'), $id, [
                                    'status' => 'done',
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                                $ret['location'] = 'reload';
                            } else {
                                // ไม่มีสิทธิ์แก้ไข
                                $ret['alert'] = Language::get('Can not be performed this request');
                            }
                        }
                    }
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