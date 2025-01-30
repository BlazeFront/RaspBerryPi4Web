<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json'); // Set JSON response type

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "downloads";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Get the video ID from the query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing ID parameter"]);
    exit();
}

// Fetch the URL for the video
$sql = "SELECT url FROM downloads WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Database error: Unable to prepare statement. " . $conn->error);
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
    exit();
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "No video found with ID: " . $id]);
    exit();
}

$stmt->bind_result($url);
$stmt->fetch();

// Extract YouTube Video ID
$parsedUrl = parse_url($url);
parse_str($parsedUrl['query'] ?? '', $queryParams);
$videoId = $queryParams['v'] ?? null;

if (!$videoId) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid YouTube URL"]);
    exit();
}

// Construct Clean YouTube URL
$cleanUrl = "https://www.youtube.com/watch?v=" . $videoId;

// Get video title using yt-dlp
$videoTitle = shell_exec("yt-dlp --get-title " . escapeshellarg($cleanUrl));

if (!$videoTitle) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch video title"]);
    exit();
}

$safeTitle = trim(preg_replace('/[\/:*?"<>|]/', '', $videoTitle));
$outputFile = "downloads/" . $safeTitle . ".mp3";

// Serve the file if it already exists
if (file_exists($outputFile)) {
    header('Content-Type: audio/mpeg');
    header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    exit();
}

// Create the downloads directory if it doesn't exist
if (!is_dir('downloads')) {
    mkdir('downloads', 0777, true);
}

// Download the MP3
$command = sprintf(
    'yt-dlp -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o %s %s',
    escapeshellarg($outputFile),
    escapeshellarg($cleanUrl)
);
shell_exec($command);

if (!file_exists($outputFile)) {
    http_response_code(500);
    echo json_encode(["error" => "MP3 file could not be created"]);
    exit();
}

// Update database
$updateSql = "UPDATE downloads SET downloaded = 1 WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);

if ($updateStmt) {
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    $updateStmt->close();
}

// Serve the MP3 file
header('Content-Type: audio/mpeg');
header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
header('Content-Length: ' . filesize($outputFile));
readfile($outputFile);
exit();

?>
