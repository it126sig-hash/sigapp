<?php
$db = new mysqli('localhost', 'root', '', 'sigapp');
$res = $db->query("SELECT id_mkdt, is_kpr FROM mkdt LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
