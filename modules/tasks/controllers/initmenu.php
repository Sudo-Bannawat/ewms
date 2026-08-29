<?php
/**
 * @filesource modules/tasks/controllers/initmenu.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Initmenu;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * Init Menus
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นโหลดเมนูทั้งหมด
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        // สมาชิก
        if ($login) {
            // สามารถเพิ่มงานได้
            $can_add = Login::checkPermission($login, 'can_add_tasks');
            $submenus = [
                [
                    'text' => '{LNG_List of} {LNG_Task}',
                    'url' => 'index.php?module=tasks'
                ]
            ];
            if ($can_add) {
                $submenus[] = [
                    'text' => '{LNG_Add new} {LNG_Task}',
                    'url' => 'index.php?module=tasks-write'
                ];
            }
            $menu->addTopLvlMenu('tasks', '{LNG_Task}', null, $submenus, 'member');
            // เมนูตั้งค่า
            if (Login::checkPermission($login, 'can_config')) {
                $menu->add('settings', '{LNG_Task}', null, [
                    [
                        'text' => '{LNG_Task settings}',
                        'url' => 'index.php?module=tasks-settings'
                    ]
                ], 'tasks');
            }
        }
    }
}