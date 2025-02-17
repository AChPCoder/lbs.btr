<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

class COopComponentsYdiskManager extends CBitrixComponent
{
    // region includeComponent related stuff

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
        return $arParams;
    }

    private static $addFileInputName = 'add_file';
    private static $editFileInputName = 'edit_file';

    /** It`s a override method code for using component without component.php, only with class.php, template.php and others
     * <blockquote>Метод не будет вызван при ajax запросе</blockquote>
     * @return mixed Value, which will be returned at `->IncludeComponent`
     */
    public function executeComponent()
    {
        // подключаем файл переводов (./lang/<lang_id>/class.php)
        Loc::loadLanguageFile(__FILE__);

        // do stuff ...

//        /** @var CMain $APPLICATION */
//        global $APPLICATION;
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        $request_values = $request->getValues();
        $is_post = $request->isPost();
        $type = $request_values['type'] ?? 'view';
        $is_view = !$is_post && $type == 'view';
        if($is_view) {
            // getting list
            $this->arResult['type'] = 'view';

            // getting popup messages (display of errors)
            $this->arResult['errors'] = [];

            $data = $this->getFilesList();
            $files_data = [];
            $offset = mb_strlen(self::$root_path) - 1;
            /** @var \Arhitector\Yandex\Disk\Resource\Closed $item */
            foreach ($data as $item) {
                $path = $item->get('path');
                $is_starts_with = mb_strpos($path, self::$root_path) === 0;
                $length = mb_strlen($path) - mb_strlen(self::$root_path);
                $length = max($length, 0);
                $app_path = $is_starts_with ? ('app:/' . ltrim(mb_substr($path, $offset + 1, $length), '/')) : $path;
                $files_data[] = [
                    'path' => $path,
                    'app_path' => $app_path,
                ];
            }
            $this->arResult['files'] = $files_data;
            $this->arResult['add_file_input_name'] = self::$addFileInputName;
            $this->arResult['edit_file_input_name'] = self::$editFileInputName;
            $this->arResult['errors'] = $context->getApplication()->getSession()->get('ydisk_errors');
            $context->getApplication()->getSession()->remove('ydisk_errors');
            $this->arResult['success_msg'] = $context->getApplication()->getSession()->get('ydisk_success_msg');
            $context->getApplication()->getSession()->remove('ydisk_success_msg');

            $this->includeComponentTemplate(); // работает как echo содержимого шаблона компонента
            return ''; // работает как return значение вызова метода IncludeComponent
        }

        if ($is_post && !in_array($type, ['add', 'edit'])) {
            LocalRedirect($this->arParams['VIEW_URL']);
        }

        $errors = [];

        $success_msg = false;

        switch ($type) {
            case "add":
                $file_tmp_saved_name = $this->savePostFile(self::$addFileInputName);
                if($file_tmp_saved_name === false) {
                    $errors['TMP_DOWNLOAD'] = 'Не удалось предварительно получить файл для загрузки';
                    break;
                }
                $is_successful_upload = $this->uploadOnYDiskLocalFileByFullPath(
                    $this->getFileYDiskFullPathByName(pathinfo($file_tmp_saved_name, PATHINFO_BASENAME)),
                    $file_tmp_saved_name
                );
                if(!$is_successful_upload) {
                    $errors['YDISK_UPLOAD'] = 'Не удалось загрузить файл в Яндекс Диск';
                    break;
                }
                $success_msg = 'Добавление файла успешно выполнено';
                $is_removed = $this->removeLocalFile($file_tmp_saved_name);
                if (!$is_removed) {
                    $errors['YDISK_UPLOAD_AFTER_REMOVED'] = 'Не удалось удалить предварительно полученный файл для загрузки';
                }
                break;
            case "remove":
                $file_name = $request_values['f'] ?? '';
                $is_exists = $this->checkFileExists($file_name);
                if (!$is_exists) {
                    $errors['NOT_FOUND'] = 'Файл для удаления не существует';
                    break;
                }
                $is_file_removed = $this->removeYDiskFileByName($file_name);
                if (!$is_file_removed) {
                    $errors['YDISK_DELETE'] = 'Ошибка удаления файла';
                    break;
                }
                $success_msg = 'Удаление файла успешно выполнено';

                break;
            case "edit":
                $file_name = $request_values['edit_old_filename'] ?? '';
                $is_exists = $this->checkFileExists($file_name);
                if (!$is_exists) {
                    $errors['NOT_FOUND'] = 'Указанного файла для замены не существует';
                    break;
                }
                $file_tmp_saved_name = $this->savePostFile(self::$editFileInputName);
                if($file_tmp_saved_name === false) {
                    $errors['TMP_DOWNLOAD'] = 'Не удалось предварительно получить файл для загрузки';
                    break;
                }
                $is_successful_upload = $this->uploadOnYDiskLocalFileByFullPath(
                    $this->getFileYDiskFullPathByName($file_name),
                    $file_tmp_saved_name
                );
                if(!$is_successful_upload) {
                    $errors['YDISK_UPLOAD'] = 'Не удалось загрузить файл в Яндекс Диск';
                    break;
                }
                $this->removeLocalFile($file_tmp_saved_name);
                $success_msg = 'Изменение файла успешно выполнено';

                break;
            default:
                LocalRedirect($this->arParams['VIEW_URL']);
                break;
        }


        if (in_array($type, ['add', 'remove', 'edit'])) {
            $context->getApplication()->getSession()->set('ydisk_errors', $errors);
            $context->getApplication()->getSession()->set('ydisk_success_msg', $success_msg);
            LocalRedirect($this->arParams['VIEW_URL']);
        }

        $this->arResult['type'] = $type;

        $this->includeComponentTemplate(); // работает как echo содержимого шаблона компонента
        return ''; // работает как return значение вызова метода IncludeComponent
    }

    // endregion

    // region common private fns

    // region Y.Disk api fns
    private const YDISK_FOLDER = 'YDISK_FOLDER';

    /**
     * @var \Arhitector\Yandex\Disk|null
     */
    private static $instance = null;

    private static $root_path = null;

    private static function getYDiskInstance() {
        if(isset(self::$instance)) {
            return self::$instance;
        }
        self::$instance = new Arhitector\Yandex\Disk(\App\Helpers\LocalConfigH::getInstance()->yandexDiskToken);
        self::$root_path = self::$instance->getResource('app:/')->path;
        return self::$instance;
    }

    private function checkFolder()
    {
        // try getting folder disk:/self::YDISK_FOLDER/
        // not exist return false;
        // exists return true
        return self::getYDiskInstance()->getResource('app:/' . self::YDISK_FOLDER . '/')->has();
    }

    private function createStartFolder()
    {
        $res = false;
        try {
            $res = self::getYDiskInstance()->getResource('app:/' . self::YDISK_FOLDER . '/')->create();
        }
        catch (\Exception $ex) {
            return false;
        }
        // try to create folder without override
        // return boolval of result in API using
        return $res;
    }

    /**
     * @return bool
     */
    private function checkFolderAndCreateOnNotExists()
    {
        if ($this->checkFolder()) {
            return true;
        }
        return $this->createStartFolder();
    }

    /**
     * @var bool|null
     */
    private $checkedFolder = null;

    /**
     * @return \Arhitector\Yandex\Client\Container\Collection
     */
    private function getFilesList()
    {
        if (!isset($this->checkedFolder)) {
            $this->checkedFolder = $this->checkFolderAndCreateOnNotExists();
        }

        if (!$this->checkedFolder) {
            return [];
        }

        /** @var \Arhitector\Yandex\Disk\Resource\Closed $resource */
        $resource = self::getYDiskInstance()->getResource('app:/' . self::YDISK_FOLDER . '/');

//        echo '<pre>';var_dump($resource->toArray());echo '</pre>';exit;

        return $resource->items;
    }

    private function getFileYDiskFullPathByName($filename) {
        return 'app:/' . self::YDISK_FOLDER . '/' . $filename;
    }

    /**
     * @param $filename
     * @param $return_file_if_exists
     * @return \Arhitector\Yandex\Disk\Resource\Closed|bool
     */
    private function checkFileExists($filename, $return_file_if_exists = false)
    {
        $file = self::getYDiskInstance()->getResource($this->getFileYDiskFullPathByName($filename));
        if($return_file_if_exists && $file->has()) {
            return $file;
        }
        return $file->has();
    }

    /**
     * @param $filename
     * @return bool
     */
    private function removeYDiskFileByName($filename) {
        /** @var \Arhitector\Yandex\Disk\Resource\Closed|false $file */
        $file = $this->checkFileExists($filename, true);
        if ($file === false) {
            return false;
        }
        return $file->delete(true) !== false;
    }

    /**
     * @param $filename
     * @return bool
     */
    private function uploadOnYDiskLocalFileByFullPath($remote_filepath, $local_filepath) {
        $resource = self::getYDiskInstance()->getResource($remote_filepath);
        return $resource->upload($local_filepath, true) !== false;
    }
    // endregion

    // region local files fns

    /**
     * @param $full_file_path
     * @return bool
     */
    private function removeLocalFile($full_file_path)
    {
        if (!file_exists($full_file_path)) {
            return true;
        }

        return unlink($full_file_path);
    }

    /**
     * @param $file_input_name
     * @return string|false
     */
    private function savePostFile($file_input_name)
    {
        $file_data = $_FILES[$file_input_name] ?? null;
        if(!isset($file_data)) {
            return false;
        }
        $filename = $file_data['name'];
        $save_folder = \Bitrix\Main\Loader::getDocumentRoot() . '/upload/tmp_ydisk_uploads';
        if(!is_dir($save_folder)) {
            mkdir($save_folder);
        }
        $new_full_path = $save_folder . '/' . $filename;
        $is_done = move_uploaded_file($file_data['tmp_name'], $new_full_path);
        $result = $is_done;
        if ($is_done) {
            $result = $new_full_path;
        }
        return $result;
    }

    // endregion

    // endregion
}
