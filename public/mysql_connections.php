<?php

function throwMysqlError($message) {
    header('HTTP/1.0 500 MySQL Error');
    echo "<p>MySQL Error: $message</p>";
    include('public/html-tail.html');
    exit();
}

$con = mysqli_connect($hostname, $username, $password, $database);
if (mysqli_connect_error())
    throwMysqlError(mysqli_connect_error());
if (!mysqli_set_charset($con, "utf8"))
    throwMysqlError('set_charset: ' . mysqli_error($con));
