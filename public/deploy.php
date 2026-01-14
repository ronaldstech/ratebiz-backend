<?php
/**
 * Deployment script for RateBiz Backend
 * This script handles GitHub webhooks to automate deployment on Hostinger.
 */

// Define a secret key for security (set this in your GitHub WebHook settings)
$secret = 'ratebiz_deploy_secret_2026'; 

// Get the signature from the header
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!$signature) {
    http_response_code(403);
    die('No signature provided.');
}

// Get the raw POST content
$payload = file_get_contents('php://input');

// Verify the signature
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Invalid signature.');
}

// Deployment logic
echo "Starting deployment...\n";

// Execute git pull
// Note: Ensure the SSH key is added to the user's SSH agent or ~/.ssh/authorized_keys
// and that the web server user has access to the repository.
$output = shell_exec('git pull origin main 2>&1');
echo "<pre>$output</pre>";

// Optional: Run composer install if composer.json changed (might be slow for webhook)
// if (strpos($payload, 'composer.json') !== false) {
//     $composer_output = shell_exec('composer install --no-dev --optimize-autoloader 2>&1');
//     echo "<pre>$composer_output</pre>";
// }

echo "Deployment finished.";
?>
