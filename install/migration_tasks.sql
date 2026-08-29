-- Migration for Pending Tasks System (ระบบงานค้าง)
-- Add tasks table for deadline tracking + telegram alerts
-- สำหรับอัปเกรดจากระบบเดิมที่มีฐานข้อมูลติดตั้งอยู่แล้ว
-- เปลี่ยน {prefix} เป็นค่า prefix ของแต่ละระบบ (เช่น app) ก่อนสั่ง execute

-- ========== Tasks Table ==========
CREATE TABLE IF NOT EXISTS `{prefix}_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `deadline` datetime NOT NULL,
  `status` enum('pending','done','overdue') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `deadline` (`deadline`),
  KEY `status` (`status`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{prefix}_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ========== Task Notifications Log ==========
CREATE TABLE IF NOT EXISTS `{prefix}_task_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` enum('telegram','email','sms') NOT NULL DEFAULT 'telegram',
  `sent_at` datetime DEFAULT NULL,
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `response` text,
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `sent_at` (`sent_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `{prefix}_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `{prefix}_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ========== Update Users Table: Add telegram_chat_id ==========
ALTER TABLE `{prefix}_user`
  ADD COLUMN `telegram_chat_id` BIGINT(20) DEFAULT NULL COMMENT 'Telegram Chat ID for notifications' AFTER `telegram_id`;

-- ========== Indexes ==========
ALTER TABLE `{prefix}_tasks` ADD INDEX `idx_deadline_status` (`deadline`, `status`);
ALTER TABLE `{prefix}_task_notifications` ADD INDEX `idx_task_status` (`task_id`, `status`);