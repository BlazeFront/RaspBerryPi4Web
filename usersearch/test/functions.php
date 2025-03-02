<?php
// functions.php - Enhanced username search functions

function fetchAltAccounts($username) {
    $url = "https://namemc.com/search?q=" . urlencode($username);
    
    $html = @file_get_contents($url);
    if (!$html) return [];

    preg_match_all('/<a class=\"card\\" href=\"\/profile\/(.*?)\"/', $html, $matches);

    return array_unique($matches[1]);
}

function crawlWebForUsername($username) {
    $searchEngines = [
        "https://html.duckduckgo.com/html?q=" . urlencode($username), 
        "https://search.yahoo.com/search?p=" . urlencode($username)
    ];

    $results = [];
    foreach ($searchEngines as $engine) {
        $html = @file_get_contents($engine);
        if (!$html) continue;

        preg_match_all('/<a href=\"(https?:\/\/[^"]+)\"/', $html, $matches, PREG_PATTERN_ORDER);

        foreach ($matches[1] as $link) {
            if (strpos($link, 'duckduckgo.com') === false && strpos($link, 'yahoo.com') === false) {
                $results[] = ["title" => "Found on: " . parse_url($link, PHP_URL_HOST), "link" => $link];
            }
        }
    }

    return $results;
}
?>