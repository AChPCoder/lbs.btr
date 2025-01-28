<?php

namespace Dev\Site\Handlers;

class Iblock
{
    private static $log_iBlock_code = 'LOG';

    // region DB helper functions

    public static function getIBlockByData($code = null, $id = null)
    {
        $filter_data = [];

        if (isset($code)) {
            $filter_data['CODE'] = $code;
        }

        if (isset($id)) {
            $filter_data['ID'] = $id;
        }

        if (empty($filter_data)) {
            return null;
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $listResult = \CIBlock::GetList(array("SORT" => "ASC"), $filter_data);

        return $listResult->Fetch();
    }


    public static function getIBlockSectionByData($iblock_id = null, $id = null, $iblock_section_id = null, $name = null)
    {
        $filter_data = [];

        if (isset($iblock_id)) {
            $filter_data['IBLOCK_ID'] = $iblock_id;
        }

        if (isset($iblock_section_id)) {
            $filter_data['IBLOCK_SECTION_ID'] = $iblock_section_id;
        }

        if (isset($id)) {
            $filter_data['ID'] = $id;
        }

        if (isset($name)) {
            $filter_data['NAME'] = $name;
        }

        if (empty($filter_data)) {
            return null;
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $listResult = \CIBlockSection::GetList(array("SORT" => "ASC"), $filter_data);

        return $listResult->Fetch();
    }

    public static function getIBlockElemByData($iBlockId, $id)
    {
        $filter_data = [];

        if (isset($id)) {
            $filter_data['ID'] = $id;
        }

        if (isset($iBlockId)) {
            $filter_data['IBLOCK_ID'] = $iBlockId;
        }

        if (empty($filter_data)) {
            return null;
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $listResult = \CIBlockElement::GetList(array("SORT" => "ASC"), $filter_data);

        return $listResult->Fetch();
    }

    // endregion

    // region debug helpers

    private static function errLog($err)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log_iblock_logging_error.txt', $err . "\n", FILE_APPEND);
    }

    private static function successLog($msg)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log_iblock_logging_success.txt', $msg . "\n", FILE_APPEND);
    }

    // endregion

    // region Near DB helpers

    private static $log_iBlock_id = null;

    public static function getLogIBlockId()
    {
        if (isset(self::$log_iBlock_id)) {
            return self::$log_iBlock_id;
        }

        $cur_iBlock = self::getIBlockByData(self::$log_iBlock_code);

        if (!isset($cur_iBlock)) {
            return null;
        }

        return $cur_iBlock['ID'] ?? null;
    }

    /** Получение имени элемента лога
     * @param $id string ИД элемента инфоблока, из которого надо взять его имя и код
     * @return string|null
     */
    public static function getLogItemSectionById($id)
    {
        $cur_iBlock = self::getIBlockByData(null, $id);

        if (!isset($cur_iBlock)) {
            return null;
        }

        $name = $cur_iBlock['NAME'];
        $code = $cur_iBlock['CODE'];

        return $code . '|' . $name;
    }

    /** Получение описания для анонса с учетом указанного ИД элемента инфоблока и ИД инфоблока
     * @param $id string ИД элемента инфоблока
     * @param $iBlockId string ИД инфоблока
     * @return string|null Строка описания
     */
    public static function getLogItemDescriptionByIdAndIBlockId($id, $iBlockId)
    {
        $cur_iBlockElem = self::getIBlockElemByData($iBlockId, $id);
        if (!isset($cur_iBlockElem)) {
            return null;
        }

        $cur_iBlock = self::getIBlockByData(null, $cur_iBlockElem['IBLOCK_ID']);

        if (!isset($cur_iBlock)) {
            return null;
        }

        $cur_iBlock_name = $cur_iBlock['NAME'];

        $sections = [];

        if (isset($cur_iBlockElem['IBLOCK_SECTION_ID'])) {
            $closestParentSection = self::getIBlockSectionByData($cur_iBlock['ID'], $cur_iBlockElem['IBLOCK_SECTION_ID']);

            $is_ok = isset($closestParentSection) && $closestParentSection !== false;
            if ($is_ok) {
                $sections[] = $closestParentSection['NAME'];
                $i = 0;
                while ($is_ok) {
                    if ($i > 1000) {
                        self::errLog('Ошибка с получение описания!');
                        break;
                    }
                    $cur_cycle_parent_id = $closestParentSection['IBLOCK_SECTION_ID'];
                    if (!isset($cur_cycle_parent_id)) {
                        break;
                    }
                    $closestParentSection = self::getIBlockSectionByData(null, $cur_cycle_parent_id);
                    $is_ok = isset($closestParentSection) && $closestParentSection !== false;
                    if ($is_ok) {
                        $sections[] = $closestParentSection['NAME'];
                    }
                    $i++;
                }
            }

            unset($closestParentSection);
        }

        $separator = ' -> ';

        $section_part = '';
        if (!empty($sections)) {
            $sections = array_reverse($sections);
            $section_part = implode($separator, $sections);
        }
        $cur_iBlockElem_name = $cur_iBlockElem['NAME'];

        $res_parts = [$cur_iBlock_name, $section_part, $cur_iBlockElem_name];
        $res_parts = array_filter($res_parts);

        return implode($separator, $res_parts);
    }

    public static function getLogSectionByName($section_name)
    {
        $iblock_id = self::getLogIBlockId();

        return self::getIBlockSectionByData($iblock_id, null, null, $section_name);
    }

    public static function createLogSectionByName($section_name)
    {
        $iblock_id = self::getLogIBlockId();
        $IBlockSectionInstance = new \CIBlockSection();

        $addData = [
            'IBLOCK_ID' => $iblock_id,
            'NAME' => $section_name,
        ];

        $add_id = $IBlockSectionInstance->Add($addData);

        return boolval($add_id);
    }

    // endregion

    /** Логирование вех инфоблоков, кроме `LOG`
     * <br/> `OnAfterIBlockElementAdd` & `OnAfterIBlockElementUpdate`
     */
    public static function addLog(&$arFields)
    {
        $log_iBlockId = self::getLogIBlockId();
        $excludeIBlockId = $log_iBlockId;
        if (!isset($excludeIBlockId)) {
            self::errLog('Нет инфоблока для логирования!');
            // не логируем если нет инфоблока для логирования
            return;
        }
        $currentIBlockElemId = $arFields['ID'];
        $currentIBlockId = $arFields['IBLOCK_ID'];
        $is_ok = $excludeIBlockId != $currentIBlockId;
        if (!$is_ok) {
            // отсекаем элементы инфоблока для логирования
            return;
        }

        $section_name = \Dev\Site\Handlers\Iblock::getLogItemSectionById($currentIBlockId);

        $sectionElem = self::getLogSectionByName($section_name);

        $is_ok = isset($sectionElem) && $sectionElem !== false;
        if (!$is_ok) {
            if (!self::createLogSectionByName($section_name)) {
                // если нет секции и не удалось её создать, то выходим
                self::errLog('Нет секции и не удалось её создать!');
                return;
            }

            $sectionElem = self::getLogSectionByName($section_name);
            $is_ok = isset($sectionElem) && $sectionElem !== false;
            if (!$is_ok) {
                self::errLog('Не найдена секция но она была создана!');
                return;
            }
        }

        $log_iBlock_section_id = $sectionElem['ID'];
        $log_iBlock_name = $currentIBlockElemId;
        $log_iBlock_start = time(); // timestamp of add/edit iblock
        $log_iBlock_anons_descr = \Dev\Site\Handlers\Iblock::getLogItemDescriptionByIdAndIBlockId(
            $currentIBlockElemId, $currentIBlockId
        );

        $currentSavedLogWithThatIdQuery = \CIBlockElement::GetList(
            ["SORT" => "ASC"],
            [
                'IBLOCK_ID' => $log_iBlockId,
                'NAME' => $log_iBlock_name,
            ],
            false,
            false,
            ['ID', 'IBLOCK']
        );

        $currentSavedLogWithThatId = $currentSavedLogWithThatIdQuery->Fetch();
        $needEdit = isset($currentSavedLogWithThatId) && isset($currentSavedLogWithThatId['ID']);
        $editIblockId = $needEdit ? $currentSavedLogWithThatId['ID'] : null;

        $el = new \CIBlockElement();

        global $USER;

        $itemData = [
            'MODIFIED_BY' => $USER->GetID(),
            'IBLOCK_ID' => intval($log_iBlockId),
            'IBLOCK_SECTION_ID' => intval($log_iBlock_section_id),
            'NAME' => $log_iBlock_name,
            'ACTIVE_FROM' => \Bitrix\Main\Type\DateTime::createFromTimestamp($log_iBlock_start),
            'PREVIEW_TEXT' => $log_iBlock_anons_descr,
        ];


        if ($needEdit) {
            $is_ok = $el->Update($editIblockId, $itemData);
        } else {
            $add_id = $el->Add($itemData);
            $is_ok = boolval($add_id);
        }

        if ($is_ok) {
            self::successLog(print_r([$needEdit ? $editIblockId : $add_id, $itemData], true));
        } else {
            self::errLog(print_r($itemData, true));
        }
    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }
}
