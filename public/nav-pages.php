<?php

function print_page_navbar($type, $count, $items_per_page) {
  global $mysql_connection;
  global $is_mobile;

  $no_of_pages = ceil($count / $items_per_page);

  if ($no_of_pages < 2) return;

  print_line("<div id=\"nav-pages\">");
  for ($i = 1; $i <= $no_of_pages; $i++) {
    if ($i == 1 || $i == 2 || $i == $no_of_pages) {
      $classes = "nav-item-box";
      if ($i == 2 && $no_of_pages != 2) $classes .= " middle";
      print_line("<span class=\"$classes\">", 2);
      print_line("<div style=\"margin:0;position:absolute;top:0\">", 3);
    }

    print_line("<span class=\"nav-item noselect\" data-page=\"$i\" onclick=\"window.location.hash=$i\">$i</span>", 4);

    if ($i == 1 || $i == $no_of_pages - 1 || $i == $no_of_pages) {
      print_line("</div>", 3);
      print_line("</span>", 2);
    }
  }
  print_line("</div>");
  print_line("<div style=\"height:8vw\"></div>");

  echo "
  <script>
    const type = `$type`
    const itemsPerPage = $items_per_page
    const noOfPages = $no_of_pages
  </script>
  <script src=\"/scripts/pages-navbar.js\"></script>
  <link rel=\"stylesheet\" href=\"/styles/pages-navbar.css\">";
  
  if (!$is_mobile)
    print_line("<link rel=\"stylesheet\" href=\"/styles/pages-navbar-web.css\">");
}
  