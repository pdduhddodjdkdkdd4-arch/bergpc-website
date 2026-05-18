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
    '2528' => [
        'name' => 'Crypto Fraud & Recovery',
        'honeypot' => 'wpforms[fields][3]',
        'fields' => [
            '27' => 'Disclaimer Acceptance',
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '25' => 'Phone',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State', 'country' => 'Country']],
            '17' => 'Company',
            '2' => 'Case Details',
            '18' => 'Cryptocurrency Type',
            '19' => 'Amount Lost',
            '30' => 'How Did You Hear About Us?',
            '20' => 'Date of Loss',
            '21' => 'Transaction ID',
            '22' => 'Additional Information',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '4147' => [
        'name' => 'Meta Crypto Scam Ads Investigation',
        'honeypot' => 'wpforms[fields][3]',
        'fields' => [
            '27' => 'Disclaimer Acceptance',
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '25' => 'Phone',
            '3' => 'Honeypot',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State']],
            '17' => 'Company',
            '2' => 'Case Details',
            '29' => 'Platform',
            '18' => 'Cryptocurrency Type',
            '30' => 'How Did You Hear About Us?',
            '31' => 'Additional Information',
            '19' => 'Amount Lost',
            '32' => 'Date of Incident 1',
            '33' => 'Date of Incident 2',
            '34' => 'Date of Incident 3',
            '22' => 'Additional Details',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '3952' => [
        'name' => 'Coinbase Data Breach',
        'honeypot' => 'wpforms[fields][3]',
        'fields' => [
            '27' => 'Disclaimer Acceptance',
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '25' => 'Phone',
            '3' => 'Honeypot',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State']],
            '17' => 'Company',
            '2' => 'Case Details',
            '29' => 'Platform',
            '18' => 'Cryptocurrency Type',
            '30' => 'How Did You Hear About Us?',
            '31' => 'Additional Information',
            '19' => 'Amount Lost',
            '32' => 'Date of Incident 1',
            '33' => 'Date of Incident 2',
            '34' => 'Date of Incident 3',
            '22' => 'Additional Details',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '4296' => [
        'name' => 'Business Litigation',
        'honeypot' => 'wpforms[fields][99]',
        'fields' => [
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '25' => 'Phone',
            '2' => 'Case Details',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State']],
            '22' => 'Additional Information',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '4002' => [
        'name' => 'Crypto Business Transactions',
        'honeypot' => 'wpforms[fields][99]',
        'fields' => [
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '2' => 'Transaction Details',
            '1' => 'Email',
            '25' => 'Phone',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State']],
            '22' => 'Additional Information',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '4275' => [
        'name' => 'Crypto Litigation',
        'honeypot' => 'wpforms[fields][99]',
        'fields' => [
            '13' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '25' => 'Phone',
            '26' => ['label' => 'Address', 'subfields' => ['address1' => 'Street Address', 'city' => 'City', 'state' => 'State']],
            '2' => 'Case Details',
            '22' => 'Additional Information',
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '3600' => [
        'name' => 'vCard Disclaimer',
        'honeypot' => 'wpforms[fields][99]',
        'fields' => [
            '1' => 'Email',
            '19' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '9' => 'Disclaimer Agreement'
        ]
    ],
    '3778' => [
        'name' => 'Lawyer List Disclaimer',
        'honeypot' => 'wpforms[fields][99]',
        'fields' => [
            '19' => ['label' => 'Name', 'subfields' => ['first' => 'First Name', 'last' => 'Last Name']],
            '1' => 'Email',
            '9' => 'Disclaimer Agreement'
        ]
    ]
]);

define('CSRF_TOKEN_LIFETIME', 3600);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

require_once __DIR__ . '/db.php';
