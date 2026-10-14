<?php
$dirs = [
    'resources/views/auth',
    'resources/views/layouts',
    'resources/views/dashboard',
    'resources/views/inspections',
    'resources/views/supervisor',
    'resources/views/components',
    'resources/views/master-data',
    'resources/views/poka-yoke',
];

foreach ($dirs as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $fixed = str_replace('material-symbols-Outlined', 'material-symbols-outlined', $content);
        if ($content !== $fixed) {
            file_put_contents($file, $fixed);
            echo "Fixed: $file\n";
        } else {
            echo "OK (already fixed): $file\n";
        }
    }
}
echo "\nAll done.\n";
