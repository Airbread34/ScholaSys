<?php
// reCAPTCHA v3 Configuration
define('RECAPTCHA_SITE_KEY', '6LfYQbYsAAAAANS7mPPKqK4lGbg96675vuDH0nYj');
define('RECAPTCHA_SECRET_KEY', '6LfYQbYsAAAAADHO8ayi_ClOnhIU_9FJV5xGHVv1');
define('RECAPTCHA_SCORE_THRESHOLD', 0.3); // Adjust for local testing 0.5 but 0.3 for production because of the low score of v3, you can also check the score in the reCAPTCHA admin console and adjust accordingly
?>