<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "Mr53st52!1337";
$dbname = "downloads";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data sorted by ID in descending order
$sql = "SELECT id, name, url, downloaded FROM downloads ORDER BY id DESC";
$result = $conn->query($sql);

$videoIds = []; // Array to store video IDs
$rows = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;

        // Extract video ID from the URL
        $url = $row['url'];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams); // Extract query params
        if (isset($queryParams['v'])) {
            $videoIds[] = $queryParams['v']; // Add to video ID array
        }
    }
}

// Generate YouTube playlist link
$playlistLink = "";
if (!empty($videoIds)) {
    $playlistLink = "https://www.youtube.com/watch_videos?video_ids=" . implode(",", $videoIds);
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'tableRows' => $rows,
    'playlistLink' => $playlistLink
]);

$conn->close();
?>
