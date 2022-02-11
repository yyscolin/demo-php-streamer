<?php

function throwMysqlError($message) {
    header('HTTP/1.0 500 MySQL Error');
    echo "<p>MySQL Error: $message</p>";
    exit();
}

$mysql_connection = mysqli_connect(
    $_SERVER['MYSQL_HOSTNAME'],
    $_SERVER['MYSQL_USERNAME'],
    $_SERVER['MYSQL_PASSWORD'],
    $_SERVER['MYSQL_DATABASE'],
    $_SERVER['MYSQL_PORT']
);

if (mysqli_connect_error())
    throwMysqlError(mysqli_connect_error());
if (!mysqli_set_charset($mysql_connection, "utf8mb4"))
    throwMysqlError('set_charset: ' . mysqli_error($mysql_connection));
