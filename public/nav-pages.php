<?php

function print_page_navbar($type, $query, $items_per_page, $current_page) {
  global $con;

  /** Determine page range */
  $query = "select count(*) as count from ($query) as t";
  $db_response = mysqli_query($con, $query);
  $r = mysqli_fetch_object($db_response);
  if (get_others_star() != null) $r->count++; // Determine if "Other" star exists
  $no_of_pages = ceil(($r->count)/$items_per_page);

  if ($no_of_pages < 2) return;
  
  /** Display page navigation bar */
  print_line("<div id='nav-pages'>");
  for ($i = 1; $i <= $no_of_pages; $i++) {
    $is_selected = $i == (int)$current_page ? "selected" : "";
  
    if ($i == 1 || $i == 2 || $i == $no_of_pages) {
      $classes = "nav-item-box";
      if ($i == 2 && $no_of_pages != 2) $classes .= " middle";
      print_line("<span class='$classes'>", 2);
      print_line("<div style='margin:0;position:absolute;top:0'>", 3);
    }
  
    print_line("<span class='nav-item noselect $is_selected' data-page='$i' onclick='openPage(this)'>$i</span>", 4);
  
    if ($i == 1 || $i == $no_of_pages - 1 || $i == $no_of_pages) {
      print_line("</div>", 3);
      print_line("</span>", 2);
    }
  }
  print_line("</div>");
  print_line("<div style='height:8vw'></div>");

  echo "
  <script>
    const type = '$type'
    const itemsPerPage = $items_per_page
    const noOfPages = $no_of_pages
  </script>
  <script src='/scripts/pages-navbar.js'></script>
  <link rel='stylesheet' href='/styles/pages-navbar.css'>";
  
  if (!$is_mobile)
    print_line("<link rel='stylesheet' href='/styles/pages-navbar-web.css'>");
}
  