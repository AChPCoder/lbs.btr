# Проект сайта на 1С-Битрикс: Управление сайтом
Сделан на демонстрационной версии 1С-Битрикс: Управление сайтом
в комплектации "Малый бизнес".

[Ссылка на загрузку демоверсии (раздел "Дистрибутивы для PHP")](https://www.1c-bitrix.ru/download/cms.php#tab-subsection-3)

---

## Выполнение задания 1

### Статус выполнения

Для задания 1 выполнено:

1. [x] развертка проекта,
2. [x] подключение composer директории,
3. [x] создание шаблона news.list.

### О запуске проекта (Apache&PHP&MySQL&1С-Битрикс: Управление сайтом&Настройки локального сайта)

#### Окружение проекта

Детали окружения проекта:

* Веб сервер Apache 2.4.58
* PHP версии 8.1.25
* MySQL совместимый сервер MariaDB 10.4.32

#### Перед первым запуском проекта

Включенные PHP параметры в файле php.ini
```ini
short_open_tag=On
log_errors=On
extension=curl
extension=gd
```

В файле `./site/bitrix/php_interface/after_connect_d7.php` внесены
рекомендации по параметрам соединения к БД
```php
<?php
$this->queryExecute("SET sql_mode=''");
$this->queryExecute("SET NAMES 'utf8mb4'");
$this->queryExecute("SET collation_connection = 'utf8mb4_unicode_ci'");
```

Для проекта создан VirtualHost из настроек Apache, пример приложен ниже:
```apacheconf
<VirtualHost *:80>
    ServerAdmin webmaster@lbs.btr
    DocumentRoot "C:/root/app/xampp8125/htdocs/lbs.btr/site"
    ServerName lbs.btr
    ErrorLog "logs/lbs.btr-error.log"
    CustomLog "logs/lbs.btr-access.log" common
</VirtualHost>
```

На тестовой машине настроен dns сопоставление ip-domain, ниже пример записи в Windows hosts файл
```ini
127.0.0.1 lbs.btr
```

Для инсталляции сайта использовано тестовое доменное имя `lbs.btr` (без использования реального домена), название сайта `LBS BTRX+`, URL сервера (без http://): `lbs.btr`

#### После первого запуска проекта и прохождения инсталляции через веб-интерфейс

При развертке проекта на компьютере (хост-системе) доступны алиасы для
`php` и `composer`, чтобы выполнять подключение библиотек сайта.
Подключение composer к сайту выполнялось по инструкции с сайта портала
разработчиков 1С-Битрикс, [её ссылка: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4637](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4637)

При успешном конфигурировании 1С-Битрикс и внешнего composer.json
добавлен следующий код в файл `./site/bitrix/php_interface/init.php`
```php
<?php

$fn_get_composer_path = function () {
    $composer_path = $_SERVER["DOCUMENT_ROOT"] . '/../bx_my_composer/vendor/autoload.php';
    $is_ok = file_exists($composer_path);
    if(!$is_ok) {
        return null;
    }
    return realpath($composer_path);
};
// если путь есть, то подключаем
if (($composer_path = $fn_get_composer_path()) !== null) {
    require_once($composer_path);
}
```

### Об создании и кастомизации шаблона компонента news.list

Для хранения измененных шаблонов создана папка `/local` (в папке `/site`)

На тестовом сайте используется шаблон `Адаптивный шаблон интернет-магазина`,
его ид=`eshop_bootstrap_v4`

В данном случае по умолчанию шаблоном компонента `news.list` является
шаблон `bootstrap_v4`.

Войдя в аккаунт администратора можно скопировать шаблон в один из существующих шаблонов сайта. В данном случае шаблон (`bootstrap_v4`) скопирован в шаблон `.default`

Получен путь до шаблона компонента такой: `/site/local/templates/.default/components/bitrix/news.list/`

Содержимое папки шаблона (<news.list> - это путь до папки компонента):
* `<news.list>/bootstrap_v4/images/` - папка до картинок компонента
* `<news.list>/bootstrap_v4/lang/` - папка до языковых параметров (переводов) компонента
* `<news.list>/bootstrap_v4/.parameters.php` - получение данных для шаблона (получение списка новостей из инфоблока и других параметров)
* `<news.list>/bootstrap_v4/.result_modifier.php` - подготовка уже полученных данных для шаблона (уточнение параметров элементов списка новостей из инфоблока и других параметров)
* `<news.list>/bootstrap_v4/script.js` - JavaScript функции для дополнительного функционала на стороне клиента
* `<news.list>/bootstrap_v4/style.css` - Стилизация компонента
* `<news.list>/bootstrap_v4/template.php` - Файл шаблона компонента. Данные проходят такой путь: `.parameters.php -> .result_modifier.php -> template.php`


---

..
