<?php

/** @global CUser $USER */

// подключение 1c bitrix api
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
// запрет пользователям, неавторизованным как админ
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}

// region Блок данных для импорта данных инфоблока

// свойства с типом список - [ACTIVITY,FIELD,OFFICE,LOCATION,TYPE,SALARY_TYPE,SCHEDULE]
/** @var $target_iBlockData array  Дополнительные данные для импорта в инфоблок */
$target_iBlockData = [
    'SID' => 'VACANCIES',
    'PROPS' => [
        'ACTIVITY' => [
            'iblockKey' => 'ACTIVITY',
            'csvColumnIndex' => 9, // R10C1 или J1
            // ACTIVITY Тип занятости [List]
        ],
        'FIELD' => [
            'iblockKey' => 'FIELD',
            'csvColumnIndex' => 11, // R12C1 или L1
            // FIELD Сфера деятельности [List]
        ],
        'OFFICE' => [
            'iblockKey' => 'OFFICE',
            'csvColumnIndex' => 1, // R2C1 или B1
            // OFFICE Комбинат/Офис [List]
        ],
        'LOCATION' => [
            'iblockKey' => 'LOCATION',
            'csvColumnIndex' => 2, // R3C1 или C1
            // LOCATION Местоположение [List]
        ],
        'REQUIRE' => [
            'iblockKey' => 'REQUIRE',
            'csvColumnIndex' => 4, // R5C1 или E1
            // REQUIRE Требования к соискателю
        ],
        'DUTY' => [
            'iblockKey' => 'DUTY',
            'csvColumnIndex' => 5, // R6C1 или F1
            // DUTY Основные обязанности
        ],
        'CONDITIONS' => [
            'iblockKey' => 'CONDITIONS',
            'csvColumnIndex' => 6, // R7C1 или G1
            // CONDITIONS Условия работы
        ],
        'EMAIL' => [
            'iblockKey' => 'EMAIL',
            'csvColumnIndex' => 12, // R13C1 или M1
            // EMAIL Электронная почта (e-mail)
        ],
        'DATE' => [
            'iblockKey' => 'DATE',
            // DATE Дата размещения
            'csvColumnIndex' => null, // ставим текущую дату
        ],
        'TYPE' => [
            'iblockKey' => 'TYPE',
            'csvColumnIndex' => 8, // R9C1 или I1
            // TYPE Тип вакансии [List]
        ],
        'SALARY_TYPE' => [
            'iblockKey' => 'SALARY_TYPE',
            'csvColumnIndex' => null, // определяем по строке "Зарплата"
            // SALARY_TYPE Заработная плата [List]
        ],
        'SALARY_VALUE' => [
            'iblockKey' => 'SALARY_VALUE',
            'csvColumnIndex' => 7, // R8C1 или H1
            // SALARY_VALUE Заработная плата (значение)
        ],
        'SCHEDULE' => [
            'iblockKey' => 'SCHEDULE',
            'csvColumnIndex' => 10, // R11C1 или K1
            // SCHEDULE График работы [List]
        ],
    ],
];
$iBlockElementName_csvColumnIndex = 3;
$iBlockActiveState_csvColumnIndex = 14;

// endregion

// подключаем модуль инфоблоков
\Bitrix\Main\Loader::includeModule('iblock');

/** Остановка выполнения импорта с выводом одного сообщения
 * @param $msg string
 */
$fn_stopExecution = function ($msg) {
    echo $msg;
    exit;
};

// region Блок получения инфоблока по его символьному коду

// получение элементов инфоблока с символьным_ид=VACANCIES
$iBlocks = CIBlock::GetList([], ['CODE' => $target_iBlockData['SID']]);
$cur_iBlock = $iBlocks->GetNext();
$target_iBlockData['ID'] = $cur_iBlock['ID'] ?? null;

// работаем только когда есть инфоблок для импорта
if (!isset($target_iBlockData['ID'])) {
    $fn_stopExecution('Инфоблок для импорта не найден');
}

// endregion

// region Блок получения возможных значений свойств с типом "Список"

// собираем существующие варианты значений типа "Список" из настроек свойств инфоблока
$rsElement = CIBlockPropertyEnum::getList([], [
    'IBLOCK_ID' => $target_iBlockData['ID']
]);

/** Дополнительная очистка значения для значения свойства "Комбинат/Офис"
 * @param $old_key string старый ключ значения из списка
 * @return string новый ключ значения из списка
 */
$fn_extraOfficeKeyListItemPrepare = function ($old_key) {
    $new_key = str_replace(['»', '«', '(', ')'], '', $old_key);
    $new_key_arr = explode(' ', $new_key);
    $new_key_arr = array_filter($new_key_arr, function ($el) {
        return $el !== '';
    });
    return implode(' ', $new_key_arr);
};

