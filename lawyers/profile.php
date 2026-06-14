<?php
/**
 * Dynamic Lawyer Profile Page
 * 
 * This file handles lawyer profile pages dynamically without requiring
 * static HTML files for each lawyer. It reads lawyer data from the database
 * and renders the profile using the same template structure.
 */

// Get the slug from URL parameter
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    // No slug provided, redirect to lawyers list
    header('Location: ../');
    exit;
}

// Validate slug format (alphanumeric and hyphens only)
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    http_response_code(404);
    include __DIR__ . '/../index.htm';
    exit;
}

// Load database connection
$configPath = __DIR__ . '/../admin/config.php';
$dbPath = __DIR__ . '/../admin/db.php';

if (!file_exists($configPath) || !file_exists($dbPath)) {
    http_response_code(500);
    die('Configuration files not found.');
}

require_once $configPath;
require_once $dbPath;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM lawyers WHERE slug = ?");
    $stmt->execute([$slug]);
    $lawyer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $lawyer = false;
}

// Lawyer not found in database
if (!$lawyer) {
    http_response_code(404);
    include __DIR__ . '/../index.htm';
    exit;
}

// Generate dynamic values
$now = date('c');
$publishedDate = $lawyer['created_at'] ?? $now;
$modifiedDate = $lawyer['updated_at'] ?? $now;

// Generate description from bio (first 160 chars, break at word boundary)
$description = substr($lawyer['bio'], 0, 160);
if (strlen($lawyer['bio']) > 160) {
    $description = substr($description, 0, strrpos($description, ' ')) . '...';
}

// Convert bio to HTML paragraphs
$bioHtml = $lawyer['bio'];

// Normalize image path for absolute URLs
$imageAbsUrl = 'https://bergppc.com/' . ltrim($lawyer['image'], '../../');

// Generate JSON-LD schema (matching static page structure: WebPage, ImageObject, BreadcrumbList, WebSite, Organization)
$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            '@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/',
            'url' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/',
            'name' => $lawyer['name'] . ' | ' . ($lawyer['title'] ?? 'Attorney') . ' | Berg PC',
            'isPartOf' => ['@id' => 'https://bergppc.com/#website'],
            'primaryImageOfPage' => ['@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#primaryimage'],
            'image' => ['@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#primaryimage'],
            'thumbnailUrl' => $imageAbsUrl,
            'datePublished' => $publishedDate,
            'dateModified' => $modifiedDate,
            'description' => $description,
            'breadcrumb' => ['@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#breadcrumb'],
            'inLanguage' => 'en-US',
            'potentialAction' => [['@type' => 'ReadAction', 'target' => ['https://bergppc.com/lawyers/' . $lawyer['slug'] . '/']]]
        ],
        [
            '@type' => 'ImageObject',
            'inLanguage' => 'en-US',
            '@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#primaryimage',
            'url' => $imageAbsUrl,
            'contentUrl' => $imageAbsUrl,
            'width' => 2560,
            'height' => 1706
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://bergppc.com/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Lawyers', 'item' => 'https://bergppc.com/lawyers/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $lawyer['name']]
            ]
        ],
        [
            '@type' => 'WebSite',
            '@id' => 'https://bergppc.com/#website',
            'url' => 'https://bergppc.com/',
            'name' => 'Berg PC',
            'description' => 'Trial & Business Lawyers',
            'publisher' => ['@id' => 'https://bergppc.com/#organization'],
            'potentialAction' => [
                [
                    '@type' => 'SearchAction',
                    'target' => ['@type' => 'EntryPoint', 'urlTemplate' => 'https://bergppc.com/?s={search_term_string}'],
                    'query-input' => ['@type' => 'PropertyValueSpecification', 'valueRequired' => true, 'valueName' => 'search_term_string']
                ]
            ],
            'inLanguage' => 'en-US'
        ],
        [
            '@type' => 'Organization',
            '@id' => 'https://bergppc.com/#organization',
            'name' => 'Berg PC',
            'url' => 'https://bergppc.com/',
            'logo' => [
                '@type' => 'ImageObject',
                'inLanguage' => 'en-US',
                '@id' => 'https://bergppc.com/#/schema/logo/image/',
                'url' => 'https://bergppc.com/wp-content/uploads/2024/12/cropped-Berg-PC-White-2.png',
                'contentUrl' => 'https://bergppc.com/wp-content/uploads/2024/12/cropped-Berg-PC-White-2.png',
                'width' => 1272,
                'height' => 498,
                'caption' => 'Berg PC'
            ],
            'image' => ['@id' => 'https://bergppc.com/#/schema/logo/image/']
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Load template
$templatePath = __DIR__ . '/../admin/templates/lawyer_profile.tpl';
if (!file_exists($templatePath)) {
    http_response_code(500);
    die('Template file not found.');
}

$template = file_get_contents($templatePath);

// URL-encode the full page title for JS usage (e.g. "John Doe | Attorney | Berg PC")
$lawyerNameEncoded = rawurlencode($lawyer['name'] . ' | ' . ($lawyer['title'] ?? 'Attorney') . ' | Berg PC');

// Replace placeholders
$replacements = [
    '{{LAWYER_NAME}}' => htmlspecialchars($lawyer['name']),
    '{{LAWYER_NAME_ENCODED}}' => $lawyerNameEncoded,
    '{{LAWYER_TITLE}}' => htmlspecialchars($lawyer['title'] ?? 'Attorney'),
    '{{LAWYER_SLUG}}' => htmlspecialchars($lawyer['slug']),
    '{{LAWYER_IMAGE_PATH}}' => htmlspecialchars($lawyer['image']),
    '{{LAWYER_BIO}}' => $bioHtml,
    '{{LAWYER_DESCRIPTION}}' => htmlspecialchars($description),
    '{{LAWYER_DATE_PUBLISHED}}' => $publishedDate,
    '{{LAWYER_DATE_MODIFIED}}' => $modifiedDate,
    '{{CURRENT_YEAR}}' => date('Y'),
    '{{LAWYER_JSON_LD}}' => $jsonLd,
];

$html = str_replace(array_keys($replacements), array_values($replacements), $template);

// Fix relative paths - template uses ../../wp-content/ but profile.php is in /lawyers/ directory
// so we need to change to ../wp-content/ (one level up instead of two)
$html = str_replace('../../wp-content/', '../wp-content/', $html);
$html = str_replace('../../wp-includes/', '../wp-includes/', $html);
$html = str_replace('../../feed/', '../feed/', $html);
$html = str_replace('../../comments/', '../comments/', $html);
$html = str_replace('../../wp-json/', '../wp-json/', $html);
$html = str_replace('../../signals/', '../signals/', $html);
$html = str_replace('../../index.htm', '../', $html);
$html = str_replace('../../lawyers/', './', $html);
$html = str_replace('../../practice-areas/', '../practice-areas/', $html);

// Output the HTML
echo $html;
