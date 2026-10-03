<?php
$db = new mysqli('localhost', 'root', '', 'sigapp');
$res = $db->query("SELECT is_kpr, COUNT(*) as c FROM mkdt WHERE is_kpr IS NULL OR is_kpr = '' GROUP BY is_kpr");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
