<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/** Класс компонента, настроенный на работу без файла component.php */
class CCustomComponentsAllNewsList extends CBitrixComponent
{
    // region Подготовка входных параметров

    private function getStdParams()
    {
        return [
            'IBLOCK_TYPE' => 'news', // string
            'IBLOCK_ID' => null, // int | int[]
            'FILTER_FIELDS' => [], // array
            'NEWS_COUNT' => 3, // int > 0
            'DETAIL_URL' => '', // string
            'SECTION_URL' => '', // string
            'LIST_URL' => '', // string
        ];
    }

    /** Подготавливаем параметры компонента
     * <br/>
     * Event called from includeComponent before component execution.
     * Takes component parameters as argument and should return it formatted as needed.
     *
     * @param $arParams array Параметры компонента, переданные через использование CMain::IncludeComponent
     * @return array Prepared new $arParams
     * @see \CBitrixComponent::onPrepareComponentParams
     * @see \CMain::IncludeComponent
     */
    public function onPrepareComponentParams($arParams)
    {
        $stdParams = $this->getStdParams();

        foreach ($stdParams as $key => $value) {
            if(key_exists($key, $arParams)) {
                switch ($key) {
                    case 'NEWS_COUNT':
                    case 'IBLOCK_ID':
                        $v = $arParams[$key];
                        $is_ok_as_int = is_numeric($v);
                        if ($is_ok_as_int) {
                            $arParams["${key}_RAW"] = $v;
                            $arParams[$key] = intval($v);
                        }
                        break;
                    case 'DETAIL_URL':
                    case 'SECTION_URL':
                    case 'LIST_URL':
                        $arParams[$key] = trim($arParams[$key]);
                        break;
                }

                continue;
            }

            // устанавливаем значения по умолчанию
            $arParams["$key"] = $value;
        }

        return $arParams;
    }

    // endregion

    // region private class functions

    private $filterFieldsErrors = [];

