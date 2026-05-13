<?php
define('ADMIN_PATH', 'admin-x7k9m');
define('SESSION_TIMEOUT', 3600);
define('DATA_DIR', __DIR__ . '/../data');
define('SUBMISSIONS_DIR', DATA_DIR . '/submissions');
define('LAWYERS_FILE', DATA_DIR . '/lawyers.json');
define('UPLOAD_DIR', __DIR__ . '/../wp-content/uploads/lawyers');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('SITE_URL', '');

define('DB_HOST', '68.178.239.252');
define('DB_NAME', 'bergpcc');
define('DB_USER', 'Klinevargas');
define('DB_PASSWORD', 'a&oxspTw3G903S;[');
define('DB_CHARSET', 'utf8mb4');

define('ADMIN_USERNAME', 'bergpc_admin');
define('ADMIN_FALLBACK_PASSWORD', 'admin123');

define('FORM_CONFIGS', [
    '2528' => ['name' => 'Crypto Fraud & Recovery', 'honeypot' => 'wpforms[fields][3]'],
    '4147' => ['name' => 'Meta Crypto Scam Ads Investigation', 'honeypot' => 'wpforms[fields][3]'],
    '3952' => ['name' => 'Coinbase Data Breach', 'honeypot' => 'wpforms[fields][3]'],
    '4296' => ['name' => 'Business Litigation', 'honeypot' => 'wpforms[fields][1]'],
    '4002' => ['name' => 'Crypto Business Transactions', 'honeypot' => 'wpforms[fields][1]'],
    '4275' => ['name' => 'Crypto Litigation', 'honeypot' => 'wpforms[fields][1]'],
    '3600' => ['name' => 'vCard Disclaimer', 'honeypot' => 'wpforms[fields][1]'],
    '3778' => ['name' => 'Lawyer List Disclaimer', 'honeypot' => 'wpforms[fields][1]'],
]);

define('CSRF_TOKEN_LIFETIME', 3600);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
