var FWP = {};

(function($) {
    $(function() {

        var facet_count = 0;
        var template_count = 0;

        // Load
        $.post(ajaxurl, {
            action: 'facetwp_load'
        }, function(response) {
            $.each(response.facets, function(idx, obj) {
                var $this = $('.facets-hidden .facetwp-facet').clone();
                $this.attr('data-id', facet_count);
                $this.find('.facet-label').val(obj.label);
                $this.find('.facet-name').val(obj.name);
                $this.find('.facet-type').val(obj.type);

                // Facet load hook
                wp.hooks.doAction('facetwp/load/' + obj.type, $this, obj);

                $('.facetwp-facets').append($this);
                $('.facetwp-content-facets .facetwp-tabs ul').append('<li data-id="' + facet_count + '">' + obj.label + '</li>');
                facet_count++;

                // Trigger conditional toggles
                $this.find('.facet-type').trigger('change');
            });

            $.each(response.templates, function(idx, obj) {
                var $this = $('.templates-hidden .facetwp-template').clone();
                $this.attr('data-id', template_count);
                $this.find('.template-label').val(obj.label);
                $this.find('.template-name').val(obj.name);
                $this.find('.template-query').val(obj.query);
                $this.find('.template-template').val(obj.template);
                $('.facetwp-templates').append($this);
                $('.facetwp-content-templates .facetwp-tabs ul').append('<li data-id="' + template_count + '">' + obj.label + '</li>');
                template_count++;
            });

            $.each(response.settings, function(key, val) {
                var $this = $('.facetwp-setting[data-name=' + key + ']');
                $this.val(val);
            });

            // Set the UI elements
            $('.facetwp-content-facets .facetwp-tabs li:first').click();
            $('.facetwp-content-templates .facetwp-tabs li:first').click();

            // Hide the preloader
            $('.facetwp-loading').hide();
            $('.facetwp-header-nav a:first').click();
        }, 'json');


        // Is the indexer running?
        FWP.get_progress = function() {
            $.post(ajaxurl, {
                'action': 'facetwp_heartbeat'
            }, function(response) {

                // Remove extra spaces added by some themes
                var response = response.trim();

                if ('-1' == response) {
                    $('.facetwp-response').html('Indexing complete.');
                }
                else if ($.isNumeric(response)) {
                    $('.facetwp-response').html('Indexing... ' + response + '%');
                    $('.facetwp-response').show();
                    setTimeout(function() {
                        FWP.get_progress();
                    }, 5000);
                }
                else {
                    $('.facetwp-response').html(response);
                }
            });
        }
        FWP.get_progress();


        // Topnav
        $(document).on('click', '.facetwp-nav-tab', function() {
            var tab = $(this).attr('rel');
            $('.facetwp-nav-tab').removeClass('active');
            $(this).addClass('active');
            $('.facetwp-content').removeClass('active');
            $('.facetwp-content-' + tab).addClass('active');
        });


        // Conditionals based on facet type
        $(document).on('change', '.facet-type', function() {
            var val = $(this).val();
            var $facet = $(this).closest('.facetwp-facet');
            $facet.find('.facetwp-show').show();
            $facet.find('.facetwp-conditional').hide();
            $facet.find('.facetwp-conditional.type-' + val).show();
            wp.hooks.doAction('facetwp/change/' + val, $(this));
        });


        // Conditionals based on facet source
        $(document).on('change', '.facet-source', function() {
            var val = $(this).val();
            var $facet = $(this).closest('.facetwp-facet');
            var facet_type = $facet.find('.facet-type').val();
            var display = (-1 < val.indexOf('tax/')) ? 'table-row' : 'none';
            if ('checkboxes' == facet_type) {
                $facet.find('.type-checkboxes .facet-parent-term').closest('tr').css({ 'display' : display });
                $facet.find('.type-checkboxes .facet-hierarchical').closest('tr').css({ 'display' : display });
            }
            else if ('dropdown' == facet_type) {
                $facet.find('.type-dropdown .facet-parent-term').closest('tr').css({ 'display' : display });
            }
        });


        // "Add Facet" button
        $(document).on('click', '.add-facet', function() {
            var html = $('.facets-hidden').html();
            $('.facetwp-facets').append(html);
            $('.facetwp-facets .facetwp-facet:last').attr('data-id', facet_count);
            $('.facetwp-content-facets .facetwp-tabs ul').append('<li data-id="' + facet_count + '">New Facet</li>');
            $('.facetwp-content-facets .facetwp-tabs li:last').click();

            // Trigger conditional toggles
            $('.facetwp-facets .facetwp-facet:last .facet-type').trigger('change');
            $('.facetwp-facets .facetwp-facet:last .facet-source').trigger('change');
            facet_count++;
        });


        // "Add Template" button
        $(document).on('click', '.add-template', function() {
            var html = $('.templates-hidden').html();
            $('.facetwp-templates').append(html);
            $('.facetwp-templates .facetwp-template:last').attr('data-id', template_count);
            $('.facetwp-content-templates .facetwp-tabs ul').append('<li data-id="' + template_count + '">New Template</li>');
            $('.facetwp-content-templates .facetwp-tabs li:last').click();
            template_count++;
        });


        // "Remove Facet" button
        $(document).on('click', '.remove-facet', function() {
            if (confirm('You are about to delete this facet. Continue?')) {
                var id = $(this).closest('.facetwp-facet').attr('data-id');
                $(this).closest('.facetwp-facet').remove();
                $('.facetwp-content-facets .facetwp-tabs li[data-id=' + id + ']').remove();
                $('.facetwp-content-facets .facetwp-tabs li:first').click();
            }
        });


        // "Remove Template" button
        $(document).on('click', '.remove-template', function() {
            if (confirm('You are about to delete this template. Continue?')) {
                var id = $(this).closest('.facetwp-template').attr('data-id');
                $(this).closest('.facetwp-template').remove();
                $('.facetwp-content-templates .facetwp-tabs li[data-id=' + id + ']').remove();
                $('.facetwp-content-templates .facetwp-tabs li:first').click();
            }
        });


        // Sidebar link click
        $(document).on('click', '.facetwp-tabs li', function() {
            var id = $(this).attr('data-id');
            var $parent = $(this).closest('.facetwp-content');
            var type = $parent.hasClass('facetwp-content-facets') ? 'facet' : 'template';
            $(this).siblings('li').removeClass('active');
            $(this).addClass('active');
            $('.facetwp-' + type).hide();
            $('.facetwp-' + type + '[data-id=' + id + ']').show();

            // Trigger conditional settings
            if ('facet' == type) {
                $('.facetwp-' + type + '[data-id=' + id + ']').find('.facet-source').trigger('change');
            }

            // Make sure the content area is tall enough
            var nav_height = $(this).closest('.facetwp-tabs').height();
            var content_height = $('.facetwp-' + type + '[data-id=' + id + ']').height();
            if (content_height < nav_height) {
                $('.facetwp-' + type + '[data-id=' + id + ']').height(nav_height - 40);
            }
        });


        // Change the sidebar link label
        $(document).on('keyup', '.facet-label, .template-label', function() {
            var val = $(this).val();
            var $tab = $(this).closest('.facetwp-content').find('.facetwp-tabs li.active');
            $tab.html(val);

            val = $.trim(val).toLowerCase();
            val = val.replace(/[^\w- ]/g, ''); // strip invalid characters
            val = val.replace(/[- ]/g, '_'); // replace space and hyphen with underscore
            val = val.replace(/[_]{2,}/g, '_'); // strip consecutive underscores
            $(this).siblings('.facet-name').val(val);
            $(this).siblings('.template-name').val(val);
        });


        // Save
        $(document).on('click', '.facetwp-save', function() {
            $('.facetwp-response').html('Saving...');
            $('.facetwp-response').show();

            var data = {
                'facets': [],
                'templates': [],
                'settings': {}
            };

            $('.facetwp-facets .facetwp-facet').each(function() {
                var $this = $(this);
                var type = $this.find('.facet-type').val();

                var obj = {
                    'label': $this.find('.facet-label').val(),
                    'name': $this.find('.facet-name').val(),
                    'type': $this.find('.facet-type').val()
                };

                // Facet save hook
                obj = wp.hooks.applyFilters('facetwp/save/' + obj.type, $this, obj);
                data.facets.push(obj);
            });

            $('.facetwp-templates .facetwp-template').each(function() {
                var $this = $(this);
                data.templates.push({
                    'label': $this.find('.template-label').val(),
                    'name': $this.find('.template-name').val(),
                    'query': $this.find('.template-query').val(),
                    'template': $this.find('.template-template').val()
                });
            });

            $('.facetwp-content-settings .facetwp-setting').each(function() {
                var name = $(this).attr('data-name');
                data.settings[name] = $(this).val();
            });

            $.post(ajaxurl, {
                'action': 'facetwp_save',
                'data': JSON.stringify(data)
            }, function(response) {
                $('.facetwp-response').html(response);
            });
        });


        // Export
        $(document).on('click', '.export-submit', function() {
                $('.export-code').show();
                $('.export-code').val('');
                $.post(ajaxurl, {
                    action: 'facetwp_migrate',
                    action_type: 'export',
                    items: $('.export-items').val()
                },
                function(response) {
                    $('.export-code').val(response);
                });
        });


        // Import
        $(document).on('click', '.import-submit', function() {
            $('.facetwp-response').show();
            $('.facetwp-response').html('Importing...');
            $.post(ajaxurl, {
                action: 'facetwp_migrate',
                action_type: 'import',
                import_code: $('.import-code').val(),
                overwrite: $('.import-overwrite').is(':checked') ? 1 : 0
            },
            function(response) {
                $('.facetwp-response').html(response);
            });
        });


        // Rebuild index
        $(document).on('click', '.facetwp-rebuild', function() {
            $.post(ajaxurl, { action: 'facetwp_rebuild_index' });
            $('.facetwp-response').html('Indexing...');
            $('.facetwp-response').show();
            setTimeout(function() {
                FWP.get_progress();
            }, 5000);
        });


        // Activation
        $(document).on('click', '.facetwp-activate', function() {
            $('.facetwp-activation-status').html('Activating...');
            $.post(ajaxurl, {
                action: 'facetwp_license',
                license: $('.facetwp-license').val()
            }, function(response) {
                $('.facetwp-activation-status').html(response.message);
            }, 'json');
        });


        // Tooltips
        $(document).on('mouseover', '.facetwp-tooltip', function() {
            if ('undefined' == typeof $(this).data('powertip')) {
                var content = $(this).find('.facetwp-tooltip-content').html();
                $(this).data('powertip', content);
                $(this).powerTip({
                    placement: 'e',
                    mouseOnToPopup: true
                });
                $.powerTip.show(this);
            }
        });
    });
})(jQuery);