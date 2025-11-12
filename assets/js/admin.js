/**
 * U-Commerce Admin JavaScript
 *
 * @package UCommerce
 */

(function($) {
    'use strict';

    /**
     * U-Commerce Admin Object
     */
    const UCAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Delete confirmation
            $(document).on('click', '.uc-delete-btn', this.confirmDelete);

            // Modal handling
            $(document).on('click', '[data-uc-modal]', this.openModal);
            $(document).on('click', '.uc-modal-close, .uc-modal-backdrop', this.closeModal);

            // Form validation
            $(document).on('submit', '.uc-form-validate', this.validateForm);

            // AJAX form submission
            $(document).on('submit', '.uc-ajax-form', this.handleAjaxForm);

            // Tabs
            $(document).on('click', '.uc-tabs-nav a', this.switchTab);

            // Product search
            $(document).on('input', '.uc-product-search', this.searchProducts);

            // Barcode scanner
            $(document).on('input', '.uc-barcode-input', this.handleBarcodeInput);

            // Table search
            $(document).on('input', '.uc-table-search', this.handleTableSearch);

            // Table filters
            $(document).on('change', '.uc-table-filter', this.handleTableFilter);

            // Clear search
            $(document).on('click', '.uc-clear-search', this.clearTableSearch);
        },

        /**
         * Initialize components
         */
        initComponents: function() {
            // Initialize select2 if available
            if ($.fn.select2) {
                $('.uc-select2').select2();
            }

            // Initialize datepicker if available
            if ($.fn.datepicker) {
                $('.uc-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd'
                });
            }

            // Auto-dismiss notices
            setTimeout(function() {
                $('.uc-notice.auto-dismiss').fadeOut();
            }, 5000);
        },

        /**
         * Confirm delete action
         */
        confirmDelete: function(e) {
            if (!confirm(ucData.i18n?.confirmDelete || 'Are you sure you want to delete this item?')) {
                e.preventDefault();
                return false;
            }
        },

        /**
         * Open modal
         */
        openModal: function(e) {
            e.preventDefault();
            const modalId = $(this).data('uc-modal');
            const $modal = $('#' + modalId);

            if ($modal.length) {
                $modal.addClass('active');
                $('body').addClass('uc-modal-open');
            }
        },

        /**
         * Close modal
         */
        closeModal: function(e) {
            e.preventDefault();
            $('.uc-modal').removeClass('active');
            $('body').removeClass('uc-modal-open');
        },

        /**
         * Validate form
         */
        validateForm: function(e) {
            let isValid = true;
            const $form = $(this);

            // Remove previous errors
            $form.find('.uc-form-error').remove();

            // Check required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    isValid = false;
                    $field.after('<span class="uc-form-error">This field is required.</span>');
                }
            });

            // Check email fields
            $form.find('input[type="email"]').each(function() {
                const $field = $(this);
                const email = $field.val().trim();
                if (email && !UCAdmin.isValidEmail(email)) {
                    isValid = false;
                    $field.after('<span class="uc-form-error">Please enter a valid email address.</span>');
                }
            });

            if (!isValid) {
                e.preventDefault();
            }

            return isValid;
        },

        /**
         * Handle AJAX form submission
         */
        handleAjaxForm: function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            const originalText = $submitBtn.text();

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Processing...');

            // Prepare data
            const formData = new FormData(this);
            formData.append('action', $form.data('action'));
            formData.append('nonce', ucData.nonce);

            // Send AJAX request
            $.ajax({
                url: ucData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        UCAdmin.showNotice('success', response.data.message || 'Action completed successfully.');
                        if (response.data.redirect) {
                            window.location.href = response.data.redirect;
                        } else {
                            $form[0].reset();
                        }
                    } else {
                        UCAdmin.showNotice('error', response.data.message || 'An error occurred.');
                    }
                },
                error: function() {
                    UCAdmin.showNotice('error', 'An error occurred. Please try again.');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Switch tab
         */
        switchTab: function(e) {
            e.preventDefault();

            const $link = $(this);
            const target = $link.attr('href');

            // Update nav
            $link.closest('.uc-tabs-nav').find('a').removeClass('active');
            $link.addClass('active');

            // Update content
            $link.closest('.uc-tabs').next('.uc-tabs-container').find('.uc-tab-content').removeClass('active');
            $(target).addClass('active');
        },

        /**
         * Search products
         */
        searchProducts: function() {
            const $input = $(this);
            const searchTerm = $input.val().trim();

            if (searchTerm.length < 2) {
                return;
            }

            // Debounce
            clearTimeout($input.data('timeout'));
            $input.data('timeout', setTimeout(function() {
                $.ajax({
                    url: ucData.ajaxUrl,
                    type: 'GET',
                    data: {
                        action: 'uc_search_products',
                        nonce: ucData.nonce,
                        search: searchTerm
                    },
                    success: function(response) {
                        if (response.success) {
                            UCAdmin.displayProductResults(response.data.products, $input);
                        }
                    }
                });
            }, 300));
        },

        /**
         * Display product search results
         */
        displayProductResults: function(products, $input) {
            let $results = $input.next('.uc-search-results');

            if (!$results.length) {
                $results = $('<div class="uc-search-results"></div>');
                $input.after($results);
            }

            if (products.length === 0) {
                $results.html('<div class="no-results">No products found.</div>');
                return;
            }

            let html = '<ul>';
            products.forEach(function(product) {
                html += '<li data-product-id="' + product.id + '">';
                html += '<strong>' + product.name + '</strong> - ' + product.sku;
                html += '</li>';
            });
            html += '</ul>';

            $results.html(html);

            // Handle product selection
            $results.find('li').on('click', function() {
                const productId = $(this).data('product-id');
                const productName = $(this).find('strong').text();
                $input.val(productName).data('product-id', productId);
                $results.empty();
            });
        },

        /**
         * Handle barcode input
         */
        handleBarcodeInput: function() {
            const $input = $(this);
            const barcode = $input.val().trim();

            // Typically barcodes are 13 digits (EAN-13)
            if (barcode.length === 13) {
                UCAdmin.lookupBarcode(barcode, $input);
            }
        },

        /**
         * Lookup barcode
         */
        lookupBarcode: function(barcode, $input) {
            $.ajax({
                url: ucData.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'uc_lookup_barcode',
                    nonce: ucData.nonce,
                    barcode: barcode
                },
                success: function(response) {
                    if (response.success) {
                        $input.trigger('barcode-found', [response.data.product]);
                        UCAdmin.showNotice('success', 'Product found: ' + response.data.product.name);
                    } else {
                        UCAdmin.showNotice('error', 'Product not found for this barcode.');
                    }
                }
            });
        },

        /**
         * Show notice
         */
        showNotice: function(type, message) {
            const $notice = $('<div class="uc-notice uc-notice-' + type + ' auto-dismiss">' + message + '</div>');
            $('.wrap > h1').after($notice);

            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Validate email
         */
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount) {
            return ucData.currency + ' ' + parseFloat(amount).toFixed(2);
        },

        /**
         * Handle table search
         */
        handleTableSearch: function() {
            const $search = $(this);
            const $table = $search.closest('.uc-table-controls').next('table');
            const searchTerm = $search.val().toLowerCase().trim();

            // Debounce search
            clearTimeout($search.data('timeout'));
            $search.data('timeout', setTimeout(function() {
                UCAdmin.filterTable($table);
            }, 300));
        },

        /**
         * Handle table filter
         */
        handleTableFilter: function() {
            const $filter = $(this);
            const $table = $filter.closest('.uc-table-controls').next('table');
            UCAdmin.filterTable($table);
        },

        /**
         * Filter table based on search and filters
         */
        filterTable: function($table) {
            const $controls = $table.prev('.uc-table-controls');
            const $search = $controls.find('.uc-table-search');
            const searchTerm = $search.val().toLowerCase().trim();

            // Get all active filters
            const filters = {};
            $controls.find('.uc-table-filter').each(function() {
                const $filter = $(this);
                const filterType = $filter.data('filter');
                const filterValue = $filter.val();
                if (filterValue) {
                    filters[filterType] = filterValue.toLowerCase();
                }
            });

            let visibleCount = 0;
            let totalCount = 0;

            // Filter table rows
            $table.find('tbody tr').each(function() {
                const $row = $(this);
                const rowText = $row.text().toLowerCase();
                let show = true;
                totalCount++;

                // Apply search filter
                if (searchTerm && !rowText.includes(searchTerm)) {
                    show = false;
                }

                // Apply dropdown filters
                if (show) {
                    for (const filterType in filters) {
                        const $cell = $row.find('[data-filter-' + filterType + ']');
                        if ($cell.length) {
                            const cellValue = $cell.data('filter-' + filterType).toString().toLowerCase();
                            if (cellValue !== filters[filterType]) {
                                show = false;
                                break;
                            }
                        }
                    }
                }

                // Show or hide row
                if (show) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });

            // Update results count
            const $count = $controls.find('.uc-results-count');
            if ($count.length) {
                if (searchTerm || Object.keys(filters).length > 0) {
                    $count.text('Showing ' + visibleCount + ' of ' + totalCount + ' items');
                } else {
                    $count.text('');
                }
            }
        },

        /**
         * Clear table search
         */
        clearTableSearch: function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $controls = $btn.closest('.uc-table-controls');
            const $search = $controls.find('.uc-table-search');
            const $table = $controls.next('table');

            $search.val('');
            $controls.find('.uc-table-filter').val('');
            UCAdmin.filterTable($table);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        UCAdmin.init();
    });

    // Expose to global scope
    window.UCAdmin = UCAdmin;

})(jQuery);
