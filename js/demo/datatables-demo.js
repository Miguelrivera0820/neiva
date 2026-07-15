// Call the dataTables jQuery plugin only on pages that load it.
if (window.jQuery && $.fn.DataTable) {
  $(document).ready(function() {
    if ($('#dataTable').length) {
      $('#dataTable').DataTable();
    }
  });
}
