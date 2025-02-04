<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

/** @var array $ITEMS */
$ITEMS = $arResult["ITEMS"];
/** @var bool $IS_GROUPED_ELEMS */
$IS_GROUPED_ELEMS = $arResult["IS_GROUPED_ELEMS"];

?>
<div class="custom_components--container">
    <div class="ccc--list">
        <?php if (empty($ITEMS)) { ?>
            <div><?= GetMessage('CUSTOM_ALLNEWS_TEMPLATE_NO_ITEMS') ?></div>
        <?php } else { ?>
            <?php if ($IS_GROUPED_ELEMS) { ?>
                <?php foreach ($ITEMS as $iblock_id => $ITEM_group) { ?>
                    <h2 class="ccc--l-item-group">
                        <?= str_replace('#ID#',$iblock_id,GetMessage('CUSTOM_ALLNEWS_TEMPLATE_GROUP_NAME_TEMPLATE')) ?>
                    </h2>
                    <?php foreach ($ITEM_group as $id => $ITEM) { ?>
                        <div class="ccc--l-item">
                            <h3>
                                <?= $ITEM['NAME'] ?>
                            </h3>
                            <div>
                                <?= $ITEM['PREVIEW_TEXT'] ?>
                            </div>
                            <a href="<?= $ITEM["DETAIL_PAGE_URL"] ?>"><?= GetMessage('CUSTOM_ALLNEWS_TEMPLATE_GO_TO_DETAIL') ?></a>
                        </div>
                    <?php } ?>
                <?php } ?>
            <?php } else { ?>
                <?php foreach ($ITEMS as $id => $ITEM) { ?>
                    <div class="ccc--l-item">
                        <h2>
                            <?= $ITEM['NAME'] ?>
                        </h2>
                        <div>
                            <?= $ITEM['PREVIEW_TEXT'] ?>
                        </div>
                        <a href="<?= $ITEM["DETAIL_PAGE_URL"] ?>"><?= GetMessage('CUSTOM_ALLNEWS_TEMPLATE_GO_TO_DETAIL') ?></a>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<style>
    .custom_components--container, .ccc--list, .ccc--l-item {
        display: flex;
        flex-direction: column;
        position: relative;
        width: 100%;
    }
</style>
