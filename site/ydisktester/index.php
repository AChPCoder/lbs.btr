<?php

/** @global CMain $APPLICATION */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тест CRUD Yandex.Disk");

?>
<?php $APPLICATION->IncludeComponent(
    "oop_components:ydisk.manager",
    ".default",
    [
        'VIEW_URL' => '/ydisktester/',
        'ADD_URL' => '/ydisktester/?type=add',
        'EDIT_URL' => '/ydisktester/?type=edit',
        'DELETE_URL' => '/ydisktester/?type=remove',
    ],
    false
);?>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>