<?php
/**
 * Second pass: fix remaining dark-mode inline style colors in dashboard, inspections, etc.
 */
$dirs = [
    'resources/views/dashboard',
    'resources/views/inspections',
    'resources/views/supervisor',
    'resources/views/components',
    'resources/views/master-data',
    'resources/views/poka-yoke',
];

// Fix remaining inline hex colors still left dark
$replacements = [
    // Indigo accent text on dark bg → keep indigo but adjust shade for legibility on light
    // KPI numbers / metric big text
    'color: #818cf8'  => 'color: #4f46e5',  // indigo-400 → indigo-600
    'color: #a78bfa'  => 'color: #7c3aed',  // violet-400 → violet-600

    // Dark gray text that now needs to be a bit darker for light bg
    'color: #475569'  => 'color: #374151',
    'color: #64748b'  => 'color: #6b7280',

    // Background inline styles remaining dark
    'background-color: #1e293b' => 'background-color: #f1f5f9',
    'background: #1e293b'       => 'background: #f1f5f9',
    'background: #0f172a'       => 'background: #f8fafc',
    'background-color: #0f172a' => 'background-color: #f8fafc',

    // Inline style bg on KPI cards etc.
    'background: rgba(15, 23, 42' => 'background: rgba(241, 245, 249',
    'background-color: rgba(15, 23, 42' => 'background-color: rgba(241, 245, 249',

    // Tailwind bg classes via inline style that slipped through
    'bg-slate-950/40' => 'bg-slate-50',
    'bg-slate-950/30' => 'bg-slate-50',

    // Remaining text-white on elements that will be on light bg
    // (those on indigo buttons are OK – leave those)
    // KPI card icon wrappers - indigo/amber/emerald/rose accents → keep, they look fine on white cards

    // Table zebra / hover
    'hover:bg-slate-800/40' => 'hover:bg-indigo-50',
    'hover:bg-slate-800/30' => 'hover:bg-slate-50',
    'hover:bg-slate-800/20' => 'hover:bg-slate-50',

    // Status text on light bg
    'text-emerald-300' => 'text-emerald-700',
    'text-emerald-400' => 'text-emerald-600',
    'text-rose-300'    => 'text-rose-700',
    'text-rose-400'    => 'text-rose-600',
    'text-amber-300'   => 'text-amber-700',
    'text-amber-400'   => 'text-amber-700',
    'text-red-300'     => 'text-red-700',
    'text-red-400'     => 'text-red-600',
    'text-blue-300'    => 'text-blue-700',
    'text-blue-400'    => 'text-blue-600',
    'text-indigo-300'  => 'text-indigo-700',
    'text-indigo-400'  => 'text-indigo-600',
    'text-violet-300'  => 'text-violet-700',
    'text-violet-400'  => 'text-violet-600',
    'text-cyan-300'    => 'text-cyan-700',
    'text-cyan-400'    => 'text-cyan-600',

    // Colored bg/border combos for status - make them fully light-mode
    'bg-emerald-500/20' => 'bg-emerald-100',
    'bg-emerald-500/10' => 'bg-emerald-50',
    'bg-rose-500/20'    => 'bg-rose-100',
    'bg-rose-500/10'    => 'bg-rose-50',
    'bg-amber-500/20'   => 'bg-amber-100',
    'bg-amber-500/10'   => 'bg-amber-50',
    'bg-red-500/20'     => 'bg-red-100',
    'bg-blue-500/20'    => 'bg-blue-100',
    'bg-indigo-500/20'  => 'bg-indigo-100',
    'bg-indigo-500/10'  => 'bg-indigo-50',
    'bg-violet-500/20'  => 'bg-violet-100',
    'bg-cyan-500/20'    => 'bg-cyan-100',

    'border-emerald-500/30' => 'border-emerald-200',
    'border-emerald-500/20' => 'border-emerald-200',
    'border-rose-500/30'    => 'border-rose-200',
    'border-rose-500/20'    => 'border-rose-200',
    'border-amber-500/30'   => 'border-amber-200',
    'border-amber-500/20'   => 'border-amber-200',
    'border-red-500/30'     => 'border-red-200',
    'border-blue-500/30'    => 'border-blue-200',
    'border-indigo-500/30'  => 'border-indigo-200',
    'border-indigo-500/20'  => 'border-indigo-200',
    'border-violet-500/30'  => 'border-violet-200',
    'border-cyan-500/30'    => 'border-cyan-200',

    // Gradient from dark
    'from-slate-800'    => 'from-slate-100',
    'from-slate-900'    => 'from-white',
    'via-slate-900'     => 'via-white',
    'to-slate-800'      => 'to-slate-100',
    'to-slate-950'      => 'to-slate-50',
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
            echo "Pass-2 fixed: $file\n";
        } else {
            echo "No changes: $file\n";
        }
    }
}
echo "\nDone. Pass-2 fixed $totalFixed files.\n";
