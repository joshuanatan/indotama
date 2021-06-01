<?php
$page_title = "Dashboard Cabang";
$breadcrumb = array(
  "Cabang", "Dashboard"
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Data table CSS -->
  <link href="<?php echo base_url(); ?>vendors/bower_components/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo base_url(); ?>vendors/bower_components/datatables.net-responsive/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css" />
  <?php $this->load->view('req/mm_css.php'); ?>
</head>

<body>
  <div class="preloader-it">
    <div class="la-anim-1"></div>
  </div>
  <div class="wrapper theme-1-active pimary-color-pink">
    <?php $this->load->view('req/mm_menubar'); ?>
    <div class="page-wrapper">

      <div class="container-fluid pt-25">
        <div class="row" id="widget_row"></div>
        <div class="row" id="table_row"></div>
        <div class="row" id="chart_row"></div>
        <?php $this->load->view('req/mm_footer.php'); ?>
      </div>
    </div>
  </div>
</body>

</html>

<?php $this->load->view('req/mm_js.php'); ?>
<?php $this->load->view("_core_script/menubar_func"); ?>

<!-- Data table JavaScript -->
<script src="<?php echo base_url(); ?>vendors/bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/datatables.net-buttons/js/buttons.flash.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/jszip/dist/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/pdfmake/build/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/pdfmake/build/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>vendors/bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>asset/dist/js/export-table-data.js"></script>


<script src="<?php echo base_url(); ?>vendors/chart.js/Chart.min.js"></script>
<script src="<?php echo base_url(); ?>asset/dashboard_elements/widget_elem.js"></script>
<script src="<?php echo base_url(); ?>asset/dashboard_elements/table_elem.js"></script>
<script src="<?php echo base_url(); ?>asset/dashboard_elements/chart_elem.js"></script>
<script>
  var chart_content = [];
  $.ajax({
    url: "<?php echo base_url(); ?>ws/cabang/dashboard/<?php echo $id_cabang; ?>",
    type: "GET",
    dataType: "JSON",
    success: function(respond) {
      if (respond["status"].toLowerCase() == "success") {
        var html_widget = "";
        var amt_widget = 0;
        var html_table = "";
        var amt_table = 0;
        var html_chart = "";
        var amt_chart = 0;
        for (var a = 0; a < respond["content"].length; a++) {
          if (respond["content"][a]["type"].toLowerCase() == "widget") {
            var widget_data = respond["content"][a]["data"];
            var widget_title = respond["content"][a]["title"];
            html_widget += populate_widget_data(widget_data, widget_title);
            amt_widget++;
          } else if (respond["content"][a]["type"].toLowerCase() == "table") {
            var table_header = respond["content"][a]["header"];
            var table_data = respond["content"][a]["data"];
            var table_title = respond["content"][a]["title"];
            html_table += populate_table_data(table_header, table_data, table_title, amt_table);
            amt_table++;
          } else if (respond["content"][a]["type"].toLowerCase() == "chart") {
            var chart_title = respond["content"][a]["title"];
            html_chart += populate_chart_data(chart_title, amt_chart);
            amt_chart++;
            chart_content.push(respond["content"][a]);
          }
        }
        $("#widget_row").html(html_widget);
        $("#table_row").html(html_table);
        $("#chart_row").html(html_chart);

        /*init chart*/
        for (var a = 0; a < chart_content.length; a++) {
          var chart_data = chart_content[a]["data"];
          var xlabel = chart_content[a]["xlabel"];
          init_chart_data(xlabel, chart_data, a);
        }

        /*init table*/
        for (var a = 0; a < amt_table; a++) {
          $(`#table${a}`).DataTable();
        }
      }
    }
  });
</script>
<script>
  window.onload = function() {
    if (!window.location.hash) {
      window.location = window.location + '#loaded';
      window.location.reload();
    }
  }
</script>