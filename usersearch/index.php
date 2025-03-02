<?php
// Function to fetch past usernames from crafty.gg
function fetchUsernames($username) {
    $url = "https://crafty.gg/@$username";
    $html = fetchURL($url);

    if (!$html) {
        return ['Error fetching data'];
    }

    preg_match_all('/<a href="\/players\?search=([^"]+)">(\d+)\. <b>([^<]+)<\/b><\/a>/', $html, $matches);
    return $matches[3] ?? ['No usernames found'];
}

// Function to fetch content using cURL
function fetchURL($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36");
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "<p style='color: red;'>cURL Error: $error</p>";
        return false;
    }

    return $response;
}

// Function to crawl search engines for mentions of a username
function crawlWebForUsername($username) {
    $searchEngines = [
        "https://html.duckduckgo.com/html?q=" . urlencode($username),
        "https://search.yahoo.com/search?p=" . urlencode($username),
        "https://www.google.com/search?q=" . urlencode($username)
    ];

    $results = [];

    foreach ($searchEngines as $engine) {
        $html = fetchURL($engine);
        if (!$html) {
            continue;
        }

        preg_match_all('/<a href="(https?:\/\/[^"]+)"/', $html, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[1] as $link) {
            if (strpos($link, 'duckduckgo.com') === false && strpos($link, 'yahoo.com') === false && strpos($link, 'google.com') === false) {
                $results[] = [
                    "title" => $link,
                    "link" => $link
                ];
            }
        }
    }

    return $results;
}

// Handle user input
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
