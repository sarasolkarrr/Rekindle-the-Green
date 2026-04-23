<?php
$username = "root";
$password = "";
$server = "localhost";
$db = "rekindle-the-green";

$con = mysqli_connect($server, $username, $password);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!mysqli_select_db($con, $db)) {
    die("Database selection failed: " . mysqli_error($con));
}

mysqli_set_charset($con, "utf8mb4");
?>