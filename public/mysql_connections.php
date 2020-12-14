<?php

function throwMysqlError($message) {
    header('HTTP/1.0 500 MySQL Error');
    echo "<p>MySQL Error: $message</p>";
    include('public/html-tail.html');
    exit();
}

$con = mysqli_connect(
    $_SERVER['MYSQL_HOSTNAME'],
    $_SERVER['MYSQL_USERNAME'],
    $_SERVER['MYSQL_PASSWORD'],
    $_SERVER['MYSQL_DATABASE']
);

if (mysqli_connect_error())
    throwMysqlError(mysqli_connect_error());
if (!mysqli_set_charset($con, "utf8"))
    throwMysqlError('set_charset: ' . mysqli_error($con));
