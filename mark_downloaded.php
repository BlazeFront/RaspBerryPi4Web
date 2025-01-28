<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "Mr53st52!1337";
$dbname = "downloads";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Get the video ID from the query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    // Fetch the URL for the video
    $sql = "SELECT url FROM downloads WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Database error: Unable to prepare statement. " . $conn->error);
        http_response_code(500);
        die("Database error: Unable to prepare statement.");
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($url);
        $stmt->fetch();

        // Parse the URL and clean it to keep only the video ID
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Extract the `v` parameter (video ID)
        $videoId = $queryParams['v'] ?? null;

        if (!$videoId) {
            error_log("Invalid YouTube URL: " . $url);
            http_response_code(500);
            echo "Error: Invalid YouTube URL.";
            exit();
        }

        // Construct a clean URL using only the video ID
        $cleanUrl = "https://www.youtube.com/watch?v=" . $videoId;

        // Get the sanitized video title
        $videoTitle = shell_exec("yt-dlp --no-cache-dir --get-title " . escapeshellarg($cleanUrl));
        if ($videoTitle === null || $videoTitle === "") {
            error_log("Failed to fetch video title for URL: " . $cleanUrl);
            http_response_code(500);
            echo "Error: Unable to fetch video title.";
            exit();
        }
        $videoTitle = trim($videoTitle);
        $safeTitle = preg_replace('/[\/:*?"<>|]/', '', $videoTitle);

        // Path for the output MP3 file
        $outputFile = "downloads/" . $safeTitle . ".mp3";

        // Create the downloads directory if it doesn't exist
        if (!is_dir('downloads')) {
            mkdir('downloads', 0777, true);
        }

        // Download the MP3 if it doesn't exist
        if (!file_exists($outputFile)) {
            $command = sprintf(
                'yt-dlp --no-cache-dir -f bestaudio --extract-audio --audio-format mp3 --audio-quality 0 -o %s %s',
                escapeshellarg($outputFile),
                escapeshellarg($cleanUrl)
            );
            $result = shell_exec($command);

            // Check for errors in shell execution
            if (!file_exists($outputFile)) {
                error_log("MP3 file could not be created. Command: " . $command);
                http_response_code(500);
                echo "Error: MP3 file could not be created.";
                exit();
            }
        }

        // Check if the MP3 exists
        if (file_exists($outputFile)) {
            // Mark the download as completed in the database
            $updateSql = "UPDATE downloads SET downloaded = 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);

            if ($updateStmt) {
                $updateStmt->bind_param("i", $id);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                error_log("Failed to prepare update statement: " . $conn->error);
            }

            // Serve the MP3 file for download
            header('Content-Type: audio/mpeg');
            header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
            header('Content-Length: ' . filesize($outputFile));
            readfile($outputFile);

            // Stop further execution
            exit();
        } else {
            error_log("MP3 file could not be found after supposed download: " . $outputFile);
            echo "Error: MP3 file could not be created.";
        }
    } else {
        error_log("No video found with ID: " . $id);
        echo "No video found with ID: " . htmlspecialchars($id);
    }

    $stmt->close();
} else {
    error_log("Invalid ID parameter provided.");
    echo "Invalid ID.";
}

$conn->close();
?>
