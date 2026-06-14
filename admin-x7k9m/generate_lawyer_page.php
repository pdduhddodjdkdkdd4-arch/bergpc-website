<?php
require_once __DIR__ . '/config.php';

function generateLawyerPage($lawyer) {
    if (!preg_match('/^[a-z0-9-]+$/', $lawyer['slug'])) {
        return false;
    }

    $templateFile = __DIR__ . '/templates/lawyer_profile.tpl';
    if (!file_exists($templateFile)) {
        return false;
    }

    $template = file_get_contents($templateFile);

    $description = substr($lawyer['bio'], 0, 160);
    if (strlen($lawyer['bio']) > 160) {
        $description = substr($description, 0, strrpos($description, ' ')) . '...';
    }

    $pageId = rand(1000, 9999);
    $wpImageId = rand(3000, 9999);
    $now = date('c');
    $publishedDate = $lawyer['created_at'] ?? $now;
    $modifiedDate = $lawyer['updated_at'] ?? $now;

    $bioHtml = $lawyer['bio'];

    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                '@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#webpage',
                'url' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/',
                'name' => $lawyer['name'] . ' | ' . $lawyer['title'] . ' | Berg PC',
                'isPartOf' => ['@id' => 'https://bergppc.com/#website'],
                'thumbnailUrl' => 'https://bergppc.com/' . ltrim($lawyer['image'], '../../'),
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
                'url' => 'https://bergppc.com/' . ltrim($lawyer['image'], '../../'),
                'contentUrl' => 'https://bergppc.com/' . ltrim($lawyer['image'], '../../'),
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => 'https://bergppc.com/lawyers/' . $lawyer['slug'] . '/#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://bergppc.com/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Team', 'item' => 'https://bergppc.com/lawyers/'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $lawyer['name']]
                ]
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $replacements = [
        '{{LAWYER_NAME}}' => $lawyer['name'],
        '{{LAWYER_TITLE}}' => $lawyer['title'],
        '{{LAWYER_SLUG}}' => $lawyer['slug'],
        '{{LAWYER_IMAGE_PATH}}' => $lawyer['image'],
        '{{LAWYER_BIO}}' => $bioHtml,
        '{{LAWYER_DESCRIPTION}}' => htmlspecialchars($description),
        '{{LAWYER_DATE_PUBLISHED}}' => $publishedDate,
        '{{LAWYER_DATE_MODIFIED}}' => $modifiedDate,
        '{{LAWYER_PAGE_ID}}' => $pageId,
        '{{LAWYER_WP_IMAGE_ID}}' => $wpImageId,
        '{{CURRENT_YEAR}}' => date('Y'),
        '{{LAWYER_JSON_LD}}' => $jsonLd,
    ];

    $html = str_replace(array_keys($replacements), array_values($replacements), $template);

    $pageDir = __DIR__ . '/../lawyers/' . $lawyer['slug'];
    if (!is_dir($pageDir)) {
        mkdir($pageDir, 0755, true);
    }

    return file_put_contents($pageDir . '/index.htm', $html) !== false;
}
