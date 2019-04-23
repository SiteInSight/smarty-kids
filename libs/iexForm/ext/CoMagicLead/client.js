(function () {

    /**
     * В коллбэках доступны:
     * form.extensionName
     * form.getPreparedPrefix(prefix)
     * form.getFieldValue(name, prefix = '__EXTENSION__')
     * form.setFieldValue(name, value, prefix = '__EXTENSION__')
     */

    return {
        'onSubmit': function (form){
            if (window.Comagic !== undefined) {
                form.setFieldValue('credentials', JSON.stringify(Comagic.getCredentials()));
            }
        },
    }
})();