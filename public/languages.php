<?php

$dictionary = array(
    "stars"=>array("jp"=>"キャスト"),
    "movies"=>array("jp"=>"作品"),
    "keyword"=>array("jp"=>"キーワード"),
    "go"=>array("jp"=>"行く"),
    "release date"=>array("jp"=>"発売日"),
    "duration"=>array("jp"=>"収録時間"),
    "minutes"=>array("jp"=>"分鐘"),
    "search"=>array("jp"=>"捜索")
);

function get_text($text_reference, $option_callback=null) {
    global $dictionary;

    $language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";
    $trans_list = $dictionary[$text_reference];
    $text = $trans_list && array_key_exists($language, $trans_list)
        ? $trans_list[$language]
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