/** Свойства с типом "Список" и их значения в настройках инфоблока  */
$properties_with_list_type = [];
while ($el = $rsElement->Fetch()) {
    $property_code = $el['PROPERTY_CODE'];
    if (!key_exists($property_code, $properties_with_list_type)) {
        $properties_with_list_type[$property_code] = [
            'property_id' => $el['PROPERTY_ID'],
            'property_code' => $el['PROPERTY_CODE'],
            'property_name' => $el['PROPERTY_NAME'],
            'values' => []
        ];
    }
    $property_item_id = $el['ID'];
    $property_item_key = str_replace("&nbsp;", '', $el['VALUE']);
    $property_item_key = preg_replace('/[,.]/', '', $property_item_key);
    $property_item_key = trim($property_item_key);
    $property_item_key = mb_strtolower($property_item_key);
    if ($property_code == 'OFFICE') {
        $property_item_key = $fn_extraOfficeKeyListItemPrepare($property_item_key);
    }
    $properties_with_list_type[$property_code]['values'][$property_item_key] = $property_item_id;
}
unset($property_code, $property_item_id, $property_item_key);

// endregion

/** Для удаления старых значений в инфоблоке "Вакансии"
 * @param string $IBLOCK_ID ИД инфоблока
 */
$fn_removeOldIBlockValues = function ($IBLOCK_ID) {
    // получение элементов инфоблока с ид=$IBLOCK_ID
    $rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);

    // удаление всех элементов инфоблока с ид=$IBLOCK_ID
    while ($element = $rsElements->GetNext()) {
        CIBlockElement::Delete($element['ID']);
    }
};

$csv_file_path = __DIR__ . '/vacancy.csv';
if (!file_exists($csv_file_path)) {
    $fn_stopExecution('Файл для импорта не найден');
}

// удаляем старые значения из ИБ "Вакансии"
$fn_removeOldIBlockValues($target_iBlockData['ID']);

$handle = fopen($csv_file_path, "r");
$is_ok = $handle !== false;
if (!$is_ok) {
    $fn_stopExecution('Ошибка чтения файла для импорта как csv');
}

// region Блок для определения не оформленных опций

/** Это нужно для уведомления об неудачных попытках сопоставить значение и элемент списка
 * @var $not_found_assignments_for_list array массив с неудавшимися попытками найти соотвествие значения из импорта и варианта из списка
 */
$not_found_assignments_for_list = [];
/** Сохраним неудачную попытку сопоставить значение и элемент списка
 * @param $key string
 * @param $v string
 */
$fn_saveNotFoundKeyValue = function ($key, $v) use (&$not_found_assignments_for_list) {
    if (!key_exists($key, $not_found_assignments_for_list)) {
        $not_found_assignments_for_list[$key] = [];
    }
    if (!in_array($v, $not_found_assignments_for_list[$key])) {
        $not_found_assignments_for_list[$key][] = $v;
    }
};

// endregion

// region Блок определения и форматирования значений свойств

/** Преобразуем значение из csv в соответствии с вводимым свойством
 * @param $key string
 * @param $value string
 */
$fn_formatPropValueFromCsvRow = function ($key, $value) use ($properties_with_list_type, $fn_saveNotFoundKeyValue) {
    $new_value = trim($value);
    $new_value = str_replace("\n", '', $new_value);

    // если данный ключ - это значение для свойства с типом "Список"
    $is_exist_prop_with_list_type_at_key = key_exists($key, $properties_with_list_type);
    if ($is_exist_prop_with_list_type_at_key) {
        $csv_row_value = str_replace("&nbsp;", '', $new_value);
        $csv_row_value = preg_replace('/[,.]/', '', $csv_row_value);
        $csv_row_value = mb_strtolower($csv_row_value);

        // ... то ищем ИД уже существующего значения из всех значений свойства типа "Список"

        // [Начало]: изменение значения поля с сырого текста на его ИД в списке значений для свойства инфоблока
        $arSimilar = [];
        $found = false;
        // перебираем значения свойств с данным ключом
        foreach ($properties_with_list_type[$key]['values'] as $propKey => $propVal) {
            /** @var $propKey string ключ-имя значения свойства типа "Список" */
            /** @var $propVal string ИД значения свойства типа "Список" */

            if ($key == 'OFFICE') {
                $arSimilar[similar_text($csv_row_value, $propKey)] = $propVal;
            }

            // для всех совпадений по ключу (это относится ко всем свойствам с типом "Список")
            if (mb_strlen($csv_row_value) && mb_stripos($propKey, $csv_row_value) !== false) {
                $new_value = $propVal;
                $found = true;
                break;
            }

            if ($key == 'OFFICE' && !is_numeric($csv_row_value)) {
                ksort($arSimilar);
                $new_value = array_pop($arSimilar);
                $found = true;
            }
        }
        if (!$found && $csv_row_value !== '') {
            $fn_saveNotFoundKeyValue($key, $csv_row_value);
        }
        // [Конец]: изменение значения поля с сырого текста на его ИД в списке значений для свойства инфоблока

        return $new_value;
    }
    // если это не из списка, значит работаем как со значением

    // если это строка с маркированным списком, значит делим на строки по маркеру, очищаем их, и выводим массив
    // массив в качестве значения принимается у свойств с установленным параметром "множественное"
    // в csv это есть у свойств REQUIRE, DUTY, CONDITIONS - реализация подразумевает, что символ маркера в csv будет только для них
    $is_contains_bullet_point = stripos($value, '•') !== false;
    if ($is_contains_bullet_point) {
        $new_value = explode('•', $new_value);
        $new_value = array_map('trim', $new_value);
        $new_value = array_filter($new_value);
    }

    return $new_value;
};

