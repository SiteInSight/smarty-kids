(function () {
    /**
     * В коллбэках доступны:
     * form.extensionName
     * form.getPreparedPrefix(prefix)
     * form.getFieldValue(name, prefix = '__EXTENSION__')
     * form.setFieldValue(name, value, prefix = '__EXTENSION__')
     */
    return {
        'onSubmit': function (form) {
            if (form.$wrap.find('[name="' + form.getPreparedPrefix('__EXTENSION__') + 'position"]').length){
                form.setFieldValue('position', form.$wrap.data('pform-position'));
            }
            if(form.$wrap.find('[name="' + form.getPreparedPrefix('__EXTENSION__') + 'url"]').length){
                form.setFieldValue('url', window.location.href);
            }
        },
    }
})();