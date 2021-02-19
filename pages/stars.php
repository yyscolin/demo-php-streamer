<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");

print_page_header([  
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<style>#main-block{font-size:0}</style>",
  "<title>".get_text("stars", ucfirst)." - Demo PHP Streamer</title>"
]);

$type = 'stars';
$items_per_page = 50;
$current_page = isset($_COOKIE["Stars-Page"]) ? $_COOKIE["Stars-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

$stars = [];
$db_query = "select id, name_f, name_l, name_j, dob, ifnull(t2.count, 0) as count from (
  select stars.*, max(release_date) as release_date
  from stars left join casts on stars.id = casts.star
  left join vids on vids.id = casts.vid group by stars.id
) t1 left join (
  select star, count(star) as count
  from casts where vid in (
    select id from vids where status=1
  ) group by star
) t2 on t1.id = t2.star
where display = 1
order by release_date desc, id";
$db_response = mysqli_query($con, $db_query);
while ($r = mysqli_fetch_object($db_response)) $stars[] = $r;
$stars[] = get_others_star(); //Add "Others" star to the list

print_line("<div id='main-block'>");
for ($i = $limit_start; $i < $limit_start + $items_per_page; $i++) {
  print_star_box($stars[$i]);
}
print_line("</div>");

print_page_navbar($type, $db_query, $items_per_page, $current_page);
print_page_footer();

?>