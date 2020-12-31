<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>Demo PHP Streamer</title>"
]);

print_line("<div id='main-block'>");

/** Get random stars */
$stars = [];
$randCount = 50;
print_line("<h2>Random Stars</h2>", 2);
print_line("<div style='font-size:0;width:100%;overflow-y:auto'>", 2);
print_line("<div style='text-align:center'>", 3);
$db_query = "
  select id, name_l, name_f, name_j, dob, coalesce(count, 0) as count
  from stars left join (
    select star, count(star) as count from casts group by star
  ) as t on stars.id = t.star
  where display = 1
  order by rand() limit $randCount";
$db_response = mysqli_query($con, $db_query);
while ($r = mysqli_fetch_object($db_response)) $stars[] = $r;
$stars[] = get_others_star(); //Add "Others" star to the list

foreach ($stars as $star) print_star_box($star, 4);

print_line("</div>", 3);
print_line("</div>", 2);

/** Get random vids */
$randCount = 5;
print_line("<h2>Random Videos</h2>", 2);
$db_query = "select id, title from vids where status = 3 order by rand() limit $randCount";
$db_response = mysqli_query($con, $db_query);
while ($r = mysqli_fetch_object($db_response)) {
  print_vid_box($r, 2);
}
print_line("</div>");

print_page_footer();

?>
