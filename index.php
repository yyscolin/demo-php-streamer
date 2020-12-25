<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>Demo PHP Streamer</title>"
]);

/** establish mysql connection */
require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");

/** Get stars in random order */
$randCount = 5;
echo "<h2>STARS</h2>"
    ."<div style='width:95vw;overflow-y:auto'><div style='text-align:center'>";
$dbQuery = "
  select id, name_l, name_f, name_j, dob, coalesce(count, 0) as count
  from stars left join (
    select star, count(star) as count from casts group by star
  ) as t on stars.id = t.star
  where display = 1
  order by rand()";
$dbResponse = mysqli_query($con, $dbQuery);
while ($r = mysqli_fetch_object($dbResponse)) {
  print_star_box($r);
}

/** Print other stars box */
$dbQuery = "select count(id) as count from vids where id not in (select vid from casts)";
$dbResponse = mysqli_query($con, $dbQuery);
$r = mysqli_fetch_object($dbResponse);
$r->name_f = "Others";
print_star_box($r);
echo "\n</div></div>";

/** Get random vids */
$randCount = 5;
echo "<h2>Random Videos</h2>";
$dbQuery = "select id, title from vids where status = 3 order by rand() limit $randCount";
$dbResponse = mysqli_query($con, $dbQuery);
print_line("<div id='main-block'>");
while ($r = mysqli_fetch_object($dbResponse)) {
  print_vid_box($r, 2);
}
print_line("</div>");

print_page_footer();

?>
