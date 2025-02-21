<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

class CTestTaskComponentsTestTask extends CBitrixComponent
{
    /** Это метод, переопределенный для использования компонента без файла `component.php`, только с `class.php`, и
     * другими файлами если необходимо (template.php и т.д.)
     * <blockquote>Метод не будет вызван при ajax запросе</blockquote>
     * @return mixed Значение, что будет возвращено на `->IncludeComponent`
     */
    public function executeComponent()
    {
        // подключаем файл переводов (./lang/<lang_id>/class.php)
        // Loc::loadLanguageFile(__FILE__);

        // region Получение $_GET параметров

        $datetime_from = $_GET['datetime_from'] ?? null;
        $datetime_to = $_GET['datetime_to'] ?? null;
        $checkedParams = $this->validateParameters([
            self::GET_KEY_DATETIME_FROM => $datetime_from,
            self::GET_KEY_DATETIME_TO => $datetime_to,
        ]);
        $is_ok = $checkedParams['is_ok_datetime_from'] && $checkedParams['is_ok_datetime_to'];

        if (!$is_ok) {
            echo '<div style="color: #E81123; font-weight: bold; display: flex; flex-direction: column">';
            echo '<div>Ошибки в параметрах для компонента</div>';
            foreach ($checkedParams['errors'] as $error) {
                echo '<div>'.$error.'</div>';
            }
            echo '</div>';
            return '';
        }

        // region Готовые и проверенные параметры

        $datetime_from = $checkedParams['preparedParams'][self::GET_KEY_DATETIME_FROM];
        $datetime_to = $checkedParams['preparedParams'][self::GET_KEY_DATETIME_TO];

        // endregion

        // region Сбор нужных ид инфоблоков

        $this->prepareIBlockData();

        $null_id_item_codes = [];
        if(isset(self::$iblocksForAWork)) {
            foreach (self::$iblocksForAWork as $item) {
                if(!isset($item['ID'])) {
                    $null_id_item_codes[] = $item['CODE'];
                }
            }
        }
        $is_ok = empty($null_id_item_codes);
        if(!$is_ok) {
            echo '<div style="color: #E81123; font-weight: bold; display: flex; flex-direction: column">';
            echo '<div>Ошибки в нехватке инфоблоков для компонента</div>';
            echo '<div>Нет инфоблоков с символьными кодами:'.implode(', ', $null_id_item_codes).'</div>';
            echo '</div>';
            return '';
        }

        // endregion

        // endregion

        // region Данные, что используются компонентом

        $employeeId = $this->arParams['employeeId'] ?? 0;

        $employee = $this->getEmployeeById($employeeId);

        // Проверка параметра сотрудника
        $is_ok = isset($employee);
        if(!$is_ok) {
            echo '<div style="color: #E81123; font-weight: bold; display: flex; flex-direction: column">';
            echo '<div>Ошибки данных сотрудника</div>';
            echo '<div>Сотрудника с ИД="' . (int)$employeeId . '" не существует</div>';
            echo '</div>';
            return '';
        }

        // Вывод для сверки входных параметров и результата получения списка
        echo '<div style="display:flex; flex-direction: column">';
            $parts = [
                '<b>Входные данные</b>',
                'Сотрудник: ' . $employee['NAME'] . ' [ИД=' . $employee['ID'] . ']',
                'Время начала поездки: ' . $datetime_from->toString(),
                'Время окончания поездки: ' . $datetime_to->toString(),
            ];
            echo '<div>';
            echo implode('</div><div>', $parts);
            echo '</div>';

        echo '</div>';

        // endregion

        // region Получение ИД машин, подходящих под условия

        /** @var int[]|null $allowedComfortTypes */
        $allowedComfortTypes_IdsArr = $this->getAllowedComfortTypesByEmployeeId($employeeId);

        // Для должности сотрудника обязательно должны быть доступны уровни комфорта служебного автомобиля
        $is_ok = isset($allowedComfortTypes_IdsArr) && count($allowedComfortTypes_IdsArr);
        if(!$is_ok) {
            echo '<div style="color: #E81123; font-weight: bold; display: flex; flex-direction: column">';
            echo '<div>Ошибки данных сотрудника</div>';
            echo '<div>Для сотрудника нет заданных уровней комфорта</div>';
            echo '</div>';
            return '';
        }

        // Получение занятых машин с нужными типами комфорта
        /** @var int[]|null $busyCarsAtRangeAndWithComfortTypes_IdsArr */
        $busyCarsAtRangeAndWithComfortTypes_IdsArr = $this->getBusyCarsAtRangeAndWithComfortTypes($datetime_from, $datetime_to, $allowedComfortTypes_IdsArr);
        $busyCarsAtRangeAndWithComfortTypes_IdsArr ??= [];

        // Получение ИД машин, подходящих под условия и незанятых в других бронированиях
        $carsIds = $this->getAllowedCarsByExcludeBusyCars($allowedComfortTypes_IdsArr, $busyCarsAtRangeAndWithComfortTypes_IdsArr);

        // Предупреждение об отсутствии доступных машин при указанных входных параметрах
        $is_ok = isset($carsIds) && count($carsIds);
        if(!$is_ok) {
            echo '<div style="color: #bfb30d; font-weight: bold; display: flex; flex-direction: column">';
            echo '<div>Для сотрудника нет машин</div>';
            echo '<div>Для сотрудника нет машин, доступных по условиям</div>';
            echo '</div>';
            return '';
        }

        // endregion

        // region Получение данных машин, подходящих под условия

        $cars = $this->getCarsWithDataByCarsIds($carsIds);

        echo '<div style="color: #1fbf0d; font-weight: bold; display: flex; flex-direction: column">';
        echo '<div>Для сотрудника есть машины для бронирования!</div>';

        echo '<div style="display:flex; flex-direction: column">';
        foreach ($cars as $car) {
            echo '  <div>';
            $model_name = $car['PROPS'][self::IBIPC_COMPANY_CAR_MODEL_NAME]['VALUES'][0]['VALUE'];
            $comfort_type = $car[self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT];
            $comfort_type_name = isset($comfort_type) ? $comfort_type['NAME'] : '';
            $driver = $car[self::IBIPC_DRIVER_ID_EXT];
            $driver_fio = isset($comfort_type) ? $driver['NAME'] : '';
            $parts = [
                'Машина',
                'Модель: ' . $model_name,
            ];
            if (!empty($comfort_type_name)) {
                $parts[] = 'Уровень комфорта: ' . $comfort_type_name;
            }
            if (!empty($driver_fio)) {
                $parts[] = 'Водитель: ' . $driver_fio;
            }
            echo '<span>';
            echo implode(' | </span><span>', $parts);
            echo '</span>';
            echo '  </div>';
        }
        echo '</div>';

        echo '</div>';

        // endregion

        // $this->includeComponentTemplate(); // работает как echo содержимого шаблона компонента
        return '';
    }