    private function validateFilterFields()
    {
        $filter_fields = $this->arParams['FILTER_FIELDS'];

        // region Валидация параметра фильтров полей
        if (!is_array($filter_fields)) {
            $this->filterFieldsErrors[] = 'Неверный формат фильтра, параметр должен быть массивом';
            return false;
        }
        // endregion

        // region Пропуск, если фильтр не задан, но является пустым массивом
        if (count($filter_fields) == 0) {
            return true;
        }
        // endregion

        foreach ($filter_fields as $key => $params) {
            // region Валидация ключа поля
            $is_ok_key = key_exists($key, self::$stdFieldKeys);
            if (!$is_ok_key) {
                $this->filterFieldsErrors[] = str_replace('#KEY#', $key, Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_KEY'));
                continue;
            }
            // endregion

            $type_from_std_arr = self::$stdFieldKeys[$key];

            // region Валидация ключа типа фильтра
            $type = $params['type'] ?? $type_from_std_arr;
            $all_types_prefixes = self::getStdFieldsAllowedOpsAsPrefixByType();
            $all_keys_for_types = array_keys($all_types_prefixes);
            $is_ok_type = in_array($type, $all_keys_for_types);
            if (!$is_ok_type) {
                $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_TYPE');
                $err_msg = str_replace('#KEY#', $key, $err_msg);
                $err_msg = str_replace('#TYPE#', $type, $err_msg);
                $this->filterFieldsErrors[] = $err_msg;
                continue;
            }
            // endregion

            // region Валидация префикса фильтра
            $prefix = $params['prefix'] ?? null;
            $current_prefixes_for_type = $all_types_prefixes[$type];
            $is_ok_prefix = in_array($prefix, $current_prefixes_for_type);
            if (!$is_ok_prefix) {
                $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_PREFIX');
                $err_msg = str_replace('#KEY#', $key, $err_msg);
                $err_msg = str_replace('#TYPE#', $type, $err_msg);
                $err_msg = str_replace('#PREFIX#', $type, $err_msg);
                $this->filterFieldsErrors[] = $err_msg;
                continue;
            }
            // endregion

            // region Валидация значения фильтра
            $value = $params['value'] ?? null;
            switch ($type) {
                case "string":
                    $is_ok_value = is_string($value);
                    if (!$is_ok_value) {
                        $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_VALUE_STR');
                        $err_msg = str_replace('#KEY#', $key, $err_msg);
                        $this->filterFieldsErrors[] = $err_msg;
                    }
                    break;
                case "number":
                    $is_ok_value = is_numeric($value);
                    if (!$is_ok_value) {
                        $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_VALUE_NUM');
                        $err_msg = str_replace('#KEY#', $key, $err_msg);
                        $this->filterFieldsErrors[] = $err_msg;
                    }
                    break;
                case "date":
                case "time":
                    try {
                        $dt = new \Bitrix\Main\Type\DateTime($value, 'Y-m-d H:i:s');
                    } catch (\Exception) {
                        $dt = null;
                    }

                    $is_ok_value = isset($dt);
                    if (!$is_ok_value) {
                        $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_VALUE_DT');
                        $err_msg = str_replace('#KEY#', $key, $err_msg);
                        $this->filterFieldsErrors[] = $err_msg;
                    } else {
                        $this->arParams['FILTER_FIELDS'][$key]['value_raw'] = $value;
                        $this->arParams['FILTER_FIELDS'][$key]['value'] = $dt;
                    }
                    break;
                default:
                    $err_msg = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_FIELD_FILTER_VALUE_UNKNOWN');
                    $err_msg = str_replace('#KEY#', $key, $err_msg);
                    $this->filterFieldsErrors[] = $err_msg;
                    $is_ok_value = false;
                    break;
            }
            if (!$is_ok_value) {
                return false;
            }
            // endregion
        }

        // region Возвращать ошибку валидации, если есть сообщения об ошибке
        if (!empty($this->filterFieldsErrors)) {
            return false;
        }
        // endregion

        return true;
    }

    // region Поля для фильтрации
    private static $stdFieldKeys = [
        "ID" => 'number',
        "CODE" => 'string',
        "XML_ID" => 'string',
        "NAME" => 'string',
        "TAGS" => 'string',
        "SORT" => 'number',
        "PREVIEW_TEXT" => 'string',
        "PREVIEW_PICTURE" => 'number',
        "DETAIL_TEXT" => 'string',
        "DETAIL_PICTURE" => 'number',
        "DATE_ACTIVE_FROM" => 'date',
        "ACTIVE_FROM" => 'time',
        "DATE_ACTIVE_TO" => 'date',
        "ACTIVE_TO" => 'time',
        "SHOW_COUNTER" => 'number',
        "SHOW_COUNTER_START" => 'date',
        "IBLOCK_TYPE_ID" => 'string',
        "IBLOCK_ID" => 'number',
        "IBLOCK_CODE" => 'string',
        "IBLOCK_NAME" => 'string',
        "IBLOCK_EXTERNAL_ID" => 'string',
        "DATE_CREATE" => 'date',
        "CREATED_BY" => 'number',
        "CREATED_USER_NAME" => 'string',
        "TIMESTAMP_X" => 'date',
        "MODIFIED_BY" => 'number',
        "USER_NAME" => 'string',
    ];

    private static $stdFieldsAllowedOps = null;

    private static function getStdFieldsAllowedOpsAsPrefixByType()
    {
        if (isset(self::$stdFieldsAllowedOps)) {
            return self::$stdFieldsAllowedOps;
        }
        $empty_str = [''];
        $negate_str = ['!', '!=', '!%', '!?'];
        $equal_str = ['='];
        $equal_num_n_date_str = ['=', '%', '?'];
        $registry_independent_search_str = ['%', '!%'];
        $diapason_str = ['><', '!><'];
        $more_n_less_str = ['>', '<', '>=', '<=', '!>', '!<', '!>=', '!<='];
        $false_str = ['false', '!false'];

        self::$stdFieldsAllowedOps = [
            'string' => array_merge(
                $empty_str,
//                $diapason_str,
                $equal_str,
                $negate_str,
                $registry_independent_search_str,
                $more_n_less_str,
            ),
            'number' => array_merge(
                $empty_str,
                $false_str,
//                $diapason_str,
                $equal_num_n_date_str,
                $negate_str,
                $more_n_less_str,
            ),
            'date' => array_merge(
                $empty_str,
                $false_str,
//                $diapason_str,
                $equal_num_n_date_str,
                $negate_str,
                $more_n_less_str,
            ),
            'time' => array_merge(
                $empty_str,
                $false_str,
//                $diapason_str,
                $equal_num_n_date_str,
                $negate_str,
                $more_n_less_str,
            ),
        ];

        return self::$stdFieldsAllowedOps;
    }

    // endregion

    /** @var $iblock_types array? */
    private static $iblock_types = null;

    private static function getIBlockTypes()
    {
        if (isset(self::$iblock_types)) {
            return self::$iblock_types;
        }
        $ciblocktyperesult = \CIBlockType::GetList();
        $res_iblock_els_ids = [];
        while ($arrResultRow = $ciblocktyperesult->Fetch()) {
            $res_iblock_els_ids[] = $arrResultRow['ID'];
        }
        self::$iblock_types = $res_iblock_els_ids;
        return self::$iblock_types;
    }

    /** Получение новостей
     * @param $iblock_type string
     */
    private function getIblockIdsList($iblock_type)
    {
        $arFilter = [
            "ACTIVE" => "Y",
            'TYPE' => $iblock_type,
            "SITE_ID" => SITE_ID,
        ];

        $ciblockresult = \CIBlock::GetList(
            ['SORT' => 'ASC',],
            $arFilter
        );

        // получение ид элементов инфоблока
        $res_iblock_ids = [];
        while ($arrResultRow = $ciblockresult->Fetch()) {
            $res_iblock_ids[] = (int)$arrResultRow['ID'];
        }

        return $res_iblock_ids;
    }

    /** Получение новостей
     * @param $iblock_id int|int[]
     */
    private function getListItemIds($iblock_id, $limit = null)
    {
        $arFilter = [
            'IBLOCK_ID' => $iblock_id,
            "ACTIVE" => "Y",
            "SITE_ID" => SITE_ID,
        ];

        if ($this->arParams['FILTER_FIELDS']) {
            foreach ($this->arParams['FILTER_FIELDS'] as $FILTER_FIELD_key => $FILTER_FIELD) {
                $filter_name = $FILTER_FIELD['prefix'] . $FILTER_FIELD_key;
                $arFilter[$filter_name] = $FILTER_FIELD['value'];
            }
        }

        $arNavStartParams = false;

        $is_ok_limit = isset($limit) && $limit > 0;
        if($is_ok_limit) {
            $arNavStartParams = [
                'nPageSize' => $limit,
            ];
        }

        $ciblockresult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            $arNavStartParams,
            ['ID', 'IBLOCK_ID']
        );

        // получение ид элементов инфоблока
        $res_iblock_els_ids = [];
        while ($arrResultRow = $ciblockresult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $res_iblock_els_ids[$id] = [
                'ID' => $id,
                'IBLOCK_ID' => intval($arrResultRow['IBLOCK_ID']),
            ];
        }

        return $res_iblock_els_ids;
    }

