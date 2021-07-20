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
$random_count = 50;
print_line("<h2>Random Stars</h2>", 2);
print_line("<div style='font-size:0;width:100%;overflow-y:auto'>", 2);
print_line("<div style='text-align:center'>", 3);
$db_query = "
  select id, name_$language as name, coalesce(count, 0) as count
  from entities join (
    select entity, count(*) as count from xref_entities_vids
    where vid in (
      select id from vids where status=1
    ) and `is`='star' group by entity
  ) as t on entities.id = t.entity
  where status = 1
  order by rand() limit $random_count";
$db_response = mysqli_query($con, $db_query);
while ($r = mysqli_fetch_object($db_response)) $stars[] = $r;

/** Add "Others" star to the list */
$other_stars = get_others_star();
if ($other_stars) $stars[] = get_others_star();

foreach ($stars as $star) print_star_box($star, 4);

print_line("</div>", 3);
print_line("</div>", 2);

/** Get random vids */
$random_count = 5;
print_line("<h2>Random Videos</h2>", 2);
$vids = get_vids_from_database();
$random_indexes = array_rand($vids, $random_count);
foreach ($random_indexes as $i) print_vid_box($vids[$i], 2);
print_line("</div>");

print_page_footer();

?>
