<?php
require __DIR__.'/vendor/autoload.php';
$app=require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlite = DB::connection('sqlite');
$tables = ['users','projects','buildings','floors','panoramas','hotspots','videos','site_settings','migrations'];
$out = "-- pano.sql for InfinityFree MySQL (utf8mb4, key 191)\nSET FOREIGN_KEY_CHECKS=0;\n";

foreach ($tables as $table) {
  try {
    $cols = $sqlite->select("PRAGMA table_info('$table')");
    if (empty($cols)) continue;
    // Get MySQL CREATE from local schema via show create? Fallback to generate
    $rows = $sqlite->table($table)->get();
    $out .= "\n-- Table $table\n";
    $out .= "DROP TABLE IF EXISTS `$table`;\n";
    // Generate CREATE from SQLite and convert to MySQL
    $create = $sqlite->selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'");
    if ($create && $create->sql) {
      $sql = $create->sql;
      // Convert SQLite to MySQL: replace AUTOINCREMENT, types
      $sql = str_replace('"','`',$sql);
      $sql = str_replace('AUTOINCREMENT','AUTO_INCREMENT',$sql);
      $sql = preg_replace('/varchar\(255\)/i','varchar(191)',$sql); // fix key length
      // Ensure MySQL engine
      if (stripos($sql,'CREATE TABLE')===0) {
        $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
      }
      $out .= $sql."\n";
    }
    foreach ($rows as $row) {
      $data = (array)$row;
      $keys = array_map(fn($k)=>"`$k`", array_keys($data));
      $vals = array_map(function($v){
        if (is_null($v)) return "NULL";
        return "'".str_replace("'","''", $v)."'";
      }, array_values($data));
      $out .= "INSERT INTO `$table` (".implode(',',$keys).") VALUES (".implode(',',$vals).");\n";
    }
  } catch(Throwable $e){ $out .= "-- skip $table: ".$e->getMessage()."\n"; }
}
$out .= "SET FOREIGN_KEY_CHECKS=1;\n";
file_put_contents('C:/Users/Admin/AppData/Local/Temp/opencode/pano.sql', $out);
echo "Exported to C:/Users/Admin/AppData/Local/Temp/opencode/pano.sql (".number_format(strlen($out)/1024,1)." KB)\n";
foreach ($tables as $t) {
  try { $c = $sqlite->table($t)->count(); echo "$t: $c\n"; } catch(Throwable $e){ echo "$t: - \n"; }
}
