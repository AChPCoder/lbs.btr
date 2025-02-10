<?php

use \Bitrix\Main\Localization\Loc;

class CIBlockPropertyCProp
{
    private static $showedCss = false;
    private static $showedJs = false;

    /** Метод возвращает массив описывающий поведение пользовательского свойства.<br/>
     * Вызывается по событию
     * <a href="https://dev.1c-bitrix.ru/api_help/iblock/events/OnIBlockPropertyBuildList.php">OnIBlockPropertyBuildList</a>.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * @return array{
     *     PROPERTY_TYPE: string,
     *     USER_TYPE: string,
     *     DESCRIPTION: string,
     *     CheckFields: string[],
     *     GetUIFilterProperty: string[],
     *     GetLength: string[],
     *     ConvertToDB: string[],
     *     ConvertFromDB: string[],
     *     GetPropertyFieldHtml: string[],
     *     GetPropertyFieldHtmlMulty: string[],
     *     GetAdminListViewHTML: string[],
     *     GetPublicViewHTML: string[],
     *     GetPublicEditHTML: string[],
     *     GetSettingsHTML: string[],
     *     PrepareSettings: string[],
     * } Массив со структурой:<ul>
     *   <li>PROPERTY_TYPE - строковое значение, может быть одним из:
     *     <ul>
     *       <li>S - строка</li>
     *       <li>N - число с плавающей точкой</li>
     *       <li>L - список значений</li>
     *       <li>F - файл</li>
     *       <li>G - привязка к разделам</li>
     *       <li>E - привязка к элементам</li>
     *     </ul>
     *     Обязательное поле
     *   </li>
     *   <li>USER_TYPE - Уникальный идентификатор пользовательского свойства. Обязательное поле</li>
     *   <li>DESCRIPTION - Краткое описание. Будет выведено в списке выбора типа свойства при редактировании информационного блока. Обязательное поле</li>
     *   <li>CheckFields - Не обязательное. Значением этого поля должен быть массив из двух элементов. В первом должно быть название
     *     класса, а во втором название метода который будет вызван при наступлении соответствующего события.</li>
     *   <li>GetLength - Аналогично полю `CheckFields`. <i>Доступно с версии 18.5.0.</i></li>
     *   <li>ConvertToDB - Аналогично полю `CheckFields`.</li>
     *   <li>ConvertFromDB - Аналогично полю `CheckFields`.</li>
     *   <li>GetPropertyFieldHtml - Аналогично полю `CheckFields`.</li>
     *   <li>GetPropertyFieldHtmlMulty - Необязательный обработчик. Является аналогом `GetPropertyFieldHtml` за
     *     исключением того, что в `value` приходят несколько значений..</li>
     *   <li>GetAdminListViewHTML - Аналогично полю `CheckFields`.</li>
     *   <li>GetPublicViewHTML - Аналогично полю `CheckFields`.</li>
     *   <li>GetPublicEditHTML - Аналогично полю `CheckFields`.</li>
     *   <li>GetSettingsHTML - Аналогично полю `CheckFields`.</li>
     *   <li>PrepareSettings - Аналогично полю `CheckFields`.</li>
     * </ul>
     */
    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'CPROP',
            'DESCRIPTION' => Loc::getMessage('IEX_CPROP_DESC'),
            'GetLength' => [__CLASS__, 'GetLength'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings' => [__CLASS__, 'PrepareUserSettings'],
        ];
    }

    /** Метод должен вернуть HTML отображения элемента управления для редактирования значений свойства в административной части.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается во время построения формы редактирования элемента.</blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $value array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания. В массиве: <ul>
     * <li>"VALUE" - html безопасное имя для значения,</li>
     * <li>"DESCRIPTION" - html безопасное имя для описания,</li>
     * <li>"MODE" - может принимать зачение "FORM_FILL" при вызове из формы редактирования элемента или
     * "iblock_element_admin" при редактировании в режиме просмотра списка элементов, а также "EDIT_FORM" при редактировании инфоблока.</li>
     * <li>"FORM_NAME" - имя формы в которую будет встроен элемент управления.</li>
     * </ul>
     * @return string
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        // это код, что нужен для вывода полей комплексного свойства внутри формы с комплексным полем (то есть не настройки, а значения)
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('IEX_CPROP_CLEAR_TEXT');

        self::showCss();
        self::showJs();

        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            /** Поля из настроек */
            $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
        } else {
            return '<span>' . Loc::getMessage('IEX_CPROP_ERROR_INCORRECT_SETTINGS') . '</span>';
        }

        $result = '';
        $result .= '<div class="mf-gray"><a class="cl mf-toggle">' . $hideText . '</a>';
        if ($arProperty['MULTIPLE'] === 'Y') {
            $result .= ' | <a class="cl mf-delete">' . $clearText . '</a></div>';
        }
        $result .= '<table class="mf-fields-list active">';

        /** @var string $code */
        /** @var array $arItem */
        foreach ($arFields as $code => $arItem) {
            if ($arItem['TYPE'] === 'string') {
                $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'file') {
                $result .= self::showFile($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'text') {
                $result .= self::showTextarea($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'date') {
                $result .= self::showDate($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'element') {
                $result .= self::showBindElement($code, $arItem['TITLE'], $value, $strHTMLControlName);
            } elseif ($arItem['TYPE'] === 'customhtml') {
                $result .= self::showStringHtmlEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
            }
        }

        $result .= '</table>';

        return $result;
    }

    /** Метод должна вернуть безопасный HTML отображения значения свойства в публичной части сайта.<br/>
     * Если она вернет пустое значение, то значение отображаться не будет.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote>
     *   <b>Примечание:</b> вызывается из метода
     *   <a href="https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockformatproperties/getdisplayvalue.php">CIBlockFormatProperties::GetDisplayValue</a>,
     *   которая используется компонентами модуля информационных блоков для форматирования значений свойств.
     * </blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $value array{VALUE: string} Значение свойства.
     * @param $strHTMLControlName array Пустой массив
     * @return string
     * @see \CIBlockFormatProperties::GetDisplayValue
     */
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return $value;
    }

    /** Метод возвращает безопасный HTML отображения настроек свойства для формы редактирования инфоблока.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается при построении формы редактирования инфоблока.</blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $strHTMLControlName array{NAME: string} Имя элемента управления для заполнения настроек свойства, где "NAME" - html безопасное имя для настроек
     * @param $arPropertyFields array Пустой массив
     * @return string HTML для встраивания в форму редактирования инфоблока.<br/>
     * В параметре arPropertyFields можно вернуть дополнительные флаги управления формой:<ul>
     * <li>HIDE - массив названий полей свойства которые будут скрыты для редактирования.
     * Возможные значения: MULTIPLE, SEARCHABLE, FILTRABLE, WITH_DESCRIPTION, MULTIPLE_CNT, ROW_COUNT, COL_COUNT и DEFAULT_VALUE.</li>
     * <li>SHOW - массив полей которые должны быть показаны даже если базовое свойство их не поддерживает.
     * Возможные значения: MULTIPLE, SEARCHABLE, FILTRABLE, WITH_DESCRIPTION, MULTIPLE_CNT, ROW_COUNT и COL_COUNT.</li>
     * <li>SET - ассоциативный массив полей для принудительного выставления значений в случае если они не отображаются в форме.
     * Возможные значения: MULTIPLE, SEARCHABLE, FILTRABLE, WITH_DESCRIPTION, MULTIPLE_CNT, ROW_COUNT и COL_COUNT.</li>
     * <li>USER_TYPE_SETTINGS_TITLE - строка для отображения в качестве заголовка секции настроек.</li>
     * </ul>
     */
    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        // это код, что нужен для вывода настроек полей комплексного свойства внутри формы настроек ИБ (то есть настройки, а не значения)
        $btnAdd = Loc::getMessage('IEX_CPROP_SETTING_BTN_ADD');
        $settingsTitle = Loc::getMessage('IEX_CPROP_SETTINGS_TITLE');

        $arPropertyFields = [
            'USER_TYPE_SETTINGS_TITLE' => $settingsTitle,
            'HIDE' => ['ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE', 'SEARCHABLE', 'SMART_FILTER', 'WITH_DESCRIPTION', 'FILTRABLE', 'MULTIPLE_CNT', 'IS_REQUIRED'],
            'SET' => [
                'MULTIPLE_CNT' => 1,
                'SMART_FILTER' => 'N',
                'FILTRABLE' => 'N',
            ],
        ];

        self::showJsForSetting($strHTMLControlName["NAME"]);
        self::showCssForSetting();

        $result = '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">        
                <tr valign="top" class="heading mf-setting-title">
                   <td>XML_ID</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_TITLE') . '</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_SORT') . '</td>
                   <td>' . Loc::getMessage('IEX_CPROP_SETTING_FIELD_TYPE') . '</td>
                </tr>';


        $arSetting = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        if (!empty($arSetting)) {
            foreach ($arSetting as $code => $arItem) {
                $result .= '
                       <tr valign="top">
                           <td><input type="text" class="inp-code" size="20" value="' . $code . '"></td>
                           <td><input type="text" class="inp-title" size="35" name="' . $strHTMLControlName["NAME"] . '[' . $code . '_TITLE]" value="' . $arItem['TITLE'] . '"></td>
                           <td><input type="text" class="inp-sort" size="5" name="' . $strHTMLControlName["NAME"] . '[' . $code . '_SORT]" value="' . $arItem['SORT'] . '"></td>
                           <td>
                                <select class="inp-type" name="' . $strHTMLControlName["NAME"] . '[' . $code . '_TYPE]">
                                    ' . self::getOptionList($arItem['TYPE']) . '
                                </select>                        
                           </td>
                       </tr>';
            }
        }

        $result .= '
               <tr valign="top">
                    <td><input type="text" class="inp-code" size="20"></td>
                    <td><input type="text" class="inp-title" size="35"></td>
                    <td><input type="text" class="inp-sort" size="5" value="500"></td>
                    <td>
                        <select class="inp-type"> ' . self::getOptionList() . '</select>                        
                    </td>
               </tr>
             </table>   
                
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <input type="button" value="' . $btnAdd . '" onclick="addNewRows()">
                    </td>
                </tr>
                </td></tr>';

        return $result;
    }

    /** Метод возвращает либо массив с дополнительными настройками свойства, либо весь набор настроек, включая стандартные.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание №1:</b> до версии модуля Информационные блоки <b>12.5.7</b> метод возвращает только массив с дополнительными настройками свойства.</blockquote>
     * <blockquote><b>Примечание №2:</b> вызывается перед сохранением метаданных свойства в базу данных.</blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @return string|array Если метод возвращает весь набор настроек, то в этом случае
     * дополнительные настройки передаются в ключе USER_TYPE_SETTINGS в виде массива.
     */
    public static function PrepareUserSettings($arProperty)
    {
        $result = [];
        if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }
        return $result;
    }

    /** Метод должен вернуть фактическую длину значения свойства.<br/>
     * Этот метод нужен только для свойств значения которых представляют собой сложные структуры (например массив).<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается при проверке обязательности заполнения значения свойства перед
     * добавлением или изменением элемента, если свойство помечено как обязательное.</blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @return int Целое число.
     */
    public static function GetLength($arProperty, $arValue)
    {
        $arFields = self::prepareSetting(unserialize($arProperty['USER_TYPE_SETTINGS']));

        $result = false;
        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                if (!empty($value['name']) || (!empty($value['OLD']) && empty($value['DEL']))) {
                    $result = true;
                    break;
                }
            } else {
                if (!empty($value)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /** Метод должен преобразовать значение свойства в формат пригодный для сохранения в базе данных.<br/>
     * И вернуть массив вида array("VALUE" => "...", "DESCRIPTION" => "...").<br/>
     * Если значение свойства это массив, то разумным будет использование функции serialize.<br/>
     * А вот Дата/время преобразуется в ODBC формат "YYYY-MM-DD HH:MI:SS".<br/>
     * Это определит возможности сортировки и фильтрации по значениям данного свойства.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается перед сохранением значения свойства в БД.</blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @return array{VALUE: string, DESCRIPTION: string} Строка представление для БД.
     */
    public static function ConvertToDB($arProperty, $arValue)
    {
        /** Текущие поля кастомного свойства (настройки) */
        $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);

        // в массиве значений все данные готовы к сохранению, кроме тех с типом "файл"
        foreach ($arValue['VALUE'] as $code => $value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                $arValue['VALUE'][$code] = self::prepareFileToDB($value);
            }
        }

        // в одном $arValue хранится данные поля инфоблока с данным комплексным свойством (а данные там (в БД) - это json-encoded строка)
        $isEmpty = true;
        foreach ($arValue['VALUE'] as $v) {
            if (!empty($v)) {
                $isEmpty = false;
                break;
            }
        }

        // если хоть в одном из полей кастомного свойства будет значение, то записывается массив в json строку
        if ($isEmpty === false) {
            $arResult['VALUE'] = json_encode($arValue['VALUE']); // Описание поля остается
        } else {
            $arResult = ['VALUE' => '', 'DESCRIPTION' => '']; // Описание поля стирается
        }

        return $arResult;
    }

    /** Метод должен преобразовать значение свойства из формата пригодного для сохранения в базе данных в формат обработки.
     * И вернуть массив вида array("VALUE" => "...", "DESCRIPTION" => "...").
     * Дополняет ConvertToDB. Метод статический при использовании штатных свойств.
     * У свойств, созданных клиентом, обязан быть статическим при использовании php7.
     * <blockquote>
     *   <b>Примечание:</b> Вызывается в методе <a href="https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockresult/getnext.php">CIBlockResult::Fetch</a>.
     * Для корректной работы необходимо в фильтре метода
     * <a href="https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockelement/getlist.php">CIBlockElement::GetList</a>
     * указать "IBLOCK_ID".
     * </blockquote>
     * @param $arProperty array Метаданные свойства. См. <a href="https://dev.1c-bitrix.ru/api_help/iblock/fields.php#fproperty">Свойства элементов инфоблока</a>
     * @param $arValue array{VALUE: string, DESCRIPTION: string} Значение свойства.
     * @return array{VALUE: array, DESCRIPTION: string} Строка представление для БД.
     * @see \CIBlockResult::Fetch
     * @see \CIBlockResult::GetList
     */
    public static function ConvertFromDB($arProperty, $arValue)
    {
        $return = [];

        // если значение поля не пусто, ...
        if (!empty($arValue['VALUE'])) {
            // работаем с ним как с json строкой
            $arData = json_decode($arValue['VALUE'], true);

            foreach ($arData as $code => $value) { // набор данных в массив для ключа 'value'
                $return['VALUE'][$code] = $value;
            }
        }
        return $return;
    }

    //Internals

    /** Получить HTML обычного строкового поля комплексного свойства
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showString($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td><input type="text" value="' . $v . '" name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"/></td>
                </tr>';

        return $result;
    }

    /** Получить HTML файлового поля комплексного свойств
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showFile($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        if (!empty($arValue['VALUE'][$code]) && !is_array($arValue['VALUE'][$code])) {
            $fileId = $arValue['VALUE'][$code];
        } else if (!empty($arValue['VALUE'][$code]['OLD'])) {
            $fileId = $arValue['VALUE'][$code]['OLD'];
        } else {
            $fileId = '';
        }

        if (!empty($fileId)) {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if ($arPicture) {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/' . $strImageStorePath . '/' . $arPicture['SUBDIR'] . '/' . $arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if (in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])) {
                    $content = '<img src="' . $sImagePath . '">';
                } else {
                    $content = '<div class="mf-file-name">' . $arPicture['FILE_NAME'] . '</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">' . $title . ': </td>
                        <td>
                            <table class="mf-img-table">
                                <tr>
                                    <td>' . $content . '<br>
                                        <div>
                                            <label><input name="' . $strHTMLControlName['VALUE'] . '[' . $code . '][DEL]" value="Y" type="checkbox"> ' . Loc::getMessage("IEX_CPROP_FILE_DELETE") . '</label>
                                            <input name="' . $strHTMLControlName['VALUE'] . '[' . $code . '][OLD]" value="' . $fileId . '" type="hidden">
                                        </div>
                                    </td>
                                </tr>
                            </table>                      
                        </td>
                    </tr>';
            }
        } else {
            $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td><input type="file" value="" name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"/></td>
                </tr>';
        }

        return $result;
    }

    /** Получить HTML строкового поля (с большим количеством символов) комплексного свойства
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                    <td align="right" valign="top">' . $title . ': </td>
                    <td><textarea rows="8" name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']">' . $v . '</textarea></td>
                </tr>';

        return $result;
    }

    /** Получить HTML поля даты комплексного свойства
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showDate($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';
        $result .= '<tr>
                        <td align="right" valign="top">' . $title . ': </td>
                        <td>
                            <table>
                                <tr>
                                    <td style="padding: 0;">
                                        <div class="adm-input-wrap adm-input-wrap-calendar">
                                            <input class="adm-input adm-input-calendar" type="text" name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']" size="23" value="' . $v . '">
                                            <span class="adm-calendar-icon"
                                                  onclick="BX.calendar({node: this, field:\'' . $strHTMLControlName['VALUE'] . '[' . $code . ']\', form: \'\', bTime: true, bHideTime: false});"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';

        return $result;
    }

    /** Получить HTML поля привязки к элементу комплексного свойства
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $elUrl = '';
        if (!empty($v)) {
            $arElem = \CIBlockElement::GetList([], ['ID' => $v], false, ['nPageSize' => 1], ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if (!empty($arElem)) {
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $arElem['IBLOCK_ID'] . '&ID=' . $arElem['ID'] . '&type=' . $arElem['IBLOCK_TYPE_ID'] . '">' . $arElem['NAME'] . '</a>';
            }
        }

        $result .= '<tr>
                    <td align="right">' . $title . ': </td>
                    <td>
                        <input name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']" id="' . $strHTMLControlName['VALUE'] . '[' . $code . ']" value="' . $v . '" size="8" type="text" class="mf-inp-bind-elem">
                        <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n=' . $strHTMLControlName['VALUE'] . '&k=' . $code . '\', 900, 700);">&nbsp;
                        <span>' . $elUrl . '</span>
                    </td>
                </tr>';

        return $result;
    }

    /** Получить HTML поля строки с HTML-редактором комплексного свойства
     *
     * <blockquote><b>Важно:</b> инициируется для подгруженных при загрузке элементов,
     * чтобы он для множественных новых добавленных блоков работал, надо применить эти изменения и
     * снова открыть эту форму, редактор будет работать.</blockquote>
     * <blockquote><b>Для доработки:</b> нужно после нажатия BX кнопки "Добавить" внутри множественного поля сделать
     * инициализацию HTML редактора, так как сейчас он копируется без привязок к JS функционалу</blockquote>
     * @param $code string Ключ поля внутри комплексного свойства
     * @param $title string Название поля внутри комплексного свойства
     * @param $arValue array{VALUE: array, DESCRIPTION: string} Значение свойства.
     * @param $strHTMLControlName array{VALUE: string, DESCRIPTION: string, MODE: string, FORM_NAME: string} Имена элементов управления
     * для заполнения значения свойства и его описания.
     * @return string
     * @see self::GetPropertyFieldHtml Детальное описание <code>$strHTMLControlName</code> описано в этой функции
     */
    private static function showStringHtmlEditor($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';

        $input_name = $strHTMLControlName['VALUE'] . '[' . $code . ']';
        $input_name_raw = $input_name;

        // region Bug workaround - BX.HTML editor не работает с именами полей которые содержат квадратные скобки

        $replacer_str_bracket_start = '__0__';
        $replacer_str_bracket_end = '__1__';
        $input_name = str_replace('[', $replacer_str_bracket_start, $input_name);
        $input_name = str_replace(']', $replacer_str_bracket_end, $input_name);

        // endregion

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $rows = 4;
        ob_start();

        \CFileMan::AddHTMLEditorFrame(
            $input_name,
            $v,
            "${input_name}[EDITOR_TYPE]",
//            strlen($arHtmlControl["VALUE"])?"html":"text",
            "html",
            [
                'height' => $rows * 10,
            ],
            "N",
            0,
            "",
            'data-trg-cprop-customhtml="' . $input_name_raw . '"',
        );

        $html = ob_get_contents();
        ob_end_clean();

        $result .= '<tr class="fn_trg_cprop_customhtml__row">
                    <td align="right">' . $title . ': </td>
                    <td>' . $html . '</td>
                </tr>';

        return $result;
    }

    /** Приложение своего стиля для полей кастомного свойства
     * @return void Но делает вставку <code><style></style></code> элемента при вызове
     */
    private static function showCss()
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .cl {
                    cursor: pointer;
                }

                .mf-gray {
                    color: #797777;
                }

                .mf-fields-list {
                    display: none;
                    padding-top: 10px;
                    margin-bottom: 10px !important;
                    margin-left: -300px !important;
                    border-bottom: 1px #e0e8ea solid !important;
                }

                .mf-fields-list.active {
                    display: block;
                }

                .mf-fields-list td {
                    padding-bottom: 5px;
                }

                .mf-fields-list td:first-child {
                    width: 300px;
                    color: #616060;
                }

                .mf-fields-list td:last-child {
                    padding-left: 5px;
                }

                .mf-fields-list input[type="text"] {
                    width: 350px !important;
                }

                .mf-fields-list textarea {
                    min-width: 350px;
                    max-width: 650px;
                    color: #000;
                }

                .mf-fields-list img {
                    max-height: 150px;
                    margin: 5px 0;
                }

                .mf-img-table {
                    background-color: #e0e8e9;
                    color: #616060;
                    width: 100%;
                }

                .mf-fields-list input[type="text"].adm-input-calendar {
                    width: 170px !important;
                }

                .mf-file-name {
                    word-break: break-word;
                    padding: 5px 5px 0 0;
                    color: #101010;
                }

                .mf-fields-list input[type="text"].mf-inp-bind-elem {
                    width: unset !important;
                }
            </style>
            <?
        }
    }

    /** Приложение своего скрипта для полей кастомного свойства
     * @return void Но делает вставку <code><script></script></code> элемента при вызове и подключает jQuery
     */
    private static function showJs()
    {
        $showText = Loc::getMessage('IEX_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('IEX_CPROP_HIDE_TEXT');

        CJSCore::Init(["jquery"]);
        if (!self::$showedJs) {
            self::$showedJs = true;
            ?>
            <script>
                $(document).on('click', 'a.mf-toggle', function (e) {
                    e.preventDefault();

                    var table = $(this).closest('tr').find('table.mf-fields-list');
                    $(table).toggleClass('active');
                    if ($(table).hasClass('active')) {
                        $(this).text('<?=$hideText?>');
                    } else {
                        $(this).text('<?=$showText?>');
                    }
                });

                $(document).on('click', 'a.mf-delete', function (e) {
                    e.preventDefault();

                    var textInputs = $(this).closest('tr').find('input[type="text"]');
                    $(textInputs).each(function (i, item) {
                        $(item).val('');
                    });

                    var textarea = $(this).closest('tr').find('textarea');
                    $(textarea).each(function (i, item) {
                        $(item).text('');
                    });

                    var checkBoxInputs = $(this).closest('tr').find('input[type="checkbox"]');
                    $(checkBoxInputs).each(function (i, item) {
                        $(item).attr('checked', 'checked');
                    });

                    $(this).closest('tr').hide('slow');
                });
            </script>
            <?
        }
    }

    /** Приложение своего скрипта для настроек
     * @return void Но делает вставку <code><script></script></code> элемента при вызове и подключает jQuery
     */
    private static function showJsForSetting($inputName)
    {
        CJSCore::Init(["jquery"]);
        ?>
        <script>
            function addNewRows() {
                $("#many-fields-table").append('' +
                    '<tr valign="top">' +
                    '<td><input type="text" class="inp-code" size="20"></td>' +
                    '<td><input type="text" class="inp-title" size="35"></td>' +
                    '<td><input type="text" class="inp-sort" size="5" value="500"></td>' +
                    '<td><select class="inp-type"><?=self::getOptionList()?></select></td>' +
                    '</tr>');
            }


            $(document).on('change', '.inp-code', function () {
                const code = $(this).val();

                if (code.length <= 0) {
                    $(this).closest('tr').find('input.inp-title').removeAttr('name');
                    $(this).closest('tr').find('input.inp-sort').removeAttr('name');
                    $(this).closest('tr').find('select.inp-type').removeAttr('name');
                } else {
                    $(this).closest('tr').find('input.inp-title').attr('name', '<?=$inputName?>[' + code + '_TITLE]');
                    $(this).closest('tr').find('input.inp-sort').attr('name', '<?=$inputName?>[' + code + '_SORT]');
                    $(this).closest('tr').find('select.inp-type').attr('name', '<?=$inputName?>[' + code + '_TYPE]');
                }
            });

            $(document).on('input', '.inp-sort', function () {
                /** @type {string} */
                const num = $(this).val();
                $(this).val(num.replace(/[^0-9]/gim, ''));
            });
        </script>
        <?
    }

    /** Приложение своего стиля для настроек
     * @return void Но делает вставку <code><style></style></code> элемента при вызове
     */
    private static function showCssForSetting()
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                .many-fields-table {
                    margin: 0 auto; /*display: inline;*/
                }

                .mf-setting-title td {
                    text-align: center !important;
                    border-bottom: unset !important;
                }

                .many-fields-table td {
                    text-align: center;
                }

                .many-fields-table > input, .many-fields-table > select {
                    width: 90% !important;
                }

                .inp-sort {
                    text-align: center;
                }

                .inp-type {
                    min-width: 125px;
                }
            </style>
            <?
        }
    }

    /** Подготовка настроек (полей в массиве)
     * @param $arSetting array одномерный массив настроек комплексного свойства<br/>
     * Его структура такова: внутри набор элементов, относящихся к некоторому ключу.<br/>
     * Для каждого ключа `key` создается 3 ключа с данными в форме:<ul>
     * <li>`key_TITLE` - заголовок ключа</li>
     * <li>`key_SORT` - значение сортировки ключа</li>
     * <li>`key_TYPE` - тип ключа</li>
     * </ul>
     * @return array распределенный по ключам (полей внутри) массив настроек
     */
    private static function prepareSetting($arSetting)
    {
        /** Распределенный по ключам массив */
        $arResult = [];

        foreach ($arSetting as $key => $value) {
            if (strstr($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            } else if (strstr($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = $value;
            } else if (strstr($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }

        if (!function_exists('cmp')) {
            function cmp($a, $b)
            {
                if ($a['SORT'] == $b['SORT']) {
                    return 0;
                }
                return ($a['SORT'] < $b['SORT']) ? -1 : 1;
            }
        }

        uasort($arResult, 'cmp');

        return $arResult;
    }

    /** Получить список возможных типов поля комплексного свойства
     * @return string
     * @var string $selected Выбранный тип
     */
    private static function getOptionList($selected = 'string')
    {
        $result = '';
        $arOption = [
            'string' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('IEX_CPROP_FIELD_TYPE_ELEMENT'),
            'customhtml' => Loc::getMessage('IEX_CPROP_FIELD_STRING_HTML_EDITOR'),
        ];

        foreach ($arOption as $code => $name) {
            $s = '';
            if ($code === $selected) {
                $s = 'selected';
            }

            $result .= '<option value="' . $code . '" ' . $s . '>' . $name . '</option>';
        }

        return $result;
    }

    /** Подготовка файла к сохранению в БД */
    private static function prepareFileToDB($arValue)
    {
        $result = false;

        if (!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])) {
            // если отмечено удаление и старое значение существует
            CFile::Delete($arValue['OLD']);
        } elseif (!empty($arValue['OLD'])) {
            // если НЕ отмечено удаление и старое значение существует
            $result = $arValue['OLD'];
        } elseif (!empty($arValue['name'])) {
            // если НЕ отмечено удаление и старое значение НЕ существует, но есть имя нового файла
            $result = CFile::SaveFile($arValue, 'vote');
        }

        return $result;
    }

    /** Получить расширение по имени файла
     * @param $filePath string
     * @see self::showFile
     */
    private static function getExtension($filePath)
    {
        $parts = explode('.', $filePath);
        return array_pop($parts);
    }
}