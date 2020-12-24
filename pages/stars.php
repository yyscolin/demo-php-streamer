<?php

include("../public/mysql_connections.php");
include("../public/nav-pages.php");
include("../public/common.php");

echo "  
  <link rel='stylesheet' href='/styles/star-box.css'>
  <link rel='stylesheet' href='/styles/poster.css'>
  <title>".get_text("stars", ucfirst)." - Demo PHP Streamer</title>";
include("../public/common-mid.php");

$type = 'stars';
$items_per_page = 50;
$current_page = isset($_COOKIE["Stars-Page"]) ? $_COOKIE["Stars-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

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

include("../public/box-star.php");

$res = mysqli_query($con, "$query limit $limit_start, $items_per_page");
echo "<div id='main-block'>";
while ($r = mysqli_fetch_object($res)) {
    print_star_box($r);
}
echo "</div>";

print_page_navbar($type, $query, $items_per_page, $current_page);

include("../public/html-tail.html");

?>