# Fix JavaScript Syntax Error in lawyers.php

## Summary
The error "Uncaught SyntaxError: missing ) after argument list (at lawyers.php:157:187)" is caused by inline JavaScript in the `onclick` handler at line 252 where lawyer data is JSON-encoded directly into an HTML attribute.

## Current State Analysis

### Problem Location
[Line 252](file:///d:/JZ/bergpc.com/bergpc.com/admin-x7k9m/lawyers.php#L252):
```php
<button onclick='showEditForm(<?php echo json_encode($lawyer, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)' class="btn btn-secondary btn-sm">Edit</button>
```

### Root Cause
- The `JSON_HEX_APOS` flag converts single quotes to `\u0027`
- When this escape sequence is inside a single-quoted HTML attribute value, the browser doesn't properly interpret it as a JavaScript Unicode escape
- This causes the JSON parsing to fail with "missing ) after argument list"

### Affected Data
The `bio` field (and potentially other fields) can contain:
- Single quotes (apostrophes) which get converted to `\u0027`
- HTML content from the Quill rich text editor
- Special characters that may break the JavaScript string parsing

## Proposed Changes

### Fix Approach: Use data attributes + escape JSON for JavaScript context

Modify [line 252](file:///d:/JZ/bergpc.com/bergpc.com/admin-x7k9m/lawyers.php#L252) to use a data attribute with properly escaped JSON:

**Before:**
```php
<button onclick='showEditForm(<?php echo json_encode($lawyer, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)' class="btn btn-secondary btn-sm">Edit</button>
```

**After:**
```php
<button onclick='showEditForm(<?php echo htmlspecialchars(json_encode($lawyer), ENT_QUOTES, 'UTF-8'); ?>)' class="btn btn-secondary btn-sm">Edit</button>
```

### Why This Works
1. `json_encode($lawyer)` produces valid JSON
2. `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` escapes the JSON for safe inclusion in an HTML attribute, converting:
   - `"` to `&quot;`
   - `'` to `&#039;` (or &apos;)
   - `<` to `&lt;`
   - `>` to `&gt;`
3. When the browser parses the HTML attribute, it gets the literal string representation of the JSON
4. JavaScript then parses this string as JSON, which works correctly

### Alternative Approach (More Robust)
If the above doesn't work, use data attributes instead of inline onclick:

```php
<button data-lawyer='<?php echo htmlspecialchars(json_encode($lawyer), ENT_QUOTES, 'UTF-8'); ?>' onclick='showEditForm(JSON.parse(this.dataset.lawyer))' class="btn btn-secondary btn-sm">Edit</button>
```

But the simpler `htmlspecialchars(json_encode(...))` approach should suffice.

## Files to Modify
- `d:\JZ\bergpc.com\bergpc.com\admin-x7k9m\lawyers.php` - Line 252

## Assumptions
- The PHP server is running with UTF-8 encoding
- The `bio` field may contain HTML from the Quill editor
- `json_encode` produces valid JSON for the lawyer data

## Verification Steps
1. After the fix, load the lawyers.php page in a browser
2. Open the browser's developer console (F12)
3. Verify there are no JavaScript syntax errors
4. Click the "Edit" button for any lawyer and verify the form populates correctly
5. Check that the bio content displays properly with any HTML formatting preserved
