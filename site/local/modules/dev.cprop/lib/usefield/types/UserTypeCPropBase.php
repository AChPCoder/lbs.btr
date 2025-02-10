<?php

/** Данный тип реализуется при работе как НЕ `множественный`, для реализации множественного
 * комплексного пользовательскго поля требуется доработка (и тестирование).
 */
class UserTypeCPropBase extends \Bitrix\Main\UserField\Types\StringType
{
    // region Init

    /** Представляет собой обязательный метод для определения типа пользовательского поля любой сущности.<br/>
     * Возвращает массив данных типа.
     * <ul>
     * <li>в `EDIT_CALLBACK` можно записать что-то вроде этого `[static::class, 'renderEdit']` или этого `'globalVisibleMethodRenderEdit'`</li>
     * <li>в `VIEW_CALLBACK` можно записать что-то вроде этого `[static::class, 'renderView']` или этого `'globalVisibleMethodRenderView'`</li>
     * <li>в `BASE_TYPE` можно записать что-то вроде этого `\CUserTypeManager::BASE_TYPE_STRING`</li>
     * </ul>
     * @return array{
     *     USER_TYPE_ID: string,
     *     CLASS_NAME: string,
     *     EDIT_CALLBACK: string|array{0: string, 1:string},
     *     VIEW_CALLBACK: string|array{0: string, 1:string},
     *     USE_FIELD_COMPONENT: string,
     *     DESCRIPTION: string,
     *     BASE_TYPE: string,
     * }
     */
    public static function GetUserTypeDescription(): array
    {
        return parent::GetUserTypeDescription();
    }

    // endregion

    // region Form of UF type

    /** Метод возвращает безопасный HTML отображения настроек свойства для формы редактирования типа пользовательского поля.<br/>
     * Метод статический при использовании штатных свойств UF полей.<br/>
     * Обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается при построении формы редактирования типа пользовательского поля.</blockquote>
     * @param $userField array{
     *     ID: string,
     *     ENTITY_ID: string,
     *     FIELD_NAME: string,
     *     USER_TYPE_ID: string,
     *     XML_ID: string,
     *     SORT: string,
     *     MULTIPLE: string,
     *     MANDATORY: string,
     *     SHOW_FILTER: string,
     *     SHOW_IN_LIST: string,
     *     EDIT_IN_LIST: string,
     *     IS_SEARCHABLE: string,
     *     SETTINGS: array{USER_VALUE: array},
     *     EDIT_FORM_LABEL: array{en:string, ru:string},
     *     LIST_COLUMN_LABEL: array{en:string, ru:string},
     *     LIST_FILTER_LABEL: array{en:string, ru:string},
     *     ERROR_MESSAGE: array{en:string, ru:string},
     *     HELP_MESSAGE: array{en:string, ru:string}
     * } Данные текущего поля для настроек
     * <table style="white-space: nowrap; border: solid 1px #ABABAB;" width="100%" cellspacing="0" border="1">
     * <thead>
     * <tr>
     * <th>key</th>
     * <th>type</th>
     * <th>description</th>
     * </tr>
     * </thead>
     * <tbody><tr>
     * <td>ID</td>
     * <td>string</td>
     * <td>ИД</td>
     * </tr>
     * <tr>
     * <td>ENTITY_ID</td>
     * <td>string</td>
     * <td>ИД сущности UF поля, то к чему оно привязано</td>
     * </tr>
     * <tr>
     * <td>FIELD_NAME</td>
     * <td>string</td>
     * <td>Имя UF поля</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE_ID</td>
     * <td>string</td>
     * <td>ИД типа UF поля, задается PHP кодом типа из его класса</td>
     * </tr>
     * <tr>
     * <td>XML_ID</td>
     * <td>string</td>
     * <td>XML ID типа UF поля</td>
     * </tr>
     * <tr>
     * <td>SORT</td>
     * <td>string</td>
     * <td>Порядок сортировки (среди других UF полей)</td>
     * </tr>
     * <tr>
     * <td>MULTIPLE</td>
     * <td>string Y|N</td>
     * <td>Множественное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>MANDATORY</td>
     * <td>string Y|N</td>
     * <td>Обязательное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_FILTER</td>
     * <td>string Y|N</td>
     * <td>Показывать в фильтре? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_IN_LIST</td>
     * <td>string Y|N</td>
     * <td>Показывать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>EDIT_IN_LIST</td>
     * <td>string Y|N</td>
     * <td>Редактировать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>IS_SEARCHABLE</td>
     * <td>string Y|N</td>
     * <td>Доступен для поиска (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SETTINGS</td>
     * <td>array</td>
     * <td>Массив дополнительных настроек UF поля (туда будут записаны свои нужные настройки)</td>
     * </tr>
     * <tr>
     * <td>EDIT_FORM_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>LIST_COLUMN_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля в колонке списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>LIST_FILTER_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля в фильтре списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>ERROR_MESSAGE</td>
     * <td>array{en:string,ru:string}</td>
     * <td></td>
     * </tr>
     * <tr>
     * <td>HELP_MESSAGE</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Подсказка UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * </tbody></table>
     *
     * @param $additionalParameters array{
     *     NAME: string
     * }|null Дополнительные параметры поля, с ключом NAME хранится ключ данных поля, в котором хранятся дополнительные настройки
     * @param $varsFromForm array|false
     * @return string
     * @see \CIBlockPropertyCProp::GetSettingsHTML Аналог этой функции
     */
    public static function GetSettingsHTML($userField, ?array $additionalParameters, $varsFromForm): string {
        return parent::GetSettingsHTML($userField, $additionalParameters, $varsFromForm);
    }

