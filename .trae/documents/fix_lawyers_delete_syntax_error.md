# Plan: Fix JavaScript Syntax Error in lawyers.php Delete Button

## Summary
The error occurs when clicking the Delete button because single quotes in lawyer names are not being properly escaped for JavaScript. The current fix uses `htmlspecialchars(..., ENT_QUOTES)` which produces HTML entities (`&#039;`), but these are decoded AFTER JavaScript parsing, causing syntax errors.

## Current State Analysis
- File: `d:\JZ\bergpc.com\bergpc.com\admin-x7k9m\lawyers.php` line 257
- Current code uses `htmlspecialchars(..., ENT_QUOTES)` which produces `&#039;`
- `&#039;` is HTML entity for single quote, decoded after JavaScript parsing
- Browser sees: `confirmDelete('14', 'Lisa Dowlen 'Lisa' Autry')` - broken!
- Edit button correctly uses `json_encode` which produces `\u0027` - JavaScript Unicode escape

## Problem Demonstration
```html
<!-- Current broken output: -->
<button onclick="confirmDelete('14', 'Lisa Dowlen &#039;Lisa&#039; Autry')">Delete</button>
<!-- Browser sees: confirmDelete('14', 'Lisa Dowlen 'Lisa' Autry') -->

<!-- Edit button correct output: -->
<button onclick='showEditForm({"name":"Lisa Dowlen \u0027Lisa\u0027 Autry",...})'>Edit</button>
<!-- Works because \u0027 is valid JavaScript -->
```

## Proposed Changes

### Fix line 257 - Use json_encode for name parameter
**File:** `admin-x7k9m/lawyers.php` (line 257)

**Change:**
```php
// Before (broken - htmlspecialchars produces HTML entities that break JS):
<button onclick="confirmDelete('<?php echo htmlspecialchars($lawyer['id'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($lawyer['name'], ENT_QUOTES); ?>')" class="btn btn-danger btn-sm">Delete</button>

// After (correct - json_encode produces JavaScript Unicode escapes):
<button onclick="confirmDelete(<?php echo json_encode($lawyer['id']); ?>, <?php echo json_encode($lawyer['name']); ?>)" class="btn btn-danger btn-sm">Delete</button>
```

Note: `json_encode` will:
1. Wrap strings in quotes automatically
2. Escape special characters using `\uXXXX` notation
3. Handle any character set properly

## Verification
1. Access the lawyers admin page
2. Find a lawyer with single quote in name (e.g., "Lisa Dowlen 'Lisa' Autry")
3. Click Delete button - modal should appear without JavaScript errors