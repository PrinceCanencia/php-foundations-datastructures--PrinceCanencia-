<?php

require_once __DIR__ . '/BST.php'; 

if (!class_exists('BST')) {
    class Node {
        public $key; public $value; public $left; public $right;
        function __construct($k, $v) { $this->key = $k; $this->value = $v; }
    }
    class BST {
        public $root = null;
        function insert($key, $value) {
            $this->root = $this->insertNode($this->root, $key, $value);
        }
        private function insertNode($node, $key, $value) {
            if (!$node) return new Node($key, $value);
            $cmp = strcmp($key, $node->key);
            if ($cmp < 0) $node->left = $this->insertNode($node->left, $key, $value);
            elseif ($cmp > 0) $node->right = $this->insertNode($node->right, $key, $value);
            else $node->value = $value;
            return $node;
        }
        function find($key) {
            $node = $this->root;
            while ($node) {
                $cmp = strcmp($key, $node->key);
                if ($cmp === 0) return $node->value;
                $node = $cmp < 0 ? $node->left : $node->right;
            }
            return null;
        }
        function inorder() {
            $out = [];
            $this->inorderRec($this->root, $out);
            return $out;
        }
        private function inorderRec($node, &$out) {
            if (!$node) return;
            $this->inorderRec($node->left, $out);
            $out[] = ['key' => $node->key, 'value' => $node->value];
            $this->inorderRec($node->right, $out);
        }
    }
}


$booksFile = __DIR__ . '/books.json';
if (!file_exists($booksFile)) {
    die("Error: books.json not found.");
}
$booksData = json_decode(file_get_contents($booksFile), true);
if (!is_array($booksData)) {
    die("Error: Invalid books.json format.");
}


$bst = new BST();
foreach ($booksData as $book) {
    if (!empty($book['title'])) {
        $bst->insert($book['title'], $book);
    }
}


$searchTitle = trim($_GET['title'] ?? '');
$foundBook = null;
if ($searchTitle !== '') {
    $foundBook = $bst->find($searchTitle);
}


$inorderBooks = $bst->inorder();


function getBookImage($title) {
    $baseDir = __DIR__ . '/images/';
    $safeTitle = strtolower(preg_replace('/[^a-z0-9]/', '', $title));

    if (!is_dir($baseDir)) {
        return 'images/picture.jpg';
    }

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

    return 'images/picture.jpg'; 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Library System (BST)</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, sans-serif; background:#f0f6ff; padding:30px; max-width:1000px; margin:auto; }
h1 { text-align:center; }
form { text-align:center; margin-bottom:20px; }
input[type=text] { width:70%; padding:8px; border-radius:6px; border:1px solid #aaa; }
button { padding:8px 14px; border:none; background:#0066ff; color:white; border-radius:6px; cursor:pointer; }
button:hover { background:#0052cc; }
.card { background:white; padding:15px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.1); margin-bottom:20px; }
img { max-width:150px; height:auto; border-radius:6px; display:block; margin-bottom:10px; }
.book-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:15px; }
.small { color:#555; font-size:0.9em; }
</style>
</head>
<body>

<h1>📚 Library System — BST (by Title)</h1>

<form method="get">
    <input type="text" name="title" placeholder="Enter book title..." value="<?php echo htmlspecialchars($searchTitle); ?>">
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

<h2>📖 All Books (Alphabetically — Inorder Traversal)</h2>
<div class="book-grid">
<?php foreach ($inorderBooks as $entry): $b = $entry['value']; ?>
    <?php $imagePath = getBookImage($b['title']); ?>
    <div class="card">
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
        <strong><?php echo htmlspecialchars($b['title']); ?></strong>
        <div class="small">👤 <?php echo htmlspecialchars($b['author']); ?></div>
        <div class="small">📅 <?php echo htmlspecialchars($b['year']); ?></div>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