    /** Эта функция вызывается перед сохранением метаданных свойства в БД.<br/>
     *  В функции следует "очищать" массив с настройками свойства с данным типом.<br/>
     *  Чтобы случайно / намеренно никто не записал какой-либо мусор сюда.
     * @param $userField array{
     *     ID: string,
     *     ENTITY_ID: string,
     *     FIELD_NAME: string,
     *     USER_TYPE_ID: string,
     *     XML_ID: string,
     *     SORT: string,
     *     MULTIPLE: string,
     *     MANDATORY: string,
     *     SHOW_FILTER: string,
     *     SHOW_IN_LIST: string,
     *     EDIT_IN_LIST: string,
     *     IS_SEARCHABLE: string,
     *     SETTINGS: array{USER_VALUE: array},
     *     EDIT_FORM_LABEL: array{en:string, ru:string},
     *     LIST_COLUMN_LABEL: array{en:string, ru:string},
     *     LIST_FILTER_LABEL: array{en:string, ru:string},
     *     ERROR_MESSAGE: array{en:string, ru:string},
     *     HELP_MESSAGE: array{en:string, ru:string}
     * } Массив описиывающий поле. Внимание! Это описание поля ещё не сохраненного в БД поля!<br/>
     * <table style="white-space: nowrap; border: solid 1px #ABABAB;" width="100%" cellspacing="0" border="1">
     * <thead>
     * <tr>
     * <th>key</th>
     * <th>type</th>
     * <th>description</th>
     * </tr>
     * </thead>
     * <tbody><tr>
     * <td>ID</td>
     * <td>string</td>
     * <td>ID</td>
     * </tr>
     * <tr>
     * <td>ENTITY_ID</td>
     * <td>string</td>
     * <td>ИД сущности UF поля, то к чему оно привязано</td>
     * </tr>
     * <tr>
     * <td>FIELD_NAME</td>
     * <td>string</td>
     * <td>Имя UF поля</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE_ID</td>
     * <td>string</td>
     * <td>ИД типа UF поля, задается PHP кодом типа из его класса</td>
     * </tr>
     * <tr>
     * <td>XML_ID</td>
     * <td>string</td>
     * <td>XML ID типа UF поля</td>
     * </tr>
     * <tr>
     * <td>SORT</td>
     * <td>string</td>
     * <td>Порядок сортировки (среди других UF полей)</td>
     * </tr>
     * <tr>
     * <td>MULTIPLE</td>
     * <td>string Y|N</td>
     * <td>Множественное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>MANDATORY</td>
     * <td>string Y|N</td>
     * <td>Обязательное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_FILTER</td>
     * <td>string Y|N</td>
     * <td>Показывать в фильтре? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_IN_LIST</td>
     * <td>string Y|N</td>
     * <td>Показывать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>EDIT_IN_LIST</td>
     * <td>string Y|N</td>
     * <td>Редактировать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>IS_SEARCHABLE</td>
     * <td>string Y|N</td>
     * <td>Доступен для поиска (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SETTINGS</td>
     * <td>array</td>
     * <td>Массив дополнительных настроек UF поля (туда будут записаны свои нужные настройки)</td>
     * </tr>
     * <tr>
     * <td>EDIT_FORM_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>LIST_COLUMN_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля в колонке списка сущности (если отобрадается)</td>
     * </tr>
     * <tr>
     * <td>LIST_FILTER_LABEL</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Имя UF поля в фильтре списка сущности (если отобрадается)</td>
     * </tr>
     * <tr>
     * <td>ERROR_MESSAGE</td>
     * <td>array{en:string,ru:string}</td>
     * <td></td>
     * </tr>
     * <tr>
     * <td>HELP_MESSAGE</td>
     * <td>array{en:string,ru:string}</td>
     * <td>Подсказка UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * </tbody></table>
     *
     * @return string[] Массив, что позднее будет сериализован и сохранен в БД.
     */
    public static function prepareSettings(array $userField): array {
        return parent::prepareSettings($userField);
    }

