<?php
/**
 * @filesource modules/tasks/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=tasks-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @param object $config
     *
     * @return string
     */
    public function render($config)
    {
        $form = Html::create('form', [
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/tasks/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-config',
            'title' => '{LNG_Task notification}'
        ]);
        // task_reminder_days
        $fieldset->add('number', [
            'id' => 'task_reminder_days',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'item',
            'label' => '{LNG_Remind before (days)}',
            'comment' => '{LNG_Start notifying when the task is due within this number of days}',
            'min' => 1,
            'max' => 365,
            'value' => isset($config->task_reminder_days) ? $config->task_reminder_days : 1
        ]);
        // แจ้งเตือนซ้ำ
        $fieldset->add('checkboxgroups', [
            'id' => 'task_reminder_dates',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Reminder repeat}',
            'comment' => '{LNG_Remind again every number of days until the task is done}',
            'options' => [
                1 => '1 {LNG_day}',
                3 => '3 {LNG_day}',
                7 => '7 {LNG_day}',
                14 => '14 {LNG_day}'
            ],
            'value' => empty($config->task_reminder_dates) ? [1] : $config->task_reminder_dates
        ]);
        // cron_secret
        $fieldset->add('text', [
            'id' => 'cron_secret',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'item',
            'label' => 'cron_secret',
            'comment' => '{LNG_Secret key for calling the cron endpoint} ({LNG_Example}: index.php/cron_tasks.php?secret=XXXX)',
            'value' => isset($config->cron_secret) ? $config->cron_secret : 'CHANGE_THIS_SECRET_KEY'
        ]);
        // task_user_permission
        $fieldset->add('checkboxgroups', [
            'id' => 'task_user_permission',
            'labelClass' => 'g-input icon-list',
            'itemClass' => 'item',
            'label' => '{LNG_Permission}',
            'comment' => '{LNG_Default license When member registration}',
            'options' => \Tasks\Init\Controller::updatePermissions([]),
            'value' => isset($config->task_user_permission) ? $config->task_user_permission : []
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ]);
        // คืนค่า HTML
        return $form->render();
    }
}