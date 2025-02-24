<?php
// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database credentials
$servername = "localhost";
$username = "root";
$password = "Mr53st52!1337";
$dbname = "downloads";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['url']) && isset($data['downloaded'])) {
    $url = $data['url'];
    $downloaded = false;
    $name = "Unknown Title"; // Default title if yt-dlp fails

    // Parse the URL and get the video ID
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'] ?? '', $queryParams);
    $videoId = $queryParams['v'] ?? null;

    if ($videoId) {
        $cleanUrl = "https://www.youtube.com/watch?v=" . $videoId;

        // Execute yt-dlp and capture the output and errors
        $command = "yt-dlp --no-cache-dir --get-title " . escapeshellarg($cleanUrl);
        $videoTitle = shell_exec($command);

        // Check for errors in yt-dlp execution
        if ($videoTitle === null || trim($videoTitle) === "") {
            error_log("yt-dlp command failed");
            $videoTitle = "Unknown Title";
        }

        $videoTitle = trim($videoTitle);
        $safeTitle = preg_replace('/[\/:*?"<>|]/', '', $videoTitle); // Sanitize title

        // Set the name to the safe title
        $name = $safeTitle;
    }

    // Check for existing entries with the same name
    $checkSql = "SELECT id, downloaded FROM downloads WHERE name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Existing entry found
        $checkStmt->bind_result($existingId, $existingDownloaded);
        $idsToKeep = [];
        $downloadedState = null;

        while ($checkStmt->fetch()) {
            $idsToKeep[] = $existingId;
            $downloadedState = $existingDownloaded; // Transfer the downloaded state
        }

        // Remove all but one entry
        if (count($idsToKeep) > 1) {
            $idsToRemove = array_slice($idsToKeep, 1); // Remove all but the first
            $placeholders = implode(',', array_fill(0, count($idsToRemove), '?'));
            $removeSql = "DELETE FROM downloads WHERE id IN ($placeholders)";
            $removeStmt = $conn->prepare($removeSql);
            $removeStmt->bind_param(str_repeat('i', count($idsToRemove)), ...$idsToRemove);
            $removeStmt->execute();
            $removeStmt->close();
        }

        // Get the next auto-increment ID
        $nextIdResult = $conn->query("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'downloads'");
        $nextIdRow = $nextIdResult->fetch_assoc();
        $nextId = $nextIdRow['AUTO_INCREMENT'];

        // Update the remaining entry's ID and other details
        $updateSql = "UPDATE downloads SET id = ?, downloaded = ?, url = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iisi", $nextId, $downloadedState, $url, $idsToKeep[0]); // Use transferred downloaded state
        if ($updateStmt->execute()) {
            // Adjust the auto-increment value to skip the manually set ID
            $conn->query("ALTER TABLE downloads AUTO_INCREMENT = " . ($nextId + 1));
            echo json_encode(["success" => true, "message" => "Existing entry updated with a new ID and state."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating entry: " . $updateStmt->error]);
        }
        $updateStmt->close();
    } else {
        // Insert as a new entry if no existing entry found
        $insertSql = "INSERT INTO downloads (url, downloaded, name) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sis", $url, $downloaded, $name); // "sis" = string, integer, string

        if ($insertStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Entry added successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error adding entry: " . $insertStmt->error]);
        }

        $insertStmt->close();
    }

    $checkStmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid input data."]);
}

$conn->close();
