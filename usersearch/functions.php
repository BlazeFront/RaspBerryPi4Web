<?php
// functions.php - Contains scraping functions for username search

function fetchAltAccounts($username) {
    $url = "https://namemc.com/search?q=" . urlencode($username);
    
    $html = @file_get_contents($url);
    if (!$html) return [];

    preg_match_all('/<a href=\"\/profile\/(.*?)\"/', $html, $matches);
    
    return array_unique($matches[1]);
}

function searchUsernameOnSites($username) {
    $sites = [
        "https://www.reddit.com/user/" . urlencode($username),
        "https://twitter.com/search?q=" . urlencode($username),
        "https://www.instagram.com/" . urlencode($username),
        "https://www.tiktok.com/@" . urlencode($username),
        "https://github.com/" . urlencode($username)
    ];

    $results = [];
    foreach ($sites as $site) {
        $results[] = ["title" => "Profile/mention on " . parse_url($site, PHP_URL_HOST), "link" => $site];
    }

    return $results;
}
?>