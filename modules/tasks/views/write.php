<?php
/**
 * @filesource modules/tasks/views/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Write;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=tasks-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มสร้าง/แก้ไข งานค้าง
     *
     * @param object $index
     * @param array  $login
     *
     * @return string
     */
    public function render($index, $login)
    {
        // ผู้จัดการ/แอดมิน
        $can_manage = $login['status'] == 1 || Login::checkPermission($login, 'can_manage_tasks');
        $form = Html::create('form', [
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/tasks/model/write/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-write',
            'title' => '{LNG_Details of} {LNG_Task}'
        ]);
        if ($can_manage) {
            // ผู้รับผิดชอบ
            $fieldset->add('select', [
                'id' => 'task_user_id',
                'labelClass' => 'g-input icon-customer',
                'itemClass' => 'item',
                'label' => '{LNG_Assignee}',
                'options' => [0 => '{LNG_Unassigned}'] + \Tasks\Index\Model::getUsers(),
                'value' => (int) $index->user_id
            ]);
        }
        // title
        $fieldset->add('text', [
            'id' => 'task_title',
            'labelClass' => 'g-input icon-edit',
            'itemClass' => 'item',
            'label' => '{LNG_Task}',
            'maxlength' => 255,
            'value' => $index->title
        ]);
        // description
        $fieldset->add('textarea', [
            'id' => 'task_description',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Description}',
            'comment' => '{LNG_Note or additional notes}',
            'rows' => 5,
            'value' => $index->description
        ]);
        // deadline
        $fieldset->add('datetime', [
            'id' => 'task_deadline',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'item',
            'label' => '{LNG_Deadline}',
            'comment' => '{LNG_Enter the date and time the task must be completed}',
            'value' => empty($index->deadline) ? date('Y-m-d\TH:i', strtotime('+1 day')) : str_replace(' ', 'T', $index->deadline)
        ]);
        $groups = $fieldset->add('groups');
        // priority
        $groups->add('select', [
            'id' => 'task_priority',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'width50',
            'label' => '{LNG_Priority}',
            'options' => Language::get('TASK_PRIORITIES'),
            'value' => $index->priority
        ]);
        // status
        $groups->add('select', [
            'id' => 'task_status',
            'labelClass' => 'g-input icon-star0',
            'itemClass' => 'width50',
            'label' => '{LNG_Status}',
            'options' => Language::get('TASK_STATUSES'),
            'value' => $index->status
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button ok large icon-save',
            'value' => '{LNG_Save}'
        ]);
        // id
        $fieldset->add('hidden', [
            'id' => 'id',
            'value' => $index->id
        ]);
        // คืนค่า HTML
        return $form->render();
    }
}