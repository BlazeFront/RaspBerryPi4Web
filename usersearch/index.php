<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if cURL is available on the server
if (!function_exists('curl_init')) {
    die('cURL is not enabled on this server.');
}

// Function to fetch content using cURL with better error handling
function fetchContentUsingCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    // Execute the request
    $response = curl_exec($ch);

    // Check for cURL errors
    if(curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    // Close the cURL session
    curl_close($ch);

    return $response;
}

// Function to crawl the web for username
function crawlWebForUsername($username) {
    $searchEngines = [
        "https://html.duckduckgo.com/html?q=" . urlencode($username),
        "https://search.yahoo.com/search?p=" . urlencode($username),
        "https://www.google.com/search?q=" . urlencode($username)
    ];

    $results = [];

    // Loop through search engines
    foreach ($searchEngines as $engine) {
        // Fetch the HTML content of the search engine page
        $html = fetchContentUsingCurl($engine);
        if (!$html) continue;

        // Parse the result links from the HTML
        if (strpos($engine, "duckduckgo.com") !== false) {
            preg_match_all('/<a class="result__a" href="([^"]+)">/', $html, $matches, PREG_PATTERN_ORDER);
        } elseif (strpos($engine, "yahoo.com") !== false) {
            preg_match_all('/<a class="d-of-v1" href="([^"]+)"/', $html, $matches, PREG_PATTERN_ORDER);
        } elseif (strpos($engine, "google.com") !== false) {
            preg_match_all('/<a href="\/url\?q=([^"&]+)&amp;/i', $html, $matches, PREG_PATTERN_ORDER);
        }

        // Add matches to results
        foreach ($matches[1] as $link) {
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $results[] = $link;
            }
        }
    }

    return $results;
}

// Process the query parameter and fetch data
if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);
    $url = "https://crafty.gg/@$query";

    // Fetch the HTML content of the crafty.gg page
    $html = fetchContentUsingCurl($url);

    if ($html !== false) {
        preg_match_all('/<a href="\/players\?search=([^"]+)">[\d]+\. <b>([^<]+)<\/b><\/a>/', $html, $matches);
        $usernames = $matches[2] ?? [];
    } else {
        $usernames = ['Error fetching data'];
    }

    // Crawl the web for mentions of each username
    $allResults = [];
    foreach ($usernames as $username) {
        // Add the username itself to the results list
        $allResults[] = ["title" => "Username: " . htmlspecialchars($username), "link" => "#"];

        // Fetch web mentions for the username
        $webResults = crawlWebForUsername($username);
        if (!empty($webResults)) {
            foreach ($webResults as $result) {
                $allResults[] = ["title" => $result, "link" => $result];
            }
        } else {
            $allResults[] = ["title" => "No relevant mentions found for: " . htmlspecialchars($username), "link" => "#"];
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
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($result['link']); ?>" target="_blank">
                                <strong><?php echo htmlspecialchars($result['title']); ?></strong>
                            </a>
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
