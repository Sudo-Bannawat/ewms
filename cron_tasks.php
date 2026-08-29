<?php
/**
 * @filesource cron_tasks.php
 *
 * Cron endpoint for pending tasks deadline notifications.
 *
 * Call via: curl "https://domain.com/cron_tasks.php?secret=YOUR_SECRET_KEY"
 * Or add to crontab: * * * * * curl -s "https://domain.com/cron_tasks.php?secret=YOUR_SECRET_KEY" > /dev/null 2>&1
 *
 * The secret key is stored in settings/config.php (cron_secret), configurable from
 * Settings > Task settings.
 *
 * @author Bannawat
 * @since 1.0
 */

// โหลด framework
require_once __DIR__.'/load.php';

// ยังไม่ได้ติดตั้ง
if (!is_file(__DIR__.'/settings/config.php')) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

// โหลด config และเชื่อมต่อฐานข้อมูล
Kotchasan::createWebApplication('Gcms\Config');

// ตรวจสอบ cron secret
$cronSecret = isset($_GET['secret']) ? $_GET['secret'] : (isset($_POST['secret']) ? $_POST['secret'] : null);
$config = \Kotchasan\Config::load(ROOT_PATH.'settings/config.php');
$configSecret = isset($config->cron_secret) ? $config->cron_secret : '';
if ($configSecret == '' || $cronSecret === null || $cronSecret !== $configSecret) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // ตรวจสอบงานที่เลยกำหนดและแจ้งเตือนงานที่ใกล้ถึงกำหนดส่ง
    $result = \Tasks\Cron\Model::run();
    echo json_encode([
        'status' => 'success',
        'overdue_marked' => $result['overdue_marked'],
        'notifications' => $result['notifications'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}