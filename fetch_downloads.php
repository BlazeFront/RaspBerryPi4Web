<?php
// Update yt-dlp to the latest version
shell_exec("yt-dlp -U");
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

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='remove-field' onclick='removeFromDatabase(\"" . htmlspecialchars($row['id'] ?? 'NULL') . "\", this)'> <i class='fa-solid fa-angles-left'></i> </td>";
        echo "<td class='url-cell' onclick='copyToClipboard(\"" . htmlspecialchars($row['name'] ?? 'NULL') . "\")'>" . htmlspecialchars($row['name'] ?? 'NULL') . "</td>";
        echo "<td class='icon-cell' style='cursor:pointer' onclick='copyToClipboard(\"" . htmlspecialchars($row['url']) . "\")'> <i class='fa-regular fa-copy'></i> </td>";
        echo "<td>" . ($row['downloaded'] ? "<i style='cursor:pointer' class='fa-solid fa-square-check' onclick='toggleDownloaded(" . htmlspecialchars($row['id']) . ", this)'></i>" : "<i class='fa-regular fa-square' onclick='toggleDownloaded(" . htmlspecialchars($row['id']) . ", this)'></i>") . "</td>";
        echo "<td class='icon-cell'><a href='" . htmlspecialchars($row['url']) . "' target='_blank'><i class='fab fa-youtube'></i></a></td>";
        echo "<td value='\"" . htmlspecialchars($row['id'] ?? 'NULL') . "\"' class='download-icon'><a href='javascript:void(0);' onclick='markDownloaded(" . $row['id'] . ", this)'><i class='fas fa-download'></i></a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No data found</td></tr>";
}

$conn->close();
?>
