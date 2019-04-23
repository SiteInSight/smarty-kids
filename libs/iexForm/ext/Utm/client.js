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
            form.setFieldValue('getstring', window.location.search.substr(1));
        }
    }
})();