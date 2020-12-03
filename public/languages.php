<?php

$dictionary = array(
    "stars"=>array("jp"=>"女優"),
    "videos"=>array("jp"=>"ビデオ"),
    "keyword"=>array("jp"=>"キーワード"),
    "go"=>array("jp"=>"行く"),
    "release date"=>array("jp"=>"発売日"),
    "duration"=>array("jp"=>"収録時間"),
    "minutes"=>array("jp"=>"分鐘"),
    "search"=>array("jp"=>"捜索")
);

$language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";
function get_text($text_reference, $option_callback=null) {
    global $dictionary;
    global $language;

    if (array_key_exists($text_reference, $dictionary)) {
        $text = $dictionary[$text_reference];
        if (array_key_exists($language, $text)) {
            $text = $text[$language];
        } else if (array_key_exists("en", $text)) {
            $text = $text["en"];
        } else {
            $text = $text_reference;
        }
    } else {
        $text = $text_reference;
    }

    if ($option_callback) {
        return $option_callback($text);
    } else {
        return $text;
    }
}