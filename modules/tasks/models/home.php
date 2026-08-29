<?php
/**
 * @filesource modules/tasks/models/home.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Home;

use Kotchasan\Database\Sql;

/**
 * โมเดลสำหรับอ่านข้อมูลแสดงในหน้า Home
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * จำนวนงานที่ยังไม่เสร็จ (pending และ overdue)
     *
     * @param array $login
     *
     * @return int
     */
    public static function getPending($login)
    {
        $where = [['A.status', '!=', 'done']];
        if ($login['status'] != 1) {
            $where[] = ['A.user_id', $login['id']];
        }
        $search = static::createQuery()
            ->from('tasks A')
            ->where($where)
            ->first(Sql::COUNT('A.id', 'count'));
        return $search ? (int) $search->count : 0;
    }

    /**
     * จำนวนงานที่เลยกำหนดส่ง
     *
     * @param array $login
     *
     * @return int
     */
    public static function getOverdue($login)
    {
        $where = [['A.status', 'overdue']];
        if ($login['status'] != 1) {
            $where[] = ['A.user_id', $login['id']];
        }
        $search = static::createQuery()
            ->from('tasks A')
            ->where($where)
            ->first(Sql::COUNT('A.id', 'count'));
        return $search ? (int) $search->count : 0;
    }
}