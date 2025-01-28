<?php
// Set the secret key (same as in the GitHub webhook settings)
$secret = "Mr53st52";

// Get the payload and signature from GitHub
$payload = file_get_contents("php://input");
$signature = $_SERVER["HTTP_X_HUB_SIGNATURE"];

// Validate the request
$hash = "sha1=" . hash_hmac("sha1", $payload, $secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die("Invalid signature.");
}

// Execute the deployment script
exec("sudo /var/www/html/RaspBerryPi4Web/update_webapp.sh 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "Deployment successful: " . implode("\n", $output);
} else {
    http_response_code(500);
    echo "Deployment failed: " . implode("\n", $output);
}
