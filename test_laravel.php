<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Laravel Deployment Diagnostic</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;line-height:1.6;} .ok{color:green;} .err{color:red;font-weight:bold;}</style>";

$base = dirname(__DIR__); // /home/vol12_3/infinityfree.com/if0_43310880
$core = $base . '/laravel_core';

echo "<p><b>Parent Directory:</b> <code>{$base}</code></p>";
echo "<p><b>Target laravel_core:</b> <code>{$core}</code></p>";

// 1. Check laravel_core
if (is_dir($core)) {
    echo "<p class='ok'>✅ Folder <code>laravel_core</code> found!</p>";
} else {
    echo "<p class='err'>❌ Folder <code>laravel_core</code> NOT found in {$base}!</p>";
    echo "<p>Folders inside parent directory:</p><ul>";
    foreach (scandir($base) as $f) {
        if ($f != '.' && $f != '..') echo "<li>{$f}</li>";
    }
    echo "</ul>";
}

// 2. Check vendor/autoload.php
$autoload = $core . '/vendor/autoload.php';
if (file_exists($autoload)) {
    echo "<p class='ok'>✅ <code>vendor/autoload.php</code> found!</p>";
} else {
    echo "<p class='err'>❌ <code>vendor/autoload.php</code> missing!</p>";
}

// 3. Check .env
$envFile = $core . '/.env';
if (file_exists($envFile)) {
    echo "<p class='ok'>✅ <code>.env</code> file found!</p>";
} else {
    echo "<p class='err'>❌ <code>.env</code> file missing in <code>laravel_core/</code>!</p>";
}

// 4. Check storage writable
$storage = $core . '/storage';
if (is_writable($storage)) {
    echo "<p class='ok'>✅ <code>storage/</code> folder is writable!</p>";
} else {
    echo "<p class='err'>⚠️ <code>storage/</code> folder is NOT writable (set 775 or 777 in FileZilla)!</p>";
}

// 5. Test MySQL connection directly
echo "<h3>Database Connection Test:</h3>";
try {
    $mysqli = new mysqli('sql209.infinityfree.com', 'if0_43310880', 'fh8Qtwy5mu1uFpA', 'if0_43310880_iqc');
    if ($mysqli->connect_error) {
        echo "<p class='err'>❌ Database connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p class='ok'>✅ Connected to MySQL database <code>if0_43310880_iqc</code> successfully!</p>";
        $res = $mysqli->query("SHOW TABLES");
        echo "<p>Tables in database: <b>" . $res->num_rows . "</b> tables found.</p>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "<p class='err'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><b>Next:</b> Once all items above show green ✅, your <code>index.php</code> will run smoothly!</p>";

