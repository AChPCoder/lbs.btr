<?php

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$rsSections = CIBlockSection::GetList(
    array("SORT" => "ASC"),
    array(
        "=IBLOCK_ID" => $arParams['IBLOCK_ID'],
        "=ACTIVE" => "Y"
    )
);

// Тут вместо инкрементного индекса, ID раздела
while ($arSection = $rsSections->GetNext()) {
    $arSections[$arSection['ID']] = $arSection;
}

// По нему производим неявную фильрацию
foreach ($arResult["ITEMS"] as $arItem) {
    $arSections[$arItem['IBLOCK_SECTION_ID']]['ITEMS'][] = $arItem;
}

$curPage = $APPLICATION->GetCurPage(true);

$SECTION_URL_TEMPLATE = $arParams['SECTION_URL'];
foreach ($arSections as &$arSection) {
    $arSection['FULL_URL'] = str_replace('#SECTION_CODE#', $arSection['CODE'], $SECTION_URL_TEMPLATE);
    $arSection['IS_ACTIVE'] = mb_strpos($arSection['FULL_URL'], $curPage) === 0;
}
unset($arSection);

$arResult["SECTIONS"] = $arSections;


