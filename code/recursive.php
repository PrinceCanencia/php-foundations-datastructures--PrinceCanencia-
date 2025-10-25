<?php

$booksFile = __DIR__ . '/books.json';
if (!file_exists($booksFile)) {
    die("Error: books.json not found.");
}

$booksData = json_decode(file_get_contents($booksFile), true);
if (!is_array($booksData)) {
    die("Error: Invalid books.json format.");
}

// 🔹 Function to find the best matching local image (.jpg)
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

        // Use the best match if similarity > 40%
        if ($bestMatch && $highestScore > 40) {
            return $bestMatch;
        }
    }

    // fallback image
    return 'images/picture.jpg';
}

// 🔹 Recursive display function
function displayLibrary($library, $indent = 0) {
    $space = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $indent);
    foreach ($library as $key => $value) {
        if (is_array($value)) {
            // Book item
            if (isset($value['title']) && isset($value['author'])) {
                $imgPath = getBookImage($value['title']);
                echo "{$space}📖 <strong>" . htmlspecialchars($value['title']) . "</strong><br>";
                echo "{$space}&nbsp;&nbsp;👤 Author: " . htmlspecialchars($value['author']) . "<br>";
                echo "{$space}&nbsp;&nbsp;🌍 Country: " . htmlspecialchars($value['country']) . "<br>";
                echo "{$space}&nbsp;&nbsp;🗣️ Language: " . htmlspecialchars($value['language']) . "<br>";
                echo "{$space}&nbsp;&nbsp;📅 Year: " . htmlspecialchars($value['year']) . "<br>";
                echo "{$space}&nbsp;&nbsp;📖 Pages: " . intval($value['pages']) . "<br>";
                echo "{$space}&nbsp;&nbsp;<img src='" . htmlspecialchars($imgPath) . "' alt='" . htmlspecialchars($value['title']) . "' style='max-width:100px;border-radius:6px;margin-top:5px;'><br><br>";
            } 
            // Nested group (Language / Country)
            else {
                echo "{$space}<strong>📚 " . htmlspecialchars($key) . "</strong><br>";
                displayLibrary($value, $indent + 1);
            }
        } else {
            echo "{$space}- " . htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
        }
    }
}

// 🔹 Group books by Language → Country
$library = [];
foreach ($booksData as $book) {
    $lang = $book['language'] ?? 'Unknown';
    $country = $book['country'] ?? 'Unknown';
    $library[$lang][$country][] = $book;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recursive Library Display</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, sans-serif; background:#f4f8ff; padding:30px; max-width:900px; margin:auto; }
h1 { text-align:center; }
div.library { font-family: monospace; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
img { box-shadow:0 1px 3px rgba(0,0,0,0.1); }
</style>
</head>
<body>
<h1>📚 Recursive Library Display</h1>
<div class="library">
<?php displayLibrary($library); ?>
</div>
</body>
</html>
