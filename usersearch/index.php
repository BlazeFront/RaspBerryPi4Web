<?php
function crawlWebForUsername($username) {
    // Define search engine URLs for crawling.
    $searchEngines = [
        "https://html.duckduckgo.com/html?q=" . urlencode($username),
        "https://search.yahoo.com/search?p=" . urlencode($username),
        "https://www.google.com/search?q=" . urlencode($username)
    ];

    $results = [];
    // Loop through each search engine URL
    foreach ($searchEngines as $engine) {
        // Fetch the HTML content of the search engine results page.
        $html = @file_get_contents($engine);
        if (!$html) continue;  // If the HTML couldn't be fetched, skip this engine.

        // Use regex to extract all links from the HTML.
        preg_match_all('/<a href=\"(https?:\/\/[^"]+)\"/', $html, $matches, PREG_PATTERN_ORDER);

        // Loop through the extracted links and add them to the results if they aren't from the search engines themselves.
        foreach ($matches[1] as $link) {
            // Avoid links from the search engines' own pages (i.e., DuckDuckGo, Yahoo)
            if (strpos($link, 'duckduckgo.com') === false && strpos($link, 'yahoo.com') === false) {
                $results[] = ["title" => "Found on: " . parse_url($link, PHP_URL_HOST), "link" => $link];
            }
        }
    }

    return $results;
}

if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);
    $url = "https://crafty.gg/@$query";
    
    $html = file_get_contents($url);
    
    if ($html !== false) {
        preg_match_all('/<a href="\/players\?search=([^"]+)">[\d]+\. <b>([^<]+)<\/b><\/a>/', $html, $matches);
        $usernames = $matches[2] ?? [];
    } else {
        $usernames = ['Error fetching data'];
    }

    // Now crawl the web for each username.
    $crawlResults = [];
    foreach ($usernames as $username) {
        $crawlResults[$username] = crawlWebForUsername($username);
    }
} else {
    $usernames = [];
    $crawlResults = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Username Search</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .search-box { width: 400px; padding: 10px; }
        .result { margin-top: 20px; }
        .crawl-results { margin-top: 20px; }
        .crawl-link { margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="GET">
        <input type="text" name="q" class="search-box" placeholder="Search usernames..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <button type="submit">Search</button>
    </form>
    
    <div class="result">
        <?php if (!empty($usernames)): ?>
            <h3>Usernames Found:</h3>
            <ul>
                <?php foreach ($usernames as $username): ?>
                    <li><?php echo htmlspecialchars($username); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <div class="crawl-results">
        <?php if (!empty($crawlResults)): ?>
            <h3>Web Crawling Results:</h3>
            <?php foreach ($crawlResults as $username => $results): ?>
                <h4>Results for "<?php echo htmlspecialchars($username); ?>":</h4>
                <?php if (!empty($results)): ?>
                    <ul>
                        <?php foreach ($results as $result): ?>
                            <li class="crawl-link">
                                <a href="<?php echo htmlspecialchars($result['link']); ?>" target="_blank"><?php echo htmlspecialchars($result['title']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No results found for <?php echo htmlspecialchars($username); ?>.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
