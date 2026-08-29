<?php
/**
 * @filesource modules/tasks/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Init;

/**
 * Init Module
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * รายการ permission ของโมดูล
     *
     * @param array $permissions
     *
     * @return array
     */
    public static function updatePermissions($permissions)
    {
        $permissions['can_manage_tasks'] = '{LNG_Can manage all} {LNG_Task}';
        $permissions['can_add_tasks'] = '{LNG_Can add} {LNG_Task}';
        $permissions['can_delete_tasks'] = '{LNG_Can delete} {LNG_Task}';
        $permissions['can_view_tasks'] = '{LNG_Can view} {LNG_Task}';
        return $permissions;
    }

    /**
     * สมัครสมาชิก ใช้ค่าเริ่มต้นของโมดูล
     *
     * @param array $permissions
     * @param array $user
     *
     * @return array
     */
    public static function newRegister($permissions, $user)
    {
        return empty(self::$cfg->task_user_permission) ? $permissions : array_merge($permissions, self::$cfg->task_user_permission);
    }
}