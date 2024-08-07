<?php

function print_movie_box($movie, $default_indentation=1) {
  if ($movie) {
    $img = $movie->img;
    $href = "/pages/movie.php?id=$movie->id";
    $title = $movie->name ? $movie->name : "&ltNo title&gt";
    $style = "";
  } else {
    $img = "";
    $href = "";
    $title = "";
    $style = "display:none";
  }

  print_line("<div class='poster' style='$style'>", $default_indentation);
  print_line("<a href='$href'>", $default_indentation + 1);
  print_line("<img src='$img' onerror=\"this.src=`/images/default-cover.jpg`\">", $default_indentation + 2);
  print_line("</a>", $default_indentation + 1);
  print_line("<p class='title'>$title</p>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}