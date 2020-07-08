<?php
$page_title = "Dashboard";
$breadcrumb = array(
    "Dashboard"
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <!-- Data table CSS -->
        <link href="<?php echo base_url(); ?>vendors/bower_components/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>vendors/bower_components/datatables.net-responsive/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css" />
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">
        <?php $this->load->view('req/mm_menubar');?>
            <div class="page-wrapper">

                <div class="container-fluid pt-25">
                    <!-- Row -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-light block counter"><span class="counter-anim">914,001</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Penjualan Bulan Ini</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-light block counter"><span class="counter-anim">914,001</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Penjualan Bulan Ini</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-light block counter"><span class="counter-anim">914,001</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Permintaan Barang</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-light block counter"><span class="counter-anim">914,001</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Permintaan Cabang</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient1">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-light block counter"><span class="counter-anim">2100</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Penjualan Bulan Lalu</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient2">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 txt-light data-wrap-left">
                                                        <span class="block counter"><span class="counter-anim">54,876</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Penjualan Tahun Ini</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0 bg-gradient2">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 txt-light data-wrap-left">
                                                        <span class="block counter"><span class="counter-anim">54,876</span></span>
                                                        <span class="weight-1500 block font-13 txt-light">Pemberian belum Dikirim</span>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Row -->
                    <!-- Row -->
                    <div class="row">
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Daftar Barang Urgen Restok</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered table-striped display mb-30"  id = "tbl_pemberian_belum_kirim">
                                                    <thead>
                                                        <tr role="row">
                                                            <th>Col 1</th>
                                                            <th>Col 2</th>
                                                            <th>Col 3</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Daftar Pengiriman Barang</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered table-striped display mb-30"  id = "tbl_pengiriman_barang">
                                                    <thead>
                                                        <tr role="row">
                                                            <th>Col 1</th>
                                                            <th>Col 2</th>
                                                            <th>Col 3</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 	
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Daftar Pemberian yang belum Dikirim</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered table-striped display mb-30"  id = "tbl_barang_urgen_restok">
                                                    <thead>
                                                        <tr role="row">
                                                            <th>Col 1</th>
                                                            <th>Col 2</th>
                                                            <th>Col 3</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 	
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Barang Custom</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-bordered table-striped display mb-30"  id = "tbl_brg_custom">
                                                    <thead>
                                                        <tr role="row">
                                                            <th>Col 1</th>
                                                            <th>Col 2</th>
                                                            <th>Col 3</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Row 1</td>
                                                            <td>Row 2</td>
                                                            <td>Row 3</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12" style = "width:100%">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Penjualan 3 Tahun Terakhir</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <canvas id="myChart" height = "100"></canvas>
                                    </div>	
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12" style = "width:100%">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Penjualan Tahun Ini</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <canvas id="myChart2" height = "100"></canvas>
                                    </div>	
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12" style = "width:100%">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Perbandingan Penjualan Tahun Ini</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <canvas id="myChart3" height = "100"></canvas>
                                    </div>	
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('req/mm_footer.php');?>
                    <!-- /Row -->
                </div>
            </div>
        </div>
    </body>
</html>

<?php $this->load->view('req/mm_js.php');?>
<?php $this->load->view("_core_script/table_func");?>

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
<script>
$(document).ready(function() {
    "use strict";
    $('#tbl_brg_custom').DataTable();
    $('#tbl_pemberian_belum_kirim').DataTable();
    $('#tbl_pengiriman_barang').DataTable();
    $('#tbl_barang_urgen_restok').DataTable();
});
</script>

<script src="<?php echo base_url();?>vendors/chart.js/Chart.min.js"></script>
<script>

var ctx = document.getElementById('myChart');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange','Red2', 'Blue2', 'Yellow2', 'Green2', 'Purple2', 'Orange2'],
        datasets: [{
            label: '# of Votes',
            data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});


var ctx = document.getElementById('myChart2');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange','Red2', 'Blue2', 'Yellow2', 'Green2', 'Purple2', 'Orange2'],
        datasets: [{
            label: '# of Votes',
            data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});


var ctx = document.getElementById('myChart3');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange','Red2', 'Blue2', 'Yellow2', 'Green2', 'Purple2', 'Orange2'],
        datasets: [
            {
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            },
            {
                label: '# of Votes',
                data: [1, 1, 1, 1, 1, 1],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }
        ]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});
</script>

<script>
window.onload = function() {
    if(!window.location.hash) {
        window.location = window.location + '#loaded';
        window.location.reload();
    }
}
</script>