    /** Получение новостей (их данных)
     * @param $iblock_id int ид инфоблока для улучшения запроса
     * @param $iblock_elem_ids int[] массив ид элементов инфоблока уже отфильтрованных по нужным условям ранее
     */
    private function getListOfElems($iblock_id, $iblock_elem_ids, $detail_url='', $section_url='', $list_url='')
    {
        $arFilter = [
            'IBLOCK_ID' => $iblock_id,
            'ID' => $iblock_elem_ids,
        ];
        $ciblockresult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            [
                "ID",
                "IBLOCK_ID",
                "IBLOCK_SECTION_ID",
                "NAME",
                "ACTIVE_FROM",
                "TIMESTAMP_X",
                "DETAIL_PAGE_URL",
                "LIST_PAGE_URL",
                "DETAIL_TEXT",
                "DETAIL_TEXT_TYPE",
                "PREVIEW_TEXT",
                "PREVIEW_TEXT_TYPE",
                "PREVIEW_PICTURE",
            ]
        );

        $ciblockresult->SetUrlTemplates($detail_url, $section_url, $list_url);

        // получение данных элементов инфоблока
        $res_iblock_els = [];
        while ($arrResultRow = $ciblockresult->GetNext()) {
            $id = (int)$arrResultRow['ID'];
            $res_iblock_els[$id] = $arrResultRow;
        }

