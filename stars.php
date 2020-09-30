<?php

session_start();

include("public/verifyLogin.php");
include('public/mysql_connections.php');
include('public/html-head.html');

echo "  <script src='/scripts/toggleStarDisplay.js'></script>
  <link rel='stylesheet' href='/styles/star-box.css'>
  <link rel='stylesheet' href='/styles/poster.css'>
  <title>Stars - Demo PHP Streamer</title>";
include('public/html-mid.html');

/** Determine page number */
$page = (int)$_GET['page'] != 0 ? $_GET['page'] : 1;
$itemNo = 50;
$limitStart = ($page-1)*$itemNo;
   

$query = "select id, name_f, name_l, name_j, dob,
    display, ifnull(t2.count, 0) as count from (
    select stars.*, max(release_date) as release_date
    from stars left join casts on stars.id = casts.star
    join vids on vids.id = casts.vid group by stars.id
) t1 left join (
    select star, count(star) as count
    from casts group by star
) t2 on t1.id = t2.star
order by release_date desc";

include('public/box-star.php');

$res = mysqli_query($con, "$query limit $limitStart, $itemNo");
echo "<h1>STARS</h1";
while ($r = mysqli_fetch_object($res)) {
    $r->name_e = $r->name_f;
    if ($r->name_l) $r->name_e .= " $r->name_l";
    print_star($r);
}
echo ">";

$subHref = '/stars';

include('public/nav-pages.php');
include('public/html-tail.html');

?>