<?php

include("public/common.php");
include('public/mysql_connections.php');

echo "  <script src='/scripts/toggleStarDisplay.js'></script>
  <link rel='stylesheet' href='/styles/star-box.css'>
  <link rel='stylesheet' href='/styles/poster.css'>
  <title>Stars - Demo PHP Streamer</title>";
include('public/html-mid.html');

$type = 'stars';
$items_per_page = 50;
$page_no = isset($_SESSION["stars-page-no"]) ? $_SESSION["stars-page-no"] : 1;
$limit_start = ($page_no - 1) * $items_per_page;

$query = "select id, name_f, name_l, name_j, dob, ifnull(t2.count, 0) as count from (
    select stars.*, max(release_date) as release_date
    from stars left join casts on stars.id = casts.star
    join vids on vids.id = casts.vid group by stars.id
) t1 left join (
    select star, count(star) as count
    from casts where vid in (
      select id from vids where status=3
    ) group by star
) t2 on t1.id = t2.star
where display = 1
order by release_date desc, id";

include('public/box-star.php');

$res = mysqli_query($con, "$query limit $limit_start, $items_per_page");
echo "<h1>STARS</h1";
while ($r = mysqli_fetch_object($res)) {
    $r->name_e = $r->name_f;
    if ($r->name_l) $r->name_e .= " $r->name_l";
    print_star($r);
}
echo ">";

include('public/nav-pages.php');
include('public/html-tail.html');

?>