<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */

/** Поля формы (ака вопросы веб-формы) */
$form_fields = $arResult["QUESTIONS"];

// ключи необходимых обычных полей формы с их переводами
$current_form_fields = [
    'name' => [
        'key' => 'name',
        'caption' => GetMessage('FORM_CW_FIELD_CAPTION__NAME'),
    ],
    'company' => [
        'key' => 'company',
        'caption' => GetMessage('FORM_CW_FIELD_CAPTION__COMPANY'),
    ],
    'email' => [
        'key' => 'email',
        'caption' => GetMessage('FORM_CW_FIELD_CAPTION__EMAIL'),
    ],
    'phone' => [
        'key' => 'phone',
        'caption' => GetMessage('FORM_CW_FIELD_CAPTION__PHONE'),
    ],
];

// ключ текстового поля формы с их переводами
$current_form_field__message = [
    'key' => 'message',
    'caption' => GetMessage('FORM_CW_FIELD_CAPTION__MESSAGE'),
];

$form_data_arr = [];

/** Вывод обычных инпутов */
$keys = array_keys($current_form_fields);
foreach ($form_fields as $FIELD_SID => $arQuestion) {
    /** @var $FIELD_SID  string символьный код вопроса */
    if(!in_array($FIELD_SID, $keys)) {
        continue;
    }

    $is_error = isset($arResult["FORM_ERRORS"][$FIELD_SID]);

    // указание необходимых атрибутов верстки для вывода поля
    $input_extra_attrs = [
        'class'=>'input__input',
        'id' => $FIELD_SID,
    ];

    // указание атрибута обязательности для вывода поля
    if($arQuestion['REQUIRED']) {
        $input_extra_attrs['required'] = "";
    }

    // данные для вывода обычного поля
    $form_data_arr[$FIELD_SID] = [
        'key' => $FIELD_SID,
        'label' => $arQuestion['CAPTION'],
        'required' => $arQuestion['REQUIRED'],
        'input_form_element' => \App\Helpers\ViewH::WebFormInputAddAttributes($arQuestion['HTML_CODE'], 'input', $input_extra_attrs),
        'is_error'=> $is_error,
        'error_str'=> !$is_error ? '' : $arResult["FORM_ERRORS"][$FIELD_SID],
    ];
}

// вывод текстового поля сообщения
$form_data_message = null;
$form_data_message_key = $current_form_field__message['key'];
if(isset($form_fields[$form_data_message_key])) {
    // данные поля сообщения
    $arQuestion__message = $form_fields[$form_data_message_key];

    $is_error = isset($arResult["FORM_ERRORS"][$form_data_message_key]);

    // атрибуты поля сообщения
    $input_extra_attrs = [
        'class'=>'input__input',
        'id' => $form_data_message_key,
    ];

    // данные для вывода поля сообщения
    $form_data_message = [
        'key' => $form_data_message_key,
        'label' => $arQuestion__message['CAPTION'],
        'required' => $arQuestion__message['REQUIRED'],
        'input_form_element' => \App\Helpers\ViewH::WebFormInputAddAttributes($arQuestion__message['HTML_CODE'], 'textarea', $input_extra_attrs),
        'is_error' => $is_error,
        'error_str' => !$is_error ? '' : $arResult["FORM_ERRORS"][$form_data_message_key],
    ];
}

?>

<div class="contact-form__wrapper">
    <div class="contact-form">
        <div class="contact-form__head">
            <div class="contact-form__head-title"><?= GetMessage('FORM_CW_TITLE') ?></div>
            <div class="contact-form__head-text"><?= GetMessage('FORM_CW_TITLE_AFTERWORDS') ?></div>
        </div>

        <?php if ($arResult["isFormErrors"] == "Y") { ?><?= $arResult["FORM_ERRORS_TEXT"]; ?><?php } ?>
        <?= $arResult["FORM_NOTE"] ?? '' ?>

        <?php if ($arResult["isFormNote"] != "Y") { ?><?php // если форма вернула не успех ?>
        <form class="contact-form__form" name="<?=$arResult["WEB_FORM_NAME"]?>" action="<?= POST_FORM_ACTION_URI ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="WEB_FORM_ID" value="<?=$arParams["WEB_FORM_ID"]?>"><?php // ид формы ?>
            <?= bitrix_sessid_post() ?><?php // ид сессии ?>

            <div class="contact-form__form-inputs">
                <?php foreach ($form_data_arr as $form_data_item) { ?>
                    <div class="input contact-form__input">
                        <label class="input__label" for="<?= $form_data_item['key'] ?>">
                            <span class="input__label-text"><?= $form_data_item['label'] ?>*</span>
                            <?= $form_data_item['input_form_element'] ?>
                            <?php if ($form_data_item['is_error']) { ?>
                            <span class="input__notification"><?= htmlspecialcharsbx($form_data_item['error_str']) ?></span>
                            <?php } ?>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <div class="contact-form__form-message">
                <?php if (isset($form_data_message)) { ?>
                    <div class="input">
                        <label class="input__label" for="<?= $form_data_message['key'] ?>">
                            <span class="input__label-text"><?= $form_data_message['label'] ?></span>
                            <?= $form_data_message['input_form_element'] ?>
                            <?php if ($form_data_message['is_error']) { ?>
                                <span class="input__notification"><?= htmlspecialcharsbx($form_data_message['error_str']) ?></span>
                            <?php } ?>
                        </label>
                    </div>
                <?php } ?>
            </div>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy"><?= GetMessage('FORM_CW_BOTTOM_POLICY') ?></div>
                <button type="submit" class="form-button contact-form__bottom-button" data-success="Отправлено"
                        data-error="Ошибка отправки">
                    <span class="form-button__title"><?= GetMessage('FORM_CW_SUBMIT') ?></span>
                </button>
                <input type="hidden" name="web_form_submit" value="Y"><?php // важный скрытый инпут, без которого заполненная форма будет проигнорирована ?>
            </div>
        </form>
        <?php }  //endif (isFormNote) ?>
    </div>
</div>
