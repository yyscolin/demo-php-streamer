<?php

$is_admin = $_SESSION['auth']==1;

function print_star($star) {
  $img_src = "/media/stars/$star->id.jpg";
  if (!file_exists(dirname(__DIR__, 1).$img_src)) {
    $img_src = "/media/stars/0.jpg";
  }

  echo "\n><div class='star-box'>"
    ."<img onclick='window.location.href=\"/star/$star->id\"' src='$img_src'>";
  
  if ($is_admin)
    echo "<p>#$star->id</p>";
  
  echo "<p>$star->name_j</p>"
    ."<p>$star->name_e</p>"
    ."<p>$star->dob</p>"
    ."<a href='/star/$star->id'>$star->count Videos</a>";
  
  if ($is_admin) {
    $display = $star->display==1 ? "hide" : "show";
    echo "<button value='$star->id' onclick='toggleStarDisplay(this)'>$display</button>";
  }
  
  echo "</div";
}