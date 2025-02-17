<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$type = $arResult['page_type'];

?>
<div class="yd-view--container fn_yd_view_root">
    <h1>CRUD для файлов на Yandex.Disk</h1>
    <div class="yd-view--popups">
        <?php ?>
        <?php if (!empty($arResult['errors'])) { ?>
            <?php foreach ($arResult['errors'] as $error) { ?>
                <div class="yd-view-popups--item yd-view-popups--item-error"><?= $error ?></div>
            <?php } ?>
        <?php } ?>
        <?php if ($arResult['success_msg']) { ?>
            <div class="yd-view-popups--item yd-view-popups--item-success"><?= $arResult['success_msg'] ?></div>
        <?php } ?>
    </div>
    <div class="yd-view--extra-forms">
        <input type="checkbox" checked="checked" class="d-none fn_toggle_add_visibility"/>
        <input type="checkbox" class="d-none fn_toggle_edit_visibility"/>
        <form action="<?= $arParams['ADD_URL'] ?>" method="post" enctype="multipart/form-data"
              class="yd-view--add-form border border-secondary p-2 fn_yd_view_add_form">
            <h2>Добавить файл</h2>
            <div class="yd-view-a-f--form-content" id="yd-view-a-f--form-content">
                <label>
                    <span>Файл для добавления:</span>
                    <input type="file" name="<?= $arResult['add_file_input_name'] ?>"/>
                </label>
                <label>
                    <input type="submit" title="Отправить"/>
                </label>
            </div>
        </form>
        <form action="<?= $arParams['EDIT_URL'] ?>" method="post" enctype="multipart/form-data"
              class="yd-view--edit-form border border-secondary p-2 fn_yd_view_edit_form">
            <h2><span>Изменить файл</span><span class="yd-view-e-f--form-close fn_edit_form_close">×</span></h2>
            <div class="yd-view-e-f--form-content" id="yd-view-e-f--form-content">
                <span><span>Будет заменён файл:</span><span class="yd-view-e-f--old-file-name fn_old_file_name"></span></span>
                <label>
                    <span>Файл для замены</span>
                    <input type="hidden" name="edit_old_filename" value=""/>
                    <input type="file" name="<?= $arResult['edit_file_input_name'] ?>"/>
                </label>
                <label>
                    <input type="submit" title="Отправить"/>
                </label>
            </div>
        </form>
    </div>
    <h2>Список файлов</h2>
    <div class="yd-view--list">
        <?php if ($arResult['files']) { ?>
            <?php foreach ($arResult['files'] as $file) {
                $parts = explode('/', $file['app_path']);
                $filename = $parts[count($parts) - 1];
                ?>
                <div class="yd-view--list-item p-2">
                    <div class="yd-view--filename"><?= $filename ?></div>
                    <div class="yd-view--actions">
                        <div class="d-block yd-view--edit btn-link fn_li_edit" data-f="<?= $filename ?>">Редактировать
                        </div>
                        <a class="d-block yd-view--delete fn_li_delete" href="#" data-f="<?= $filename ?>"
                           data-delete-template="<?= $arParams['DELETE_URL'] ?>">Удалить</a>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

    </div>
    <script>
        const isE = function (e) {
            return e !== null && (typeof e) !== 'undefined';
        };
        if (!isE(window.ydisk_manager_fns)) {
            window.ydisk_manager_fns = {
                onListItemEditClick(evt) {
                    const root = document.querySelector('.fn_yd_view_root');
                    if (!isE(root)) {
                        return;
                    }
                    const e_trg = evt.target;
                    const v = e_trg.getAttribute('data-f');
                    root.querySelector('.fn_toggle_edit_visibility').checked = true;
                    root.querySelector('.fn_toggle_add_visibility').checked = false;
                    const inputOldFilename = root.querySelector('input[name="edit_old_filename"]');
                    inputOldFilename.value = v;
                    inputOldFilename.dispatchEvent(new Event('change'));

                    const f = e_trg.getAttribute('data-f');
                    const href_t = e_trg.getAttribute('data-delete-template');
                    const url = (new URL((new URL(document.baseURI)).origin + href_t))
                    url.searchParams.set('f', f);
                    e_trg.setAttribute('href', url.href);
                    e_trg.dispatchEvent(new Event('click'));
                },
                onOldFileNameChange(evt) {
                    const root = document.querySelector('.fn_yd_view_root');
                    if (!isE(root)) {
                        return;
                    }
                    root.querySelector('.fn_old_file_name').innerHTML = evt.target.value;
                },
                onFormEditClose(evt) {
                    const root = document.querySelector('.fn_yd_view_root');
                    if (!isE(root)) {
                        return;
                    }
                    root.querySelector('.fn_toggle_edit_visibility').checked = false;
                    root.querySelector('.fn_toggle_add_visibility').checked = true;
                    root.querySelector('.fn_old_file_name').innerHTML = "";
                    root.querySelector('.fn_yd_view_edit_form')?.setAttribute('action', "");
                    root.querySelector('input[name=\'edit_old_filename\']').value = '';
                },
                onDeleteItemClick(evt) {
                    const e_trg = evt.target;
                    const delete_attr = e_trg.getAttribute('data-delete-confirmed');
                    const is_ok_for_delete = delete_attr === '1';
                    if (is_ok_for_delete) {
                        return;
                    }
                    evt.preventDefault();
                    const confirmed = confirm('You really want to remove item?');
                    e_trg.setAttribute('data-delete-confirmed', confirmed ? '1' : '0');
                    if (confirmed) {
                        const f = e_trg.getAttribute('data-f');
                        const href_t = e_trg.getAttribute('data-delete-template');
                        const url = (new URL((new URL(document.baseURI)).origin + href_t))
                        url.searchParams.set('f', f);
                        e_trg.setAttribute('href', url.href);
                        e_trg.click();
                    }
                },
                init() {
                    const self = window.ydisk_manager_fns;
                    const root = document.querySelector('.fn_yd_view_root');
                    if (!isE(root)) {
                        return;
                    }
                    root.querySelectorAll('.fn_li_edit').forEach((v, i) => {
                        v.addEventListener('click', self.onListItemEditClick);
                    });
                    root.querySelectorAll('input[name="edit_old_filename"]').forEach((v, i) => {
                        v.addEventListener('change', self.onOldFileNameChange);
                    });
                    root.querySelectorAll('.fn_edit_form_close').forEach((v, i) => {
                        v.addEventListener('click', self.onFormEditClose);
                    });
                    root.querySelectorAll('.fn_li_delete').forEach((v, i) => {
                        v.addEventListener('click', self.onDeleteItemClick);
                    });
                },
                removeInits() {
                    const self = window.ydisk_manager_fns;
                    const root = document.querySelector('.fn_yd_view_root');
                    if (!isE(root)) {
                        return;
                    }
                    root.querySelectorAll('.fn_li_edit').forEach((v, i) => {
                        v.removeEventListener('click', self.onListItemEditClick);
                    });
                    root.querySelectorAll('input[name="edit_old_filename"]').forEach((v, i) => {
                        v.removeEventListener('change', self.onOldFileNameChange);
                    });
                    root.querySelectorAll('.fn_edit_form_close').forEach((v, i) => {
                        v.removeEventListener('click', self.onFormEditClose);
                    });
                }
            };
        }
        window.ydisk_manager_fns.removeInits();
        window.ydisk_manager_fns.init();
    </script>
    <style>
        .yd-view--add-form,
        .yd-view--edit-form {
            background: #F5F5F5;
        }

        .yd-view-a-f--form-content,
        .yd-view-e-f--form-content {
            display: flex;
            flex-direction: column;
            row-gap: 1rem;
        }

        .yd-view-e-f--form-content {
            display: flex;
            flex-direction: column;
        }

        .fn_toggle_add_visibility:not(:checked) ~ .yd-view--add-form {
            display: none;
        }

        .fn_toggle_edit_visibility:not(:checked) ~ .yd-view--edit-form {
            display: none;
        }

        .yd-view--edit-form {
            position: relative;
        }

        .yd-view-e-f--old-file-name {
            font-family: monospace;
            padding: .5rem;
            border: solid 1px #56565680;
        }

        .yd-view-e-f--form-close {
            position: absolute;
            top: 0;
            right: 0;
            content: "×";
            font-size: 32px;
            cursor: pointer;
            min-width: 32px;
            min-height: 32px;
            display: block;
        }

        .yd-view--list-item {
            display: flex;
            justify-content: space-between;
            background: #fff;
        }

        .yd-view--list-item:hover {
            background: #f5f5f5;
        }

        .yd-view--list-item:not(:last-child) {
            border-bottom: solid 1px var(--secondary);
        }

        .yd-view--actions {
            display: flex;
            column-gap: 1rem;
        }

        .yd-view--edit {
            cursor: pointer;
        }

        .yd-view--delete {
            color: var(--bs-danger);
        }

        .yd-view--delete:hover {
            color: #B92D3A;
        }

        .yd-view--popups:not(:empty) {
            margin-bottom: 1rem;
            row-gap: .5rem;
        }

        .yd-view-popups--item{
            padding: .5rem;
        }

        .yd-view-popups--item-error {
            border: solid 1px #DC3545;
            background: #e6b8bc;
        }

        .yd-view-popups--item-success {
            border: solid 1px #54dc35;
            background: #c0e6b8;
        }
    </style>
</div>
