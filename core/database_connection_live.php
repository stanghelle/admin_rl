<?php

//database_connection.php

$connect = new PDO("mysql:host=radiolyngdalno02.mysql.domeneshop.no; dbname=radiolyngdalno02", "radiolyngdalno02", "Mxty2834");

date_default_timezone_set("Europe/oslo");
$db_host = 'radiolyngdalno02.mysql.domeneshop.no';
$db_user = 'radiolyngdalno02';
$db_pass = 'Mxty2834';
$db_name = 'radiolyngdalno02';

//connect and select db
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
?>
