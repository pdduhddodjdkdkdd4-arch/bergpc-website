<?php
/**
 * Legacy Form Submission Handler - Redirects to thank-you page
 * All forms now submit directly to /practice-areas/business-litigation-thank-you/
 * This file is kept for backward compatibility with any old links/bookmarks
 */

// Redirect all requests to the thank-you page
header('Location: /practice-areas/business-litigation-thank-you/');
exit;
