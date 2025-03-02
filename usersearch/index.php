<?php
// index.php - Main search page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Username Search</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <h1>Search for a Username</h1>
    <form action="search.php" method="GET">
        <input type="text" name="username" placeholder="Enter username" required>
        <button type="submit">Search</button>
    </form>
</body>
</html>