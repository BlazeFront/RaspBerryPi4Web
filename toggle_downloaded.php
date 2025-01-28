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

// Retrieve data from AJAX request
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id > 0) {
    // Toggle the downloaded state
    $sql = "UPDATE downloads SET downloaded = NOT downloaded WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        // Fetch the new state
        $result = $conn->query("SELECT downloaded FROM downloads WHERE id = $id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(['success' => true, 'downloaded' => $row['downloaded']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Entry not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
}

$conn->close();
?>
