<?php

$dictionary = array(
    "stars"=>array("jp"=>"スター"),
    "movies"=>array("jp"=>"ビデオ"),
    "keyword"=>array("jp"=>"キーワード"),
    "go"=>array("jp"=>"行く"),
    "release date"=>array("jp"=>"発売日"),
    "duration"=>array("jp"=>"収録時間"),
    "minutes"=>array("jp"=>"分鐘"),
    "search"=>array("jp"=>"捜索")
);

/** Fill up empty parts of dictionary with text reference as default */
$supported_languages = ["en", "jp"];
foreach (array_keys($dictionary) as $reference_text)
    foreach ($supported_languages as $language)
        if (!array_key_exists($language, $dictionary[$reference_text]))
            $dictionary[$reference_text][$language] = $reference_text;

/** Custom text translation/ display for this project (if any) */
$translation_modifications_file = __DIR__."/languages-local.php";
if (file_exists($translation_modifications_file)) {
    require_once($translation_modifications_file);

    foreach (array_keys($modifications) as $reference_text) {
        foreach ($modifications[$reference_text] as $language => $translated_text) {
            $dictionary[$reference_text][$language] = $translated_text;
        }
    }
}

function get_text($text_reference, $option_callback=null) {
    global $dictionary;

    $language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";
    $text = array_key_exists($text_reference, $dictionary)
        ? $dictionary[$text_reference][$language]
        : $text_reference;

    switch ($option_callback) {
        case 'strtoupper':
            return strtoupper($text);
        case 'ucfirst':
            return ucfirst($text);
        case 'ucwords':
            return ucwords($text);
        default:
            return $text;
    }
}
