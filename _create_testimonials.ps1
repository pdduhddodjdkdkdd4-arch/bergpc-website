$sourcePath = "E:\jz\doc_2026-05-15_06-30-03.git\lawyers\index.php"
$outputPath = "E:\jz\doc_2026-05-15_06-30-03.git\testimonials\index.htm"

$content = [System.IO.File]::ReadAllText($sourcePath, [System.Text.Encoding]::UTF8)

# Find the footer start position
$footerStart = $content.IndexOf('<footer')

# Find entry-content div
$entryContentMarker = 'class="entry-content clear"'
$entryContentIdx = $content.IndexOf($entryContentMarker)
$entryDivStart = $content.LastIndexOf('<div', $entryContentIdx)

# Header part: from beginning to just before entry-content div
$headerPart = $content.Substring(0, $entryDivStart)

# Footer part: from footer to end
$footerPart = $content.Substring($footerStart)

# Modify meta tags in header
$headerPart = $headerPart -replace '<title>[^<]*</title>', '<title>Testimonials | Berg PC</title>'
$headerPart = $headerPart -replace 'og:title" content="[^"]*"', 'og:title" content="Testimonials | Berg PC"'
$headerPart = $headerPart -replace 'og:description"\s+content="[^"]*"', 'og:description" content="Read testimonials and success stories from clients who have worked with Berg PC on cryptocurrency fraud recovery and business litigation cases."'
$headerPart = $headerPart -replace 'name="description"\s+content="[^"]*"', 'name="description" content="Read testimonials and success stories from clients who have worked with Berg PC on cryptocurrency fraud recovery and business litigation cases."'
$headerPart = $headerPart -replace 'og:url" content="[^"]*lawyers[^"]*"', 'og:url" content="https://bergpcc.com/testimonials/"'

# Remove PHP database code
$phpPattern = '(?s)<\?php.*?\?>'
$headerPart = [regex]::Replace($headerPart, $phpPattern, '')

# Replace page-id-517 references
$headerPart = $headerPart -replace 'page-id-517', 'page-id-9999'
$headerPart = $headerPart -replace 'page-item-517', 'page-item-9999'
$headerPart = $headerPart -replace 'post-517', 'post-9999'
$headerPart = $headerPart -replace 'page_id=517', 'page_id=9999'
$headerPart = $headerPart -replace 'elementor-page-517', 'elementor-page-9999'
$headerPart = $headerPart -replace 'elementor-id="517"', 'elementor-id="9999"'

# Replace current-menu-item for Team page with nothing (Testimonials is not current)
$headerPart = $headerPart -replace ' current-menu-item', ''
$headerPart = $headerPart -replace ' current_page_item', ''
$headerPart = $headerPart -replace ' current-menu-parent', ''
$headerPart = $headerPart -replace ' aria-current="page"', ''

# Replace lawyers/ path references in meta/canonical with testimonials/
$headerPart = $headerPart -replace 'bergpcc\.com/lawyers/', 'bergpcc.com/testimonials/'
$headerPart = $headerPart -replace 'href="index\.htm"', 'href="index.htm"'

# Create empty main content
$mainContent = @"
							<div class="entry-content clear" itemprop="text">
								<div data-elementor-type="wp-page" data-elementor-id="9999" class="elementor elementor-9999" data-elementor-post-type="page">
									<div class="elementor-element e-flex e-con-boxed e-con e-parent" data-element_type="container">
										<div class="e-con-inner">
											<!-- Testimonials content will be added here -->
										</div>
									</div>
								</div>
							</div>

						</article>

					</main>
				</div>
			</div>
		</div>
"@

# Combine parts
$finalContent = $headerPart + $mainContent + $footerPart

# Write file with UTF-8 BOM to match other pages
$utf8Bom = New-Object System.Text.UTF8Encoding($true)
[System.IO.File]::WriteAllText($outputPath, $finalContent, $utf8Bom)

$fileSize = [System.IO.File]::ReadAllBytes($outputPath).Length
Write-Host "Created: $outputPath"
Write-Host "File size: $fileSize bytes"
