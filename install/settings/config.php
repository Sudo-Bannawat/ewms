<?php
/* config.php */
return [
    'version' => '6.9.0',
    'web_title' => 'ระบบงานค้าง',
    'web_description' => 'ระบบบริหารงานค้าง พร้อมแจ้งเตือนผ่าน Telegram',
    'timezone' => 'Asia/Bangkok',
    'member_status' => array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ'
    ),
    'color_status' => array(
        0 => '#259B24',
        1 => '#FF0000'
    ),
    'default_icon' => 'icon-office',
    'telegram_bot_username' => '',
    'telegram_bot_token' => '',
    'telegram_chat_id' => '',
    'task_reminder_days' => 1,
    'task_reminder_dates' => array(
        0 => 1,
        1 => 3,
        2 => 7
    ),
    'cron_secret' => 'CHANGE_THIS_SECRET_KEY'
];