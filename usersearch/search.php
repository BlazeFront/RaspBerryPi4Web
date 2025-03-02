<?php
// search.php - Processes the search query
include 'functions.php';

if (isset($_GET['username'])) {
    $username = htmlspecialchars($_GET['username']);
    
    echo "<h1>Results for: $username</h1>";
    
    // Fetch related accounts from NameMC
    $altAccounts = fetchAltAccounts($username);
    echo "<h2>Possible Alt Accounts:</h2>";
    echo "<ul>";
    foreach ($altAccounts as $account) {
        echo "<li>$account</li>";
    }
    echo "</ul>";
    
    // Fetch search results from web
    $searchResults = searchUsername($username);
    echo "<h2>Search Results:</h2>";
    echo "<ul>";
    foreach ($searchResults as $result) {
        echo "<li><a href='{$result['link']}'>{$result['title']}</a></li>";
    }
    echo "</ul>";
} else {
    echo "<p>No username provided.</p>";
}
?>