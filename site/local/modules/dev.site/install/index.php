<?

class dev_site extends CModule
{
    const MODULE_ID = 'dev.site';

    public $MODULE_ID = 'dev.site',
        $MODULE_VERSION,
        $MODULE_VERSION_DATE,
        $MODULE_NAME = 'Тренировочный модуль',
        $PARTNER_NAME = 'dev';

    public function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . 'version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    /** Обертка над функцией подписывания на событие
     * @param $from_module_id string ид модуля, на события которого подписываемся
     * @param $message_id string имя события, на которое подписываемся
     * @param $to_module_id string ид текущего модуля
     * @param $to_class string класс из <id_модуля>/lib/.../класс.php FQN
     * @param $to_method string метод класс из <id_модуля>/lib/.../класс.php
     * @return void
     */
    function moduleSubscribeOnEvent($from_module_id, $message_id, $to_module_id, $to_class, $to_method) {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler($from_module_id, $message_id, $to_module_id, $to_class, $to_method);
    }

    /** Обертка над функцией подписывания на событие
     * @param $from_module_id string ид модуля, на события которого подписываемся
     * @param $message_id string имя события, на которое подписываемся
     * @param $to_module_id string ид текущего модуля
     * @param $to_class string класс из <id_модуля>/lib/.../класс.php FQN
     * @param $to_method string метод класс из <id_модуля>/lib/.../класс.php
     * @return void
     */
    function moduleUnSubscribeOnEvent($from_module_id, $message_id, $to_module_id, $to_class, $to_method) {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler($from_module_id, $message_id, $to_module_id, $to_class, $to_method);
    }

    function InstallEvents()
    {
        $this->moduleSubscribeOnEvent(
            'iblock',
            'OnAfterIBlockElementAdd',
            'dev.site',
            '\\Dev\\Site\\Handlers\\Iblock',
            'addLog'
        );
        $this->moduleSubscribeOnEvent(
            'iblock',
            'OnAfterIBlockElementUpdate',
            'dev.site',
            '\\Dev\\Site\\Handlers\\Iblock',
            'addLog'
        );
    }

    function UnInstallEvents()
    {
        $this->moduleUnSubscribeOnEvent(
            'iblock',
            'OnAfterIBlockElementAdd',
            'dev.site',
            '\\Dev\\Site\\Handlers\\Iblock',
            'addLog'
        );
        $this->moduleUnSubscribeOnEvent(
            'iblock',
            'OnAfterIBlockElementUpdate',
            'dev.site',
            '\\Dev\\Site\\Handlers\\Iblock',
            'addLog'
        );
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $this->InstallEvents();
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);

        $this->UnInstallEvents();
    }
}
