<?php

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

// Auto-sync database dump if not yet executed
$dbFlag = __DIR__ . '/storage/framework/cache/db_synced_v2.flag';
if (!file_exists($dbFlag)) {
    try {
        $host = 'sql209.infinityfree.com';
        $db   = 'if0_43310880_iqc';
        $user = 'if0_43310880';
        $pass = 'fh8Qtwy5mu1uFpA';
        $sqlFile = __DIR__ . '/database_mysql_dump.sql';
        
        if (file_exists($sqlFile)) {
            $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
            ]);
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
            
            // Clear view cache so new views compile fresh
            $views = glob(__DIR__ . '/storage/framework/views/*');
            if ($views) {
                foreach ($views as $vf) {
                    if (is_file($vf)) @unlink($vf);
                }
            }
            @file_put_contents($dbFlag, date('Y-m-d H:i:s'));
        }
    } catch (Throwable $e) {
        @file_put_contents(__DIR__ . '/storage/logs/sync_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Check if vendor/autoload.php exists directly in htdocs
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<div style='font-family:sans-serif;padding:30px;max-width:700px;margin:40px auto;border:1px solid #f87171;border-radius:8px;background:#fef2f2;'>";
    echo "<h2 style='color:#dc2626;margin-top:0;'>❌ Vendor Folder Missing in htdocs</h2>";
    echo "<p>Could not find <code>vendor/autoload.php</code> inside <code>" . __DIR__ . "</code>.</p>";
    echo "<p><b>Action:</b> In FileZilla, move all your Laravel folders (<code>app</code>, <code>vendor</code>, <code>storage</code>, <code>bootstrap</code>, <code>config</code>, <code>routes</code>, <code>.env</code>) directly inside <b><code>htdocs/</code></b>.</p>";
    echo "</div>";
    exit;
}

// Register the Composer autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

// Set public path to htdocs
$app->usePublicPath(__DIR__);

$app->handleRequest(\Illuminate\Http\Request::capture());

