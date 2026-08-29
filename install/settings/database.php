<?php
/* settings/database.php */

return array(
    'mysql' => array(
        'dbdriver' => 'mysql',
        'username' => 'root',
        'password' => '',
        'dbname' => 'ewms',
        'prefix' => 'app'
    ),
    'tables' => array(
        'category' => 'category',
        'tasks' => 'tasks',
        'task_notifications' => 'task_notifications',
        'language' => 'language',
        'logs' => 'logs',
        'user' => 'user',
        'user_meta' => 'user_meta'
    )
);