    // region Валидация входных параметров

    private const GET_KEY_DATETIME_FROM = 'from';
    private const GET_KEY_DATETIME_TO = 'to';

    private function validateParameters($params = [])
    {
        $errors = [];
        $preparedParams = [];
        $fn_validate_dt = function ($v, $err_key, &$errors_arr) {
            if(!isset($v)) {
                $errors_arr[$err_key] = $err_key . ': Не задано значение даты с временем';
                return false;
            }

            $preparedV = trim($v);

            if(empty($preparedV)) {
                $errors_arr[$err_key] = $err_key . ': Пустое значение даты с временем';
                return false;
            }

            $is_valid_format = preg_match('//', $preparedV, $matches);
            if($is_valid_format !== 1) {
                $errors_arr[$err_key] = $err_key . ': Значение даты с временем должны быть в формате YYYY-MM-DDTHH-II:SS';
                return false;
            }

            return true;
        };
        $is_ok_datetime_from = $fn_validate_dt($params[self::GET_KEY_DATETIME_FROM] ?? null, self::GET_KEY_DATETIME_FROM, $errors);
        if($is_ok_datetime_from) {
            $preparedParams[self::GET_KEY_DATETIME_FROM] = \Bitrix\Main\Type\DateTime::createFromPhp(
                date_create_from_format('Y-m-d\TH:i:s', trim($params[self::GET_KEY_DATETIME_FROM]))
            );
        }
        $is_ok_datetime_to = $fn_validate_dt($params[self::GET_KEY_DATETIME_TO] ?? null, self::GET_KEY_DATETIME_TO, $errors);
        if($is_ok_datetime_to) {
            $preparedParams[self::GET_KEY_DATETIME_TO] = \Bitrix\Main\Type\DateTime::createFromPhp(
                date_create_from_format('Y-m-d\TH:i:s', trim($params[self::GET_KEY_DATETIME_TO]))
            );
        }

        return compact('is_ok_datetime_from', 'is_ok_datetime_to', 'errors', 'preparedParams');
    }

