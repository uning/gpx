<?php
$file = $argv[1];
if(!$file || !file_exists($file)){
    die("file '$file' not exist");
}
$fd = fopen($file, 'r');
var_export(fgetcsv($fd));
echo "\n";
if($argv[2]){
    print_r(fgetcsv($fd));
}
fclose($fd);

