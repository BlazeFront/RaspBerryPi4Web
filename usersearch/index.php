<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to fetch content using cURL with improved headers and timeout handling
function fetchContentUsingCurl($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,  // Set timeout
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || $httpCode >= 400) {
        return false; // Return false on error or bad response
    }
    
    return $response;
}

// Function to crawl search engines for username mentions
function crawlWebForUsername($username) {
    $searchEngines = [
        "https://html.duckduckgo.com/html?q=" . urlencode($username),
        "https://search.yahoo.com/search?p=" . urlencode($username),
        "https://www.google.com/search?q=" . urlencode($username)
    ];

    $results = [];

    foreach ($searchEngines as $engine) {
        $html = fetchContentUsingCurl($engine);
        if (!$html) continue;

        // Extract URLs from search results using different patterns
        preg_match_all('/<a href="(https?:\/\/[^"]+)"/', $html, $matches, PREG_PATTERN_ORDER);

        foreach ($matches[1] as $link) {
            // Ensure the link is valid and not pointing back to the search engine itself
            if (
                filter_var($link, FILTER_VALIDATE_URL) &&
                !strpos($link, 'duckduckgo.com') &&
                !strpos($link, 'yahoo.com') &&
                !strpos($link, 'google.com')
            ) {
                $results[] = [
                    "title" => htmlspecialchars($link),
                    "link" => $link
                ];
            }
        }
    }

    return $results;
}

// Fetch past usernames from crafty.gg
function fetchUsernames($username) {
    $url = "https://crafty.gg/@$username";
    $html = fetchContentUsingCurl($url);
    
    if (!$html) {
        return ['Error fetching data'];
    }

    preg_match_all('/<a href="\/players\?search=([^"]+)">[\d]+\. <b>([^<]+)<\/b><\/a>/', $html, $matches);
    return $matches[2] ?? ['No usernames found'];
}

// Process user input and search for usernames
$username = $_GET['q'] ?? '';
$usernames = $username ? fetchUsernames($username) : [];
$searchResults = [];

foreach ($usernames as $user) {
    $searchResults = array_merge($searchResults, crawlWebForUsername($user));
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Username Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .search-box { width: 400px; padding: 10px; }
        .result { margin-top: 20px; }
    </style>
</head>
<body>
    <form method="GET">
        <input type="text" name="q" class="search-box" placeholder="Enter Minecraft Username..." value="<?php echo htmlspecialchars($username); ?>">
        <button type="submit">Search</button>
    </form>

    <div class="result">
        <?php if (!empty($usernames)): ?>
            <h2>Past Usernames:</h2>
            <ul>
                <?php foreach ($usernames as $name): ?>
                    <li><?php echo htmlspecialchars($name); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($searchResults)): ?>
            <h2>Web Mentions & Forum Profiles:</h2>
            <ul>
                <?php foreach ($searchResults as $result): ?>
                    <li><a href="<?php echo htmlspecialchars($result['link']); ?>" target="_blank"><?php echo htmlspecialchars($result['title']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No relevant mentions found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
