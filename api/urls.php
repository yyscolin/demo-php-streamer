<?php

require('../public/mysql_connections.php');

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    $json = file_get_contents('php://input');
    $req = json_decode($json);
    $id = $req->id;
    $urls = $req->urls;

    /** Insert url into DB */
    $query = "insert into urls (id, url) values ($id, ?)";
    $stmt = $con->prepare($query);
    foreach ($urls as $url) {
      $stmt->bind_param('s', $url);
      $stmt->execute();
    }
    $stmt->close();

    /** Return */
    header("HTTP/1.1 200 OK");
    break;
}

?>