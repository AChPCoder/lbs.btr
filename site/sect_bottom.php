<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="row">
	<div class="col-12">
		<?if (IsModuleInstalled("advertising")):?>
			<div class="mb-3">
				<?$APPLICATION->IncludeComponent(
					"bitrix:advertising.banner",
					"parallax",
					array(
						"COMPONENT_TEMPLATE" => "parallax",
						"TYPE" => "PARALLAX",
						"NOINDEX" => "Y",
						"QUANTITY" => "1",
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
						"SCALE" => "N",
						"CYCLING" => "N",
						"EFFECTS" => "",
						"ANIMATION_DURATION" => "500",
						"WRAP" => "1",
						"ARROW_NAV" => "1",
						"BULLET_NAV" => "2",
						"KEYBOARD" => "N",
						"EFFECT" => "random",
						"SPEED" => "500",
						"JQUERY" => "Y",
						"DIRECTION_NAV" => "Y",
						"CONTROL_NAV" => "Y",
						"PARALL_HEIGHT" => "400",
						"HEIGHT" => "400"
					),
					false
				);?>
			</div>
		<?endif?>
	</div>
</div>
