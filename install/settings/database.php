<?php
/* settings/database.php */

return array(
    'mysql' => array(
        'dbdriver' => 'mysql',
        'username' => 'root',
        'password' => '',
        'dbname' => 'edms',
        'prefix' => 'app'
    ),
    'tables' => array(
        'category' => 'category',
        'edms' => 'edms',
        'edms_files' => 'edms_files',
        'edms_download' => 'edms_download',
        'edms_meta' => 'edms_meta',
        'language' => 'language',
        'logs' => 'logs',
        'user' => 'user',
        'user_meta' => 'user_meta'
    )
);
