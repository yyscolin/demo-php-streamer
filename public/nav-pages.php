<?php

/** Determine page range */
$query = "select count(*) as count from ($query) as t";
$db_response = mysqli_query($con, $query);
$r = mysqli_fetch_object($db_response);
$no_of_pages = ceil($r->count/$items_per_page);

/** Display page navigation bar */
echo "\n<div id='nav-pages'>";
for ($i = 1; $i <= $no_of_pages; $i++) {
  $is_selected = $i == (int)$page_no ? "selected" : "";

  if ($i == 1 || $i == 2 || $i== $no_of_pages) {
    echo "<span class='nav-item-box'>";
    echo "<div style='margin:0;position:absolute;top:0'>";
  }

  echo "<span class='nav-item noselect $is_selected' data-page='$i' onclick='openPage(this)'>$i</span>";

  if ($i == 1 || $i == $no_of_pages - 1 || $i == $no_of_pages) {
    echo "</div>";
    echo "</span>";
  }
}

echo "\t\n</div>
  <div style='height:8vw'></div>
  <script src='/scripts/nav-bar.js'></script>
  <script>
    const maxNavLeft = $no_of_pages - 7
    const type = '$type'
    const itemsPerPage = $items_per_page
    adjustNavCss()
  </script>";