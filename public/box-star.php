<?php

function print_star_box($star=null, $default_indentation=3) {
  if ($star) {
    $img = "/media/star/$star->id";
    $star_name = $star->name ? $star->name : "&ltNo Name&gt";
    $href = "/star/$star->id";
    $style = "";
    $movies_count = $star->count;
  } else {
    $img = "";
    $style = "display:none";
    $star_name = "";
    $href = "";
    $movies_count = "";
  }

  print_line("<div class=\"star-box\" style=\"$style\">", $default_indentation);
  print_line("<a href=\"$href\">", $default_indentation + 1);
  print_line("<img src=\"$img\">", $default_indentation + 2);
  print_line("</a>", $default_indentation + 1);
  print_line("<p class=\"text name text-ellipsis\">$star_name</p>", $default_indentation + 1);
  print_line("<a class=\"text movie-count\" href=\"$href\"><span>$movies_count</span> "
    .get_text("movies", "ucfirst")."</a>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}