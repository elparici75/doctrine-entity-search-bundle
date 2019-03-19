import Routing from '../../../../../../../../assets/js/router.js'
let ES = ES || {};
let bpfx = 'elparici_entity_search_';
ES.vars = {
    searchField: $('#' + bpfx + 'searchField'),
    flushBtn: $('#' + bpfx + 'flush'),
    Headers: $('#' + bpfx + 'header'),
    dataMapping: $('#' + bpfx + 'searchField').attr("data-entity-mapping"),
    results: $('#' + bpfx + 'results'),
    loader: $('#' + bpfx + 'loader'),
    reorder_parent_class: '.' + bpfx + 'reorders',
    reorder_class: bpfx + 'reorder',
    reorder_active_class: bpfx + 'active',
    choice_parent_class: '.' + bpfx + 'choices',
    choice_class: '.' + bpfx + 'choice',
    data_order_by: 'data-order-by'
};
ES.urls = {
    headers: function() {
        return Routing.generate('entity_search_get_headers', {
            'data_mapping': ES.vars.dataMapping,
            '_locale': $('#elparici-search-table').attr('data-locale')
        });
    },
    search: function() {
        return Routing.generate('entity_search_by_params', {
            'data_mapping': ES.vars.dataMapping,
            'terms': ES.vars.searchField.val(),
            '_locale': $('#elparici-search-table').attr('data-locale')
        });
    }
};
ES.searcher = {
    byParams: function(terms, page) {
        ES.vars.results.html('');
        ES.vars.results.css('opacity', 0);
        ES.vars.loader.css('opacity', 1);
        //console.log(ES.userParams.getQbParams());
        $.ajax({
            url: ES.urls.search(),
            type: 'GET',
            dataType: 'JSON',
            data: {
                getQbParams: ES.userParams.getQbParams(),
                page: page,

            },
        }).done(function(response) {
            ES.vars.loader.css('opacity', 0);
            ES.vars.results.html(response.results);
            ES.vars.results.animate({
                'opacity': 1
            }, 500);
            $('.paginator-nav').html(response.nav);
            ES.DOMready.base();
            ES.pager.ajaxNavigation();
        }).fail(function() {
            console.log("error");
        });
    },
    getHeaders: function() {
        ES.vars.Headers.html('');
        let request = new XMLHttpRequest();
        request.open('get', ES.urls.headers());
        request.onload = function() {
            if (request.status === 200) {
                ES.vars.Headers.html(JSON.parse(request.response));
                ES.DOMready.base();
                ES.searcher.byParams([], 1);
            }
        }
        request.onerror = function() {
            console.log('errpor');
        }
        request.send()
    }
};
ES.pager = {
    paginatorGetPageValue: function(obj) {
        let activeItemValue = parseInt($('.pagination').find('.active .page-link').html());

        if (obj.attr("rel") != null) {
            if (obj.attr("rel") == "next") {
                return activeItemValue + 1;
            }
            if (obj.attr("rel") == "prev") {
                return activeItemValue - 1;
            }
        } else {
            return parseInt(obj.html());
        }
    },
    ajaxNavigation: function() {
        $('.page-link').on('click', function(e) {
            e.preventDefault();
            ES.searcher.byParams(ES.vars.searchField.val(), ES.pager.paginatorGetPageValue($(this)));
        });
    }
};
ES.userParams = {
    getQbParams: function() {
        return {
            'choices': ES.userParams.getQbChoiceParams(),
            'orderBy': ES.userParams.getQbOrderByParams()
        };
    },
    getQbOrderByParams: function() {
        let OBP = {};
        $(ES.vars.reorder_parent_class).each(function() {
            OBP[$(this).attr('data-row')] = $(this).attr(ES.vars.data_order_by);
        })
  
        return OBP;
    },
    getQbChoiceParams: function() {
        let choices = {};
        $(ES.vars.choice_parent_class).each(function() {
            let a_choices = $(this).find(ES.vars.choice_class);
            if (a_choices.length > 0) {
                choices[a_choices.attr('data-row')] = [];
            }
            a_choices.each(function() {
                if ($(this).find('input').is(':checked')) {
                    choices[a_choices.attr('data-row')].push($(this).attr('data-value'));
                }
            })
        })
        return choices;
    }
}
ES.DOMready = {
    base: function() {
        // hotfix: here function off is to prevent from multiple requests 
        $('.updateTable').off('click').on('click', function() {
            if ($(this).hasClass(ES.vars.reorder_class)) {
                let parent = $(this).closest(ES.vars.reorder_parent_class);
                parent.find('.' + ES.vars.reorder_class).each(function() {
                    $(this).removeClass(ES.vars.reorder_active_class);
                });
                $(this).addClass(ES.vars.reorder_active_class);
                parent.attr(ES.vars.data_order_by, $(this).attr(ES.vars.data_order_by));
            }
            let timer;
            let delay = 600;
            // if ($(this).hasClass('dropdown-item')) {
            //  delay = 2000;
            // }
            window.clearTimeout(timer);
            timer = window.setTimeout(function() {
                ES.searcher.byParams(ES.vars.searchField.val(), 1);
            }, delay);
        })
    }
}
export default ES;