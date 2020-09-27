<?php

/** Set default subHref to 'vids' (if forgot to set) */
if (!$subHref) $subHref = 'vids';

/** Determine page range */
$query = "select count(*) as count from ($query) as t";
$res = mysqli_query($con, $query);
$r = mysqli_fetch_object($res);
$pages = ceil($r->count/$itemNo);
$min = max($page - 4, 1);
$max = min($page + 4, $pages);

/** Display page navigation bar */
echo "\n<div id='nav-pages'>";
if ($page != 1)
echo "
  <a href='$subHref/1'>«</a>
  <a href='$subHref/".($page-1)."'>◄</a>";
for ($i = $min; $i <= $max; $i++) {
  echo "\n  " . ($i == $page
    ? "<span>$i</span>"
    : "<a href='$subHref/$i'>$i</a>");
}
if ($max != $pages) echo "
  <span>...</span>
  <a href='$subHref/$pages'>$pages</a>";
if ($page != $pages) echo "
  <a href='$subHref/".($page+1)."'>►</a>
  <a href='$subHref/$pages'>»</a>";
echo "\t\n</div>";