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

// Fetch all entries (regardless of the downloaded state)
$sql = "SELECT id, url FROM downloads";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }

    // Return entries as JSON
    echo json_encode($entries);
} else {
    echo json_encode([]); // Return empty array if no entries found
}

$conn->close();
?>
