<?php

require('../public/mysql_connections.php');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
  case 'POST':
  case 'PUT':
    $json = file_get_contents('php://input');
    $star = json_decode($json);

    /** Insert star into DB */
    $id     = $star->id      ? $star->id      : NULL;
    $name_f = $star->name_f;
    $name_l = $star->name_l  ? $star->name_l  : NULL;
    $name_j = $star->name_j  ? $star->name_j  : NULL;
    $dob    = $star->dob     ? $star->dob     : NULL;
    $query = "insert into stars (id, name_l, name_f, name_j, dob)
      values (?, ?, ?, ?, ?) on duplicate key update
      name_l=name_l, name_f=name_f, name_j=name_j, dob=dob";
    $stmt = $con->prepare($query);
    $stmt->bind_param('sssss', $id, $name_l, $name_f, $name_j, $dob);
    $stmt->execute();
    if ($method=='POST') {
      $star->id = $stmt->insert_id;
    }
    $stmt->close();
    
    /** Insert urls into DB */
    $query = "insert into urls (id, url) values ($star->id, ?)";
    $stmt = $con->prepare($query);
    foreach ($star->urls as $url) {
      $stmt->bind_param('s', $url);
      $stmt->execute();
    }
    $stmt->close();

    /** Download star image */
    if (preg_match('/data:image/', $star->pic)) {
      list($type, $data) = explode(',', $star->pic);
      $type = explode('/', $type)[1];
      $type = explode(';', $type)[0];
      $data = base64_decode($data);
      file_put_contents("../media/stars/$star->id.$type", $data);
      $star->pic = "/media/stars/$star->id.$type";
    }

    /** Return star */
    header('Content-Type: application/json');
    header("HTTP/1.1 200 OK");
    echo json_encode($star);
    break;
}

?>