<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$showing_img_available_n_exists = $arParams["DISPLAY_PICTURE"] != "N" && is_array($arResult["DETAIL_PICTURE"]);
$showing_name_available_n_exists = $arParams["DISPLAY_NAME"] != "N" && $arResult["NAME"];
$showing_date_available_n_exists = $arParams["DISPLAY_DATE"] != "N" && $arResult["DISPLAY_ACTIVE_FROM"];

?>

<div class="article-card">
    <div class="article-card__title">
        <?php if ($showing_name_available_n_exists) { ?>
        <?= $arResult["NAME"] ?>
        <?php } ?>
    </div>
    <div class="article-card__date" data-format="15 авг 2019">
    <?php if($showing_date_available_n_exists) { ?>
    <?= $arResult["DISPLAY_ACTIVE_FROM"] ?>
    <?php } ?>
    </div>

    <div class="article-card__content">
        <div class="article-card__image sticky">
            <?php if ($showing_img_available_n_exists) { ?>
            <img src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>"
                 data-width="<?= $arResult["DETAIL_PICTURE"]["WIDTH"] ?>"
                 data-height="<?= $arResult["DETAIL_PICTURE"]["HEIGHT"] ?>"
                 alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"
                 title="<?= $arResult["DETAIL_PICTURE"]["TITLE"] ?>"
                 data-object-fit="cover"
            />
            <?php } ?>
        </div>
        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && ($arResult["FIELDS"]["PREVIEW_TEXT"] ?? '')):?>
                    <p><?=$arResult["FIELDS"]["PREVIEW_TEXT"];unset($arResult["FIELDS"]["PREVIEW_TEXT"]);?></p>
                <?endif;?>
                <br data-mark="==="/>

                <?php if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && ($arResult["FIELDS"]["PREVIEW_TEXT"] ?? '')) { ?>
                    <p><?php echo $arResult["FIELDS"]["PREVIEW_TEXT"]; unset($arResult["FIELDS"]["PREVIEW_TEXT"]); ?></p>
                <?php }?>

                <?php if($arResult["NAV_RESULT"]) { ?>
                    <?php if ($arParams["DISPLAY_TOP_PAGER"]) { ?><?= $arResult["NAV_STRING"] ?><br/><?php } ?>
                    <?= $arResult["NAV_TEXT"]; ?>
                    <?php if ($arParams["DISPLAY_BOTTOM_PAGER"]) { ?><br/><?= $arResult["NAV_STRING"] ?><?php } ?>
                <?php } elseif($arResult["DETAIL_TEXT"] <> ''){ ?>
                    <?= $arResult["DETAIL_TEXT"]; ?>
                <?php } else { ?>
                    <?= $arResult["PREVIEW_TEXT"]; ?>
                <?php } ?>
            </div>
            <a class="article-card__button" href="<?=$arParams["SPECIAL_NEWS_LIST_BACK_LINK"]?>"><?=GetMessage("T_NEWS_DETAIL_BACK")?></a>
        </div>
    </div>
</div>