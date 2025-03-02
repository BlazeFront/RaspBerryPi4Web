<?php
function fetchContentUsingCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

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
        // Fetch the HTML content of the search engine results page using cURL
        $html = fetchContentUsingCurl($engine);
        if (!$html) continue;  // If the HTML couldn't be fetched, skip this engine.

        // Now parse the actual search result links. We need to capture real results.
        if (strpos($engine, "duckduckgo.com") !== false) {
            // DuckDuckGo result extraction
            preg_match_all('/<a class="result__a" href="([^"]+)">/', $html, $matches, PREG_PATTERN_ORDER);
        } elseif (strpos($engine, "yahoo.com") !== false) {
            // Yahoo result extraction
            preg_match_all('/<a class="d-of-v1" href="([^"]+)"/', $html, $matches, PREG_PATTERN_ORDER);
        } elseif (strpos($engine, "google.com") !== false) {
            // Google result extraction
            preg_match_all('/<a href="\/url\?q=([^"&]+)&amp;/i', $html, $matches, PREG_PATTERN_ORDER);
        }

        // Loop through the extracted links and add them to the results
        foreach ($matches[1] as $link) {
            // Ensure we only include real web results
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                // Fetch the page content of the result link
                $pageHtml = fetchContentUsingCurl($link);
                
                // If the page was successfully fetched
                if ($pageHtml) {
                    // Extract the page title using regex
                    preg_match('/<title>([^<]+)<\/title>/', $pageHtml, $titleMatch);
                    $title = $titleMatch[1] ?? 'No title found';

                    // Extract a meta description using regex (if available)
                    preg_match('/<meta name="description" content="([^"]+)"/', $pageHtml, $descMatch);
                    $description = $descMatch[1] ?? 'No description found';

                    // Add this page's info to the results array
                    $results[] = [
                        'title' => htmlspecialchars($title), 
                        'link' => $link,
                        'description' => htmlspecialchars($description)
                    ];
                }
            }
        }
    }

    return $results;
}

if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);
    $url = "https://crafty.gg/@$query";
    
    $html = fetchContentUsingCurl($url);
    
    if ($html !== false) {
        preg_match_all('/<a href="\/players\?search=([^"]+)">[\d]+\. <b>([^<]+)<\/b><\/a>/', $html, $matches);
        $usernames = $matches[2] ?? [];
    } else {
        $usernames = ['Error fetching data'];
    }

    // Now crawl the web for each username.
    $allResults = [];  // This will hold both usernames and web mentions
    foreach ($usernames as $username) {
        // Add username to the top of the list
        $allResults[] = ["title" => "Username: " . htmlspecialchars($username), "link" => "#", 'description' => ''];

        // Crawl the web for the username mentions
        $webResults = crawlWebForUsername($username);
        if (!empty($webResults)) {
            foreach ($webResults as $result) {
                // Add the web mention results under the username
                $allResults[] = $result;
            }
        } else {
            // If no web results found, add a message
            $allResults[] = ["title" => "No relevant mentions found for: " . htmlspecialchars($username), "link" => "#", 'description' => ''];
        }
    }
} else {
    $usernames = [];
    $allResults = [];
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
        <?php if (!empty($allResults)): ?>
            <h3>Search Results:</h3>
            <ul>
                <?php foreach ($allResults as $result): ?>
                    <li>
                        <?php if ($result['link'] == '#'): ?>
                            <strong><?php echo htmlspecialchars($result['title']); ?></strong>
                            <p><?php echo htmlspecialchars($result['description']); ?></p>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($result['link']); ?>" target="_blank">
                                <strong><?php echo htmlspecialchars($result['title']); ?></strong>
                            </a>
                            <p><?php echo htmlspecialchars($result['description']); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
