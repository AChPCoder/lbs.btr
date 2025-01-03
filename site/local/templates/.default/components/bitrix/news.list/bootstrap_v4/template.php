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

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';
?>

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
            ?>
            <a class="article-item article-list__item" id="<?= $this->GetEditAreaId($arItem['ID']); ?>"
                <?php if ($detail_link_available) { ?> href="<?= $arItem["DETAIL_PAGE_URL"] ?>"<?php } ?>>
                <div class="article-item__background">

                    <?php if ($arParams["DISPLAY_PICTURE"] != "N") { ?>
                        <?php if ($arItem["VIDEO"] ?? null) { ?>
                            <div class="news-list-item-embed-video embed-responsive embed-responsive-16by9">
                                <iframe
                                        class="embed-responsive-item"
                                        src="<? echo $arItem["VIDEO"] ?>"
                                        frameborder="0"
                                        allowfullscreen=""
                                ></iframe>
                            </div>
                        <?php } elseif ($arItem["SOUND_CLOUD"] ?? null) { ?>
                            <div class="news-list-item-embed-audio embed-responsive embed-responsive-16by9">
                                <iframe
                                        class="embed-responsive-item"
                                        width="100%"
                                        scrolling="no"
                                        frameborder="no"
                                        src="https://w.soundcloud.com/player/?url=<? echo urlencode($arItem["SOUND_CLOUD"]) ?>&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"
                                ></iframe>
                            </div>
                        <?php // создание слайдера если передан массив элементов ?>
                        <?php } elseif (isset($arItem["SLIDER"]) && is_array($arItem["SLIDER"]) && count($arItem["SLIDER"]) > 1) { ?>
                            <div class="news-list-item-embed-slider">
                                <div class="news-list-slider-container"
                                     style="width: <?= count($arItem["SLIDER"]) * 100 ?>%;left: 0;">
                                    <?
                                    foreach ($arItem["SLIDER"] as $file):?>
                                        <div class="news-list-slider-slide">
                                            <img src="<?= $file["SRC"] ?>" alt="<?= $file["DESCRIPTION"] ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="news-list-slider-arrow-container-left">
                                    <div class="news-list-slider-arrow"><i class="fa fa-angle-left"></i></div>
                                </div>
                                <div class="news-list-slider-arrow-container-right">
                                    <div class="news-list-slider-arrow"><i class="fa fa-angle-right"></i></div>
                                </div>
                                <ul class="news-list-slider-control">
                                    <?php foreach ($arItem["SLIDER"] as $i => $file): ?>
                                        <li rel="<?= ($i + 1) ?>" <? if (!$i) {
                                            echo 'class="current"';
                                        } ?>><span></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <script>
                                BX.ready(function () {
                                    new JCNewsSlider('<?=CUtil::JSEscape($this->GetEditAreaId($arItem['ID']));?>', {
                                        imagesContainerClassName: 'news-list-slider-container',
                                        leftArrowClassName: 'news-list-slider-arrow-container-left',
                                        rightArrowClassName: 'news-list-slider-arrow-container-right',
                                        controlContainerClassName: 'news-list-slider-control'
                                    });

                                    const anchor = document.querySelector('#<?= CUtil::JSEscape($this->GetEditAreaId($arItem['ID'])); ?>');
                                    if (anchor !== null) {
                                        JANewsListSlider(anchor, ['.news-list-slider-arrow-container-left', '.news-list-slider-arrow-container-right']);
                                    }
                                });
                            </script>
                        <?php // если параметры слайдера есть, но задан только 1 элемент ?>
                        <?php } elseif ($arItem["SLIDER"] ?? null) { ?>
                        <img
                                class="card-img-top"
                                src="<?= $arItem["SLIDER"][0]["SRC"] ?>"
                                width="<?= $arItem["SLIDER"][0]["WIDTH"] ?>"
                                height="<?= $arItem["SLIDER"][0]["HEIGHT"] ?>"
                                alt="<?= $arItem["SLIDER"][0]["ALT"] ?>"
                                title="<?= $arItem["SLIDER"][0]["TITLE"] ?>"
                        />
                        <?php // если есть превью картинка и в виде массива ?>
                        <?php } elseif (isset($arItem["PREVIEW_PICTURE"]) && is_array($arItem["PREVIEW_PICTURE"])) { ?>
                        <img
                                class="card-img-top"
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