/** Получение значения зарплаты по данным зарплаты для строки импорта
 * @param string $prop_salary_value значение ячейки с зарплатой (из csv)
 * @return array
 */
$fn_getSalaryValueAndType = function ($prop_salary_value) use ($properties_with_list_type) {
    // если прочерк в ячейке цены
    $is_empty = $prop_salary_value == '-' || $prop_salary_value == '';
    if ($is_empty) {
        return [
            'SALARY_VALUE' => '',
            'SALARY_TYPE' => '',
        ];
    }

    // если по договоренности, то указываем тип, но значение пустым
    $is_contract = $prop_salary_value == 'по договоренности';
    if ($is_contract) {
        return [
            'SALARY_VALUE' => '',
            'SALARY_TYPE' => $properties_with_list_type['SALARY_TYPE']['values']['договорная'],
        ];
    }

    // разбираем цену по пробелам: если первое слово - `от` или `до`, то указываем тип "от" или "до" и значение все остальные части записи цены
    $arSalary = explode(' ', $prop_salary_value);
    $arSalary = array_filter($arSalary, function ($el) {
        return $el !== '';
    });

    $to_or_from = $arSalary[0];
    $is_from_or_before = $to_or_from == 'от' || $to_or_from == 'до';
    if ($is_from_or_before) {
        $res = [];
        $res['SALARY_TYPE'] = $properties_with_list_type['SALARY_TYPE']['values'][$to_or_from];
        array_splice($arSalary, 0, 1);
        $res['SALARY_VALUE'] = implode(' ', $arSalary);
        return $res;
    }

    // рассматриваем цену как равную указанной
    return [
        'SALARY_VALUE' => $prop_salary_value,
        'SALARY_TYPE' => $properties_with_list_type['SALARY_TYPE']['values']['='],
    ];
};

// endregion

$row = 1;

$el = new CIBlockElement();

$today_dmy_format = 'd.m.Y';

while (($data = fgetcsv($handle, 1000, ",")) !== false) {
    // region Чтение первой строки с заголовками

    if ($row == 1) {
        $row++;
        continue;
    }

    // endregion
    $row++;

    // region Данные строки csv, которые нужно взять для импорта

    // возьмем параметры по их расположению в csv файле (индексы с 0)
    /** @var array $PROP список всех полей из CSV - ключи это символьные коды в инфоблоке "Вакансии" */
    $PROP = [];

    foreach ($target_iBlockData['PROPS'] as $prop_key => $prop_value_data) {
        switch ($prop_key) {
            case 'DATE':
                $PROP_value = date($today_dmy_format);
                break;
            case 'SALARY_TYPE':
                $PROP_value = '';
                break;
            default:
                $PROP_value = $data[$prop_value_data['csvColumnIndex']];
                $PROP_value = $fn_formatPropValueFromCsvRow($prop_key, $PROP_value);
                break;
        }

        $PROP[$prop_key] = $PROP_value;
    }

    $pair_salary_value_n_type = $fn_getSalaryValueAndType($PROP['SALARY_VALUE']);
    $PROP['SALARY_TYPE'] = $pair_salary_value_n_type['SALARY_TYPE'];
    $PROP['SALARY_VALUE'] = $pair_salary_value_n_type['SALARY_VALUE'];

    $iBlockElementName = $data[$iBlockElementName_csvColumnIndex];

    $is_row_active = boolval($data[$iBlockActiveState_csvColumnIndex]);

    // endregion

    // region Формирование полей импорта и добавление элемента

    $arLoadProductArray = [
        "MODIFIED_BY" => $USER->GetID(),
        "IBLOCK_SECTION_ID" => false,
        "IBLOCK_ID" => $target_iBlockData['ID'],
        "PROPERTY_VALUES" => $PROP,
        "NAME" => $iBlockElementName,
        "ACTIVE" => $is_row_active ? 'Y' : 'N',
    ];

    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
        echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
    } else {
        echo "Error: " . $el->LAST_ERROR . '<br>';
    }

    // endregion
}

if (!empty($not_found_assignments_for_list)) {
    echo 'Есть не распознанные значения по свойствам с типом "Список", которых возможно нет среди вариантов значений самого свойства инфоблока:' . "\n" . '<br/>';
    foreach ($not_found_assignments_for_list as $key => $value_arr) {
        echo '• для свойства с символьным кодом "' . $key . '" это значения: "' . implode('", "', $value_arr) . '"' . "\n" . '<br/>';
    }
}

fclose($handle);