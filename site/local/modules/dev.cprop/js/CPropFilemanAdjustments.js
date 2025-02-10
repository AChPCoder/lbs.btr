(function () {
    const isE = function (v) {
        return v !== null && (typeof v) !== 'undefined';
    };
    if(isE(window.CPropFilemanAdjustments)) {
        return;
    }
    window.CPropFilemanAdjustments = {
        isDebug: false,
        debugLog(msg) {
            const self = window.CPropFilemanAdjustments;
            if (self.isDebug !== true) {
                return;
            }
            console.log(msg);
        },
        init() {
            const self = window.CPropFilemanAdjustments;
            self.debugLog('init loaded!');
            self.eventSubscribing();
        },
        getTextareaForNameReplacing (bx_editor) {
            const bx_editor_container = bx_editor.dom.cont;
            if(!isE(bx_editor_container)) {
                self.debugLog('Контейнер редактора не найден');
                return null;
            }
            const trg_textarea_row = bx_editor_container.closest('.fn_trg_cprop_customhtml__row');
            if(!isE(trg_textarea_row)) {
                self.debugLog('Строка с редактором для обработки не найдена');
                return null;
            }
            const trg_textarea = trg_textarea_row.querySelector('[data-trg-cprop-customhtml]');
            if(!isE(trg_textarea)) {
                self.debugLog('Инпут типа textarea с редактором HTML для обработки не найдена');
                return null;
            }
            return trg_textarea;
        },
        eventSubscribing() {
            const self = window.CPropFilemanAdjustments;
            BX.addCustomEvent('OnEditorInitedBefore', function(editor){
                self.debugLog(editor);
                self.debugLog('OnEditorInitedBefore called');
                setTimeout(()=>{
                    self.debugLog(self.getTextareaForNameReplacing(editor));
                },500);

            });

            BX.addCustomEvent('OnSubmit', function(){
                const self = window.CPropFilemanAdjustments;
                const editor = this; // Эта функция получает привязку к вызывающему событие объекту - то есть активному BXEditor из html-editor.js
                self.debugLog(editor);
                self.debugLog('OnSubmit called');
                const trg_textarea = self.getTextareaForNameReplacing(editor);
                if (!isE(trg_textarea)) {
                    self.debugLog('Target textarea for CProp name fixing not found!');
                    return;
                }
                const isProcedeed = trg_textarea.getAttribute('CPropProcedeed');
                const is_ok = !isE(isProcedeed) || isProcedeed !== '1';
                if(!is_ok) {
                    return;
                }
                let trg_textarea_name = trg_textarea.getAttribute('name');
                trg_textarea_name = trg_textarea_name.replaceAll('__0__', '[');
                trg_textarea_name = trg_textarea_name.replaceAll('__1__', ']');
                trg_textarea.setAttribute('name', trg_textarea_name);
                trg_textarea.setAttribute('CPropProcedeed', '1');
            });
        },
    };
    window.CPropFilemanAdjustments.init();
})()