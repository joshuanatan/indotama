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
                                        <h6 class="panel-title txt-dark">Informasi Toko</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class = "table table-bordered" style = "color:black">
                                                    <tr>
                                                        <td style = "height:50px">Nama Toko</td>
                                                        <td id = "nama_edit"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Kode Toko</td>
                                                        <td id = "kode_edit"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Logo Toko</td>
                                                        <td id = "logo_download"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Kop Surat</td>
                                                        <td style = "height:50px" id = "kop_surat_download"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Surat Non PKP</td>
                                                        <td style = "height:50px" id = "nonpkp_download"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style = "height:50px">Surat Pernyataan Nomor Rekening</td>
                                                        <td style = "height:50px" id = "pernyataan_rek_download"></td>
                                                    </tr>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-dark">Daftar Cabang</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Nama Cabang</th>
                                                        <th>No Telp</th>
                                                        <th>Alamat</th>
                                                        <th>Action</th>
                                                    </thead>
                                                    <tbody id = "container_daftar_cabang"></tbody>
                                                </table>
                                            </div>	
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                    <div class="row" id = "widget_row"></div>
                    <div class="row" id = "table_row"></div>
                    <div class="row" id = "chart_row"></div>
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
<script>
var chart_content = [];
$.ajax({
    url:"<?php echo base_url();?>ws/toko/pengaturan",
    type:"GET",
    dataType:"JSON",
    success:function(respond){
        if(respond["status"].toLowerCase() == "success"){
            var html = "";
            $("#nama_edit").html(respond["content"][0]["nama"]);
            $("#kode_edit").html(respond["content"][0]["kode"]);

            if(respond["content"][0]["logo"] != "-"){
                
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/toko/logo/${respond["content"][0]["logo"]}" class = "col-lg-12 btn btn-primary btn-sm" >Download</a>`; 
                $("#logo_download").html(html);
            }
            else{
                
                $("#logo_download").html("No Data");
            }

            if(respond["content"][0]["kop_surat"] != "-"){
                
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/toko/kop_surat/${respond["content"][0]["kop_surat"]}" class = "col-lg-12 btn btn-primary btn-sm" >Download</a>`; 
                $("#kop_surat_download").html(html);
            }
            else{
                
                $("#kop_surat_download").html("No Data");
            }

            if(respond["content"][0]["nonpkp"] != "-"){
                
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/toko/nonpkp/${respond["content"][0]["nonpkp"]}" class = "col-lg-12 btn btn-primary btn-sm" >Download</a>`; 
                $("#nonpkp_download").html(html);
            }
            else{
                
                $("#nonpkp_download").html("No Data");
            }

            if(respond["content"][0]["pernyataan_rek"] != "-"){
                
                html = `<a target = "_blank" href = "<?php echo base_url();?>asset/uploads/toko/pernyataan_rek/${respond["content"][0]["pernyataan_rek"]}" class = "col-lg-12 btn btn-primary btn-sm" >Download</a>`; 
                $("#pernyataan_rek_download").html(html);
            }
            else{
                
                $("#pernyataan_rek_download").html("No Data");
            }

        }
    }
});
$.ajax({
    url:"<?php echo base_url();?>ws/toko/list_cabang",
    type:"GET",
    dataType:"JSON",
    success:function(respond){
        if(respond["status"].toLowerCase() == "success"){
            var html = "";
            for(var a = 0; a<respond["content"].length; a++){
                html += `
                <tr>
                    <td>${respond["content"][a]["daerah"]}</td>
                    <td>${respond["content"][a]["notelp"]}</td>
                    <td>${respond["content"][a]["alamat"]}</td>
                    <td>
                        <a target = "_blank" href = "<?php echo base_url();?>toko/dashboard_cabang_toko/${respond["content"][a]["id"]}" type = "button" class = "btn btn-primary btn-sm">Beranda</a> 
                    </td>
                </tr>`;
            }
            $("#container_daftar_cabang").html(html);
        }   
    }
});
$.ajax({
    url:"<?php echo base_url();?>ws/toko/dashboard",
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
            }
            $("#widget_row").html(html_widget);
            $("#table_row").html(html_table);
            $("#chart_row").html(html_chart);

            /*init chart*/
            for(var a = 0; a<chart_content.length; a++){
                var chart_data = chart_content[a]["data"];
                var xlabel = chart_content[a]["xlabel"];
                init_chart_data(xlabel,chart_data,a);
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
