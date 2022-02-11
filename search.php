<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$type = $_GET['type'];
$search_query = $_GET['query'];

if (!isset($type) || !isset($search_query) || !in_array($type, ['movie', 'star'])) {
  header("HTTP/1.0 400");
  exit();
}

$search_results = search_database_by_query($type, $search_query);

function print_search_results() {
  global $type;
  global $search_results;

  if (count($search_results) > 0) {
    echo "<table id='results-table' class='$type'>";

    foreach ($search_results AS $search_result) {
      if ($type == 'movie') {
        $name = "<p>$search_result->name</p>";
        $subtitle = $search_result->release_date ? "<p class='subtitle'>$search_result->release_date</p>" : "";
        $media_path = $search_result->img;
      } else {
        $name = "<p>$search_result->name</p>";
        // $subtitle = $search_result->dob ? "<p class='subtitle'>$search_result->dob</p>" : "";
        $subtitle = "";
        $media_path = $search_result->img;
      }
    
      echo "<tr class='noselect' onclick='window.location.href=\"/$type/$search_result->id\"'>";
      echo "<td><img src='$media_path'></td>";
      echo "<td style='text-align:left'>$name$subtitle</td>";
      echo "</tr>";
    }

    echo "</table>";
  }
}

print_page_header([
  "<link rel='stylesheet' href='/styles/page-search.css'>",
  !$is_mobile && !$is_iPad ? "<link rel='stylesheet' href='/styles/page-search-web.css'>" : null,
  "<title>".get_text("search", 'ucfirst')." - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

?>
  
  <div id='main-block'>
    <form id='search-form'>
      <select name='type'>
        <option value='star'<?php if ($type == 'star') echo "selected='selected'"; ?>><?=get_text("stars", 'ucfirst')?></option>
        <option value='movie'<?php if ($type == 'movie') echo "selected='selected'"; ?>><?=get_text("movies", 'ucfirst')?></option>
      </select>
      <input name='query' value='<?=$search_query?>'>
      <button type='submit'><?=get_text("search", 'ucfirst')?></button>
    </form>
    <?php print_search_results(); ?>

  </div><?php

print_page_footer();

?>
