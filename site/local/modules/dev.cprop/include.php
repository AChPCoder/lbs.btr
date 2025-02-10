<?php

\Bitrix\Main\Loader::registerAutoLoadClasses('dev.cprop', [
    'CIBlockPropertyCProp' => 'lib/CIBlockPropertyCProp.php',
    'UserTypeCPropBase' => 'lib/usefield/types/UserTypeCPropBase.php',
    'UserTypeCProp' => 'lib/usefield/types/UserTypeCProp.php',
]);

/* Событие срабатывает перед запуском скриптов визуального редактора */
AddEventHandler("fileman", "OnBeforeHTMLEditorScriptRuns", "vb_OnBeforeHTMLEditorScriptRuns");

/** Подключение логики исправления визуального редактора (так как он не принимает ключи инпута с квадратными скобками)
 * @return void
 * @see site/bitrix/js/fileman/html_editor/html-editor.js Здесь нужные события
 */
function vb_OnBeforeHTMLEditorScriptRuns()
{
    GLOBAL $APPLICATION;

    //Скрипт, добавляющий новые кнопки в визуальный редактор - указывать путь от папки .../site/
    $APPLICATION->AddHeadScript('/local/modules/dev.cprop/js/CPropFilemanAdjustments.js');
}