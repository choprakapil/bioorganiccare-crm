<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$database = config('database.connections.mysql.database');
$filename = $database . '_backup_' . date('Y_m_d_H_i_s') . '.sql';
$handle = fopen($filename, 'w');

if (!$handle) {
    die("Could not open file for writing\n");
}

// Get all tables
$tables = DB::select('SHOW TABLES');
$key = "Tables_in_" . $database;

foreach ($tables as $table) {
    $tableName = $table->$key;
    
    // Create Table statement
    $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
    $createSql = $createTable->{'Create Table'} . ";\n\n";
    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
    fwrite($handle, $createSql);
    
    // Get data
    $rows = DB::table($tableName)->get();
    foreach ($rows as $row) {
        $rowArray = (array)$row;
        $columns = array_keys($rowArray);
        $values = array_values($rowArray);
        
        $escapedValues = array_map(function($value) {
            if (is_null($value)) return 'NULL';
            return "'" . addslashes($value) . "'";
        }, $values);
        
        $insertSql = "INSERT INTO `{$tableName}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
        fwrite($handle, $insertSql);
    }
    fwrite($handle, "\n");
}

fclose($handle);
echo "Database backup created: {$filename}\n";
