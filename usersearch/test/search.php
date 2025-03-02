<?php
// search.php - Processes the username search and finds linked accounts dynamically
include 'functions.php';

if (isset($_GET['username'])) {
    $username = htmlspecialchars($_GET['username']);
    
    echo "<h1>Results for: $username</h1>";

    // Find alt accounts using NameMC
    echo "<h2>Possible Alt Accounts:</h2>";
    $altAccounts = fetchAltAccounts($username);
    if (!empty($altAccounts)) {
        echo "<ul>";
        foreach ($altAccounts as $account) {
            echo "<li>$account</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No linked accounts found.</p>";
    }

    // Crawl the web for mentions of the username
    echo "<h2>Web Mentions & Forum Profiles:</h2>";
    $results = crawlWebForUsername($username);
    if (!empty($results)) {
        echo "<ul>";
        foreach ($results as $result) {
            echo "<li><a href='{$result['link']}' target='_blank'>{$result['title']}</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No relevant mentions found.</p>";
    }
} else {
    echo "<p>No username provided.</p>";
}
?>