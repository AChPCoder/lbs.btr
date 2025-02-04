<?php
/** @global CMain $APPLICATION */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кастомные новости");
?><?php $APPLICATION->IncludeComponent(
	"custom_components:allnews.list",
	".default", 
	[
        'IBLOCK_TYPE' => 'news', // тип инфоблоков, в которых искать элементы
//        'IBLOCK_ID' => '4', // опциональный параметр, может быть массивом
        'NEWS_COUNT' => 20,
        'FILTER_FIELDS' => [
            'TIMESTAMP_X' => [
                'prefix' => '>',
                'value' => '2025-01-04 08:39:00',
            ],
            'NAME' => [
                'prefix' => '',
                'value' => '%1%',
            ],
            'PREVIEW_TEXT' => [
                'prefix' => '',
                'value' => '%тест%',
            ],
        ]
    ],
	false
);?><?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>