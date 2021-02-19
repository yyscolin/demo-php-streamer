<?php

function get_api_vids($limit_start, $items_per_page) {
  global $con;

  $db_query = "select id, title, release_date, duration from vids where status=1 order by modify_timestamp desc limit ?, ?";
  $stmt = $con->prepare($db_query);
  $stmt->bind_param('ss', $limit_start, $items_per_page);
  $stmt->execute();
  $db_response = $stmt->get_result();

  $vids = [];
  while ($r = $db_response->fetch_object()) {
    $r->img = get_img_src("vid", $r->id);
    if ($_SERVER["show_vid_code"] == "true") {
      $r->title = $r->id." ".$r->title;
    }
    array_push($vids, $r);
  }

  return $vids;
}

function get_api_stars($limit_start, $items_per_page) {
  global $con;

  function get_api_format($star) {
    $star->name = get_locale_star_name($star);
    $star->img = get_img_src("star", $star->id);

    unset($star->name_l);
    unset($star->name_f);
    unset($star->name_j);

    return $star;
  }

  $db_query = "select id, name_f, name_l, name_j, dob, ifnull(t2.count, 0) as count
  from (
    select stars.*, max(release_date) as release_date
    from stars left join casts on stars.id = casts.star
    left join vids on vids.id = casts.vid group by stars.id
  ) t1 left join (
    select star, count(star) as count
    from casts where vid in (
    select id from vids where status=1
    ) group by star
  ) t2 on t1.id = t2.star
  where display=1
  order by release_date desc, id limit ?, ?";

  $stmt = $con->prepare($db_query);
  $stmt->bind_param('ss', $limit_start, $items_per_page);
  $stmt->execute();
  $db_response = $stmt->get_result();

  $stars = [];
  while ($r = mysqli_fetch_object($db_response)) $stars[] = get_api_format($r);

  /** Add "Other" star */
  if (count($stars) < $items_per_page) {
    $others = get_others_star();
    if ($others) $stars[] = get_api_format($others);
  }

  return $stars;
}

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
require_once($_SERVER['DOCUMENT_ROOT']."/public/languages.php");

$type = $_GET['type'];
setcookie(ucfirst($type)."-Page", $_GET['page-no'], time() + 86400, "/");
$items_per_page = $_GET['items-count'];
$limit_start = ($_GET['page-no'] - 1) * $items_per_page;

$api_response = $type == 'vids'
  ? get_api_vids($limit_start, $items_per_page)
  : get_api_stars($limit_start, $items_per_page);
header('Content-type: application/json');
echo json_encode($api_response);

?>