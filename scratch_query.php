<?php
$content = file_get_contents('db/sigapp-20260614.sql');
preg_match_all('/CREATE TABLE `([^`]+)`/', $content, $matches);
foreach ($matches[1] as $table) {
    echo $table . "\n";
}
