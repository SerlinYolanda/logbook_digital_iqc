<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$dump = file_get_contents(__DIR__ . '/database_mysql_dump.sql');

// Remove COMMIT; and SET FOREIGN_KEY_CHECKS=1; temporarily to append seed data
$dump = str_replace(["SET FOREIGN_KEY_CHECKS=1;\n", "COMMIT;\n"], '', $dump);

$dump .= "\n-- ============================================================\n";
$dump .= "-- SEEDED DATA FOR PT OSI ELECTRONICS BATAM\n";
$dump .= "-- ============================================================\n\n";

$tables = [
    'users',
    'suppliers',
    'parts',
    'part_parameters',
    'defect_categories',
    'receivings',
    'logbooks',
    'measurements',
    'defects',
    'nest_processes',
    'mrb_dispositions',
    'audit_trails',
    'instruments'
];

foreach ($tables as $tbl) {
    $rows = DB::table($tbl)->get();
    if ($rows->count() === 0) continue;

    $dump .= "-- Data for table `{$tbl}`\n";
    $dump .= "LOCK TABLES `{$tbl}` WRITE;\n";

    foreach ($rows as $row) {
        $cols = array_keys((array) $row);
        $vals = [];
        foreach ((array) $row as $val) {
            if ($val === null) {
                $vals[] = "NULL";
            } elseif (is_numeric($val) && !is_string($val)) {
                $vals[] = $val;
            } else {
                $vals[] = "'" . addslashes((string)$val) . "'";
            }
        }
        $dump .= "INSERT INTO `{$tbl}` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $vals) . ");\n";
    }

    $dump .= "UNLOCK TABLES;\n\n";
}

$dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
$dump .= "COMMIT;\n";

file_put_contents(__DIR__ . '/database_mysql_dump.sql', $dump);
echo "Full MySQL DDL + Seed dump written (" . strlen($dump) . " bytes)\n";
