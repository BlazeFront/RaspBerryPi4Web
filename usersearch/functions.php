<?php
// functions.php - Contains helper functions

function fetchAltAccounts($username) {
    $url = "https://namemc.com/search?q=" . urlencode($username);
    
    // Fetch and parse the page (simplified example)
    $html = file_get_contents($url);
    preg_match_all('/<a href=\"\/profile\/(.*?)\"/', $html, $matches);
    
    return array_unique($matches[1]);
}

function searchUsername($username) {
    $query = urlencode($username . " site:reddit.com OR site:twitter.com OR site:facebook.com OR site:instagram.com");
    $searchUrl = "https://www.google.com/search?q=" . $query;
    
    return [['title' => 'Google Search', 'link' => $searchUrl]];
}
?>