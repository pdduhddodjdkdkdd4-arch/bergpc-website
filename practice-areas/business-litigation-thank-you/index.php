<?php
/**
 * Thank You Page with Form Submission Handler
 * This page handles form submissions and displays appropriate content based on status
 */

// Start output buffering to handle headers properly
ob_start();

// Define paths relative to project root
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/admin/config.php';
require_once $projectRoot . '/admin/db.php';

// Initialize status variables
$submissionStatus = 'none'; // 'none', 'loading', 'success', 'error'
$errorMessage = '';
$formId = '';
$formData = [];

// Check if this is a form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionStatus = 'loading';
    
    // Get form ID
    $formId = $_POST['form_id'] ?? '';
    
    // Validate form ID
    if (empty($formId) || !isset(FORM_CONFIGS[$formId])) {
        $submissionStatus = 'error';
        $errorMessage = 'Invalid form submission. Please try again.';
    } else {
        $formConfig = FORM_CONFIGS[$formId];
        
        // Check honeypot for spam
        $honeypotConfig = $formConfig['honeypot'];
        $honeypotFieldId = $honeypotConfig;
        if (preg_match('/\[(\d+)\]$/', $honeypotConfig, $matches)) {
            $honeypotFieldId = $matches[1];
        }
        
        $honeypotValue = '';
        if (isset($_POST['wpforms']) && isset($_POST['wpforms']['fields']) && isset($_POST['wpforms']['fields'][$honeypotFieldId])) {
            $honeypotValue = $_POST['wpforms']['fields'][$honeypotFieldId];
        } elseif (isset($_POST[$honeypotConfig])) {
            $honeypotValue = $_POST[$honeypotConfig];
        }
        
        // If honeypot is filled, treat as spam but show success
        if (!empty($honeypotValue)) {
            $submissionStatus = 'success';
        } else {
            // Flatten array data
            function flattenArray($array, $prefix = '') {
                $result = [];
                foreach ($array as $key => $value) {
                    $newKey = $prefix ? $prefix . '[' . $key . ']' : $key;
                    if (is_array($value)) {
                        $result = array_merge($result, flattenArray($value, $newKey));
                    } else {
                        $result[$newKey] = $value;
                    }
                }
                return $result;
            }
            
            $formData = [];
            
            if (isset($_POST['wpforms']) && is_array($_POST['wpforms'])) {
                $wpformsData = flattenArray($_POST['wpforms'], 'wpforms');
                $formData = array_merge($formData, $wpformsData);
            }
            
            foreach ($_POST as $key => $value) {
                if ($key !== 'wpforms') {
                    if (is_array($value)) {
                        $flatSub = flattenArray($value, $key);
                        $formData = array_merge($formData, $flatSub);
                    } else {
                        $formData[$key] = $value;
                    }
                }
            }
            
            if (empty($formData)) {
                $submissionStatus = 'error';
                $errorMessage = 'No form data received. Please try again.';
            } else {
                try {
                    $db = Database::getInstance()->getConnection();
                    $submissionId = uniqid('sub_', true);
                    
                    $stmt = $db->prepare("INSERT INTO form_submissions
                        (submission_id, form_id, form_name, data, ip, user_agent)
                        VALUES (?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $submissionId,
                        $formId,
                        $formConfig['name'],
                        json_encode($formData),
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                    ]);
                    
                    $submissionStatus = 'success';
                } catch(PDOException $e) {
                    $submissionStatus = 'error';
                    $errorMessage = 'We encountered a problem processing your submission. Please try again or contact us directly.';
                    // Log error for admin (don't show database details to user)
                    error_log('Form submission error: ' . $e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="preload" href="../../wp-content/astra-local-fonts/lora/0QIvMX1D_JOuMwr7Iw.woff2" as="font" type="font/woff2" crossorigin="">
    <link rel="preload" href="../../wp-content/astra-local-fonts/montserrat/JTUSjIg1_i6t8kCHKm459Wlhyw.woff2" as="font" type="font/woff2" crossorigin="">
    <meta name='robots' content='noindex, nofollow'>
    <title>Thank You | Berg PC</title>
    <meta name="description" content="Thank you for contacting Berg PC. We will get back to you as soon as possible.">
    <link rel="canonical" href="./">
    
    <link rel='stylesheet' id='astra-theme-css-css' href='../../wp-content/themes/astra/assets/css/minified/main.min.css?ver=4.13.1' media='all'>
    <style id='astra-theme-css-inline-css'>
        :root {
            --ast-container-default-xlg-padding: 6.67em;
            --ast-container-default-lg-padding: 5.67em;
            --ast-container-default-slg-padding: 4.34em;
            --ast-container-default-md-padding: 3.34em;
            --ast-container-default-sm-padding: 6.67em;
            --ast-container-default-xs-padding: 2.4em;
            --ast-container-default-xxs-padding: 1.4em;
            --ast-normal-container-width: 1200px;
            --ast-narrow-container-width: 750px;
        }
        html { font-size: 106.25%; }
        a, .page-title { color: var(--ast-global-color-0); }
        a:hover, a:focus { color: var(--ast-global-color-1); }
        body, button, input, select, textarea, .ast-button, .ast-custom-button {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            font-size: 17px;
            font-size: 1rem;
            line-height: 1.7em;
        }
        h1, h2, h3, h4, h5, h6, .entry-content :where(h1, h2, h3, h4, h5, h6), .site-title, .site-title a {
            font-family: 'Lora', serif;
            font-weight: 400;
            line-height: 1.2em;
        }
        h1, .entry-content :where(h1) { font-size: 72px; line-height: 1.1em; }
        h2, .entry-content :where(h2) { font-size: 40px; line-height: 1.3em; }
        h3, .entry-content :where(h3) { font-size: 32px; line-height: 1.3em; }
        body, h1, h2, h3, h4, h5, h6 { color: var(--ast-global-color-3); }
        :root {
            --ast-global-color-0: #0274be;
            --ast-global-color-1: #3a3a3a;
            --ast-global-color-2: #3a3a3a;
            --ast-global-color-3: #4B4F58;
            --ast-global-color-4: #F5F5F5;
            --ast-global-color-5: #FFFFFF;
            --ast-global-color-6: #f2f5f7;
            --ast-global-color-7: #424242;
            --ast-global-color-8: #000000;
            --ast-border-color: #dddddd;
        }
        .site-logo-img img { transition: all 0.2s linear; }
        
        .submission-message {
            padding: 30px 40px;
            margin: 40px auto;
            max-width: 600px;
            text-align: center;
            border-radius: 8px;
        }
        .submission-success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .submission-error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .submission-message h2 {
            margin-bottom: 15px;
            font-size: 28px;
        }
        .submission-message p {
            margin-bottom: 0;
            font-size: 18px;
        }
        .submission-message .btn-home {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 30px;
            background: var(--ast-global-color-0);
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        .submission-message .btn-home:hover {
            background: var(--ast-global-color-1);
        }
        
        @media (max-width:921px) {
            h1, .entry-content :where(h1) { font-size: 40px; }
            h2, .entry-content :where(h2) { font-size: 32px; }
            h3, .entry-content :where(h3) { font-size: 24px; }
        }
        @media (max-width:544px) {
            h1, .entry-content :where(h1) { font-size: 32px; }
            h2, .entry-content :where(h2) { font-size: 26px; }
            h3, .entry-content :where(h3) { font-size: 18px; }
        }
    </style>
    
    <link rel='stylesheet' id='wp-block-library-css' href='../../wp-includes/css/dist/block-library/style.min.css?ver=6.9.4' media='all'>
    <script src="../../wp-content/themes/astra/assets/js/minified/frontend.min.js?ver=4.13.1" id="astra-theme-js-js"></script>
</head>

<body itemtype='https://schema.org/WebPage' itemscope='itemscope' class="wp-singular page-template-default page wp-custom-logo wp-embed-responsive wp-theme-astra ehf-template-astra ehf-stylesheet-astra ast-desktop ast-separate-container ast-two-container ast-no-sidebar astra-4.13.1 ast-single-post ast-inherit-site-logo-transparent ast-hfb-header ast-normal-title-enabled elementor-default">

<a class="skip-link screen-reader-text" href="#content">Skip to content</a>

<div class="hfeed site" id="page">
    <!-- Header -->
    <header class="site-header header-main-layout-1 ast-primary-menu-enabled ast-hide-custom-menu-mobile ast-builder-menu-toggle-icon ast-mobile-header-inline" id="masthead" itemtype="https://schema.org/WPHeader" itemscope="itemscope">
        <div id="ast-desktop-header" data-toggle-type="dropdown">
            <div class="ast-above-header-wrap">
                <div class="ast-above-header-bar ast-above-header site-header-focus-item" data-section="section-above-header-builder">
                    <div class="site-above-header-wrap ast-builder-grid-row-container site-header-focus-item ast-container" data-section="section-above-header-builder">
                        <div class="ast-builder-grid-row ast-builder-grid-row-has-sides ast-builder-grid-row-no-center">
                            <div class="site-header-above-section-left site-header-section ast-flex site-header-section-left">
                                <div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="title_tagline">
                                    <div class="site-branding ast-site-identity" itemtype="https://schema.org/Organization" itemscope="itemscope">
                                        <span class="site-logo-img"><a href="../../" class="custom-logo-link" rel="home">
                                            <img width="280" height="110" src="../../wp-content/uploads/2024/12/cropped-Berg-PC-White-2-280x110.png" class="custom-logo" alt="Berg PC" decoding="async">
                                        </a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="site-header-above-section-right site-header-section ast-flex ast-grid-right-section"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ast-main-header-wrap main-header-bar-wrap">
                <div class="ast-primary-header-bar ast-primary-header main-header-bar site-header-focus-item" data-section="section-primary-header-builder">
                    <div class="site-primary-header-wrap ast-builder-grid-row-container site-header-focus-item ast-container" data-section="section-primary-header-builder">
                        <div class="ast-builder-grid-row ast-builder-grid-row-has-sides ast-builder-grid-row-no-center">
                            <div class="site-header-primary-section-left site-header-section ast-flex site-header-section-left">
                                <div class="ast-builder-menu-1 ast-builder-menu ast-flex ast-builder-menu-1-focus-item ast-builder-layout-element site-header-focus-item" data-section="section-hb-menu-1">
                                    <div class="ast-main-header-bar-alignment">
                                        <div class="main-header-bar-navigation">
                                            <nav class="site-navigation ast-flex-grow-1 navigation-accessibility site-header-focus-item" id="primary-site-navigation-desktop" aria-label="Primary Site Navigation" itemtype="https://schema.org/SiteNavigationElement" itemscope="itemscope">
                                                <div class="main-navigation ast-inline-flex">
                                                    <ul id="ast-hf-menu-1" class="main-header-menu ast-menu-shadow ast-nav-menu ast-flex submenu-with-border inline-on-mobile">
                                                        <li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="../../lawyers/" class="menu-link">Team</a></li>
                                                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children">
                                                            <a href="../../practice-areas/business-litigation/" class="menu-link">Business Litigation</a>
                                                            <ul class="sub-menu">
                                                                <li class="menu-item"><a href="../../practice-areas/business-litigation/breach-of-contract/" class="menu-link">Breach of Contract</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/business-litigation/partnership-disputes/" class="menu-link">Partnership Disputes</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/business-litigation/business-divorce/" class="menu-link">Business Divorce</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/business-litigation/non-compete-disputes/" class="menu-link">Non-Competes</a></li>
                                                            </ul>
                                                        </li>
                                                        <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children">
                                                            <a href="../../practice-areas/crypto-litigation/" class="menu-link">Crypto Litigation</a>
                                                            <ul class="sub-menu">
                                                                <li class="menu-item"><a href="../../practice-areas/crypto-litigation/fraud-recovery/" class="menu-link">Crypto Recovery</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/crypto-blockchain-business-transactions/" class="menu-link">Crypto Transactions</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/" class="menu-link">Meta Crypto Scam Ads</a></li>
                                                                <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-coinbase-data-breach/" class="menu-link">Coinbase Data Breach</a></li>
                                                            </ul>
                                                        </li>
                                                        <li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="../../blog/" class="menu-link">Blog</a></li>
                                                    </ul>
                                                </div>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="site-header-primary-section-right site-header-section ast-flex ast-grid-right-section"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Header -->
        <div id="ast-mobile-header" class="ast-mobile-header-wrap" data-type="dropdown">
            <div class="ast-main-header-wrap main-header-bar-wrap">
                <div class="ast-primary-header-bar ast-primary-header main-header-bar site-primary-header-wrap site-header-focus-item">
                    <div class="ast-builder-grid-row ast-builder-grid-row-has-sides ast-builder-grid-row-no-center">
                        <div class="site-header-primary-section-left site-header-section ast-flex site-header-section-left">
                            <div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="title_tagline">
                                <div class="site-branding ast-site-identity">
                                    <span class="site-logo-img"><a href="../../" class="custom-logo-link" rel="home">
                                        <img width="280" height="110" src="../../wp-content/uploads/2024/12/cropped-Berg-PC-White-2-280x110.png" class="custom-logo" alt="Berg PC">
                                    </a></span>
                                </div>
                            </div>
                        </div>
                        <div class="site-header-primary-section-right site-header-section ast-flex ast-grid-right-section">
                            <div class="ast-builder-layout-element ast-flex site-header-focus-item" data-section="section-header-mobile-trigger">
                                <div class="ast-button-wrap">
                                    <button type="button" class="menu-toggle main-header-menu-toggle ast-mobile-menu-trigger-fill" aria-expanded="false" aria-label="Main menu toggle">
                                        <span class="mobile-menu-toggle-icon">
                                            <span aria-hidden="true" class="ahfb-svg-iconset ast-inline-flex svg-baseline">
                                                <svg class='ast-mobile-svg ast-menu2-svg' fill='currentColor' version='1.1' xmlns='http://www.w3.org/2000/svg' width='24' height='28' viewbox='0 0 24 28'>
                                                    <path d='M24 21v2c0 0.547-0.453 1-1 1h-22c-0.547 0-1-0.453-1-1v-2c0-0.547 0.453-1 1-1h22c0.547 0 1 0.453 1 1zM24 13v2c0 0.547-0.453 1-1 1h-22c-0.547 0-1-0.453-1-1v-2c0-0.547 0.453-1 1-1h22c0.547 0 1 0.453 1 1zM24 5v2c0 0.547-0.453 1-1 1h-22c-0.547 0-1-0.453-1-1v-2c0-0.547 0.453-1 1-1h22c0.547 0 1 0.453 1 1z'></path>
                                                </svg>
                                            </span>
                                            <span aria-hidden="true" class="ahfb-svg-iconset ast-inline-flex svg-baseline">
                                                <svg class='ast-mobile-svg ast-close-svg' fill='currentColor' version='1.1' xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewbox='0 0 24 24'>
                                                    <path d='M5.293 6.707l5.293 5.293-5.293 5.293c-0.391 0.391-0.391 1.024 0 1.414s1.024 0.391 1.414 0l5.293-5.293 5.293 5.293c0.391 0.391 1.024 0.391 1.414 0s0.391-1.024 0-1.414l-5.293-5.293 5.293-5.293c0.391-0.391 0.391-1.024 0-1.414s-1.024-0.391-1.414 0l-5.293 5.293-5.293-5.293c-0.391-0.391-1.024-0.391-1.414 0s-0.391 1.024 0 1.414z'></path>
                                                </svg>
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ast-mobile-header-content content-align-flex-start">
                    <div class="ast-builder-menu-mobile ast-builder-menu ast-builder-menu-mobile-focus-item ast-builder-layout-element site-header-focus-item" data-section="section-header-mobile-menu">
                        <div class="ast-main-header-bar-alignment">
                            <div class="main-header-bar-navigation">
                                <nav class="site-navigation ast-flex-grow-1 navigation-accessibility site-header-focus-item" id="ast-mobile-site-navigation" aria-label="Site Navigation: Mobile">
                                    <div class="main-navigation">
                                        <ul id="ast-hf-mobile-menu" class="main-header-menu ast-nav-menu ast-flex submenu-with-border astra-menu-animation-fade stack-on-mobile">
                                            <li class="menu-item"><a href="../../" class="menu-link">Home</a></li>
                                            <li class="menu-item"><a href="../../lawyers/" class="menu-link">Team</a></li>
                                            <li class="menu-item menu-item-has-children">
                                                <a href="../../practice-areas/business-litigation/" class="menu-link">Business Litigation</a>
                                                <ul class="sub-menu">
                                                    <li class="menu-item"><a href="../../practice-areas/business-litigation/breach-of-contract/" class="menu-link">Breach of Contract</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/business-litigation/partnership-disputes/" class="menu-link">Partnership Disputes</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/business-litigation/business-divorce/" class="menu-link">Business Divorce</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/business-litigation/non-compete-disputes/" class="menu-link">Non-Competes</a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item menu-item-has-children">
                                                <a href="../../practice-areas/crypto-litigation/fraud-recovery/" class="menu-link">Crypto Litigation</a>
                                                <ul class="sub-menu">
                                                    <li class="menu-item"><a href="../../practice-areas/crypto-litigation/fraud-recovery/" class="menu-link">Crypto Recovery</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/crypto-blockchain-business-transactions/" class="menu-link">Crypto Transactions</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/" class="menu-link">Meta Crypto Scam Ads</a></li>
                                                    <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-coinbase-data-breach/" class="menu-link">Coinbase Data Breach</a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item"><a href="../../blog/" class="menu-link">Blog</a></li>
                                            <li class="menu-item"><a href="../../contact/" class="menu-link">Contact</a></li>
                                        </ul>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div id="content" class="site-content">
        <div class="ast-container">
            <div id="primary" class="content-area primary">
                <main id="main" class="site-main">
                    <article class="post-2230 page type-page status-publish ast-article-single" id="post-2230" itemtype="https://schema.org/CreativeWork" itemscope="itemscope">
                        
                        <header class="entry-header">
                            <h1 class="entry-title" itemprop="headline">Thank You</h1>
                        </header>

                        <div class="entry-content clear" data-ast-blocks-layout="true" itemprop="text">
                            
                            <?php if ($submissionStatus === 'success'): ?>
                                <!-- Success State -->
                                <div class="submission-message submission-success" id="submission-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    <h2>Message Sent Successfully!</h2>
                                    <p>Thank you for contacting us. We will get back to you as soon as possible.</p>
                                    <a href="../../" class="btn-home">Return to Homepage</a>
                                </div>
                            
                            <?php elseif ($submissionStatus === 'error'): ?>
                                <!-- Error State -->
                                <div class="submission-message submission-error" id="submission-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                    <h2>Submission Failed</h2>
                                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                                    <p style="margin-top: 15px;">Please try again or contact us directly at <a href="mailto:info@bergpcc.com" style="color: #721c24; text-decoration: underline;">info@bergpcc.com</a> or call <a href="tel:713-526-0200" style="color: #721c24; text-decoration: underline;">(713) 526-0200</a>.</p>
                                    <a href="../../" class="btn-home" style="background: #dc3545;">Return to Homepage</a>
                                </div>
                            
                            <?php else: ?>
                                <!-- Default State (Direct Access) -->
                                <p>Thank you for contacting us. We will get back to you as soon as possible.</p>
                            <?php endif; ?>
                            
                        </div>
                    </article>
                </main>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer" id="colophon" itemtype="https://schema.org/WPFooter" itemscope="itemscope">
        <div class="site-primary-footer-wrap ast-builder-grid-row-container site-footer-focus-item ast-builder-grid-row-4-equal ast-builder-grid-row-tablet-full ast-builder-grid-row-mobile-full ast-footer-row-stack ast-footer-row-tablet-stack ast-footer-row-mobile-stack" data-section="section-primary-footer-builder">
            <div class="ast-builder-grid-row-container-inner">
                <div class="ast-builder-footer-grid-columns site-primary-footer-inner-wrap ast-builder-grid-row">
                    
                    <!-- Footer Section 1 - Contact Info -->
                    <div class="site-footer-primary-section-1 site-footer-section site-footer-section-1">
                        <div class="footer-widget-area widget-area site-footer-focus-item ast-footer-html-1" data-section="section-fb-html-1">
                            <div class="ast-header-html inner-link-style-">
                                <div class="ast-builder-html-element">
                                    <p><img class="alignnone wp-image-1938" src="../../wp-content/uploads/2024/11/cropped-Berg-PC-bg-Blue-1-e1732045234138-300x158.png" alt="" width="143" height="75"></p>
                                    <p>24 Greenway Plaza,<br>Suite 1800<br>Houston, Texas 77046</p>
                                    <p><a href="mailto:info@bergpcc.com" style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512" fill="currentColor">
                                            <path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"></path>
                                        </svg>
                                        <span>info@bergpcc.com</span>
                                    </a></p>
                                    <p><a href="tel:713-526-0200" style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512" fill="currentColor">
                                            <path d="M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A23.94 23.94 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a23.94 23.94 0 0 0-14.1-27.6z"></path>
                                        </svg>
                                        <span>(713) 526-0200</span>
                                    </a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer Section 2 - Our Firm -->
                    <div class="site-footer-primary-section-2 site-footer-section site-footer-section-2">
                        <aside class="footer-widget-area widget-area site-footer-focus-item footer-widget-area-inner" data-section="sidebar-widgets-footer-widget-1" aria-label="Footer Widget 1" role="region">
                            <section id="nav_menu-1" class="widget widget_nav_menu">
                                <h2 class="widget-title">Our Firm</h2>
                                <nav class="menu-footer-our-firm-container" aria-label="Our Firm">
                                    <ul id="menu-footer-our-firm" class="menu">
                                        <li class="menu-item"><a href="../../lawyers/" class="menu-link">Team</a></li>
                                    </ul>
                                </nav>
                            </section>
                        </aside>
                    </div>
                    
                    <!-- Footer Section 3 - Business Litigation -->
                    <div class="site-footer-primary-section-3 site-footer-section site-footer-section-3">
                        <aside class="footer-widget-area widget-area site-footer-focus-item footer-widget-area-inner" data-section="sidebar-widgets-footer-widget-2" aria-label="Footer Widget 2" role="region">
                            <section id="nav_menu-2" class="widget widget_nav_menu">
                                <h2 class="widget-title">Business Litigation</h2>
                                <nav class="menu-footer-business-litigation-container" aria-label="Business Litigation">
                                    <ul id="menu-footer-business-litigation" class="menu">
                                        <li class="menu-item"><a href="../../practice-areas/business-litigation/breach-of-contract/" class="menu-link">Breach of Contract</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/business-litigation/partnership-disputes/" class="menu-link">Partnership Disputes</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/business-litigation/business-divorce/" class="menu-link">Business Divorce</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/business-litigation/non-compete-disputes/" class="menu-link">Non-Compete Disputes</a></li>
                                    </ul>
                                </nav>
                            </section>
                        </aside>
                    </div>
                    
                    <!-- Footer Section 4 - Crypto Litigation -->
                    <div class="site-footer-primary-section-4 site-footer-section site-footer-section-4">
                        <aside class="footer-widget-area widget-area site-footer-focus-item footer-widget-area-inner" data-section="sidebar-widgets-footer-widget-4" aria-label="Footer Widget 4" role="region">
                            <section id="nav_menu-7" class="widget widget_nav_menu">
                                <h2 class="widget-title">Crypto Litigation</h2>
                                <nav class="menu-footer-crypto-litigation-container" aria-label="Crypto Litigation">
                                    <ul id="menu-footer-crypto-litigation" class="menu">
                                        <li class="menu-item"><a href="../../practice-areas/crypto-litigation/fraud-recovery/" class="menu-link">Crypto Fraud &amp; Recovery</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/crypto-blockchain-business-transactions/" class="menu-link">Crypto Business Transactions</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-coinbase-data-breach/" class="menu-link">Coinbase Data Breach</a></li>
                                        <li class="menu-item"><a href="../../practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/" class="menu-link">Meta Crypto Scam Ads</a></li>
                                    </ul>
                                </nav>
                            </section>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Below Footer - Copyright -->
        <div class="site-below-footer-wrap ast-builder-grid-row-container site-footer-focus-item ast-builder-grid-row-full ast-builder-grid-row-tablet-full ast-builder-grid-row-mobile-full ast-footer-row-stack ast-footer-row-tablet-stack ast-footer-row-mobile-stack" data-section="section-below-footer-builder">
            <div class="ast-builder-grid-row-container-inner">
                <div class="ast-builder-footer-grid-columns site-below-footer-inner-wrap ast-builder-grid-row">
                    <div class="site-footer-below-section-1 site-footer-section site-footer-section-1">
                        <div class="ast-builder-layout-element ast-flex site-footer-focus-item ast-footer-copyright" data-section="section-footer-builder">
                            <div class="ast-footer-copyright">
                                <p>Copyright &copy; <?php echo date('Y'); ?> Berg PC</p>
                            </div>
                        </div>
                        <div class="footer-widget-area widget-area site-footer-focus-item ast-footer-html-2" data-section="section-fb-html-2">
                            <div class="ast-header-html inner-link-style-">
                                <div class="ast-builder-html-element">
                                    <p><a href="../../privacy-policy/">Privacy Policy</a> | <a href="../../disclaimer/">Disclaimer</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileHeader = document.querySelector('.ast-mobile-header-content');
    
    if (menuToggle && mobileHeader) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            mobileHeader.classList.toggle('active');
            document.body.classList.toggle('ast-mobile-menu-active');
        });
    }
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>
