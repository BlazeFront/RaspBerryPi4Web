<?php
// Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "Mr53st52!1337";
$dbname = "downloads";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("Database connection error.");
}

// Get the video ID from the query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$videoTitle = isset($_GET['title']) ? (String)$_GET['title'] : null;

if (!$id) {
    error_log("Invalid ID parameter provided.");
    http_response_code(400);
    die("Invalid ID.");
}

// Fetch the URL for the video
$sql = "SELECT url FROM downloads WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Database error: Unable to prepare statement. " . $conn->error);
    http_response_code(500);
    die("Database error.");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    error_log("No video found with ID: " . $id);
    http_response_code(404);
    die("No video found.");
}

$stmt->bind_result($url);
$stmt->fetch();
$stmt->close();

// Parse the URL and extract the video ID
$parsedUrl = parse_url($url);
parse_str($parsedUrl['query'] ?? '', $queryParams);

$videoId = $queryParams['v'] ?? null;

if (!$videoId) {
    error_log("Invalid YouTube URL: " . $url);
    http_response_code(400);
    die("Invalid YouTube URL.");
}

// Construct a clean YouTube URL
$cleanUrl = "https://www.youtube.com/watch?v=" . $videoId;

$videoTitle = trim($videoTitle);
$safeTitle = preg_replace('/[\/:*?"<>|]/', '', $videoTitle);

// Path for the output MP3 file
$outputFile = __DIR__ . "/downloads/" . $safeTitle . ".mp3";

// Check if the file already exists
if (file_exists($outputFile)) {
    // Serve the MP3 file immediately
    header('Content-Type: audio/mpeg');
    header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    exit();
}

// Create the downloads directory if it doesn't exist
if (!is_dir(__DIR__ . '/downloads')) {
    mkdir(__DIR__ . '/downloads', 0777, true);
}

// Download the MP3
$command = sprintf(
    'yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o %s %s 2>&1',
    escapeshellarg($outputFile),
    escapeshellarg($cleanUrl)
);
$output = shell_exec($command);

// Verify the file was created
if (!file_exists($outputFile)) {
    error_log("MP3 file could not be created. Command: " . $command . " Output: " . $output);
    http_response_code(500);
    die("Error: MP3 file could not be created.");
}

// Update database to mark download as completed
$updateSql = "UPDATE downloads SET downloaded = 1 WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);

if ($updateStmt) {
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    error_log("Failed to prepare update statement: " . $conn->error);
}

// Serve the MP3 file after downloading
header('Content-Type: audio/mpeg');
header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
header('Content-Length: ' . filesize($outputFile));
readfile($outputFile);

$conn->close();
exit();
?>
