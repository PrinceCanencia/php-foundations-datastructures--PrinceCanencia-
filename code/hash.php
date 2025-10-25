<?php

$booksFile = __DIR__ . '/books.json';
if (!file_exists($booksFile)) {
    die("Error: books.json not found.");
}

$booksData = json_decode(file_get_contents($booksFile), true);
if (!is_array($booksData)) {
    die("Error: books.json is invalid.");
}

$searchTitle = trim($_GET['title'] ?? '');
$foundBook = null;

if ($searchTitle !== '') {
    foreach ($booksData as $book) {
        if (strcasecmp($book['title'], $searchTitle) === 0) {
            $foundBook = $book;
            break;
        }
    }
}


function getBookImage($title) {
    $baseDir = __DIR__ . '/images/';
    $safeTitle = strtolower(preg_replace('/[^a-z0-9]/', '', $title)); 

    if (is_dir($baseDir)) {
        $files = scandir($baseDir);
        $bestMatch = null;
        $highestScore = 0;

        foreach ($files as $file) {
            if (preg_match('/\.jpg$/i', $file)) {
                $cleanName = strtolower(preg_replace('/[^a-z0-9]/', '', pathinfo($file, PATHINFO_FILENAME)));
                similar_text($safeTitle, $cleanName, $percent);
                if ($percent > $highestScore) {
                    $highestScore = $percent;
                    $bestMatch = 'images/' . $file;
                }
            }
        }

        
        if ($bestMatch && $highestScore > 40) {
            return $bestMatch;
        }
    }

    
    return 'images/picture.jpg';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Search (Local Images)</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, sans-serif; background:#f0f6ff; padding:30px; max-width:600px; margin:auto; }
h1 { text-align:center; }
form { text-align:center; margin-bottom:20px; }
input[type=text] { width:70%; padding:8px; border-radius:6px; border:1px solid #aaa; }
button { padding:8px 14px; border:none; background:#0066ff; color:white; border-radius:6px; cursor:pointer; }
button:hover { background:#0052cc; }
.card { background:white; padding:15px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
img { max-width:180px; height:auto; border-radius:6px; margin-bottom:10px; display:block; }
.small { color:#555; font-size:0.9em; }
</style>
</head>
<body>
<h1>📚 Search Book by Title</h1>

<form method="get">
    <input type="text" name="title" placeholder="Enter book title..." value="<?php echo htmlspecialchars($searchTitle); ?>" required>
    <button type="submit">Search</button>
</form>

<?php if ($searchTitle !== ''): ?>
    <?php if ($foundBook): ?>
        <div class="card">
            <?php $imagePath = getBookImage($foundBook['title']); ?>
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($foundBook['title']); ?>">

            <h2><?php echo htmlspecialchars($foundBook['title']); ?></h2>
            <div class="small">👤 Author: <?php echo htmlspecialchars($foundBook['author']); ?></div>
            <div class="small">🌍 Country: <?php echo htmlspecialchars($foundBook['country']); ?></div>
            <div class="small">🗣️ Language: <?php echo htmlspecialchars($foundBook['language']); ?></div>
            <div class="small">📖 Pages: <?php echo intval($foundBook['pages']); ?></div>
            <div class="small">📅 Year: <?php echo htmlspecialchars($foundBook['year']); ?></div>
        </div>
    <?php else: ?>
        <div class="card"><strong>❌ Book not found.</strong></div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
