<?php

require('../public/mysql_connections.php');

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    $json = file_get_contents('php://input');
    $vid = json_decode($json);
    $stars = $vid->stars;

    /** Insert vid into DB */
    $title        = $vid->title         ? $vid->title         : NULL;
    $release_date = $vid->release_date  ? $vid->release_date  : NULL;
    $duration     = $vid->duration      ? $vid->duration      : NULL;
    $query = "
      insert into vids (id, title, release_date, duration)
      values ('$vid->id', ?, ?, ?)
      on duplicate key
      update title=title, release_date=release_date, duration=duration
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param('sss', $title, $release_date, $duration);
    $stmt->execute();
    $stmt->close();

    /** Insert casts into DB */
    $query = "
      insert into casts (vid, star)
      values ('$vid->id', ?)
      on duplicate key
      update vid=vid, star=star
    ";
    $stmt = $con->prepare($query);
    foreach ($stars as $star) {
      $stmt->bind_param('s', $star);
      $stmt->execute();
    }
    $stmt->close();

    /** Download vid cover */
    list($type, $data) = explode(',', $vid->cover);
    $type = explode('/', $type)[1];
    $type = explode(';', $type)[0];
    $data = base64_decode($data);
    file_put_contents("../media/covers/$vid->id.$type", $data);
    
    header("HTTP/1.1 200 OK");
    break;
}

?>