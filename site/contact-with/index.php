<?php
// Созданная страница для размещения компонента form.result.new
/** @global CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(" Связаться");

// Подключаем модуль веб-форм
CModule::IncludeModule("form");
$FORM_SID = 'contact_with';
$rsForm = CForm::GetBySID($FORM_SID);
$arForm = $rsForm->Fetch();

?><?php $APPLICATION->IncludeComponent(
	"bitrix:form.result.new", 
	"bootstrap_v4", 
	array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "N",
		"SUCCESS_URL" => "",
		"USE_EXTENDED_ERRORS" => "Y",
		"WEB_FORM_ID" => $arForm["ID"],
		"COMPONENT_TEMPLATE" => "bootstrap_v4",
		"VARIABLE_ALIASES" => array(
			"WEB_FORM_ID" => "WEB_FORM_ID",
			"RESULT_ID" => "RESULT_ID",
		)
	),
	false
);?><?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>