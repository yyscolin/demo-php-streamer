<?php

session_start();

if (
  !isset($_GET['type']) ||
  !isset($_GET['page-no']) ||
  !isset($_GET['items-count']) ||
  ($_GET['type'] != 'vids' && $_GET['type'] != 'stars') ||
  !is_numeric($_GET['page-no']) ||
  !is_numeric($_GET['items-count']) ||
  $_GET['page-no'] < 1 ||
  $_GET['items-count'] < 1
) {
  header("HTTP/1.0 400");
  exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/languages.php");

$type = $_GET['type'];
setcookie(ucfirst($type)."-Page", $_GET['page-no'], time() + 86400, "/");
$items_per_page = $_GET['items-count'];
$limit_start = ($_GET['page-no'] - 1) * $items_per_page;
$query = $type == 'vids'
  ? "select id, title, release_date, duration from vids where status=3 order by modify_timestamp desc limit ?, ?"
  : "select id, name_f, name_l, name_j, dob, ifnull(t2.count, 0) as count
    from (
      select stars.*, max(release_date) as release_date
      from stars left join casts on stars.id = casts.star
      left join vids on vids.id = casts.vid group by stars.id
    ) t1 left join (
      select star, count(star) as count
      from casts where vid in (
      select id from vids where status=3
      ) group by star
    ) t2 on t1.id = t2.star
    where display=1
    order by release_date desc, id limit ?, ?";
$stmt = $con->prepare($query);
$stmt->bind_param('ss', $limit_start, $items_per_page);
$stmt->execute();
$db_response = $stmt->get_result();

$api_response = [];
while ($r = $db_response->fetch_object()) {
  $r->img = get_img_src($type, $r->id);
  if ($type == 'stars') {
    $r->name = get_locale_star_name($r);

    unset($r->name_l);
    unset($r->name_f);
    unset($r->name_j);
  } else {
    if ($_SERVER["show_vid_code"] == "true") {
      $r->title = $r->id." ".$r->title;
    }
  }
  array_push($api_response, $r);
}

header('Content-type: application/json');
echo json_encode($api_response);

?>