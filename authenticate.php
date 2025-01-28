<?php
session_start();

// Set your secure credentials
$valid_username = 'Carlos';
$valid_password = password_hash('Carlos', PASSWORD_BCRYPT); // Replace 'securepassword' with your password

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die("Forbidden");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && password_verify($password, $valid_password)) {
        $_SESSION['logged_in'] = true;
        // Update yt-dlp to the latest version
        shell_exec("yt-dlp -U");
        header('Location: index.php'); // Redirect to your main page
        exit();
    } else {
        // Redirect back to login with an error
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
}
?>
