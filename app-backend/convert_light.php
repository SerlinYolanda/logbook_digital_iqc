<?php
/**
 * Light Mode Converter for Logbook Digital IQC Portal
 * Converts all Tailwind dark classes to light mode equivalents
 */

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

// Map: dark Tailwind class => light Tailwind class
// Order matters: more specific first
$replacements = [
    // ─── HTML class="" attribute dark→light ───────────────────────────────

    // Body / page background
    'h-full bg-slate-950 text-slate-100'  => 'h-full bg-slate-50 text-slate-800',
    'bg-slate-950 text-slate-100'         => 'bg-slate-50 text-slate-800',
    'font-sans bg-slate-950 text-slate-100' => 'font-sans bg-slate-50 text-slate-800',

    // Backgrounds – absolute colors
    'bg-slate-950/90' => 'bg-white/95',
    'bg-slate-950/80' => 'bg-white/90',
    'bg-slate-950/70' => 'bg-white/85',
    'bg-slate-950/60' => 'bg-slate-50/90',
    'bg-slate-950'    => 'bg-slate-50',

    'bg-slate-900/95' => 'bg-white/98',
    'bg-slate-900/90' => 'bg-white/95',
    'bg-slate-900/80' => 'bg-white/90',
    'bg-slate-900/70' => 'bg-white/85',
    'bg-slate-900/60' => 'bg-white/80',
    'bg-slate-900/40' => 'bg-slate-50/60',
    'bg-slate-900'    => 'bg-white',

    'bg-slate-800/90' => 'bg-slate-100/90',
    'bg-slate-800/80' => 'bg-slate-100/80',
    'bg-slate-800/40' => 'bg-slate-50/60',
    'bg-slate-800/30' => 'bg-slate-50/50',
    'bg-slate-800'    => 'bg-slate-100',

    'bg-slate-700/60' => 'bg-slate-200/60',
    'bg-slate-700/50' => 'bg-slate-200/50',
    'bg-slate-700'    => 'bg-slate-200',

    // Hover backgrounds
    'hover:bg-slate-950/60' => 'hover:bg-slate-50',
    'hover:bg-slate-900/40' => 'hover:bg-slate-50',
    'hover:bg-slate-900'    => 'hover:bg-slate-50',
    'hover:bg-slate-800/80' => 'hover:bg-slate-100',
    'hover:bg-slate-800/60' => 'hover:bg-slate-100',
    'hover:bg-slate-800'    => 'hover:bg-slate-100',
    'hover:bg-slate-700/60' => 'hover:bg-slate-200',
    'hover:bg-slate-700/50' => 'hover:bg-slate-200',
    'hover:bg-slate-700'    => 'hover:bg-slate-200',

    // Borders
    'border-slate-800/90' => 'border-slate-200',
    'border-slate-800/80' => 'border-slate-200',
    'border-slate-800/60' => 'border-slate-200',
    'border-slate-800'    => 'border-slate-200',
    'border-slate-700/80' => 'border-slate-300',
    'border-slate-700/60' => 'border-slate-300',
    'border-slate-700'    => 'border-slate-300',

    // Text colors
    'text-slate-100' => 'text-slate-800',
    'text-slate-200' => 'text-slate-700',
    'text-slate-300' => 'text-slate-600',
    // Note: text-slate-400 and text-slate-500 can stay (they work in light mode too)
    'text-white'     => 'text-slate-900',

    // Hover text
    'hover:text-white'      => 'hover:text-slate-900',
    'hover:text-slate-100'  => 'hover:text-slate-800',
    'hover:text-slate-200'  => 'hover:text-slate-700',
    'hover:text-slate-300'  => 'hover:text-slate-600',

    // Divide / ring
    'divide-slate-800/80' => 'divide-slate-200',
    'divide-slate-800'    => 'divide-slate-200',

    // Specific backdrop
    'backdrop-blur-md'  => 'backdrop-blur-sm',

    // ─── Inline style colors ────────────────────────────────────────────────
    // Body / page bg
    'background-color: #020617'   => 'background-color: #f8fafc',
    'background: #020617'         => 'background: #f8fafc',
    'background-color: #0b1120'   => 'background-color: #ffffff',
    'background: #0b1120'         => 'background: #ffffff',
    "background: #0b1120 !important" => "background: #ffffff !important",
    "background-color: #0b1120 !important" => "background-color: #ffffff !important",

    // Card bg
    'background: rgba(15, 23, 42, 0.95)'  => 'background: rgba(255,255,255,0.98)',
    'background: rgba(15,23,42,0.95)'     => 'background: rgba(255,255,255,0.98)',
    'background: rgba(15, 23, 42, 0.9)'   => 'background: rgba(255,255,255,0.95)',

    // Text on dark → dark text
    'color: #f1f5f9'           => 'color: #0f172a',
    'color: #f8fafc'           => 'color: #0f172a',
    "color: #f1f5f9 !important" => "color: #0f172a !important",

    // Border dark → light
    'border: 1px solid rgba(51, 65, 85, 0.8)'  => 'border: 1px solid #e2e8f0',
    'border: 1px solid #1e293b'                 => 'border: 1px solid #e2e8f0',
    'border-color: #1e293b'                     => 'border-color: #e2e8f0',
    "border-color: #1e293b !important"          => "border-color: #e2e8f0 !important",

    // Placeholder
    'color: #3f4f68'                          => 'color: #94a3b8',
    "color: #3f4f68 !important"               => "color: #94a3b8 !important",
    "color-scheme: dark"                      => "color-scheme: light",

    // CSS overrides in <style> blocks
    "background-color: #0b1120 !important;\n            background-color: #0b1120 !important;" =>
        "background-color: #ffffff !important;",

    // Input bg in webkit autofill
    "-webkit-box-shadow: 0 0 0px 1000px #0b1120 inset !important;" =>
        "-webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;",
    "-webkit-text-fill-color: #f1f5f9 !important;" =>
        "-webkit-text-fill-color: #0f172a !important;",

    // Glow backgrounds
    'bg-indigo-600/20 rounded-full blur-3xl' => 'bg-indigo-100 rounded-full blur-3xl opacity-60',
    'bg-blue-600/20 rounded-full blur-3xl'   => 'bg-blue-100 rounded-full blur-3xl opacity-60',

    // Misc card shading
    'shadow-2xl shadow-indigo-500/20' => 'shadow-lg shadow-indigo-100',

    // Scrollbar track
    'background: rgba(15, 23, 42, 0.6);' => 'background: rgba(241,245,249,0.8);',
    'background: rgba(15,23,42,0.6);'    => 'background: rgba(241,245,249,0.8);',
    'background: rgba(71, 85, 105, 0.6); border-radius: 9999px;' =>
        'background: rgba(148,163,184,0.7); border-radius: 9999px;',
    'background: rgba(71,85,105,0.6); border-radius: 9999px;' =>
        'background: rgba(148,163,184,0.7); border-radius: 9999px;',
];

$totalFixed = 0;
foreach ($dirs as $dir) {
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $new = $content;
        foreach ($replacements as $from => $to) {
            $new = str_replace($from, $to, $new);
        }
        if ($new !== $content) {
            file_put_contents($file, $new);
            $totalFixed++;
            echo "Converted to light: $file\n";
        } else {
            echo "No changes: $file\n";
        }
    }
}
echo "\nDone. Converted $totalFixed files to light mode.\n";
