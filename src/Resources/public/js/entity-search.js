import ES from './components/utils.js';
$(function() {
    if (ES.vars.searchField.length) {
        var timer;
        var delay = 600;
        ES.searcher.getHeaders();
        ES.vars.searchField.on('input propertychange', function() {
            window.clearTimeout(timer);
            timer = window.setTimeout(function() {
                ES.searcher.byParams(ES.vars.searchField.val(), 1);
            }, delay);
        });
        ES.vars.flushBtn.on('click', function() {
            if (ES.vars.searchField.val() != '') {
                ES.vars.searchField.val('');
                window.clearTimeout(timer);
                timer = window.setTimeout(function() {
                    ES.searcher.byParams(ES.vars.searchField.val(), 1);
                }, delay);
            }
        })
    }
});