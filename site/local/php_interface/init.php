<?php

$fn_get_composer_path = function () {
    $composer_path = $_SERVER["DOCUMENT_ROOT"] . '/../bx_my_composer/vendor/autoload.php';
    $is_ok = file_exists($composer_path);
    if(!$is_ok) {
        return null;
    }
    return realpath($composer_path);
};
if (($composer_path = $fn_get_composer_path()) !== null) {
    require_once($composer_path);
}
