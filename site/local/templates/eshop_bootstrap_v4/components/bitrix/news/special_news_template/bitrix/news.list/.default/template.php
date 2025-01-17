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

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';
?>

<div class="d-flex flex-column">
    <div><?= GetMessage('SPECIAL_NEWS_SECTION_BLOCK_TITLE') ?></div>
    <div class="d-flex mb-3" style="column-gap: 5px;">
        <?php foreach ($arResult["SECTIONS"] as $SECTION) { ?>
            <a class="d-block border border-primary text-primary"
               style="padding: 5px 8px;<?php if ($SECTION['IS_ACTIVE']) { ?>box-shadow: inset 0 0 0 1px var(--primary);<?php }?>"
               href="<?= $SECTION['FULL_URL']?>">
                <?= htmlspecialcharsbx($SECTION['NAME']) ?>
            </a>
        <?php } ?>
    </div>
</div>

<div class="article-list--wrapper">
    <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?><br/>
    <? endif; ?>

    <div class="article-list<?= $themeClass ?>">
        <?php foreach ($arResult["ITEMS"] as $arItem) { ?>
            <?php
            $this->AddEditAction(
                $arItem['ID'],
                $arItem['EDIT_LINK'],
                CIBlock::GetArrayByID(
                    $arItem["IBLOCK_ID"],
                    "ELEMENT_EDIT"
                )
            );
            $this->AddDeleteAction(
                $arItem['ID'],
                $arItem['DELETE_LINK'],
                CIBlock::GetArrayByID(
                    $arItem["IBLOCK_ID"],
                    "ELEMENT_DELETE"),
                array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))
            );

            /** доступность ссылки если
             * 1) не указано "скрывать ссылку когда нет отдельной страницы"
             * или
             * 2) есть "детальный текст статьи" и права пользователя
             */
            $detail_link_available = !$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"]);
            $showing_title_available = $arParams["DISPLAY_NAME"] != "N" && $arItem["NAME"];
            $showing_description_available = $arParams["DISPLAY_PREVIEW_TEXT"] != "N" && $arItem["PREVIEW_TEXT"];
            $showing_img_available = $arParams["DISPLAY_PICTURE"] != "N";
            $showing_img_exists = isset($arItem["PREVIEW_PICTURE"]) && is_array($arItem["PREVIEW_PICTURE"]);
            ?>
            <a class="article-item article-list__item" id="<?= $this->GetEditAreaId($arItem['ID']); ?>"
                <?php if ($detail_link_available) { ?> href="<?= $arItem["DETAIL_PAGE_URL"] ?>"<?php } ?>>
                <div class="article-item__background">

                    <?php if ($showing_img_available) { ?>

                    <?php // here can be video/slider block instead image ?>
                    <?php if ($showing_img_exists) { ?>
                    <img class="card-img-top"
                         src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                         alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                         title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                    />
                    <?php } ?>

                    <?php } ?>
                </div>

                <div class="article-item__wrapper">
                    <?php if ($showing_title_available) { ?>
                        <div class="article-item__title"><?= $arItem["NAME"] ?></div>
                    <?php } ?>
                    <?php if ($showing_description_available) { ?>
                        <div class="article-item__content"><?= $arItem["PREVIEW_TEXT"] ?></div>
                    <?php } ?>
                </div>
            </a>
        <?php } ?>
    </div>

    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?>
    <? endif; ?>
</div>