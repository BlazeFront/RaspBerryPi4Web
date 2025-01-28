<?php
$servername = "localhost";
$username = "root";
$password = "Mr53st52!1337";
$dbname = "downloads";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT COUNT(*) AS total_entries FROM downloads";
$result = $conn->query($sql);

// Fetch the result
$row = $result->fetch_assoc();
$total_entries = $row['total_entries'];

$conn->close();

echo $total_entries; // Output the total count
?>
