<?php
/**
 * @filesource modules/tasks/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Tasks\Index;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=tasks
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * แสดงรายการงานค้าง
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        // ค่าที่ส่งมา
        $params = [
            'status' => $request->request('status')->topic(),
            'user_id' => $request->request('user_id')->toInt()
        ];
        // ผู้จัดการ/แอดมิน สามารถเลือกดูงานของทุกคนได้
        $can_manage = $login['status'] == 1 || \Gcms\Login::checkPermission($login, 'can_manage_tasks');
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Tasks\Index\Model::toDataTable($params, $login),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('tasksIndex_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'deadline ASC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'user_id'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['title', 'assignee'],
            /* ตัวเลือกการแสดงผลที่ส่วนหัว */
            'filters' => [
                [
                    'name' => 'status',
                    'text' => '{LNG_Status}',
                    'options' => Language::get('TASK_STATUSES'),
                    'value' => $params['status']
                ]
            ],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'actions' => [
                [
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => [
                        'delete' => '{LNG_Delete}'
                    ]
                ]
            ],
            'action' => 'index.php/tasks/model/index/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'title' => [
                    'text' => '{LNG_Task}'
                ],
                'assignee' => [
                    'text' => '{LNG_Assignee}',
                    'class' => 'center'
                ],
                'deadline' => [
                    'text' => '{LNG_Deadline}',
                    'class' => 'center'
                ],
                'priority' => [
                    'text' => '{LNG_Priority}',
                    'class' => 'center'
                ],
                'status' => [
                    'text' => '{LNG_Status}',
                    'class' => 'center'
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'title' => [
                    'class' => 'topic'
                ],
                'assignee' => [
                    'class' => 'center nowrap'
                ],
                'deadline' => [
                    'class' => 'center nowrap'
                ],
                'priority' => [
                    'class' => 'center'
                ],
                'status' => [
                    'class' => 'center'
                ]
            ],
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => [$this, 'onCreateButton'],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'edit' => [
                    'class' => 'icon-edit button green',
                    'id' => ':id',
                    'text' => '{LNG_Edit}'
                ],
                'done' => [
                    'class' => 'icon-check button orange',
                    'id' => ':id',
                    'text' => '{LNG_Mark as done}'
                ],
                'delete' => [
                    'class' => 'icon-delete button red',
                    'id' => ':id',
                    'text' => '{LNG_Delete}'
                ]
            ]
        ]);
        if ($can_manage) {
            // เลือกผู้รับผิดชอบ
            $table->filters[] = [
                'name' => 'user_id',
                'text' => '{LNG_Assignee}',
                'options' => [0 => '{LNG_all}'] + \Tasks\Index\Model::getUsers(),
                'value' => $params['user_id']
            ];
        }
        // save cookie
        setcookie('tasksIndex_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML
        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array $item
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        $statuses = Language::get('TASK_STATUSES');
        $priorities = Language::get('TASK_PRIORITIES');
        $item['title'] = '<span class=second_lines>'.$item['title'].'</span>';
        $item['assignee'] = empty($item['assignee']) ? '-' : $item['assignee'];
        $item['deadline'] = Date::format($item['deadline'], 'd M Y H:i');
        $item['priority'] = '<span class="term-'.$item['priority'].'">'.(isset($priorities[$item['priority']]) ? $priorities[$item['priority']] : $item['priority']).'</span>';
        $item['status'] = '<span class="term-'.$item['status'].'">'.(isset($statuses[$item['status']]) ? $statuses[$item['status']] : $item['status']).'</span>';
        return $item;
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่
     *
     * @param array $item
     *
     * @return array
     */
    public function onCreateButton($btn, $attributes, $item)
    {
        $login = \Gcms\Login::isMember();
        $can_manage = $login['status'] == 1 || \Gcms\Login::checkPermission($login, 'can_manage_tasks');
        if ($btn == 'edit') {
            // แก้ไขเฉพาะงานของตัวเอง หรือ ผู้จัดการ
            if (!$can_manage && $item['user_id'] != $login['id']) {
                unset($attributes);
            } else {
                $attributes['href'] = 'index.php?module=tasks-write&amp;id='.$item['id'];
            }
        } elseif ($btn == 'done') {
            // เครื่องหมายทำงานเสร็จแล้ว เฉพาะงานที่ยังไม่เสร็จ
            if ($item['status'] == 'done') {
                unset($attributes);
            } elseif (!$can_manage && $item['user_id'] != $login['id']) {
                unset($attributes);
            } else {
                $attributes['id'] = 'done_'.$item['id'];
            }
        } elseif ($btn == 'delete') {
            // ลบได้เฉพาะผู้จัดการ หรือ มีสิทธิ์ลบ
            if ($can_manage) {
                $attributes['id'] = 'delete_'.$item['id'];
            } elseif (\Gcms\Login::checkPermission($login, 'can_delete_tasks')) {
                $attributes['id'] = 'delete_'.$item['id'];
            } else {
                unset($attributes);
            }
        }
        return $attributes;
    }
}