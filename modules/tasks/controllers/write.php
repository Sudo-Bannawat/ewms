<?php
/**
 * @filesource modules/tasks/controllers/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Write;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ฟอร์มสร้าง/แก้ไข งานค้าง
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Task');
        // เลือกเมนู
        $this->menu = 'tasks';
        // สมาชิก
        $login = Login::isMember();
        // สามารถเพิ่ม/แก้ไขงานได้
        if (Login::checkPermission($login, 'can_add_tasks')) {
            // ตรวจสอบรายการที่เลือก
            $index = \Tasks\Write\Model::get($request->request('id')->toInt(), $login);
            if ($index && $login['status'] != 1 && !Login::checkPermission($login, 'can_manage_tasks') && $index->user_id != $login['id']) {
                $index = null;
            }
            if ($index) {
                // ข้อความ title bar
                $title = Language::get(empty($index->id) ? 'Add new' : 'Edit');
                $this->title = $title.' '.$this->title;
                // แสดงผล
                $section = Html::create('section');
                // breadcrumbs
                $breadcrumbs = $section->add('nav', [
                    'class' => 'breadcrumbs'
                ]);
                $ul = $breadcrumbs->add('ul');
                $ul->appendChild('<li><span class="icon-calendar">{LNG_Task}</span></li>');
                $ul->appendChild('<li><a href="{BACKURL?module=tasks}">{LNG_List of}</a></li>');
                $ul->appendChild('<li><span>'.$title.'</span></li>');
                $section->add('header', [
                    'innerHTML' => '<h2 class="icon-write">'.$this->title.'</h2>'
                ]);
                $div = $section->add('div', [
                    'class' => 'content_bg'
                ]);
                // แสดงฟอร์ม
                $div->appendChild(\Tasks\Write\View::create()->render($index, $login));
                // คืนค่า HTML
                return $section->render();
            }
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}