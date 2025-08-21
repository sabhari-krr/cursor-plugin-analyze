if (typeof wlpr_jquery == 'undefined') {
    wlpr_jquery = jQuery.noConflict();
}
wlpr = window.wlpr || {};
wlpr_jquery(document).ready(function () {
    // Configure alertify
    alertify.set('notifier', 'position', 'top-right');
    
    // Initialize form handlers
    wlpr.initFormHandlers();
});

(function () {
    wlpr.initFormHandlers = function() {
        var $ = wlpr_jquery;
        
        // Handle Is Recurring checkbox
        $('#is_recurring').on('change', function() {
            if ($(this).is(':checked')) {
                $('#one_time_date_row').hide();
                $('#recurring_options').show();
                // Reset to default week selection and show week options
                $('#freq_week').prop('checked', true);
                $('.wlpr-frequency-options').hide();
                $('#week_options').show();
            } else {
                $('#one_time_date_row').show();
                $('#recurring_options').hide();
                $('.wlpr-frequency-options').hide();
            }
        });

        // Handle Frequency Type radio buttons
        $('input[name="frequency_type"]').on('change', function() {
            // Hide all frequency options first
            $('.wlpr-frequency-options').hide();
            
            // Show the selected frequency option
            var selectedType = $(this).val();
            $('#' + selectedType + '_options').show();
        });

        // Initialize form state on page load
        if ($('#is_recurring').is(':checked')) {
            $('#recurring_options').show();
            $('#one_time_date_row').hide();
        } else {
            $('#one_time_date_row').show();
            $('#recurring_options').hide();
        }

        // Always check for default selected frequency type and show its options
        // Since week is checked by default, show week options immediately
        var selectedFreq = $('input[name="frequency_type"]:checked').val();
        if (selectedFreq) {
            $('#' + selectedFreq + '_options').show();
        }

        // Initialize accordion functionality
        wlpr.initAccordion();
        
        // Initialize report selection counter
        wlpr.updateReportSelectionCounter();

        // Initialize user form handlers
        wlpr.initUserFormHandlers();
        
        // Initialize search functionality
        wlpr.initSearch();
    };

    // Search functionality with debouncing
    wlpr.initSearch = function() {
        const searchInput = wlpr_jquery('#wlpr-rule-search');
        const clearButton = wlpr_jquery('#wlpr-clear-search');
        const rulesContainer = wlpr_jquery('#wlpr-rules-table-container');
        let searchTimeout;

        if (searchInput.length === 0) return;
        
        // Check URL for existing search term and set it in the search box
        const urlParams = new URLSearchParams(window.location.search);
        const existingSearchTerm = urlParams.get('search');
        if (existingSearchTerm) {
            searchInput.val(existingSearchTerm);
            clearButton.show();
        }

        // Debounced search function
        function performSearch() {
            const searchTerm = searchInput.val().trim();
            
            if (searchTerm === '') {
                // Clear search - reload page to show all rules
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('search');
                urlParams.delete('paged'); // Reset to first page
                window.location.href = window.location.pathname + '?' + urlParams.toString();
                return;
            }

            // Show clear button
            clearButton.show();

            // Show loading state
            rulesContainer.addClass('wlpr-loading');
            
            // Add loading indicator
            if (rulesContainer.find('.wlpr-loading-indicator').length === 0) {
                rulesContainer.append('<div class="wlpr-loading-indicator"><span class="spinner is-active"></span></div>');
            }

            // Get current per_page value
            const perPage = wlpr_jquery('#wlpr-per-page').val() || 5;

            // Update URL with search term without reloading the page
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('search', searchTerm);
            urlParams.set('per_page', perPage);
            urlParams.delete('paged'); // Reset to first page when searching
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.history.pushState({ path: newUrl }, '', newUrl);

            // Make AJAX request to search
            wlpr_jquery.ajax({
                url: wlpr_localize_data.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wlpr_search_rules',
                    search_term: searchTerm,
                    page: 1, // Always start from page 1 when searching
                    per_page: perPage,
                    wlpr_nonce: wlpr_localize_data.search_nonce
                },
                success: function(response) {
                    if (response && response.success) {
                        // Update the table content
                        rulesContainer.html(response.data.table_html);
                        
                        // Update pagination if it exists
                        if (response.data.pagination_html) {
                            wlpr_jquery('.wlpr-pagination-controls').replaceWith(response.data.pagination_html);
                        }
                        
                        // Update "go to page" input max value if pagination data exists
                        if (response.data.pagination && response.data.pagination.total_pages) {
                            wlpr_jquery('#wlpr-goto-page-input').attr('max', response.data.pagination.total_pages);
                        }
                        
                        // Reinitialize delete handlers for new content
                        wlpr.initDeleteHandlers();
                        
                        // Reinitialize pagination handlers for new content
                        wlpr.initPaginationLinks();
                    } else {
                        alertify.error(response && response.data && response.data.message ? response.data.message : 'Search failed');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Search failed:', textStatus, errorThrown);
                    alertify.error('An error occurred while searching. Please try again.');
                },
                complete: function() {
                    // Remove loading state
                    rulesContainer.removeClass('wlpr-loading');
                    rulesContainer.find('.wlpr-loading-indicator').remove();
                }
            });
        }

        // Search input event with debouncing
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300); // 300ms debounce
        });

        // Clear search
        clearButton.on('click', function() {
            searchInput.val('');
            clearButton.hide();
            // Reload page to show all rules, but keep other parameters
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('search');
            urlParams.delete('paged'); // Reset to first page
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });

        // Keyboard shortcuts
        searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.val('');
                clearButton.hide();
                // Reload page to show all rules, but keep other parameters
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('search');
                urlParams.delete('paged'); // Reset to first page
                window.location.href = window.location.pathname + '?' + urlParams.toString();
            }
        });
    };

    // Accordion functionality
    wlpr.initAccordion = function() {
        var $ = wlpr_jquery;
        
        $('#report-accordion-header').on('click', function() {
            var header = $(this);
            var content = $('#report-accordion-content');
            
            // Toggle active class
            header.toggleClass('active');
            content.toggleClass('active');
        });
    };

    // Update report selection counter
    wlpr.updateReportSelectionCounter = function() {
        var $ = wlpr_jquery;
        
        // Update counter when checkboxes change
        $('input[name="report_includes[]"]').on('change', function() {
            var selectedCount = $('input[name="report_includes[]"]:checked').length;
            $('.wlpr-selected-count').text('(' + selectedCount + ' selected)');
        });
    };

    // User form handlers
    wlpr.initUserFormHandlers = function() {
        var $ = wlpr_jquery;
        
        // Handle User Is Recurring checkbox
        $('#user_is_recurring').on('change', function() {
            if ($(this).is(':checked')) {
                $('#user_one_time_date_row').hide();
                $('#user_recurring_options').show();
                // Reset to default week selection and show week options
                $('#user_freq_week').prop('checked', true);
                $('.wlpr-frequency-options').hide();
                $('#user_week_options').show();
            } else {
                $('#user_one_time_date_row').show();
                $('#user_recurring_options').hide();
                $('.wlpr-frequency-options').hide();
            }
        });

        // Handle User Frequency Type radio buttons
        $('input[name="user_frequency_type"]').on('change', function() {
            // Hide all frequency options first
            $('.wlpr-frequency-options').hide();
            
            // Show the selected frequency option
            var selectedType = $(this).val();
            $('#user_' + selectedType + '_options').show();
        });

        // Initialize user form state on page load
        if ($('#user_is_recurring').is(':checked')) {
            $('#user_recurring_options').show();
            $('#user_one_time_date_row').hide();
        } else {
            $('#user_one_time_date_row').show();
            $('#user_recurring_options').hide();
        }

        // Check for default selected frequency type in user form
        var selectedUserFreq = $('input[name="user_frequency_type"]:checked').val();
        if (selectedUserFreq) {
            $('#user_' + selectedUserFreq + '_options').show();
        }

        // Initialize user report accordion
        wlpr.initUserReportAccordion();
        
        // Initialize form submissions
        wlpr.initFormSubmissions();
        
        // Initialize delete functionality
        wlpr.initDeleteHandlers();
        
        // Initialize pagination functions
        wlpr.initPerPageSelector();
        wlpr.initPaginationDots();
        wlpr.initGotoPage();
        
        // Initialize pagination links to preserve search
        wlpr.initPaginationLinks();
        
        // Initialize bulk actions
        wlpr.initBulkActions();
    };

    wlpr.initPerPageSelector = function() {
        wlpr_jquery(document).on('change', '#wlpr-per-page', function(e) {
            e.preventDefault();
            var perPage = wlpr_jquery(this).val();
            
            // Get current URL parameters
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('per_page', perPage);
            urlParams.delete('paged'); // Reset to first page
            
            // Preserve search term if it exists
            var searchTerm = wlpr_jquery('#wlpr-rule-search').val().trim();
            if (searchTerm) {
                urlParams.set('search', searchTerm);
            }
            
            // Redirect with new parameters
            var newUrl = window.location.pathname + '?' + urlParams.toString();
            window.location.href = newUrl;
        });
    };
    
    // Standard pagination - no complex ellipsis functionality needed
    wlpr.initPaginationDots = function() {
        // Simple ellipsis - no click functionality, just visual indicator
        wlpr_jquery('.wlpr-pagination-dots').css('cursor', 'default');
    };
    
    wlpr.initGotoPage = function() {
        wlpr_jquery(document).on('click', '#wlpr-goto-page-btn', function() {
            var page = parseInt(wlpr_jquery('#wlpr-goto-page-input').val());
            var maxPage = parseInt(wlpr_jquery('#wlpr-goto-page-input').attr('max'));
            
            if (page && page >= 1 && page <= maxPage) {
                var urlParams = new URLSearchParams(window.location.search);
                urlParams.set('paged', page);
                
                // Preserve search term if it exists
                var searchTerm = wlpr_jquery('#wlpr-rule-search').val().trim();
                if (searchTerm) {
                    urlParams.set('search', searchTerm);
                }
                
                var newUrl = window.location.pathname + '?' + urlParams.toString();
                window.location.href = newUrl;
            } else {
                alertify.error('Please enter a valid page number between 1 and ' + maxPage);
            }
        });
        
        // Allow Enter key to trigger Go button
        wlpr_jquery(document).on('keypress', '#wlpr-goto-page-input', function(e) {
            if (e.which === 13) { // Enter key
                wlpr_jquery('#wlpr-goto-page-btn').click();
            }
        });
    };
    
    // Initialize pagination links to preserve search term
    wlpr.initPaginationLinks = function() {
        wlpr_jquery(document).on('click', '.wlpr-pagination a', function(e) {
            e.preventDefault();
            
            var href = wlpr_jquery(this).attr('href');
            var urlParams = new URLSearchParams(href.split('?')[1] || '');
            
            // Preserve search term if it exists
            var searchTerm = wlpr_jquery('#wlpr-rule-search').val().trim();
            if (searchTerm) {
                urlParams.set('search', searchTerm);
            }
            
            var newUrl = window.location.pathname + '?' + urlParams.toString();
            window.location.href = newUrl;
        });
    };
    


    // User report accordion functionality
    wlpr.initUserReportAccordion = function() {
        var $ = wlpr_jquery;
        
        // Main accordion toggle
        $('#user-report-accordion-header').on('click', function() {
            var header = $(this);
            var content = $('#user-report-accordion-content');
            
            header.toggleClass('active');
            content.toggleClass('active');
        });

        // Category accordion toggles
        $('.wlpr-category-header').on('click', function() {
            var header = $(this);
            var category = header.data('category');
            var content = $('#' + category + '-content');
            
            header.toggleClass('active');
            content.toggleClass('active');
        });

        // Update counters when checkboxes change
        $('input[name="user_report_includes[]"]').on('change', function() {
            wlpr.updateUserReportCounters();
        });

        // Initialize counters
        wlpr.updateUserReportCounters();
    };

    // Update user report counters
    wlpr.updateUserReportCounters = function() {
        var $ = wlpr_jquery;
        
        // Update main counter
        var totalSelected = $('input[name="user_report_includes[]"]:checked').length;
        $('#user-report-accordion-header .wlpr-selected-count').text('(' + totalSelected + ' selected)');

        // Update category counters
        ['points', 'rewards', 'levels'].forEach(function(category) {
            var categorySelected = $('input[name="user_report_includes[]"][data-category="' + category + '"]:checked').length;
            $('.wlpr-category-header[data-category="' + category + '"] .wlpr-category-count')
                .text('(' + categorySelected + ' selected)');
        });
    };

    // Form submission handlers
    wlpr.initFormSubmissions = function() {
        var $ = wlpr_jquery;
        
        // Admin form submission
        $('#wlpr-admin-form').on('submit', function(e) {
            e.preventDefault();
            wlpr.submitForm($(this), 'wlpr-admin-submit');
        });
        
        // User form submission
        $('#wlpr-user-form').on('submit', function(e) {
            e.preventDefault();
            wlpr.submitForm($(this), 'wlpr-user-submit');
        });
    };

    // Generic form submission function
    wlpr.submitForm = function($form, buttonId) {
        var $ = wlpr_jquery;
        var $button = $('#' + buttonId);
        
        // Disable button and show loading state
        if ($button.attr('disabled') === 'disabled') {
            return;
        }
        $button.attr('disabled', true);
        $button.css({
            'cursor': 'not-allowed',
            'opacity': '0.4'
        });
        
        // Prepare form data
        var formData = $form.serialize();
        
        // Add AJAX URL
        formData += '&ajax_url=' + encodeURIComponent(wlpr_localize_data.ajax_url);
        
        $.ajax({
            url: wlpr_localize_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            cache: false,
            success: function(res) {
                if (res.success) {
                    alertify.success(res.data.message);
                    // Redirect to rules page after successful save
                    setTimeout(function() {
                        window.location.href = wlpr_localize_data.rules_url || 'admin.php?page=wployalty-point-email-reminder&view=rules';
                    }, 1500);
                } else {
                    alertify.error(res.data.message);
                    // Handle field errors if any
                    if (res.data.field_error) {
                        wlpr.displayFieldErrors(res.data.field_error);
                    }
                }
            },
            error: function() {
                alertify.error('An error occurred. Please try again.');
            },
            complete: function() {
                // Re-enable button
                $button.removeAttr('disabled');
                $button.css({
                    'cursor': 'pointer',
                    'opacity': '1'
                });
            }
        });
    };

    // Display field errors
    wlpr.displayFieldErrors = function(fieldErrors) {
        var $ = wlpr_jquery;
        
        // Clear previous errors
        $('.wlpr-field-error').remove();
        
        // Add error messages
        $.each(fieldErrors, function(field, error) {
            var $field = $('[name="' + field + '"]');
            if ($field.length) {
                $field.after('<div class="wlpr-field-error">' + error + '</div>');
                $field.addClass('wlpr-error');
            }
        });
    };

    // Delete functionality
    wlpr.initDeleteHandlers = function() {
        var $ = wlpr_jquery;
        
        $('.wlpr-action-delete').on('click', function(e) {
            e.preventDefault();
            
            var ruleId = $(this).data('rule-id');
            var $row = $(this).closest('tr');
            
            // Show confirmation dialog
            alertify.confirm(
                'Delete Rule',
                'Are you sure you want to delete this rule? This action cannot be undone.',
                function() {
                    // User confirmed deletion
                    wlpr.deleteRule(ruleId, $row);
                },
                function() {
                    // User cancelled
                    alertify.error('Deletion cancelled');
                }
            );
        });
    };

    // Delete rule function
    wlpr.deleteRule = function(ruleId, $row) {
        var $ = wlpr_jquery;
        
        // Show loading state
        $row.css('opacity', '0.5');
        
        $.ajax({
            url: wlpr_localize_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wlpr_delete_reminder_rule',
                id: ruleId,
                wlpr_nonce: wlpr_localize_data.delete_reminder_rule
            },
            cache: false,
            success: function(res) {
                if (res.success) {
                    alertify.success(res.data.message);
                    
                    // Always reload the current page - server-side will handle keeping pages full
                    // This ensures that if we delete from page 2 and there are more rules,
                    // the server will fetch the next rules to keep the page full
                    window.location.reload();
                } else {
                    alertify.error(res.data.message);
                    $row.css('opacity', '1');
                }
            },
            error: function() {
                alertify.error('An error occurred while deleting the rule. Please try again.');
                $row.css('opacity', '1');
            }
        });
    };

    // Bulk actions functionality
    wlpr.initBulkActions = function() {
        var $ = wlpr_jquery;
        
        // Select all checkbox functionality
        $('#wlpr-select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.wlpr-rule-checkbox').prop('checked', isChecked);
            wlpr.updateBulkDeleteButton();
        });
        
        // Individual checkbox functionality
        $(document).on('change', '.wlpr-rule-checkbox', function() {
            wlpr.updateSelectAllState();
            wlpr.updateBulkDeleteButton();
        });
        
        // Bulk delete button click
        $('#wlpr-bulk-delete-btn').on('click', function() {
            wlpr.performBulkDelete();
        });
    };

    // Update select all checkbox state
    wlpr.updateSelectAllState = function() {
        var $ = wlpr_jquery;
        var totalCheckboxes = $('.wlpr-rule-checkbox').length;
        var checkedCheckboxes = $('.wlpr-rule-checkbox:checked').length;
        var $selectAll = $('#wlpr-select-all');
        
        if (checkedCheckboxes === 0) {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $selectAll.prop('checked', true);
            $selectAll.prop('indeterminate', false);
        } else {
            $selectAll.prop('checked', false);
            $selectAll.prop('indeterminate', true);
        }
    };

    // Update bulk delete button visibility and text
    wlpr.updateBulkDeleteButton = function() {
        var $ = wlpr_jquery;
        var checkedCount = $('.wlpr-rule-checkbox:checked').length;
        var $bulkDeleteBtn = $('#wlpr-bulk-delete-btn');
        var $bulkDeleteText = $('#wlpr-bulk-delete-text');
        
        if (checkedCount > 0) {
            $bulkDeleteBtn.show();
            $bulkDeleteText.text('Delete ' + checkedCount + ' item' + (checkedCount === 1 ? '' : 's'));
        } else {
            $bulkDeleteBtn.hide();
        }
    };

    // Perform bulk delete
    wlpr.performBulkDelete = function() {
        var $ = wlpr_jquery;
        var selectedIds = [];
        
        $('.wlpr-rule-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length === 0) {
            alertify.error('No rules selected for deletion.');
            return;
        }
        
        // Show confirmation dialog
        var message = 'Are you sure you want to delete ' + selectedIds.length + ' rule' + (selectedIds.length === 1 ? '' : 's') + '? This action cannot be undone.';
        
        alertify.confirm(
            'Bulk Delete Rules',
            message,
            function() {
                // User confirmed deletion
                wlpr.executeBulkDelete(selectedIds);
            },
            function() {
                // User cancelled
                alertify.error('Bulk deletion cancelled');
            }
        );
    };

    // Execute bulk delete
    wlpr.executeBulkDelete = function(selectedIds) {
        var $ = wlpr_jquery;
        var $bulkDeleteBtn = $('#wlpr-bulk-delete-btn');
        
        // Show loading state
        $bulkDeleteBtn.prop('disabled', true);
        $bulkDeleteBtn.html('<i class="dashicons dashicons-update-alt"></i> Deleting...');
        
        $.ajax({
            url: wlpr_localize_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wlpr_bulk_delete_reminder_rules',
                rule_ids: selectedIds,
                wlpr_nonce: wlpr_localize_data.bulk_delete_reminder_rules
            },
            cache: false,
            success: function(res) {
                if (res.success) {
                    alertify.success(res.data.message);
                    
                    // Always reload the current page - server-side will handle keeping pages full
                    window.location.reload();
                } else {
                    alertify.error(res.data.message);
                    // Reset button state
                    $bulkDeleteBtn.prop('disabled', false);
                    $bulkDeleteBtn.html('<i class="dashicons dashicons-trash"></i><span id="wlpr-bulk-delete-text">Delete</span>');
                }
            },
            error: function() {
                alertify.error('An error occurred while deleting the rules. Please try again.');
                // Reset button state
                $bulkDeleteBtn.prop('disabled', false);
                $bulkDeleteBtn.html('<i class="dashicons dashicons-trash"></i><span id="wlpr-bulk-delete-text">Delete</span>');
            }
        });
    };
}(wlpr));