<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");
?>
<?if (IsModuleInstalled("advertising")):?>
	<div class="mb-5">
		<?$APPLICATION->IncludeComponent(
			"bitrix:advertising.banner",
			"bootstrap_v4",
			array(
				"COMPONENT_TEMPLATE" => "bootstrap_v4",
				"TYPE" => "MAIN",
				"NOINDEX" => "Y",
				"QUANTITY" => "3",
				"BS_EFFECT" => "fade",
				"BS_CYCLING" => "N",
				"BS_WRAP" => "Y",
				"BS_PAUSE" => "Y",
				"BS_KEYBOARD" => "Y",
				"BS_ARROW_NAV" => "Y",
				"BS_BULLET_NAV" => "Y",
				"BS_HIDE_FOR_TABLETS" => "N",
				"BS_HIDE_FOR_PHONES" => "Y",
				"CACHE_TYPE" => "A",
				"CACHE_TIME" => "36000000",
			),
			false
		);?>
	</div>
<?endif?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>