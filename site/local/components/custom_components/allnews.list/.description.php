<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    "NAME" => GetMessage("CUSTOM_ALLNEWS_IBLOCK_DESC_LIST"),
    "DESCRIPTION" => GetMessage("CUSTOM_ALLNEWS_IBLOCK_DESC_LIST_DESC"),
//    "ICON" => "/images/news_list.gif", // path rel to site/local/components/custom_components/allnews.list/
    "SORT" => 20,
//	"SCREENSHOT" => array(
//		"/images/post-77-1108567822.jpg",
//		"/images/post-1169930140.jpg",
//	),
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "content",
        "CHILD" => [
            "ID" => "news",
            "NAME" => GetMessage("CUSTOM_ALLNEWS_IBLOCK_DESC_NEWS"),
            "SORT" => 10,
            "CHILD" => [
                "ID" => "allnews_cmpx",
            ],
        ],
    ],
];