/**
 * Sortable List Functionality
 * Enables drag and drop reordering of program items
 */

(function($) {
    'use strict';

    // Initialize sortable lists
    function initSortable(selector, table, options) {
        const defaults = {
            handle: '.drag-handle',
            placeholder: 'sortable-placeholder',
            opacity: 0.8,
            revert: 150,
            tolerance: 'pointer',
            cursor: 'grabbing',
            axis: 'y',
            containment: 'parent'
        };

        const settings = $.extend({}, defaults, options);

        $(selector).sortable({
            handle: settings.handle,
            placeholder: settings.placeholder,
            opacity: settings.opacity,
            revert: settings.revert,
            tolerance: settings.tolerance,
            cursor: settings.cursor,
            axis: settings.axis,
            containment: settings.containment,

            start: function(event, ui) {
                // Add dragging class
                ui.item.addClass('dragging');

                // Set placeholder height to match item
                ui.placeholder.height(ui.item.outerHeight());
                ui.placeholder.css('background', 'rgba(var(--bs-primary-rgb), 0.1)');
                ui.placeholder.css('border', '2px dashed var(--bs-primary)');
                ui.placeholder.css('border-radius', '0.375rem');
            },

            stop: function(event, ui) {
                // Remove dragging class
                ui.item.removeClass('dragging');
            },

            update: function(event, ui) {
                // Get the new order
                const items = $(this).sortable('toArray', { attribute: 'data-id' });
                const dagid = $(this).data('dagid');

                // Save the new order via AJAX
                saveOrder(table, items, dagid);
            }
        });

        // Enable touch support for mobile
        if ('ontouchstart' in document.documentElement) {
            $(selector).sortable('option', 'handle', '.sortable-item');
        }
    }

    // Save the new order to the server
    function saveOrder(table, items, dagid) {
        $.ajax({
            url: 'api/reorder_program.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                table: table,
                items: items,
                dagid: dagid
            }),
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Rekkefølge oppdatert',
                            showConfirmButton: false,
                            timer: 1000,
                            toast: true
                        });
                    }
                } else {
                    showError('Kunne ikke oppdatere rekkefølgen');
                }
            },
            error: function() {
                showError('Serverfeil ved oppdatering av rekkefølge');
            }
        });
    }

    // Show error notification
    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 2000,
                toast: true
            });
        } else {
            console.error(message);
        }
    }

    // Expose to global scope
    window.initSortableList = initSortable;

    // Auto-initialize sortable lists on document ready
    $(document).ready(function() {
        // Initialize for program_oversikt
        $('.sortable-list[data-table="program_oversikt"]').each(function() {
            initSortable(this, 'program_oversikt');
        });

        // Initialize for prg_pdf
        $('.sortable-list[data-table="prg_pdf"]').each(function() {
            initSortable(this, 'prg_pdf');
        });
    });

})(jQuery);
