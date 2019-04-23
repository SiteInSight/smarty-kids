(function () {

    /**
     * Заготовки коллбэков
     */

    /**
     * В коллбэках доступны:
     * form.extensionName
     * form.getPreparedPrefix(prefix)
     * form.getFieldValue(name, prefix = '__EXTENSION__')
     * form.setFieldValue(name, value, prefix = '__EXTENSION__')
     */

    return {
        'onLoad': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onLoad()');
        },

        'onShow': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onShow()');
        },

        'onHide': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onHide()');
        },

        'onFieldFull': function (form, $field) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onFieldFull() | field: ' + $field.attr('name'));

            if ($field.attr('name') === 'phone') { // если заполнено поле с телефоном, выводим номер телефона без маски
                console.log($field.inputmask('unmaskedvalue'));
            }
        },

        'onFieldEmpty': function (form, $field) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onFieldEmpty() | field: ' + $field.attr('name'));
        },

        'onFieldBlur': function (form, $field) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onFieldBlur() | field: ' + $field.attr('name') + ' | value: ' + $field.val());
        },

        'onStepBefore': function (form, currStepNum, $currStepWrap, nextStepNum, $nextStepWrap) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onStepBefore() | currStepNum:', currStepNum, ' | nextStepNum:', nextStepNum);
        },

        'onStepAfter': function (form, currStepNum, $currStepWrap, prevStepNum, $prevStepWrap) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onStepAfter() | currStepNum:', currStepNum, ' | prevStepNum:', prevStepNum);
        },

        'onSubmit': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onSubmit()');
        },

        'onError': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onError() | errors:', form.serverResponse.fields);
        },

        'onSuccess': function (form) {
            console.log('pform-id: ' + form.id + ' | ext: ' + form.extensionName + ' | callback: onSuccess()');
        }
    }
})();