    /**
     * This function is validator.
     * Called from the CheckFields method of the $USER_FIELD_MANAGER object,
     * which can be called from the Add / Update methods of the property owner entity.
     * @param array $userField
     * @param string|array $value
     * @return array
     */
    public static function checkFields(array $userField, $value): array {
        return [];
    }

    /** Указание, что в инпуте значения поля могут храниться массив (нужно для поддержки работы подполей "Файл") */
    public static function canUseArrayValueForSingleField()
    {
        return true;
    }

    // endregion

    // region Form with UF of that type

    /** Метод должен вернуть HTML отображения элемента управления для редактирования значений свойства сущности в административной части.<br/>
     * Метод статический при использовании штатных свойств.<br/>
     * Обязан быть статическим при использовании php7.
     * <blockquote><b>Примечание:</b> вызывается во время построения формы редактирования элемента.</blockquote>
     * @param $userField array{
     *     ID: string,
     *     ENTITY_ID: string,
     *     FIELD_NAME: string,
     *     USER_TYPE_ID: string,
     *     XML_ID: string,
     *     SORT: string,
     *     MULTIPLE: string,
     *     MANDATORY: string,
     *     SHOW_FILTER: string,
     *     SHOW_IN_LIST: string,
     *     EDIT_IN_LIST: string,
     *     IS_SEARCHABLE: string,
     *     SETTINGS: array{USER_VALUE: array},
     *     EDIT_FORM_LABEL: string,
     *     LIST_COLUMN_LABEL: string,
     *     LIST_FILTER_LABEL: string,
     *     ERROR_MESSAGE: string,
     *     HELP_MESSAGE: string,
     *     USER_TYPE: array{USER_TYPE_ID: string,CLASS_NAME: string,DESCRIPTION: string,BASE_TYPE: string},
     *     VALUE: string|null,
     *     ENTITY_VALUE_ID: int,
     *     CUSTOM_DATA: array,
     *     VALUE_ID: int,
     * } Данные текущего поля<br/>
     * <table style="white-space: nowrap; border: solid 1px #ABABAB" width="100%" cellspacing="0" border="1">
     * <thead>
     * <tr>
     * <th>key</th>
     * <th>type</th>
     * <th>description</th>
     * </tr>
     * </thead>
     * <tbody>
     * <tr>
     * <td>ID</td>
     * <td>string</td>
     * <td>ИД</td>
     * </tr>
     * <tr>
     * <td>ENTITY_ID</td>
     * <td>string</td>
     * <td>ИД сущности UF поля, то к чему оно привязано</td>
     * </tr>
     * <tr>
     * <td>FIELD_NAME</td>
     * <td>string</td>
     * <td>Имя UF поля</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE_ID</td>
     * <td>string</td>
     * <td>ИД типа UF поля, задается PHP кодом типа из его класса</td>
     * </tr>
     * <tr>
     * <td>XML_ID</td>
     * <td>string</td>
     * <td>XML ID типа UF поля</td>
     * </tr>
     * <tr>
     * <td>SORT</td>
     * <td>string</td>
     * <td>Порядок сортировки (среди других UF полей)</td>
     * </tr>
     * <tr>
     * <td>MULTIPLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Множественное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>MANDATORY</td>
     * <td>string "Y"|"N"</td>
     * <td>Обязательное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_FILTER</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в фильтре? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>EDIT_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Редактировать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>IS_SEARCHABLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Доступен для поиска (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SETTINGS</td>
     * <td>array</td>
     * <td>Массив дополнительных настроек UF поля (туда будут записаны свои нужные настройки)</td>
     * </tr>
     * <tr>
     * <td>EDIT_FORM_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>LIST_COLUMN_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в колонке списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>LIST_FILTER_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в фильтре списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>ERROR_MESSAGE</td>
     * <td>string</td>
     * <td></td>
     * </tr>
     * <tr>
     * <td>HELP_MESSAGE</td>
     * <td>string</td>
     * <td>Подсказка UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE</td>
     * <td>array</td>
     * <td>Массив с данными типа поля, содержит данные из <code>GetUserTypeDescription</code> класса типа UF поля</td>
     * </tr>
     * <tr>
     * <td>VALUE</td>
     * <td>string|null</td>
     * <td>Значение данного UF поля для текущего элемента сущности</td>
     * </tr>
     * <tr>
     * <td>ENTITY_VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * <tr>
     * <td>CUSTOM_DATA</td>
     * <td>array</td>
     * <td>Пока что непонятно</td>
     * </tr>
     * <tr>
     * <td>VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * </tbody>
     * </table>
     * @param $additionalParameters array{NAME: string,VALUE: string,VALIGN: string,ROWCLASS: string,mode: string}|null Дополнительные параметры
     * для поля, значения по ключам `VALIGN`, `ROWCLASS` передаются по ссылке
     * @return string HTML форма для ввода значения UF свойства (внутри будут поля этого свойства)
     * @see \CIBlockPropertyCProp::GetPropertyFieldHtml Аналог этой функции
     */
    public static function renderEditForm(array $userField, ?array $additionalParameters = []): string
    {
        return parent::renderEditForm($userField, $additionalParameters);
    }

