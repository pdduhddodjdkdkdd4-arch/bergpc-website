# Fix Testimonials relative paths based on directory depth
$baseDir = "E:\jz\doc_2026-05-15_06-30-03.git"

$files = Get-ChildItem -Path $baseDir -Recurse -Include "*.htm","*.php" -Name | Where-Object { $_ -notmatch "wp-admin|wp-includes|wp-content|admin-x7k9m|api|\.git|\.trae|_create_|_add_" }

$fixedCount = 0
$errorCount = 0

foreach ($file in $files) {
    $fullPath = Join-Path $baseDir $file
    
    $content = [System.IO.File]::ReadAllText($fullPath, [System.Text.Encoding]::UTF8)
    
    # Skip files without Testimonials menu
    if (-not $content.Contains(">Testimonials</a>")) {
        continue
    }
    
    # Determine directory depth (count slashes in path)
    $depth = ($file.Split('\').Count - 1)  # -1 because filename is included
    
    # Determine correct path based on depth
    $correctPath = switch ($depth) {
        0 { "testimonials/" }                    # Root level (index.htm)
        1 { "../testimonials/" }                 # One level deep (lawyers/, blog/, etc.)
        2 { "../../testimonials/" }              # Two levels deep (practice-areas/crypto-litigation/)
        3 { "../../../testimonials/" }           # Three levels deep (practice-areas/business-litigation/breach-of-contract/)
        4 { "../../../../testimonials/" }        # Four levels deep (rare but possible)
        default { "../../testimonials/" }        # Default fallback
    }
    
    # Fix all Testimonials links (both ../testimonials/index.htm and ../testimonials/)
    $patterns = @(
        'href="\.\./testimonials/index\.htm"',
        'href="\.\./testimonials/"'
    )
    
    $correctHref = "href=`"$correctPath`""
    $originalContent = $content
    
    foreach ($pattern in $patterns) {
        $content = [regex]::Replace($content, $pattern, $correctHref)
    }
    
    if ($content -ne $originalContent) {
        # Preserve BOM status
        $bytes = [System.IO.File]::ReadAllBytes($fullPath)
        $hasBom = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
        
        if ($hasBom) {
            $encoding = New-Object System.Text.UTF8Encoding($true)
        } else {
            $encoding = New-Object System.Text.UTF8Encoding($false)
        }
        
        [System.IO.File]::WriteAllText($fullPath, $content, $encoding)
        Write-Host "FIXED (depth=$depth): $file → $correctPath"
        $fixedCount++
    }
}

Write-Host ""
Write-Host "=== SUMMARY ==="
Write-Host "Fixed: $fixedCount"
Write-Host "Errors: $errorCount"
