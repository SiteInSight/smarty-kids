(function () {

    /**
     * В коллбэках доступны:
     * form.extensionName
     * form.getPreparedPrefix(prefix)
     * form.getFieldValue(name, prefix = '__EXTENSION__')
     * form.setFieldValue(name, value, prefix = '__EXTENSION__')
     */

    // Флаг переключащий с ga на gtag, устанавливается в конфиг-файле
    let enable_gtag = false;

    function google_gtag_send(formid, event) {
        gtag('event', 'send', {'event_category': formid, 'event_action': event});
        console.log('[GTag]: ', formid + ', ' + event);
    }

    function google_analytics_send(formid, event) {
        ga('send', 'event', formid, event);
        console.log('[GA]: ', formid + ', ' + event);
    }

    function yandex_metrika_send(formid, event) {
        if(typeof Ya.Metrika2 !== 'undefined') {
            let ya_counters_v2 = Ya.Metrika2.counters();
            for (let i = 0; i < ya_counters_v2.length; i++) {
                let ya_id = ya_counters_v2[i].id;
                let ya_obj = window['yaCounter' + ya_id];
                if (typeof ya_obj !== 'undefined') {
                    let yandex_goal = formid + '_' + event;
                    ya_obj.reachGoal(yandex_goal);
                    console.log('[YM2_' + ya_id + ']: ', yandex_goal);
                }
            }
        } else if(typeof Ya.Metrika !== 'undefined'){
            let ya_counters = Ya.Metrika.counters();
            for (let i = 0; i < ya_counters.length; i++) {
                let ya_id = ya_counters[i].id;
                let ya_obj = window['yaCounter' + ya_id];
                if (typeof ya_obj !== 'undefined') {
                    let yandex_goal = formid + '_' + event;
                    ya_obj.reachGoal(yandex_goal);
                    console.log('[YM_' + ya_id + ']: ', yandex_goal);
                }
            }
        } else {
            console.error('[YM] Счетчики Яндекс.Метрики не обнаружены');
        }


    }

    function reachGoal(formid, event) {
        console.groupCollapsed('[IexForm-Goal]: ' + formid + ' ' + event);
        if(enable_gtag){
            if (typeof gtag !== 'undefined') {
                google_gtag_send(formid, event);
            }
        } else {
            if (typeof ga !== 'undefined') {
                google_analytics_send(formid, event);
            }
        }
        if (typeof Ya !== 'undefined') {
            yandex_metrika_send(formid, event);
        }
        console.groupEnd();
    }

    return {
        'onLoad': function (form) {
            if (form.getFieldValue('enable_gtag') === 'true') {
                enable_gtag = true;
            }
        },
        'onShow': function (form) {
            if(!form.inline) {
                reachGoal(form.id, 'show');
            }
        },
        'onHide': function (form) {
            reachGoal(form.id, 'hide');
        },
        'onSubmit': function (form) {
            if(!form.multistep){
                reachGoal(form.id, 'submit');
            } else {
                reachGoal(form.id, 'submitstep');
                reachGoal(form.id, 'submitstep' + form.getFieldValue('iexstep', ''));
            }
        },
        'onSuccess': function (form) {
            reachGoal(form.id, 'success');
        }
    }
})();