    /** Метод, вызываемый при сохранении сущности с текущим пользовательским полем<br/>
     * <blockquote><b>Примечание:</b> вызывается перед сохранением значения свойства в БД.</blockquote>
     * <b>Внимание!</b> Если на форме нет поля ввода с именем $fieldName['VALUE'], то для данного комплексного поля не будет вызван этот метод.<br/>
     *
     * @param $userField array{
     * ID: string,
     * ENTITY_ID: string,
     * FIELD_NAME: string,
     * USER_TYPE_ID: string,
     * XML_ID: string,
     * SORT: string,
     * MULTIPLE: string,
     * MANDATORY: string,
     * SHOW_FILTER: string,
     * SHOW_IN_LIST: string,
     * EDIT_IN_LIST: string,
     * IS_SEARCHABLE: string,
     * SETTINGS: array{USER_VALUE: array},
     * EDIT_FORM_LABEL: string,
     * LIST_COLUMN_LABEL: string,
     * LIST_FILTER_LABEL: string,
     * ERROR_MESSAGE: string,
     * HELP_MESSAGE: string,
     * USER_TYPE: array{USER_TYPE_ID: string,CLASS_NAME: string,DESCRIPTION: string,BASE_TYPE: string},
     * VALUE: string|null,
     * ENTITY_VALUE_ID: int,
     * CUSTOM_DATA: array,
     * VALUE_ID: int,
     * } Данные текущего поля<br/>
     * <table style="white-space: nowrap; border: solid 1px #ABABAB" width="100%" cellspacing="0" border="1">
     * <thead>
     * <tr>
     * <th>key</th>
     * <th>type</th>
     * <th>description</th>
     * </tr>
     * </thead>
     * <tbody>
     * <tr>
     * <td>ID</td>
     * <td>string</td>
     * <td>ИД</td>
     * </tr>
     * <tr>
     * <td>ENTITY_ID</td>
     * <td>string</td>
     * <td>ИД сущности UF поля, то к чему оно привязано</td>
     * </tr>
     * <tr>
     * <td>FIELD_NAME</td>
     * <td>string</td>
     * <td>Имя UF поля</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE_ID</td>
     * <td>string</td>
     * <td>ИД типа UF поля, задается PHP кодом типа из его класса</td>
     * </tr>
     * <tr>
     * <td>XML_ID</td>
     * <td>string</td>
     * <td>XML ID типа UF поля</td>
     * </tr>
     * <tr>
     * <td>SORT</td>
     * <td>string</td>
     * <td>Порядок сортировки (среди других UF полей)</td>
     * </tr>
     * <tr>
     * <td>MULTIPLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Множественное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>MANDATORY</td>
     * <td>string "Y"|"N"</td>
     * <td>Обязательное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_FILTER</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в фильтре? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>EDIT_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Редактировать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>IS_SEARCHABLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Доступен для поиска (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SETTINGS</td>
     * <td>array</td>
     * <td>Массив дополнительных настроек UF поля (туда будут записаны свои нужные настройки)</td>
     * </tr>
     * <tr>
     * <td>EDIT_FORM_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>LIST_COLUMN_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в колонке списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>LIST_FILTER_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в фильтре списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>ERROR_MESSAGE</td>
     * <td>string</td>
     * <td></td>
     * </tr>
     * <tr>
     * <td>HELP_MESSAGE</td>
     * <td>string</td>
     * <td>Подсказка UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE</td>
     * <td>array</td>
     * <td>Массив с данными типа поля, содержит данные из <code>GetUserTypeDescription</code> класса типа UF поля</td>
     * </tr>
     * <tr>
     * <td>VALUE</td>
     * <td>string|null</td>
     * <td>Значение данного UF поля для текущего элемента сущности</td>
     * </tr>
     * <tr>
     * <td>ENTITY_VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * <tr>
     * <td>CUSTOM_DATA</td>
     * <td>array</td>
     * <td>Пока что непонятно</td>
     * </tr>
     * <tr>
     * <td>VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * </tbody>
     * </table>
     *
     * @param string $value Значение с формы<br/>
     *  (там на данный момент попадают только файлы, другие поля нужно получать из
     *  \Bitrix\Main\Application::getInstance()->getContext()->getRequest()
     *  )
     * @param string|false $user_id ИД пользователя
     * <hr/>
     * @return string Новое значение поля для сохранения в БД
     *
     * @see \CIBlockPropertyCProp::ConvertToDB аналог этой функции
     */
    public static function onBeforeSave(array $userField, $value, $user_id) {
        return $value;
    }

