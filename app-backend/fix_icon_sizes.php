<?php
// Fix icon sizes in all Blade views
// Tailwind text-* classes don't work on Material Symbols, replace with icon-* helpers

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

// Pattern: <span class="material-symbols-outlined text-sm">  --> <span class="material-symbols-outlined icon-sm">
// text-xs  -> icon-sm (16px)
// text-sm  -> icon-sm (16px)
// text-base-> icon-md (20px)
// text-lg  -> icon-lg (24px)
// text-xl  -> icon-xl (28px)
// text-2xl -> icon-2xl (36px)
// text-3xl -> icon-2xl (36px)
// text-4xl -> icon-2xl (36px)

$patterns = [
    // Remove Tailwind text-size classes from inside material-symbols-outlined spans
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-xs([^"]*")/' => '$1 icon-sm$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-sm([^"]*")/' => '$1 icon-sm$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-base([^"]*")/' => '$1 icon-md$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-lg([^"]*")/' => '$1 icon-lg$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-xl([^"]*")/' => '$1 icon-xl$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-2xl([^"]*")/' => '$1 icon-2xl$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-3xl([^"]*")/' => '$1 icon-2xl$2',
    '/(<span\s+class="[^"]*material-symbols-outlined[^"]*)\s+text-4xl([^"]*")/' => '$1 icon-2xl$2',
];

$totalFixed = 0;
foreach ($dirs as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $new = $content;
        foreach ($patterns as $pattern => $replacement) {
            $new = preg_replace($pattern, $replacement, $new);
        }
        if ($new !== $content) {
            file_put_contents($file, $new);
            $totalFixed++;
            echo "Fixed icon sizes: $file\n";
        } else {
            echo "No icon size changes needed: $file\n";
        }
    }
}
echo "\nDone. Fixed $totalFixed files.\n";
