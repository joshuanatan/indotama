<?php
$page_title = "Dashboard Cabang";
$breadcrumb = array(
    "Cabang","Dashboard"
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
                    <div class = "row">
                        <div class="col-lg-6">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Informasi Cabang</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class = "table table-bordered" style = "color:black">
                                                    <tr>
                                                        <td style = "height:50px">Daerah Cabang</td>
                                                        <td id = "daerah_detail"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Alamat Cabang</td>
                                                        <td id = "alamat_detail"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">No Telp Cabang</td>
                                                        <td id = "notelp_detail"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Kop Surat</td>
                                                        <td style = "height:50px" id = "kop_surat_download_container"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Surat Non PKP</td>
                                                        <td style = "height:50px" id = "nonpkp_download_container"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Surat Pernyataan Nomor Rekening</td>
                                                        <td style = "height:50px" id = "pernyataan_rek_download_container"></td>
                                                    </tr>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class = "col-lg-6">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">Home</a></li>
                                <?php for($a = 0; $a<count($breadcrumb); $a++):?>
                                <?php if($a+1 != count($breadcrumb)):?>
                                <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]);?></a></li>
                                <?php else:?>
                                <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]);?></li>
                                <?php endif;?>
                                <?php endfor;?>
                            </ol>
                        </div>
                    </div>
                    <div class="row" id = "widget_row"></div>
                    <div class="row" id = "table_row"></div>
                    <div class="row" id = "chart_row"></div>
                    <div class="row" id = "pie_row"></div>
                    <div class="row" id = "doughnut_row"></div>
                    <?php $this->load->view('req/mm_footer.php');?>
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


<script src= "<?php echo base_url();?>vendors/chart.js/Chart.min.js"></script>
<script src= "<?php echo base_url();?>asset/dashboard_elements/widget_elem.js"></script>
<script src= "<?php echo base_url();?>asset/dashboard_elements/table_elem.js"></script>
<script src= "<?php echo base_url();?>asset/dashboard_elements/chart_elem.js"></script>
<script src= "<?php echo base_url();?>asset/dashboard_elements/pie_chart_elem.js"></script>
<script src= "<?php echo base_url();?>asset/dashboard_elements/doughnut_chart_elem.js"></script>
<script>
$.ajax({
    url:"<?php echo base_url();?>ws/cabang/pengaturan",
    type:"GET",
    dataType:"JSON",
    success:function(respond){
        if(respond["status"].toLowerCase() == "success"){
            var html = "";
            $("#daerah_detail").html(respond["content"][0]["daerah"]);
            $("#alamat_detail").html(respond["content"][0]["alamat"]);
            $("#notelp_detail").html(respond["content"][0]["notelp"]);

            if(respond["content"][0]["cabang_kop_surat"] != "-"){
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/cabang/kop_surat/${respond["content"][0]["kop_surat"]}" class = "btn     btn-primary btn-sm col-lg-12" id = "kop_surat_download">Download</a>`;
                $("#kop_surat_download_container").html(html);
            }
            else{
                $("#kop_surat_download_container").html("No Data");
            }

            if(respond["content"][0]["cabang_nonpkp"] != "-"){
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/cabang/nonpkp/${respond["content"][0]["nonpkp"]}" class = "btn btn-primary   btn-sm col-lg-12" id = "nonpkp_download">Download</a>`;
                $("#nonpkp_download_container").html(html);
            }
            else{
                $("#nonpkp_download_container").html("No Data");
            }

            if(respond["content"][0]["cabang_pernyataan_rek"] != "-"){
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/cabang/pernyataan_rek/${respond["content"][0]["pernyataan_rek"]}" class = "btn   btn-primary btn-sm col-lg-12" id = "pernyataan_rek_download">Download</a>`;
                $("#pernyataan_rek_download_container").html(html);
            }
            else{
                $("#pernyataan_rek_download_container").html("No Data");
            }

            $("#kop_surat_current").val(respond["content"][0]["kop_surat"]);
            $("#nonpkp_current").val(respond["content"][0]["nonpkp"]);
            $("#pernyataan_rek_current").val(respond["content"][0]["pernyataan_rek"]);
        }
    }
});
var chart_content = [];
var pie_content = [];
var doughnut_content = [];
$.ajax({
    url:"<?php echo base_url();?>ws/cabang/dashboard",
    type:"GET",
    dataType:"JSON",
    success:function(respond){
        if(respond["status"].toLowerCase() == "success"){
            var html_widget = "";
            var amt_widget = 0;
            var html_table = "";
            var amt_table = 0;
            var html_chart = "";
            var amt_chart = 0;
            var html_pie = "";
            var amt_pie = 0;
            var html_doughnut = "";
            var amt_doughnut = 0;
            for(var a = 0; a<respond["content"].length; a++){
                if(respond["content"][a]["type"].toLowerCase() == "widget"){
                    var widget_data = respond["content"][a]["data"];
                    var widget_title = respond["content"][a]["title"];
                    html_widget += populate_widget_data(widget_data,widget_title);
                    amt_widget++;
                }
                else if(respond["content"][a]["type"].toLowerCase() == "table"){
                    var table_header = respond["content"][a]["header"];
                    var table_data = respond["content"][a]["data"];
                    var table_title = respond["content"][a]["title"];
                    html_table += populate_table_data(table_header,table_data,table_title,amt_table);
                    amt_table++;
                }
                else if(respond["content"][a]["type"].toLowerCase() == "chart"){
                    var chart_title = respond["content"][a]["title"];
                    html_chart += populate_chart_data(chart_title,amt_chart);
                    amt_chart++;
                    chart_content.push(respond["content"][a]);
                }
                else if(respond["content"][a]["type"].toLowerCase() == "pie"){
                    var pie_title = respond["content"][a]["title"];
                    html_pie += populate_pie_data(pie_title,amt_pie);
                    amt_pie++;
                    pie_content.push(respond["content"][a]);
                }
                else if(respond["content"][a]["type"].toLowerCase() == "doughnut"){
                    var doughnut_title = respond["content"][a]["title"];
                    html_doughnut += populate_doughnut_data(doughnut_title,amt_doughnut);
                    amt_doughnut++;
                    doughnut_content.push(respond["content"][a]);
                }
            }
            $("#widget_row").html(html_widget);
            $("#table_row").html(html_table);
            $("#chart_row").html(html_chart);
            $("#pie_row").html(html_pie);
            $("#doughnut_row").html(html_doughnut);

            /*init chart*/
            for(var a = 0; a<chart_content.length; a++){
                var chart_data = chart_content[a]["data"];
                var xlabel = chart_content[a]["xlabel"];
                init_chart_data(xlabel,chart_data,a);
            }
            for(var a = 0; a<pie_content.length; a++){
                var pie_data = pie_content[a]["data"];
                init_pie_data(pie_data,a);
            }
            for(var a = 0; a<doughnut_content.length; a++){
                var doughnut_data = doughnut_content[a]["data"];
                init_doughnut_data(doughnut_data,a);
            }

            /*init table*/
            for(var a = 0; a<amt_table; a++){
                $(`#table${a}`).DataTable();
            }
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
