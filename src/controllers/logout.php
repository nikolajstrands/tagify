<?php
session_start();

// Nulstill session-array
$_SESSION = array();
// Sæt cookie-udløbsdato til fortiden
setcookie(session_name(), '', time() - 2592999, '/');
// Slet session
session_destroy();

// Redirect bruger til siden
header(  'Location: ../index.html'); 
exit();
?>