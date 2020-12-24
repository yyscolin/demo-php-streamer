<?php

function redirectToHomePage() {
    header("Location: /");
    exit();
}

function print_line($line, $indentation_level=0, $is_new_line=true) {
  if ($is_new_line) echo "\n";

  for ($i = 0 ; $i < $indentation_level; $i++) {
      echo "  ";
  }

  echo $line;
}