    /** Метод, вызываемый при сохранении сущности с текущим пользовательским полем<br/>
     * <blockquote><b>Примечание:</b> вызывается перед сохранением значения свойства в БД.</blockquote>
     * <b>Внимание!</b> Если на форме нет поля ввода с именем $fieldName['VALUE'], то для данного комплексного поля не будет вызван этот метод.<br/>
     *
     * @param $userField array{
     * ID: string,
     * ENTITY_ID: string,
     * FIELD_NAME: string,
     * USER_TYPE_ID: string,
     * XML_ID: string,
     * SORT: string,
     * MULTIPLE: string,
     * MANDATORY: string,
     * SHOW_FILTER: string,
     * SHOW_IN_LIST: string,
     * EDIT_IN_LIST: string,
     * IS_SEARCHABLE: string,
     * SETTINGS: array{USER_VALUE: array},
     * EDIT_FORM_LABEL: string,
     * LIST_COLUMN_LABEL: string,
     * LIST_FILTER_LABEL: string,
     * ERROR_MESSAGE: string,
     * HELP_MESSAGE: string,
     * USER_TYPE: array{USER_TYPE_ID: string,CLASS_NAME: string,DESCRIPTION: string,BASE_TYPE: string},
     * VALUE: string|null,
     * ENTITY_VALUE_ID: int,
     * CUSTOM_DATA: array,
     * VALUE_ID: int,
     * } Данные текущего поля<br/>
     * <table style="white-space: nowrap; border: solid 1px #ABABAB" width="100%" cellspacing="0" border="1">
     * <thead>
     * <tr>
     * <th>key</th>
     * <th>type</th>
     * <th>description</th>
     * </tr>
     * </thead>
     * <tbody>
     * <tr>
     * <td>ID</td>
     * <td>string</td>
     * <td>ИД</td>
     * </tr>
     * <tr>
     * <td>ENTITY_ID</td>
     * <td>string</td>
     * <td>ИД сущности UF поля, то к чему оно привязано</td>
     * </tr>
     * <tr>
     * <td>FIELD_NAME</td>
     * <td>string</td>
     * <td>Имя UF поля</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE_ID</td>
     * <td>string</td>
     * <td>ИД типа UF поля, задается PHP кодом типа из его класса</td>
     * </tr>
     * <tr>
     * <td>XML_ID</td>
     * <td>string</td>
     * <td>XML ID типа UF поля</td>
     * </tr>
     * <tr>
     * <td>SORT</td>
     * <td>string</td>
     * <td>Порядок сортировки (среди других UF полей)</td>
     * </tr>
     * <tr>
     * <td>MULTIPLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Множественное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>MANDATORY</td>
     * <td>string "Y"|"N"</td>
     * <td>Обязательное поле? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_FILTER</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в фильтре? (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SHOW_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Показывать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>EDIT_IN_LIST</td>
     * <td>string "Y"|"N"</td>
     * <td>Редактировать в списке? (по-умолчанию Y)</td>
     * </tr>
     * <tr>
     * <td>IS_SEARCHABLE</td>
     * <td>string "Y"|"N"</td>
     * <td>Доступен для поиска (по-умолчанию N)</td>
     * </tr>
     * <tr>
     * <td>SETTINGS</td>
     * <td>array</td>
     * <td>Массив дополнительных настроек UF поля (туда будут записаны свои нужные настройки)</td>
     * </tr>
     * <tr>
     * <td>EDIT_FORM_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>LIST_COLUMN_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в колонке списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>LIST_FILTER_LABEL</td>
     * <td>string</td>
     * <td>Имя UF поля в фильтре списка сущности (если отображается)</td>
     * </tr>
     * <tr>
     * <td>ERROR_MESSAGE</td>
     * <td>string</td>
     * <td></td>
     * </tr>
     * <tr>
     * <td>HELP_MESSAGE</td>
     * <td>string</td>
     * <td>Подсказка UF поля на форме редактирования сущности с этим полем</td>
     * </tr>
     * <tr>
     * <td>USER_TYPE</td>
     * <td>array</td>
     * <td>Массив с данными типа поля, содержит данные из <code>GetUserTypeDescription</code> класса типа UF поля</td>
     * </tr>
     * <tr>
     * <td>VALUE</td>
     * <td>string|null</td>
     * <td>Значение данного UF поля для текущего элемента сущности</td>
     * </tr>
     * <tr>
     * <td>ENTITY_VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * <tr>
     * <td>CUSTOM_DATA</td>
     * <td>array</td>
     * <td>Пока что непонятно</td>
     * </tr>
     * <tr>
     * <td>VALUE_ID</td>
     * <td>int</td>
     * <td>ИД значения сущности</td>
     * </tr>
     * </tbody>
     * </table>
     *
     * @param string $value Значение с формы<br/>
     * (там на данный момент попадают толко файлы, другие поля нужно получать из
     * \Bitrix\Main\Application::getInstance()->getContext()->getRequest()
     * )
     * @param string|false $user_id ИД пользователя
     * <hr/>
     * @return string Новое значение поля для сохранения в БД
     *
     * @see \CIBlockPropertyCProp::ConvertToDB аналог этой функции, но для множественного моля
     */
    public static function onBeforeSaveAll(array $userField, $value, $user_id) {
        return $value;
    }

    // endregion
}