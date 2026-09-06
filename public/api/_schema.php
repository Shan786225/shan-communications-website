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
    <<<'SQL'
CREATE TABLE IF NOT EXISTS shan_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'custom',
    permissions TEXT NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    session_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    "CREATE TABLE IF NOT EXISTS shan_access_meta (name VARCHAR(80) PRIMARY KEY) ENGINE=InnoDB",
    <<<'SQL'
CREATE TABLE IF NOT EXISTS shan_submission_trash (
    submission_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    deleted_by BIGINT UNSIGNED NOT NULL,
    deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
SQL,
    <<<'SQL'
CREATE TABLE IF NOT EXISTS shan_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(80) NOT NULL,
    target VARCHAR(190) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    <<<'SQL'
CREATE TABLE IF NOT EXISTS shan_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    nonce CHAR(48) NOT NULL,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_message (sender_id, nonce),
    KEY idx_inbox (recipient_id, read_at, id),
    KEY idx_thread (sender_id, recipient_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
];
