// Table functionality - separate file to avoid Vue template conflicts

// Function to update status badge
function updateStatusBadge(row, isMarked) {
   const statusCell = row.find('td:last-child');
   if (isMarked) {
      statusCell.html('<span class="badge badge-success" title="Selected">SELECTED</span>');
   } else {
      statusCell.html('<span class="badge badge-secondary" title="Not selected">NORMAL</span>');
   }
}

// Export function
function exportData(format) {
   if (window.Vue && window.app) {
      window.app.exportData(format);
   } else {
      // Fallback export functionality
      window.location.href = '/export?format=' + format;
   }
}

// Initialize table functionality when DOM is ready
$(document).ready(function() {
   // Only run on pages with data table
   if ($('.data-table').length === 0) {
      return;
   }

   console.log('Initializing table functionality');

   // Store stats in localStorage for the home page
   const totalCountElement = document.getElementById('totalCount');
   const markedCountElement = document.getElementById('markedCount');

   if (totalCountElement && markedCountElement) {
      const stats = {
         total: parseInt(totalCountElement.textContent) || 0,
         marked: parseInt(markedCountElement.textContent) || 0
      };
      localStorage.setItem('appStats', JSON.stringify(stats));
   }

   // Row selection functionality
   $('tbody tr').off('click').on('click', function(e) {
      e.preventDefault();

      const $row = $(this);
      const rowId = parseInt($row.data('id'));
      const isCurrentlyMarked = $row.hasClass('row-marked');

      console.log('Row clicked:', rowId, 'Currently marked:', isCurrentlyMarked);

      // Send AJAX request
      $.ajax({
         url: '/mark',
         method: 'POST',
         contentType: 'application/json',
         data: JSON.stringify({
            id: rowId,
            marked: !isCurrentlyMarked
         }),
         success: function(response) {
            console.log('AJAX response:', response);

            if (response.success) {
               // Toggle the row-marked class
               $row.toggleClass('row-marked');

               // Update the status badge
               updateStatusBadge($row, response.marked);

               // Update counter
               const counter = $('#markedCount');
               let count = parseInt(counter.text()) || 0;
               count += response.marked ? 1 : -1;
               counter.text(count);

               console.log('Row updated, new marked status:', response.marked);
            } else {
               alert('Error: ' + (response.message || 'Unknown error'));
            }
         },
         error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            alert('Error marking record: ' + error);
         }
      });
   });

   // Clear all marks functionality
   $('#clearMarks').off('click').on('click', function() {

      const $markedRows = $('tbody tr.row-marked');
      console.log('Clearing', $markedRows.length, 'marked rows');

      $markedRows.each(function() {
         const $row = $(this);
         const rowId = parseInt($row.data('id'));

         $.ajax({
            url: '/mark',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
               id: rowId,
               marked: false
            }),
            success: function(response) {
               if (response.success) {
                  $row.removeClass('row-marked');
                  updateStatusBadge($row, false);
               }
            },
            error: function() {
               console.error('Failed to clear mark for row', rowId);
            }
         });
      });

      // Reset counter to 0
      $('#markedCount').text('0');
   });

   // Enhanced row interaction with better visual feedback
   $('tbody tr').hover(
      function() {
         $(this).css('transform', 'scale(1.01)');
      },
      function() {
         $(this).css('transform', 'scale(1)');
      }
   );
});