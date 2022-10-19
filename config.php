<?php
session_start();
$base = 'http://localhost/devsbookoo';

$db_name = 'devsbook';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

$pdo = new PDO("mysql:dbname=".$db_name.";host=".$db_host, $db_user, $db_pass);

date_default_timezone_set('America/Sao_Paulo');

//<?php echo '<pre>'; print_r($userList); <<<< código para imprimir array bonitinho