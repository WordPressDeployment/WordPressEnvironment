jQuery(document).ready(function($) {
    // Function to refresh the table content
    function refreshTable() {
        // Perform AJAX request to get updated table rows
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'refresh_table_ajax' // Action to handle in WordPress
            },
            success: function(response) {
                // Parse the response to extract new table rows
                var $newRows = $(response).find('tbody tr');

                // Get the current table rows
                var $tableRows = $('.db-table-container tbody tr');

                // Compare and update only the new or changed rows
                $newRows.each(function(index) {
                    var $newRow = $(this);
                    var existingRow = $tableRows.eq(index);

                    // Replace existing row with new row content
                    if (existingRow.length) {
                        existingRow.replaceWith($newRow);
                    } else {
                        // Append new row if it doesn't exist
                        $('.db-table-container tbody').append($newRow);
                    }
                });
            }
        });
    }

    // Call refreshTable function every second
    setInterval(refreshTable, 1000);
});