        return $res_iblock_els;
    }

    private function isIblockIdEmptyString()
    {
        return isset($this->arParams['IBLOCK_ID']) && is_string($this->arParams['IBLOCK_ID'])
            && trim($this->arParams['IBLOCK_ID']) == '';
    }

    // endregion

    /** It`s a override method code for using component without component.php, only with class.php, template.php and others
     * @return mixed Value, which will be returned at `->IncludeComponent`
     */
    public function executeComponent()
    {
        // подключаем файл переводов (./lang/<lang_id>/class.php)
        Loc::loadLanguageFile(__FILE__);

        /** Вернуть значение вызова компонента */
        $fn_return_data = function ($v = null) {
            return $v ?? ('' . self::class);
        };
        $this->arResult['ITEMS'] = [];

        // region Валидация входных параметров

        $this->arResult['errors'] = [];

        // проверяем тип инфоблока, если он из существующих, то проверка типа пройдена
        $is_ok_iblock_type = isset($this->arParams['IBLOCK_TYPE']) && is_string($this->arParams['IBLOCK_TYPE'])
            && in_array($this->arParams['IBLOCK_TYPE'], self::getIBlockTypes());
        if (!$is_ok_iblock_type) {
            $this->arResult['errors'][] = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_IBLOCK_TYPE');
        }

        // ИД инфоблока может быть пустым значением, тогда пропускаем валидацию ид ИБ
        $is_iblock_id_ok_for_check = !$this->isIblockIdEmptyString();
        if (isset($this->arParams['IBLOCK_ID']) && $is_iblock_id_ok_for_check) {
            // когда это не пустая строка, ждем в переменной число или массив чисел

            $is_ok_iblock_id_as_int = is_numeric($this->arParams['IBLOCK_ID']) && intval($this->arParams['IBLOCK_ID']) > 0;
            $is_ok_iblock_id_as_int_array = is_array($this->arParams['IBLOCK_ID'])
                && array_reduce($this->arParams['IBLOCK_ID'], function ($prev, $current) {
                    return $prev && is_numeric($current) && intval($current) > 0;
                }, true);
            $is_ok_iblock_id = $is_ok_iblock_id_as_int || $is_ok_iblock_id_as_int_array;
            if (!$is_ok_iblock_id) {
                $this->arResult['errors'][] = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_IBLOCK_ID');
            }
        }

        // проверяем число новостей на странице
        $is_ok_news_count = isset($this->arParams['NEWS_COUNT']) && is_numeric($this->arParams['NEWS_COUNT'])
            && intval($this->arParams['NEWS_COUNT']) > 0;
        if (!$is_ok_news_count) {
            $this->arResult['errors'][] = Loc::getMessage('CUSTOM_ALLNEWS_ERROR_NEWS_COUNT');
        }

        // проверяем фильтры
        $is_filter_ok = $this->validateFilterFields();
        if (!$is_filter_ok) {
            $this->arResult['errors'] = array_merge($this->arResult['errors'], $this->filterFieldsErrors);
        }

        // если при проверке произошли ошибки, то останавливаем получение новостей и выводим сообщение об ошибках
        if (!empty($this->arResult['errors'])) {
            $err_arr = $this->arResult['errors'];
            ShowError(implode("\n", $err_arr));
            return $fn_return_data('Ошибка!');
        }

        // endregion

        // region Проверяем, задан ли ид инфоблока

        $is_need_get_by_type = !(isset($this->arParams['IBLOCK_ID']) && !$this->isIblockIdEmptyString());
        if ($is_need_get_by_type) { // получаем ид инфоблока для типа инфоблока
            $iblock_ids = array_map('intval', $this::getIblockIdsList($this->arParams['IBLOCK_TYPE']));
        } else { // берем ид инфоблока из параметров
            $iblock_ids = $this->arParams['IBLOCK_ID'];
        }

        // endregion

        // region Получаем ИД элементов инфоблоков (или одного инфоблока), группируем если нужно

        /** ИД элементов инфоблока (возможно их нужно группировать) */
        $iblock_elems_ids = $this->getListItemIds($iblock_ids, $this->arParams['NEWS_COUNT']);

        $this->arResult['IS_GROUPED_ELEMS'] = false;
        if (is_array($iblock_ids)) { // нужно ли группировать? В случае получения из нескольких инфоблоков - да
            // здесь $iblock_ids int[]
            $this->arResult['IS_GROUPED_ELEMS'] = true;
            $grouped_iblock_elems = [];
            foreach ($iblock_elems_ids as $iblock_elems_id) {
                /** @var int $cycle_iblock_id */
                $cycle_iblock_id = $iblock_elems_id['IBLOCK_ID'];
                if (!key_exists($cycle_iblock_id, $grouped_iblock_elems)) {
                    $grouped_iblock_elems[$cycle_iblock_id] = [];
                }
                $grouped_iblock_elems[$cycle_iblock_id][$iblock_elems_id['ID']] = [
                    'IBLOCK_ID' => $cycle_iblock_id, // int
                    'ID' => $iblock_elems_id['ID'], // int
                ];
            }
            unset($cycle_iblock_id);

            // заполняем сгруппированные по инфоблокам элементы данными
            /** @var array $grouped_iblock_elem_in_one_iblock */
            foreach ($grouped_iblock_elems as $cycle_iblock_id => &$grouped_iblock_elem_in_one_iblock) {
                /** @var int[] $ids */
                $ids = array_column($grouped_iblock_elem_in_one_iblock, 'ID');

                $elem_data = $this->getListOfElems(
                    $cycle_iblock_id, $ids, $this->arParams["DETAIL_URL"], '', ($this->arParams["IBLOCK_URL"] ?? '')
                );

                foreach ($elem_data as $elem_item_data) {
                    $id = intval($elem_item_data['ID']);
                    $grouped_iblock_elem_in_one_iblock[$id] = array_merge($grouped_iblock_elem_in_one_iblock[$id], $elem_item_data);
                }
            }
            unset($grouped_iblock_elem_in_one_iblock);

            $this->arResult['ITEMS'] = $grouped_iblock_elems;

            $this->includeComponentTemplate();

            return $fn_return_data('');
        }

        // endregion

        // region Получение данных элементов (запрос данных уже полученных ИД)

        $iblock_elems_data = [] + $iblock_elems_ids;
        if(!empty($iblock_elems_data)) {
            $ids = array_column($iblock_elems_data, 'ID');
            $elem_data = $this->getListOfElems(
                $iblock_ids, $ids, $this->arParams["DETAIL_URL"], '', ($this->arParams["IBLOCK_URL"] ?? '')
            ); // здесь $iblock_ids int

            foreach ($elem_data as $elem_item_data) {
                $id = intval($elem_item_data['ID']);
                $iblock_elems_data[$id] = array_merge($iblock_elems_data[$id], $elem_item_data);
            }
        }

        $this->arResult['ITEMS'] = $iblock_elems_data;

        // endregion

        // работает как echo содержимого шаблона компонента
        $this->includeComponentTemplate();
        return $fn_return_data('');
    }
}