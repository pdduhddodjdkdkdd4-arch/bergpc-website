# Script to add Testimonials menu item to all pages
$baseDir = "E:\jz\doc_2026-05-15_06-30-03.git"

# Files with navigation (from earlier analysis)
$navFiles = @(
    "index.htm",
    "15-billion-in-bitcoin-seized\index.htm",
    "accepting-cryptocurrency-as-a-form-of-payment-in-your-business\index.htm",
    "are-non-compete-agreements-enforceable-in-texas\index.htm",
    "author\gberg\index.htm",
    "author\gberg\page\2\index.htm",
    "author\gberg\page\3\index.htm",
    "blog\index.htm",
    "blog\page\2\index.htm",
    "blog\page\3\index.htm",
    "category\business-litigation\index.htm",
    "category\business-litigation\page\2\index.htm",
    "category\crypto-litigation\index.htm",
    "category\news\index.htm",
    "company-uses-my-intellectual-property-without-permission\index.htm",
    "contact\index.htm",
    "crypto-scam-fund-recovery-guide-digital-asset-investigation-and-recovery-process-analysis\index.htm",
    "disclaimer\index.htm",
    "geoffrey-berg-included-in-the-best-lawyers-in-america\index.htm",
    "getting-your-share-of-the-15-billion-bitcoin-seizure\index.htm",
    "grounds-for-suing-a-business-partner\index.htm",
    "how-is-stolen-crypto-tracked\index.htm",
    "how-to-handle-disputes-in-a-partnership\index.htm",
    "how-to-spot-and-avoid-crypto-lawyer-scams\index.htm",
    "lawyers\index.htm",
    "lawyers\index.php",
    "lawyers\geoffrey-berg\index.htm",
    "lawyers\kathryn-e-nelson\index.htm",
    "lawyers\tomas-f-tijerina\index.htm",
    "lawyers\tracy-moberg\index.htm",
    "meta-earns-billions-from-victims-lured-into-crypto-scams-by-facebook-ads\index.htm",
    "podcast\index.htm",
    "practice-areas\business-litigation\index.htm",
    "practice-areas\business-litigation\breach-of-contract\index.htm",
    "practice-areas\business-litigation\business-divorce\index.htm",
    "practice-areas\business-litigation\non-compete-disputes\index.htm",
    "practice-areas\business-litigation\partnership-disputes\index.htm",
    "practice-areas\business-litigation-thank-you\index.htm",
    "practice-areas\business-litigation-thank-you\index.php",
    "practice-areas\crypto-blockchain-business-transactions\index.htm",
    "practice-areas\crypto-litigation\index.htm",
    "practice-areas\crypto-litigation\2025-meta-crypto-scam-ads-investigation\index.htm",
    "practice-areas\crypto-litigation\fraud-recovery\index.htm",
    "privacy-policy\index.htm",
    "testimonials\index.htm",
    "the-slavery-behind-crypto-scams\index.htm",
    "what-happens-when-dissolving-a-partnership\index.htm",
    "what-is-material-breach-of-contract-in-texas\index.htm"
)

# Desktop menu item template (add after Blog)
$desktopMenuItem = @"
											<li id="menu-item-testimonials-desktop"
												class="menu-item menu-item-type-post_type menu-item-object-page menu-item-testimonials-desktop">
												<a href="../testimonials/index.htm" class="menu-link">Testimonials</a>
											</li>
"@

# Mobile menu item template (add after Contact)
$mobileMenuItem = @"
											<li id="menu-item-testimonials-mobile"
												class="menu-item menu-item-type-post_type menu-item-object-page menu-item-testimonials-mobile">
												<a href="../testimonials/index.htm" class="menu-link">Testimonials</a>
											</li>
"@

$successCount = 0
$errorCount = 0
$alreadyHasTestimonials = 0

foreach ($file in $navFiles) {
    $fullPath = Join-Path $baseDir $file
    
    if (-not (Test-Path $fullPath)) {
        Write-Host "NOT FOUND: $file"
        $errorCount++
        continue
    }
    
    $content = [System.IO.File]::ReadAllText($fullPath, [System.Text.Encoding]::UTF8)
    
    # Skip if already has Testimonials menu
    if ($content.Contains(">Testimonials</a>")) {
        Write-Host "SKIP (already has): $file"
        $alreadyHasTestimonials++
        continue
    }
    
    $modified = $false
    
    # Add to desktop menu (after Blog)
    # Flexible pattern for Blog menu item with various path formats
    # Matches: href="blog/", href="../blog/index.htm", href="../../blog/", etc.
    $blogPattern = '(<a href="[^"]*blog[^"]*"[^>]*class="menu-link">Blog</a>\s*</li>)'
    if ($content -match $blogPattern) {
        $content = [regex]::Replace($content, $blogPattern, "`$1$desktopMenuItem")
        $modified = $true
    }
    
    # Add to mobile menu (after Contact)
    # Flexible pattern for Contact menu item with various path formats
    # Matches: href="contact/", href="../contact/index.htm", href="../../contact/", etc.
    $contactPattern = '(<a href="[^"]*contact[^"]*"[^>]*class="menu-link">Contact</a>\s*</li>)'
    if ($content -match $contactPattern) {
        $content = [regex]::Replace($content, $contactPattern, "`$1$mobileMenuItem")
        $modified = $true
    }
    
    if ($modified) {
        # Determine encoding - preserve existing BOM status
        $bytes = [System.IO.File]::ReadAllBytes($fullPath)
        $hasBom = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
        
        if ($hasBom) {
            $encoding = New-Object System.Text.UTF8Encoding($true)
        } else {
            $encoding = New-Object System.Text.UTF8Encoding($false)
        }
        
        [System.IO.File]::WriteAllText($fullPath, $content, $encoding)
        Write-Host "UPDATED: $file"
        $successCount++
    } else {
        Write-Host "NO CHANGE: $file"
        $errorCount++
    }
}

Write-Host ""
Write-Host "=== SUMMARY ==="
Write-Host "Updated: $successCount"
Write-Host "Skipped (already has): $alreadyHasTestimonials"
Write-Host "No change: $errorCount"