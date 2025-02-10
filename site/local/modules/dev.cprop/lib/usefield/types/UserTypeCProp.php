<?php

use Bitrix\Main\Localization\Loc;

class UserTypeCProp extends \UserTypeCPropBase
{
    // region Common static vars
    public const USER_FIELD_CPROP_TYPE_KEY = 'UF_CPROP_TYPE';
    public const USER_FIELD_CPROP_KEY = 'SETTINGS';
    public const USER_FIELD_CPROP_SUBKEY = 'USER_VALUE';

    private static $showedCss = false;
    private static $showedJs = false;

    // endregion

    // region Init

    public static function GetUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => self::USER_FIELD_CPROP_TYPE_KEY,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Комплексное свойство пользовательского поля',
            "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
        ];
    }

    public static function canUseArrayValueForSingleField() {
        return true;
    }

    // endregion Init

    // region Form of UF type

    public static function GetSettingsHTML($userField, $additionalParameters, $varsFromForm): string
    {
        // это код, что нужен для вывода настроек полей комплексного свойства внутри формы настроек ИБ (то есть настройки, а не значения)
        $btnAdd = Loc::getMessage('IEX_UF_CPROP_SETTING_BTN_ADD');

        $nameKey = self::USER_FIELD_CPROP_KEY . '[' . self::USER_FIELD_CPROP_SUBKEY . ']';

        self::showJsForSetting($nameKey);
        self::showCssForSetting();

        $result = '<tr><td colspan="2" align="center">
            <table id="many-fields-table" class="many-fields-table internal">
                <tr valign="top" class="heading mf-setting-title">
                   <td>XML_ID</td>
                   <td>' . Loc::getMessage('IEX_UF_CPROP_SETTING_FIELD_TITLE') . '</td>
                   <td>' . Loc::getMessage('IEX_UF_CPROP_SETTING_FIELD_SORT') . '</td>
                   <td>' . Loc::getMessage('IEX_UF_CPROP_SETTING_FIELD_TYPE') . '</td>
                </tr>';

        $arSetting = self::prepareCPropSetting($userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY]);

        if (!empty($arSetting)) {
            foreach ($arSetting as $code => $arItem) {
                $result .= '
                       <tr valign="top">
                           <td><input type="text" class="inp-code" size="20" value="' . $code . '"></td>
                           <td><input type="text" class="inp-title" size="35" name="' . $nameKey . '[' . $code . '_TITLE]" value="' . $arItem['TITLE'] . '"></td>
                           <td><input type="text" class="inp-sort" size="5" name="' . $nameKey . '[' . $code . '_SORT]" value="' . $arItem['SORT'] . '"></td>
                           <td>
                                <select class="inp-type" name="' . $nameKey . '[' . $code . '_TYPE]">
                                    ' . self::getUfCPropOptionList($arItem['TYPE']) . '
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
                        <select class="inp-type"> ' . self::getUfCPropOptionList() . '</select>                        
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

    /** Функция перед сохранением настроек указанного UF поля (параметры, общие для всех указанных сущностей)
     * @return string[] Массив, что позднее будет сериализован и сохранен в БД.
     */
    public static function prepareSettings(array $userField): array
    {
        return [
            self::USER_FIELD_CPROP_SUBKEY => $userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY] ?? null,
        ];
    }

    /** Получение списка полей внутри комплексного свойства */
    private static function getUfCPropOptionList($selected = 'string')
    {
        $result = '';
        $arOption = [
            'string' => Loc::getMessage('IEX_UF_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('IEX_UF_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('IEX_UF_CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('IEX_UF_CPROP_FIELD_TYPE_DATE'),
            'element' => Loc::getMessage('IEX_UF_CPROP_FIELD_TYPE_ELEMENT'),
            'customhtml' => Loc::getMessage('IEX_UF_CPROP_FIELD_STRING_HTML_EDITOR'),
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
                    '<td><select class="inp-type"><?=self::getUfCPropOptionList()?></select></td>' +
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

    // endregion

    // region private methods for all this class usage

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
    private static function prepareCPropSetting($arSetting)
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

    // endregion

    // region Form with UF of that type

    public static function renderEditForm(array $userField, ?array $additionalParameters = []): string
    {
        $additionalParameters['mode'] = self::MODE_EDIT;
        return self::GetPropertyFieldHtml($userField, $additionalParameters);
    }

    private static function GetPropertyFieldHtml($userField, $additionalParameters)
    {
        $strHTMLControlName = $additionalParameters;
        /** @var $value array Массив с данными поля. Последнее поле будет пустым, так как BX всегда ставит у множественного поля
         * дополнительное пустое значение - в данном случае будет незаполненное комплексное поле
         */
        $value = self::ConvertFromDB($userField, $additionalParameters);
        $strHTMLControlName['VALUE'] = $additionalParameters['NAME'] . '[VALUE]';

        // это код, что нужен для вывода полей комплексного свойства внутри формы с комплексным полем (то есть не настройки, а значения)
        $hideText = Loc::getMessage('IEX_UF_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('IEX_UF_CPROP_CLEAR_TEXT');

        self::showCss();
        self::showJs();

        if (!empty($userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY])) {
            /** Поля из настроек */
            $arFields = self::prepareCPropSetting($userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY]);
        } else {
            return '<span>' . Loc::getMessage('IEX_UF_CPROP_ERROR_INCORRECT_SETTINGS') . '</span>';
        }

        $result = '';
        $result .= '<div class="mf-gray"><a class="cl mf-toggle">' . $hideText . '</a>';
        if ($userField['MULTIPLE'] === 'Y') {
            $result .= ' | <a class="cl mf-delete">' . $clearText . '</a></div>';
        }

        $result .= '<table class="mf-fields-list active fn_cprop_group">';

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

    // region Private methods for UF properties at entity's form

    // region Form with UF property inputs for declared option type

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
                                        <div data-file-old-exists="1">
                                            <label><input name="' . $strHTMLControlName['VALUE'] . '[' . $code . '][DEL]" value="Y" type="checkbox"> ' . Loc::getMessage("IEX_UF_CPROP_FILE_DELETE") . '</label>
                                            <input name="' . $strHTMLControlName['VALUE'] . '[' . $code . '][OLD]" value="' . $fileId . '" type="hidden">
                                            <input style="display: none !important;" type="file" value="" name="' . $strHTMLControlName['VALUE'] . '[' . $code . ']"/>
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

    /** Получить расширение по имени файла
     * @param $filePath string
     * @see self::showFile
     */
    private static function getExtension($filePath)
    {
        $parts = explode('.', $filePath);
        return array_pop($parts);
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
        $type_field_input_name = "${input_name}[EDITOR_TYPE]";

        // region Bug workaround - BX.HTML editor не работает с именами полей которые содержат квадратные скобки

        $replacer_str_bracket_start = '__0__';
        $replacer_str_bracket_end = '__1__';
        $fn_replace_brackets = function ($raw_str) use ($replacer_str_bracket_start, $replacer_str_bracket_end) {
            $res_str = str_replace('[', $replacer_str_bracket_start, $raw_str);
            $res_str = str_replace(']', $replacer_str_bracket_end, $res_str);
            return $res_str;
        };
        $type_field_input_name = $fn_replace_brackets($type_field_input_name);
        $input_name = $fn_replace_brackets($input_name);

        // endregion

        $v = !empty($arValue['VALUE'][$code]) ? $arValue['VALUE'][$code] : '';

        $rows = 4;
        ob_start();

        \CFileMan::AddHTMLEditorFrame(
            $input_name,
            $v,
            $type_field_input_name,
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

    // endregion

    /** Приложение своего стиля для полей кастомного свойства
     * @return void Но делает вставку <code><style></style></code> элемента при вызове
     */
    private static function showCss()
    {
        if (!self::$showedCss) {
            self::$showedCss = true;
            ?>
            <style>
                [data-file-old-exists="1"] .adm-input-file {
                    display: none;
                }

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
        $showText = Loc::getMessage('IEX_UF_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('IEX_UF_CPROP_HIDE_TEXT');

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

                    const tr = $(this).closest('tr');
                    if(tr.find('input[type="file"]').length) {
                        tr.remove();
                    } else {
                        tr.hide('slow');
                    }
                });
            </script>
            <?
        }
    }

    // endregion

    /** Функция, что вызывается перед сохранением комплексного UF поля у сущности (когда это поле НЕ множественное)
     * @return string Новое значение поля для сохранения в БД
     * @see \CIBlockPropertyCProp::ConvertToDB аналог этой функции
     * @see \CUserTypeManager::Update место вызова этой функции
     */
    public static function onBeforeSave($userField, $value, $user_id)
    {
        $fieldName = $userField['FIELD_NAME'];
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $request_values = $request->getValues();
        $currentFieldsInArrWithValueKey = $request_values[$fieldName] ?? [];

        /** Текущие поля кастомного свойства (настройки) */
        $arFields = self::prepareCPropSetting($userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY]);

        // в массиве значений все данные готовы к сохранению, кроме тех с типом "файл"
        $values_arr = []+$currentFieldsInArrWithValueKey['VALUE'];
        foreach ($values_arr as $code => $inner_value) {
            if ($arFields[$code]['TYPE'] === 'file') {
                $currentFieldsInArrWithValueKey['VALUE'][$code] = self::prepareFileToDBOnBeforeSave($inner_value);
            }
        }
        if(isset($value) && !empty($value)
            && is_array($value) && key_exists('VALUE', $value)
        ) {
            foreach ($value['VALUE'] as $code => $inner_value) {
                if ($arFields[$code]['TYPE'] === 'file') {
                    if(is_numeric($currentFieldsInArrWithValueKey['VALUE'][$code])) {
                        // значит уже есть и замена не нужна
                        continue;
                    }
                    $currentFieldsInArrWithValueKey['VALUE'][$code] = self::prepareFileToDBOnBeforeSave($inner_value);
                }
            }
        }

        // в одном $arValue хранится данные поля инфоблока с данным комплексным свойством (а данные там (в БД) - это json-encoded строка)
        $isEmpty = true;
        foreach ($currentFieldsInArrWithValueKey as $v) {
            if (!empty($v)) {
                $isEmpty = false;
                break;
            }
        }

        // если хоть в одном из полей комплексного свойства будет значение, то записывается массив в json строку
        if ($isEmpty === false) {
            $result = json_encode($currentFieldsInArrWithValueKey['VALUE'], JSON_HEX_TAG);
        } else {
            $result = '';
        }

        return $result;
    }

    /** Функция, что вызывается перед сохранением комплексного UF поля у сущности (когда это поле ИМЕННО множественное)
     * @return string[] Новые значения поля для сохранения в БД
     * @see \CIBlockPropertyCProp::ConvertToDB Аналог этой функции
     * @see \CUserTypeManager::Update Место вызова этой функции
     */
    public static function onBeforeSaveAll($userField, $value, $user_id) {
        $fieldName = $userField['FIELD_NAME'];
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $request_values = $request->getValues();
        $currentFieldsInArrWithValueKey = $request_values[$fieldName] ?? [];

        /** Текущие поля кастомного свойства (настройки) */
        $arFields = self::prepareCPropSetting($userField[self::USER_FIELD_CPROP_KEY][self::USER_FIELD_CPROP_SUBKEY]);

        $uf_indexes = array_keys($currentFieldsInArrWithValueKey);
        $value_is_ok = isset($value) && is_array($value) && !empty($value);
        foreach ($uf_indexes as $uf_index) {
            $values_arr = $currentFieldsInArrWithValueKey[$uf_index]['VALUE'];
            foreach ($values_arr as $code => $inner_value) {
                if ($arFields[$code]['TYPE'] === 'file') {
                    $currentFieldsInArrWithValueKey[$uf_index]['VALUE'][$code] = self::prepareFileToDBOnBeforeSave($inner_value);
                }
            }

            $value_with_index_exists = $value_is_ok && is_array($value)
                && key_exists($uf_index, $value) && key_exists('VALUE', $value[$uf_index]);
            if(!$value_with_index_exists) {
                continue;
            }
            foreach ($value[$uf_index]['VALUE'] as $code => $inner_value) {
                if ($arFields[$code]['TYPE'] === 'file') {
                    if(is_numeric($currentFieldsInArrWithValueKey[$uf_index]['VALUE'][$code])) {
                        // значит уже есть и замена не нужна
                        continue;
                    }
                    $currentFieldsInArrWithValueKey[$uf_index]['VALUE'][$code] = self::prepareFileToDBOnBeforeSave($inner_value);
                }
            }
        }

        $isEmpty = true;
        $fully_empty_items_index_arr = [];
        foreach ($currentFieldsInArrWithValueKey as $index => $item) {
            $item_empty = true;

            foreach ($item['VALUE'] as $code => $v) {
                    if (!empty($v)) {
                        $isEmpty = false;
                        $item_empty = false;
                        continue;
                    }
            }
            if ($item_empty) {
                $fully_empty_items_index_arr[] = $index;
            }
        }

        foreach ($fully_empty_items_index_arr as $id_for_delete) {
            unset($currentFieldsInArrWithValueKey[$id_for_delete]);
        }

        // если хоть в одном из полей комплексного свойства будет значение, то записывается массив в json строку
        if ($isEmpty === false) {
            $result_arr = [];
            foreach ($currentFieldsInArrWithValueKey as $key => $item) {
                $result_arr[$key] = json_encode($item['VALUE'] ?? [], JSON_HEX_TAG);
            }
            $result = $result_arr;
        } else {
            $result = [];
        }

        return $result; // index => json_encoded_str
    }

    private static $UFCPropFielDirForSave = 'uf_cprop';

    /** Подготовка файла к сохранению в БД
     * нужна для renderEditForm или на событии сохранения или обновлении файла на форме сущности с пользовательским полем
     */
    private static function prepareFileToDBOnBeforeSave($arValue)
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
            $result = CFile::SaveFile($arValue, self::$UFCPropFielDirForSave);
        }

        return $result;
    }

    /** Получение данных из БД и их подготовка
     * @param $userField array Данные поля (настройки и др.)
     * @param $arValue array|mixed Данные экземпляра поля (который привязан к экземпляру функции,
     * строковые данные сюда приходят пройдя через `htmlspecialcharsEx`)
     * @return array
     * @see \htmlspecialcharsEx
     * @see self::GetPropertyFieldHtml
     */
    private static function ConvertFromDB($userField, $arValue)
    {
        $return = [];

        // если значение поля не пусто, ...
        if (!empty($arValue['VALUE'])) {
            // работаем с ним как с json строкой
            $arData = json_decode(htmlspecialcharsback($arValue['VALUE']), true);

            foreach ($arData as $code => $value) { // набор данных в массив для ключа 'value'
                $return['VALUE'][$code] = $value;
            }
        }
        return $return;
    }

    // endregion
}