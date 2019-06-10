<?php
session_start();
require_once('src/classes/Database.php');
require_once('src/classes/model.php');

$database = new Database();

$time_pre = microtime(true);
$user = $database->getUser('113245463');
$time_post = microtime(true);

$exec_time = $time_post - $time_pre;

echo "Det tog " . $exec_time . " sekunder.";

?>