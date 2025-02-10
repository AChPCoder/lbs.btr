<?php

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class dev_cprop extends CModule
{
    public $MODULE_ID = 'dev.cprop';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;

    function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';

        $this->MODULE_ID = 'dev.cprop';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('IEX_CPROP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('IEX_CPROP_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('IEX_CPROP_PARTNER_NAME');
        $this->PARTNER_URI = 'https://example.com'; // ссылка на адрес сайта разработчика

//        $this->FILE_PREFIX = 'cprop';
//        $this->MODULE_FOLDER = str_replace('.', '_', $this->MODULE_ID);
//        $this->FOLDER = 'bitrix';

//        $this->INSTALL_PATH_FROM = '/' . $this->FOLDER . '/modules/' . $this->MODULE_ID;
    }

    function isVersionD7()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('IEX_CPROP_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
    }


    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    function installFiles()
    {
        return true;
    }

    function uninstallFiles()
    {
        return true;
    }

    function getEvents()
    {
        return [
            [
                'FROM_MODULE' => 'iblock', // target module
                'EVENT' => 'OnIBlockPropertyBuildList', // event of target module
                'CLASS_HANDLER' => 'CIBlockPropertyCProp',
                'TO_METHOD' => 'GetUserTypeDescription' // method of this module class
            ],
            [
                'FROM_MODULE' => 'main', // target module
                'EVENT' => 'OnUserTypeBuildList', // event of target module
                'CLASS_HANDLER' => 'UserTypeCProp',
                'TO_METHOD' => 'GetUserTypeDescription' // method of this module class
            ],
        ];
    }

    private static $eventManager = null;

    /** Обертка над функцией подписывания на событие
     * @param $from_module_id string ид модуля, на события которого подписываемся
     * @param $message_id string имя события, на которое подписываемся
     * @param $to_module_id string ид текущего модуля
     * @param $to_class string класс из <id_модуля>/lib/.../класс.php FQN
     * @param $to_method string метод класс из <id_модуля>/lib/.../класс.php
     * @return void
     */
    private function moduleSubscribeOnEvent($from_module_id, $message_id, $to_module_id, $to_class, $to_method)
    {
        if (!isset($eventManager)) {
            self::$eventManager = \Bitrix\Main\EventManager::getInstance();
        }
        self::$eventManager->registerEventHandler($from_module_id, $message_id, $to_module_id, $to_class, $to_method);
    }

    /** Обертка над функцией подписывания на событие
     * @param $from_module_id string ид модуля, на события которого подписываемся
     * @param $message_id string имя события, на которое подписываемся
     * @param $to_module_id string ид текущего модуля
     * @param $to_class string класс из <id_модуля>/lib/.../класс.php FQN
     * @param $to_method string метод класс из <id_модуля>/lib/.../класс.php
     * @return void
     */
    private function moduleUnSubscribeOnEvent($from_module_id, $message_id, $to_module_id, $to_class, $to_method)
    {
        if (!isset($eventManager)) {
            self::$eventManager = \Bitrix\Main\EventManager::getInstance();
        }
        self::$eventManager->unRegisterEventHandler($from_module_id, $message_id, $to_module_id, $to_class, $to_method);
    }

    function InstallEvents()
    {
//        $classHandler = 'CIBlockPropertyCProp';
//
        $arEvents = $this->getEvents();
        foreach ($arEvents as $arEvent) {
            $this->moduleSubscribeOnEvent(
                $arEvent['FROM_MODULE'],
                $arEvent['EVENT'],
                $this->MODULE_ID,
                $arEvent['CLASS_HANDLER'],
                $arEvent['TO_METHOD']
            );
        }

        return true;
    }

    function UnInstallEvents()
    {
        $classHandler = 'CIBlockPropertyCprop';

        $arEvents = $this->getEvents();
        foreach ($arEvents as $arEvent) {
            $this->moduleUnSubscribeOnEvent(
                $arEvent['FROM_MODULE'],
                $arEvent['EVENT'],
                $this->MODULE_ID,
                $classHandler,
                $arEvent['TO_METHOD']
            );
        }

        return true;
    }
}