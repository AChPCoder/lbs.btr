<?php

/** @global CMain $APPLICATION */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тестовое задание");

?>
<div style="border-bottom: solid 1px #56565680; margin-bottom: 1rem;">start</div>
<?php $APPLICATION->IncludeComponent(
    "test_task_components:test_task",
    "",
    [
//        'employeeId' => 1818,
        'employeeId' => 1810,
    ],
    false
);?>
<div style="border-top: solid 1px #56565680; margin-top: 1rem;">end</div>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>