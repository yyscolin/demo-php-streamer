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
    require_once_once($translation_modifications_file);

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

    if ($option_callback) {
        return $option_callback($text);
    } else {
        return $text;
    }
}

function get_locale_star_name($star) {
    $language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";
    switch ($language) {
        case "jp":
            return $star->name_j ? $star->name_j : $star->name_f;
        default:
            $star_name = $star->name_f;
            if ($star->name_l) $star_name .= " ".$star->name_l;
            return $star_name;
    }
}
