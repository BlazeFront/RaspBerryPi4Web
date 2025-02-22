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

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='remove-field' onclick='removeFromDatabase(\"" . htmlspecialchars($row['id'] ?? 'NULL') . "\", this)'> <i class='fa-solid fa-angles-left'></i> </td>";
        echo "<td class='url-cell' onclick='copyToClipboard(\"" . htmlspecialchars($row['name'] ?? 'NULL') . "\")'>" . htmlspecialchars($row['name'] ?? 'NULL') . "</td>";
        echo "<td class='icon-cell' style='cursor:pointer' onclick='copyToClipboard(\"" . htmlspecialchars($row['url']) . "\")'> <i class='fa-regular fa-copy'></i> </td>";
        echo "<td>" . ($row['downloaded'] ? "<i style='cursor:pointer' class='fa-solid fa-square-check' onclick='toggleDownloaded(" . htmlspecialchars($row['id']) . ", this)'></i>" : "<i class='fa-regular fa-square' onclick='toggleDownloaded(" . htmlspecialchars($row['id']) . ", this)'></i>") . "</td>";
        echo "<td class='icon-cell'><a href='" . htmlspecialchars($row['url']) . "' target='_blank'><i class='fab fa-youtube'></i></a></td>";
        echo "<td value='\"" . htmlspecialchars($row['id'] ?? 'NULL') . "\"' class='download-icon'><a href='javascript:void(0);' onclick='markDownloaded(" . htmlspecialchars($row['id']) . ", \"" . htmlspecialchars($row['name']) . "\", this)'><i class='fas fa-download'></i></a></td>";
        echo "</tr>";

        // Extract video ID from the URL
        $url = $row['url'];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams); // Extract query params
        if (isset($queryParams['v'])) {
            $videoIds[] = $queryParams['v']; // Add to video ID array
        }
    }
} else {
    echo "<tr><td colspan='6'>No data found</td></tr>";
}

// Generate YouTube playlist link
$playlistLink = "";
if (!empty($videoIds)) {
    $playlistLink = "https://www.youtube.com/watch_videos?video_ids=" . implode(",", $videoIds);
}

// Send the playlist link as a JavaScript variable
echo "<script>window.playlistLink = '" . htmlspecialchars($playlistLink, ENT_QUOTES, 'UTF-8') . "';</script>";

$conn->close();
?>
