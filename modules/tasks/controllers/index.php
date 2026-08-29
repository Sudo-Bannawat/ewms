<?php
/**
 * @filesource modules/tasks/controllers/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Index;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * แสดงรายการงานค้าง
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::trans('{LNG_List of} {LNG_Task}');
        // เลือกเมนู
        $this->menu = 'tasks';
        // สมาชิก
        $login = Login::isMember();
        // สามารถดูงานได้
        if (Login::checkPermission($login, 'can_view_tasks')) {
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('nav', [
                'class' => 'breadcrumbs'
            ]);
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-calendar">{LNG_Task}</span></li>');
            $ul->appendChild('<li><span>{LNG_List of}</span></li>');
            $section->add('header', [
                'innerHTML' => '<h2 class="icon-calendar">'.$this->title.'</h2>'
            ]);
            $div = $section->add('div', [
                'class' => 'content_bg'
            ]);
            // ตารางรายการงาน
            $div->appendChild(\Tasks\Index\View::create()->render($request, $login));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}