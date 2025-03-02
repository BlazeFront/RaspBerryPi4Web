<?php
if (isset($_GET['q'])) {
    $query = urlencode($_GET['q']);
    $url = "https://namemc.com/search?q=$query";
    
    $html = file_get_contents($url);
    
    if ($html !== false) {
        preg_match_all('/<a class="" translate="no" href="\/search\?q=([^"]+)">([^"]+)<\/a>/', $html, $matches);
        $usernames = $matches[2] ?? [];
    } else {
        $usernames = ['Error fetching data'];
    }
} else {
    $usernames = [];
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
        <?php if (!empty($usernames)): ?>
            <ul>
                <?php foreach ($usernames as $username): ?>
                    <li><?php echo htmlspecialchars($username); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