    // endregion

    // region Получение данных инфоблоков для дальнейшего получения элементов инфоблоков

    // region Символьные коды инфоблоков

    private const IBLOCK_CODE_OF_COMPANY_CAR = 'company_car';
    private const IBLOCK_CODE_OF_COMPANY_CAR_COMFORT_TYPE = 'company_car_comfort_type';
    private const IBLOCK_CODE_OF_COMPANY_CAR_DRIVER = 'company_car_driver';
    private const IBLOCK_CODE_OF_COMPANY_CAR_BOOKING = 'company_car_booking';
    private const IBLOCK_CODE_OF_EMPLOYEE = 'employee';
    private const IBLOCK_CODE_OF_JOB = 'job';
    private const IBLOCK_CODE_OF_JOB_N_COMFORT_TYPE = 'job_and_comfort_type';

    // endregion

    // region Символьные коды свойств

    // IBIPC - InfoBLock Item Property Code

    // region Driver entity

    /** Внешний ключ для связи с сущностью "Водитель"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR
     */
    private const IBIPC_DRIVER_ID_EXT = 'driver_id_ext';

    // endregion

    // region Job entity

    /** Внешний ключ для связи с сущностью "Должность"
     * @see self::IBLOCK_CODE_OF_EMPLOYEE
     * @see self::IBLOCK_CODE_OF_JOB_N_COMFORT_TYPE
     */
    private const IBIPC_JOB_ID_EXT = 'job_id_ext';

    // endregion

    // region Company car entity

    /** Внешний ключ для связи с сущностью "Служебный автомобиль"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING
     */
    private const IBIPC_COMPANY_CAR_ID_EXT = 'company_car_id_ext';

    /** Символьный код свойства "Название модели" сущности "Служебный автомобиль"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING
     */
    private const IBIPC_COMPANY_CAR_MODEL_NAME = 'model_name';

    // endregion

    // region Company car comfort type entity

    /** Внешний ключ для связи с сущностью "Уровень комфорта служебного автомобиля"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR
     * @see self::IBLOCK_CODE_OF_JOB_N_COMFORT_TYPE
     */
    private const IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT = 'company_car_comfort_type_id_ext';

    // endregion

    // region Company car booking entity

    /** Символьный код свойства "Время начала поездки"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING
     */
    private const IBIPC_COMPANY_CAR_BOOKING_FROM = 'company_car_booking_busy_from';

    /** Символьный код свойства "Время конца поездки"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING
     */
    private const IBIPC_COMPANY_CAR_BOOKING_TO = 'company_car_booking_busy_to';

    // endregion

    // region Employee entity

    /** Внешний ключ для связи с сущностью "Сотрудник"
     * @see self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING
     */
    private const IBIPC_EMPLOYEE_EXT_ID = 'employee_id_ext';

    // endregion

    // endregion

    /** Данные инфоблоков, соответствие символьных кодов и ИД инфоблоков
     * <blockquote>ИД инфоблоков меняются при создании и удалении (то есть миграций),
     * поэтому делаем код зависимым от символьных кодов</blockquote>
     * @var array{ID:int|null,CODE:string}[]|null
     */
    private static $iblocksForAWork = null;

    /** Получим ИД инфоблоков по их кодам */
    private function prepareIBlockData()
    {
        if(isset(self::$iblocksForAWork)) {
            return;
        }

        $curData = [];

        $keys = [
            self::IBLOCK_CODE_OF_EMPLOYEE,
            self::IBLOCK_CODE_OF_COMPANY_CAR,
            self::IBLOCK_CODE_OF_COMPANY_CAR_DRIVER,
            self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING,
            self::IBLOCK_CODE_OF_COMPANY_CAR_COMFORT_TYPE,
            self::IBLOCK_CODE_OF_JOB,
            self::IBLOCK_CODE_OF_JOB_N_COMFORT_TYPE,
        ];

        foreach ($keys as $key) {
            $id = $this->getIBlockIdByCode($key);
            $curData[$key] = [
                'ID' => $id,
                'CODE' => $key,
            ];
        }

        self::$iblocksForAWork = $curData;
    }

