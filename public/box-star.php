<?php

function print_star_box($star, $default_indentation=2) {
  if ($star) {
    $img = get_img_src('star', $star->id);
    $href = "/star/$star->id";
    $star_name = get_locale_star_name($star);
    $style = "";
  } else {
    $img = "";
    $style = "display:none'";
    $star_name = "";
    $href = "";
  }

  print_line("<div class='star-box' style='$style'>", $default_indentation);
  print_line("<a href='$href'>", $default_indentation + 1);
  print_line("<img src='$img'>", $default_indentation + 2);
  print_line("</a>", $default_indentation + 1);
  print_line("<p class='text name text-ellipsis'>$star_name</p>", $default_indentation + 1);
  print_line("<p class='text dob'>$star->dob</p>", $default_indentation + 1);
  print_line("<a class='text vid-count' href='/star/$star->id'><span>$star->count</span> "
    .get_text("movies", ucfirst)."</a>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}