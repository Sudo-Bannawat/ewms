<?php
/**
 * @filesource modules/tasks/controllers/home.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Home;

use Kotchasan\Http\Request;

/**
 * Controller สำหรับการแสดงผลหน้า Home
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นสร้าง card
     *
     * @param Request               $request
     * @param \Kotchasan\Collection $card
     * @param array                 $login
     */
    public static function addCard(Request $request, $card, $login)
    {
        if ($login) {
            $count = \Tasks\Home\Model::getPending($login);
            if ($count > 0) {
                \Index\Home\Controller::renderCard($card, 'icon-calendar', '{LNG_Pending task}', number_format($count), '{LNG_Task}', 'index.php?module=tasks');
            }
            $overdue = \Tasks\Home\Model::getOverdue($login);
            if ($overdue > 0) {
                \Index\Home\Controller::renderCard($card, 'icon-warning', '{LNG_Overdue task}', number_format($overdue), '{LNG_Task}', 'index.php?module=tasks&amp;status=overdue');
            }
        }
    }
}