    /** Получение ИД инфоблока по его символьному коду
     * @param string $code Символьный код
     * @return int|null ИД инфоблока
     */
    private function getIBlockIdByCode($code) {
        $listResult = \CIBlock::GetList(["SORT" => "ASC"], [
            'CODE' => $code,
        ]);
        $listResultFetched = $listResult->Fetch();
        return isset($listResultFetched) ? intval($listResultFetched['ID']) : null;
    }

    // endregion

    // region Справочные данные сотрудника

    /** Получение Данных сотрудника
     * @param int $employeeId ИД сотрудника
     * @return array{ID: int,NAME: string}|null
     */
    private function getEmployeeById($employeeId)
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_EMPLOYEE]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Сотрудники"
            'ID'=> $employeeId, // фильтруем по ид сотрудника (хранится в свойстве элемента)
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME']
        );

        $arrResultRow = $CIBlockResult->Fetch();
        $is_ok = isset($arrResultRow) && $arrResultRow !== false;
        if(!$is_ok) {
            return null;
        }

        $id = (int)$arrResultRow['ID'];
        return [
            'ID' => $id,
            'NAME' => $arrResultRow['NAME']
        ];
    }

    // endregion

    // region Получение допустимых уровней комфорта для пользователя

    /** Получение списка уровней комфорта, доступных сотруднику
     * @param int $employeeId ИД сотрудника
     * @return int[]|null Массив ИД элементов инфоблока "Уровни комфорта"
     */
    private function getAllowedComfortTypesByEmployeeId($employeeId)
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_JOB_N_COMFORT_TYPE]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $jobElementId = $this->getJobIdByEmployeeId($employeeId);

        if(!isset($jobElementId)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Допустимые уровни комфорта"
            'PROPERTY_' . self::IBIPC_JOB_ID_EXT => $jobElementId, // фильтруем по ид должности (хранится в свойстве элемента)
        ];

        $comfort_type_ext_id_select_key = 'PROPERTY_' . strtoupper(self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT);
        $comfort_type_ext_id_key = $comfort_type_ext_id_select_key . '_VALUE';

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', $comfort_type_ext_id_select_key]
        );

        $resIblockElsIds = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow[$comfort_type_ext_id_key];
            $resIblockElsIds[] = $id;
        }

        return $resIblockElsIds;
    }

    /** Получения ИД должности для сотрудника
     * <blockquote>ИД должности сотрудника хранится в свойстве сотрудника</blockquote>
     * @param int $employeeId ИД сотрудника
     * @return int|null ИД должности указанного сотрудника
     */
    private function getJobIdByEmployeeId($employeeId)
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_EMPLOYEE]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $employeeProps = $this->getPropsForIBlockElement($iblock_id, $employeeId);

        if (!key_exists(self::IBIPC_JOB_ID_EXT, $employeeProps)) {
            return null;
        }

        $jobIdProp = $employeeProps[self::IBIPC_JOB_ID_EXT]; // должен быть основан на типе "строка" и не быть Множественным

        return intval($jobIdProp['VALUES'][array_keys($jobIdProp['VALUES'])[0]]['VALUE']) ?? null;
    }

    // endregion

    // region Получение занятых машин в диапазоне времени

    /** Получения ИД машин, занятых в указанном периоде и с определенными уровнями комфорта
     * @param \Bitrix\Main\Type\DateTime $dt_from Дата в временем начала поездки
     * @param \Bitrix\Main\Type\DateTime $dt_to Дата в временем конца поездки
     * @param int[] $comfort_types Массив ИД уровней комфорта, с которыми надо искать занятые машины
     * @return array{ID: int, IBLOCK_ID: int}[]|null Массив занятых машин
     * @see \Bitrix\Main\Type\DateTime::createFromTimestamp Пример создания такого объекта
     */
    private function getBusyCarsAtRangeAndWithComfortTypes($dt_from, $dt_to, $comfort_types)
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR_BOOKING]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $carsWithSelectedComfortTypes = $this->getCarsWithComfortTypes($comfort_types);
        $carsWithSelectedComfortTypes_IdsArr = array_column($carsWithSelectedComfortTypes, 'ID');

        $company_car_ext_id_select_key = 'PROPERTY_' . strtoupper(self::IBIPC_COMPANY_CAR_ID_EXT);
        $company_car_ext_id_key = $company_car_ext_id_select_key . '_VALUE';

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Забронированные машины"
            'PROPERTY_' . self::IBIPC_COMPANY_CAR_ID_EXT => $carsWithSelectedComfortTypes_IdsArr, // фильтруем по ид уровня комфорта (хранится в свойстве элемента)
            [
                'LOGIC' => 'OR',

                // фильтруем, машина занята если её "занятость от" находится в указанном интервале
                [
                    '>=PROPERTY_' . self::IBIPC_COMPANY_CAR_BOOKING_FROM => $dt_from->format('Y-m-d H:i:s'),
                    '<=PROPERTY_' . self::IBIPC_COMPANY_CAR_BOOKING_FROM => $dt_to->format('Y-m-d H:i:s'),
                ],

                // фильтруем, машина занята если её "занятость до" находится в указанном интервале
                [
                    '>=PROPERTY_' . self::IBIPC_COMPANY_CAR_BOOKING_TO => $dt_from->format('Y-m-d H:i:s'),
                    '<=PROPERTY_' . self::IBIPC_COMPANY_CAR_BOOKING_TO => $dt_to->format('Y-m-d H:i:s'),
                ],
            ],
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', $company_car_ext_id_select_key]
        );

        $resIblockElsIds = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow[$company_car_ext_id_key];
            $resIblockElsIds[] = $id;
        }

        return $resIblockElsIds;
    }

    /** Получение ИД машин определенных уровней комфорта
     * @param int[] $comfort_types Массив ИД уровней комфорта, с которыми надо искать машины
     * @return array{ID: int, IBLOCK_ID: int}[]|null Массив машин указанными уровнями комфорта
     */
    private function getCarsWithComfortTypes($comfort_types = [])
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Забронированные машины"
            'PROPERTY_' . self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT => $comfort_types, // фильтруем по ид уровня комфорта (хранится в свойстве элемента)
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID']
        );

        $resIblockElsIds = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $resIblockElsIds[$id] = [
                'ID' => $id,
                'IBLOCK_ID' => intval($arrResultRow['IBLOCK_ID']),
            ];
        }

        return $resIblockElsIds;
    }

    // endregion

    // region Получение доступных машин

    /** Получение ИД машин с указанными уровнями комфорта, за исключением занятых машин
     * @param int[] $exclude_cars_ids Массив машин, что заняты в других бронированиях
     * @return int[]|null Массив ИД машин
     */
    private function getAllowedCarsByExcludeBusyCars($comfort_type_ids, $exclude_cars_ids = [])
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Служебные автомобили"
            'PROPERTY_' .self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT => $comfort_type_ids, // фильтруем по ид уровня комфорта (хранится в свойстве элемента)
        ];

        if (!empty($exclude_cars_ids)) {
            $arFilter['!ID'] = $exclude_cars_ids; // исключаем занятые машины
        }

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', 'PROPERTY_' . self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT]
        );

        $resIblockElsIds = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $resIblockElsIds[] = $id;
        }

        return $resIblockElsIds;
    }

    /** Получение данных машин для указанных ИД
     * @param int[] $cars_ids Массив ИД машин
     * @return array|null Массив данных машин
     */
    private function getCarsWithDataByCarsIds($cars_ids = [])
    {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Забронированные машины"
            'ID' => $cars_ids,
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'IBLOCK_ID',
                "IBLOCK_SECTION_ID",
                "NAME",
                "ACTIVE_FROM",
                "TIMESTAMP_X",
            ]
        );

        $driversForCurrentCars = [];
        $comfortTypesForCurrentCars = [];
        $resIblockEls = [];
        // region Собственно получение данных автомобилей

        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $resIblockEls[$id] = [
                'ID' => $id,
                'IBLOCK_ID' => intval($arrResultRow['IBLOCK_ID']),
                'IBLOCK_SECTION_ID' => $arrResultRow['IBLOCK_SECTION_ID'],
                'NAME' => $arrResultRow['NAME'],
                'ACTIVE_FROM' => $arrResultRow['ACTIVE_FROM'],
                'TIMESTAMP_X' => $arrResultRow['TIMESTAMP_X'],
            ];

            $resIblockEls[$id]['PROPS'] = $this->getPropsForIBlockElement(
                $resIblockEls[$id]['IBLOCK_ID'],
                $resIblockEls[$id]['ID']
            );

            // region Сбор ИД водителей и уровней комфорта для получения их данных за один раз

            $driverIdExt = $resIblockEls[$id]['PROPS'][self::IBIPC_DRIVER_ID_EXT] ?? null;
            if (isset($driverIdExt)) {
                $driverIdExt_Id = intval($driverIdExt['VALUES'][0]['VALUE']);
                $is_already_driver_saved = key_exists($driverIdExt_Id, $driversForCurrentCars);
                if(!$is_already_driver_saved) {
                    $driversForCurrentCars[$driverIdExt_Id] = $driverIdExt_Id;
                }
            }

            $comfortTypeIdExt = $resIblockEls[$id]['PROPS'][self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT] ?? null;
            if (isset($comfortTypeIdExt)) {
                $comfortTypeIdExt_Id = intval($comfortTypeIdExt['VALUES'][0]['VALUE']);
                $is_already_comfort_type_saved = key_exists($comfortTypeIdExt_Id, $comfortTypesForCurrentCars);
                if(!$is_already_comfort_type_saved) {
                    $comfortTypesForCurrentCars[$comfortTypeIdExt_Id] = $comfortTypeIdExt_Id;
                }
            }

            // endregion
        }

        // endregion

        // region Получение данных водителей и уровней комфорта за один раз
        $all_drivers_for_saved_ids = $this->getDriversByIds($driversForCurrentCars);
        $all_drivers_for_saved_ids_arr = [];
        if(isset($all_drivers_for_saved_ids)) {
            foreach ($all_drivers_for_saved_ids as $driver) {
                $driver_id = $driver['ID'];
                $all_drivers_for_saved_ids_arr[$driver_id] = $driver;
            }
        }
        $all_comfort_types_for_saved_ids = $this->getComfortTypesByIds($comfortTypesForCurrentCars);
        $all_comfort_types_for_saved_ids_arr = [];
        if(isset($all_comfort_types_for_saved_ids)) {
            foreach ($all_comfort_types_for_saved_ids as $comfort_type) {
                $comfort_types_id = $comfort_type['ID'];
                $all_comfort_types_for_saved_ids_arr[$comfort_types_id] = $comfort_type;
            }
        }
        // endregion

        // region Сохранение полученных данных водителей и уровней комфорта внутрь данных каждого автомобиля
        foreach ($resIblockEls as $id => &$resIblockEl) {
            $cycle_driver_prop = $resIblockEl['PROPS'][self::IBIPC_DRIVER_ID_EXT] ?? null;
            $cycle_driver_id = isset($cycle_driver_prop) ? $cycle_driver_prop['VALUES'][0]['VALUE'] : null;
            $is_ok_cycle_driver_id = isset($cycle_driver_id)
                && key_exists($cycle_driver_id, $all_drivers_for_saved_ids_arr);
            $resIblockEl[self::IBIPC_DRIVER_ID_EXT] =
                ($is_ok_cycle_driver_id) ? $all_drivers_for_saved_ids_arr[$cycle_driver_id] : null;

            $cycle_comfort_type_prop = $resIblockEl['PROPS'][self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT] ?? null;
            $cycle_comfort_type_id = isset($cycle_comfort_type_prop) ? $cycle_comfort_type_prop['VALUES'][0]['VALUE'] : null;
            $is_ok_cycle_comfort_type_id = isset($cycle_comfort_type_id)
                && key_exists($cycle_comfort_type_id, $all_comfort_types_for_saved_ids_arr);
            $resIblockEl[self::IBIPC_COMPANY_CAR_COMFORT_TYPE_ID_EXT] =
                ($is_ok_cycle_comfort_type_id) ? $all_comfort_types_for_saved_ids_arr[$cycle_comfort_type_id] : null;
        }
        // endregion

        return $resIblockEls;
    }

    /** Получение массива с данными водителей по их ИД
     * @param int[] $ids Массив ИД водителей
     * @param bool $retrieve_props Получать ли свойства элементов инфоблока
     * @return array|null Массив с данными водителей
     */
    private function getDriversByIds($ids, $retrieve_props = false) {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR_DRIVER]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Водители"
            'ID' => $ids,
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME']
        );

        $resIblockEls = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $resIblockEls[$id] = [
                'ID' => $id,
                'IBLOCK_ID' => intval($arrResultRow['IBLOCK_ID']),
                'NAME' => $arrResultRow['NAME'],
            ];

            if ($retrieve_props) {
                $resIblockEls[$id]['PROPS'] = $this->getPropsForIBlockElement(
                    $resIblockEls[$id]['IBLOCK_ID'],
                    $resIblockEls[$id]['ID']
                );
            }
        }

        return $resIblockEls;
    }

    /** Получение массива с данными уровней комфорта по их ИД
     * @param int[] $ids Массив ИД уровней комфорта
     * @param bool $retrieve_props Получать ли свойства элементов инфоблока
     * @return array|null Массив с данными уровней комфорта
     */
    private function getComfortTypesByIds($ids, $retrieve_props = false) {
        $iblock_id = self::$iblocksForAWork[self::IBLOCK_CODE_OF_COMPANY_CAR_COMFORT_TYPE]['ID'] ?? null;
        if(!isset($iblock_id)) {
            return null;
        }

        $arFilter = [
            'IBLOCK_ID' => $iblock_id, // получение из инфоблока "Уровни комфорта"
            'ID' => $ids,
        ];

        $CIBlockResult = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME']
        );

        $resIblockEls = [];
        while ($arrResultRow = $CIBlockResult->Fetch()) {
            $id = (int)$arrResultRow['ID'];
            $resIblockEls[$id] = [
                'ID' => $id,
                'IBLOCK_ID' => intval($arrResultRow['IBLOCK_ID']),
                'NAME' => $arrResultRow['NAME'],
            ];

            if ($retrieve_props) {
                $resIblockEls[$id]['PROPS'] = $this->getPropsForIBlockElement(
                    $resIblockEls[$id]['IBLOCK_ID'],
                    $resIblockEls[$id]['ID']
                );
            }
        }

        return $resIblockEls;
    }

    // endregion

    // region Общие функции - Свойства элементов ИБ

    /** Получение свойств элемента инфоблока
     * @param int $iblockId ИД инфоблока
     * @param int $iblockElementId ИД элемента инфоблока
     * @return array{code: string, name: string, type: string, active: string, values: array{value: mixed, description: string}, property_type: string, value: array}[] Свойства элемента инфоблока
     */
    private function getPropsForIBlockElement($iblockId, $iblockElementId)
    {
        $propsDbres = \CIBlockElement::GetProperty($iblockId, $iblockElementId, "sort", "asc", array(">ID" => 1));
        $i = 0;
        $props = [];
        while ($prop = $propsDbres->GetNext()) {
            $i = !isset($element['PROPS'][$prop['CODE'
                ]]) ? 0 : $i+1;
            $props[$prop['CODE']]['CODE'] = $prop['CODE'];
            $props[$prop['CODE']]['NAME'] = $prop['NAME'];
            $props[$prop['CODE']]['TYPE'] = $prop['PROPERTY_TYPE'];
            $props[$prop['CODE']]['ACTIVE'] = $prop['ACTIVE'];
            $props[$prop['CODE']]['VALUES'][$i] = [
                'VALUE' => $prop['VALUE'],
                'DESCRIPTION' => $prop['DESCRIPTION'],
            ];
            if ($prop['PROPERTY_TYPE'] == 'F') {
                $props[$prop['CODE']]['VALUES'][$i]['PATH'] = \CFile::GetPath(intval($prop['VALUE'])); // читаем путь к файлу
            }
        }
        return $props;
    }

    // endregion

}
