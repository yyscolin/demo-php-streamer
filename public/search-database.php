<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/languages.php");

function search_database_by_query($type, $search_query, $itemsCount=5) {
  global $con;

  $search_results = [];

  $search_query .= '%';
  if ($type == 'vid') {
    $sql_query = "select *
      from vids where id like ? and status=1 order by modify_timestamp desc limit $itemsCount";
    $stmt = $con->prepare($sql_query);
    $stmt->bind_param('s', $search_query);
  } else {
    $sql_query = "select * from stars
      where display=1
      and (concat(name_f, ' ', name_l) like ? or concat(name_l, ' ', name_f) like ?)
      order by name_f limit $itemsCount";
    $stmt = $con->prepare($sql_query);
    $stmt->bind_param('ss', $search_query, $search_query);
  }
  
  $stmt->execute();
  $db_response = $stmt->get_result();
  while ($r = $db_response->fetch_object()) {
    $r->img = get_img_src($type, $r->id);
    if ($type == 'vid') {
      $r->name = $_SERVER['show_vid_code'] == "true" ? $r->id." ".$r->title : $r->title;
    } else {
      $r->name = get_locale_star_name($r);
    }
    array_push($search_results, $r);
  }

  return $search_results;
}

function search_database_by_id($type, $id) {
  global $con;
  global $default_imgs;

  $table = $type."s";
  $stmt = $con->prepare("select * from $table where id = ?");
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $db_response = $stmt->get_result();
  $r = $db_response->fetch_object();
  $r->img = get_img_src($type, $r->id);

  return $r;
}
