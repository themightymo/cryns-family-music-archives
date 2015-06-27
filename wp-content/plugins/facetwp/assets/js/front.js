var FWP = {
    'facets': {},
    'template': null,
    'settings': {},
    'auto_refresh': true,
    'soft_refresh': false,
    'static_facet': null,
    'loaded': false,
    'jqXHR': false,
    'extras': {},
    'paged': 1
};


(function($) {

    FWP.serialize = function(obj, prefix) {
        var str = [];
        var prefix = ('undefined' != typeof prefix) ? prefix : '';
        for (var p in obj) {
            if ('' != obj[p]) {
                str.push(prefix + encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
            }
        }
        return str.join('&');
    }


    // Refresh on each facet interaction?
    FWP.autoload = function() {
        if (FWP.auto_refresh) {
            FWP.refresh();
        }
    }


    FWP.refresh = function() {
        FWP.is_refresh = true;

        // Load facet DOM values
        FWP.parse_facets();

        // A hash change triggers window.onpopstate()
        if (FWP.loaded && ! FWP.is_popstate) {
            FWP.set_hash();
        }
        else {
            FWP.load_from_hash();
        }

        // Fire a notification event
        $(document).trigger('facetwp-refresh');

        // Send request to server
        FWP.fetch_data();

        // Cleanup
        FWP.paged = 1;
        FWP.static_facet = null;
        FWP.soft_refresh = false;
        FWP.is_refresh = false;
    }


    // Prevent Safari from triggering popstate on pageload
    $(window).on('load', function() {
        setTimeout(function() {
            $(window).on('popstate', function() {
                if (FWP.loaded && ! FWP.is_refresh) {
                    FWP.is_popstate = true;
                    FWP.refresh();
                    FWP.is_popstate = false;
                }
            });
        }, 0);
    });


    FWP.parse_facets = function() {
        FWP.facets = {};

        $('.facetwp-facet').each(function() {
            var $this = $(this);
            var facet_name = $this.attr('data-name');
            var facet_type = $this.attr('data-type');

            // Plugin hook
            wp.hooks.doAction('facetwp/refresh/' + facet_type, $this, facet_name);

            // Add pagination to the URL hash
            if (1 < FWP.paged) {
                FWP.facets['paged'] = FWP.paged;
            }

            // Add sorting to the URL hash
            if (FWP.extras.sort && 'default' != FWP.extras.sort) {
                FWP.facets['sort'] = FWP.extras.sort;
            }

            // Allow for custom loading handler
            if (false === FWP.soft_refresh && facet_name != FWP.static_facet) {
                if ('function' != typeof FWP.loading_handler) {
                    $this.html('<div class="facetwp-loading"></div>');
                }
                else {
                    FWP.loading_handler({
                        'element': $this,
                        'facet_name': facet_name,
                        'facet_type': facet_type
                    });
                }
            }
        });
    }


    FWP.build_query_string = function() {
        var query_string = '';

        if ('get' == FWP.permalink_type) {
            // Non-FacetWP URL variables
            var hash = [];
            var get_str = window.location.search.replace('?', '').split('&');
            $.each(get_str, function(idx, val) {
                var param_name = val.split('=')[0];
                if ('fwp' != param_name.substr(0, 3)) {
                    hash.push(val);
                }
            });
            hash = hash.join('&');

            // FacetWP URL variables
            var fwp_vars = FWP.serialize(FWP.facets, 'fwp_');

            if ('' != hash) {
                query_string += hash;
            }
            if ('' != fwp_vars) {
                query_string += ('' != hash ? '&' : '') + fwp_vars;
            }
        }
        else {
            query_string = FWP.serialize(FWP.facets);
        }

        return query_string;
    }


    FWP.set_hash = function() {
        var query_string = FWP.build_query_string();

        if ('get' == FWP.permalink_type) {
            if ('' != query_string) {
                query_string = '?' + query_string;
            }
            history.pushState(null, null, window.location.pathname + query_string);
        }
        else {
            window.location.hash = '!/' + query_string;
        }
    }


    FWP.load_from_hash = function() {
        if ('get' == FWP.permalink_type) {
            var hash = [];
            var get_str = window.location.search.replace('?', '').split('&');
            $.each(get_str, function(idx, val) {
                var param_name = val.split('=')[0];
                if ('fwp' == param_name.substr(0, 3)) {
                    hash.push(val.replace('fwp_', ''));
                }
            });
            hash = hash.join('&');
        }
        else {
            var hash = window.location.hash.replace('#!/', '');
        }

        // Reset facet values
        $.each(FWP.facets, function(f) {
            FWP.facets[f] = [];
        });

        FWP.paged = 1;
        FWP.extras.sort = 'default';

        if ('' != hash) {
            hash = hash.split('&');
            $.each(hash, function(idx, val) {
                var pieces = val.split('=');

                if ('paged' == pieces[0]) {
                    FWP.paged = pieces[1];
                }
                else if ('sort' == pieces[0]) {
                    FWP.extras.sort = pieces[1];
                }
                else if ('' != pieces[1]) {
                    FWP.facets[pieces[0]] = decodeURIComponent(pieces[1]).split(',');
                }
            });
        }
    }


    FWP.fetch_data = function() {
        // Abort pending requests
        if (FWP.jqXHR && FWP.jqXHR.readyState !== 4) {
            FWP.jqXHR.abort();
        }

        FWP.ajaxurl = ('wp' == FWP.template) ? document.URL : ajaxurl;

        // dataType is "text" for better JSON error handling
        FWP.jqXHR = $.post(FWP.ajaxurl, {
            'action': 'facetwp_refresh',
            'data': {
                'facets': JSON.stringify(FWP.facets),
                'static_facet': FWP.static_facet,
                'http_params': FWP_HTTP,
                'template': FWP.template,
                'extras': FWP.extras,
                'soft_refresh': FWP.soft_refresh ? 1 : 0,
                'paged': FWP.paged
            }
        }, function(response) {

            try {
                var json_object = $.parseJSON(response);
                FWP.render(json_object);
            }
            catch(e) {
                var pos = response.indexOf('{"facets');
                if (-1 < pos) {
                    var error = response.substr(0, pos);
                    var json_object = $.parseJSON(response.substr(pos));
                    FWP.render(json_object);

                    $('.facetwp-template').prepend(error);
                }
                else {
                    $('.facetwp-template').text(response);
                }
            }

            // Fire a notification event
            $(document).trigger('facetwp-loaded');
        }, 'text');
    }


    FWP.render = function(response) {
        // Populate each facet box
        $.each(response.facets, function(name, val) {
            $('.facetwp-facet-' + name).html(val);
        });

        // Populate the template
        if ('wp' == FWP.template) {
            var inject = $(response.template).find('.facetwp-template').html();
        }
        else {
            var inject = response.template;
        }

        if (! wp.hooks.applyFilters('facetwp/template_html', false, { 'response': response })) {
            $('.facetwp-template').html(inject);
        }

        // Populate the counts
        $('.facetwp-counts').html(response.counts);

        // Populate the selections
        $('.facetwp-selections').html(response.selections);

        // Populate the sort box
        if ('undefined' != typeof response.sort) {
            $('.facetwp-sort').html(response.sort);
            $('.facetwp-sort-select').val(FWP.extras.sort);
        }

        // Populate the pager
        $('.facetwp-pager').html(response.pager);

        // Populate the settings object (iterate to preserve static facet settings)
        $.each(response.settings, function(key, val) {
            FWP.settings[key] = val;
        });
    }


    FWP.reset = function() {
        FWP.parse_facets();
        $.each(FWP.facets, function(f) {
            FWP.facets[f] = [];
        });
        FWP.set_hash();
        FWP.fetch_data();
    }


    // Event handlers
    $(function() {

        if (0 < $('.facetwp-sort').length) {
            FWP.extras.sort = 'default';
        }

        if (0 < $('.facetwp-pager').length) {
            FWP.extras.pager = true;
        }

        if (0 < $('.facetwp-counts').length) {
            FWP.extras.counts = true;
        }

        if (0 < $('.facetwp-selections').length) {
            FWP.extras.selections = true;
        }

        // Make sure there's a template
        if (1 > $('.facetwp-template').length) {
            return;
        }
        else {
            var $div = $('.facetwp-template:first');
            FWP.template = $div.is('[data-name]') ? $div.attr('data-name') : 'wp';
        }

        wp.hooks.doAction('facetwp/ready');

        // Click on a selection item
        $(document).on('click', '.facetwp-selections li', function() {
            var $this = $(this);
            var facet_name = $this.attr('data-facet');
            var facet_value = $this.attr('data-value');
            var facet_type = $('.facetwp-facet-' + facet_name).attr('data-type');

            // Load the DOM values
            FWP.parse_facets();

            // Update the "FWP.facets" object
            if ('string' == typeof FWP.facets[facet_name]) {
                FWP.facets[facet_name] = '';
            }
            else if ('date_range' == facet_type) {
                FWP.facets[facet_name] = [];
            }
            else {
                var array = FWP.facets[facet_name];
                var index = array.indexOf(facet_value);
                if (-1 < index) {
                    array.splice(index, 1);
                    FWP.facets[facet_name] = array;
                }
                else {
                    FWP.facets[facet_name] = [];
                }
            }

            // Update the URL hash
            FWP.set_hash();

            // Run the AJAX request
            FWP.fetch_data();
        });

        // Pagination
        $(document).on('click', '.facetwp-page', function() {
            $('.facetwp-page').removeClass('active');
            $(this).addClass('active');

            FWP.paged = $(this).attr('data-page');
            FWP.soft_refresh = true;
            FWP.refresh();
        });

        // Sorting
        $(document).on('change', '.facetwp-sort-select', function() {
            FWP.extras.sort = $(this).val();
            FWP.soft_refresh = true;
            FWP.refresh();
        });

        FWP.refresh();
        FWP.loaded = true;
    });
})(jQuery);