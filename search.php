<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/search-database.php");

$type = $_GET['type'];
$query = $_GET['query'];
$search_results = isset($query) && ($type != 'vid' || $type != 'star')
  ? search_database_by_query($type, $query, 10)
  : [];

function print_search_results() {
  global $type;
  global $search_results;

  if (count($search_results) > 0) {
    echo "<table id='results-table' class='$type'>";

    foreach ($search_results as $r) {
      if ($type == 'vid') {
        $title = "<p>$r->id $r->title</p>";
        $subtitle = $r->release_date ? "<p class='subtitle'>$r->release_date</p>" : "";
        $media_path = "/media/covers/$r->id.jpg";
      } else {
        $title = "<p>$r->name</p>";
        $subtitle = $r->dob ? "<p class='subtitle'>$r->dob</p>" : "";
        $media_path = "/media/stars/$r->id.jpg";
      }
    
      echo "<tr class='noselect' onclick='window.location.href=\"/$type/$r->id\"'>";
      echo "<td><img src='$media_path'></td>";
      echo "<td style='text-align:left'>$title$subtitle</td>";
      echo "</tr>";
    }

    echo "</table>";
  }
}

print_page_header([
  "<link rel='stylesheet' href='/styles/page-search.css'>",
  !$is_mobile && !$is_iPad ? "<link rel='stylesheet' href='/styles/page-search-web.css'>" : null,
  "<title>".get_text("search", ucfirst)." - Demo PHP Streamer</title>"
]);

?>
  
  <div id='main-block'>
    <form id='search-form'>
      <select name='type'>
        <option value='star'<?php if ($type == 'star') echo "selected='selected'"; ?>><?php echo get_text("stars", ucfirst); ?></option>
        <option value='vid'<?php if ($type == 'vid') echo "selected='selected'"; ?>><?php echo get_text("movies", ucfirst); ?></option>
      </select>
      <input name='query' value='<?php echo $query; ?>'>
      <button type='submit'><?php echo get_text("search", ucfirst); ?></button>
    </form>
    <?php print_search_results(); ?>

  </div><?php

print_page_footer();

?>
