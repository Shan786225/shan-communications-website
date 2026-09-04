<?php
declare(strict_types=1);

if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit;
}

return [
    <<<'SQL'
CREATE TABLE IF NOT EXISTS shan_submissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id CHAR(36) NOT NULL,
    form_type ENUM('business', 'job') NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(60) NULL,
    topic VARCHAR(160) NULL,
    role_name VARCHAR(180) NULL,
    experience VARCHAR(100) NULL,
    availability VARCHAR(160) NULL,
    message TEXT NOT NULL,
    resume_url TEXT NULL,
    resume_file_name VARCHAR(255) NULL,
    resume_stored_name VARCHAR(255) NULL,
    resume_mime VARCHAR(120) NULL,
    resume_size INT UNSIGNED NULL,
    email_status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    sheets_status ENUM('pending', 'synced', 'failed', 'disabled') NOT NULL DEFAULT 'pending',
    workflow_status ENUM('new', 'in_progress', 'contacted', 'closed') NOT NULL DEFAULT 'new',
    admin_notes TEXT NULL,
    source_url VARCHAR(500) NULL,
    ip_hash CHAR(64) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_public_id (public_id),
    KEY idx_form_created (form_type, created_at),
    KEY idx_workflow_created (workflow_status, created_at),
    KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
];
