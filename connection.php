<?php
$username = "root";
$password = "";
$server = "localhost";
$db = "rekindle-the-green";

$con = mysqli_connect($server, $username, $password, $db);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>