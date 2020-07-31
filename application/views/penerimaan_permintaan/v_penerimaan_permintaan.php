<?php
$page_title = "Penerimaan Permintaan";
$breadcrumb = array(
    "Penerimaan Permintaan"
);
$notif_data = array(
    "page_title" => $page_title
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">

            <?php $this->load->view('req/mm_menubar.php');?>

            <div class="page-wrapper">
            
            <?php $this->load->view('_notification/register_success',$notif_data); ?>
            <?php $this->load->view('_notification/update_success',$notif_data); ?>
            <?php $this->load->view('_notification/delete_success',$notif_data); ?>
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-lg-12 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?></h6>
                                    </div>
                                    <div class="clearfix"></div>
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
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-12">
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-check"></i><b> - Terima Barang </b>
                                            </div>
                                            <br/>
                                            <?php $this->load->view("_base_element/table");?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('req/mm_footer.php');?>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>
<script>
    var ctrl = "penerimaan_permintaan"; 
    var url_add = "type=<?php echo $type;?>";
    var unautorized_button = ["edit_button","delete_button","detail_button"];
    var additional_button = [
        {
            class:"md-check text-success",
            style:"cursor:pointer",
            onclick:"open_terima_barang_modal()"
        }
    ]
</script>
<script>
    var delete_params = "";
    function open_terima_barang_modal(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            
            if(content[row]["status"].toLowerCase() == "diterima"){
                delete_params = "&id_brg="+content[row]["id_brg_pemenuhan"];
                $("#id_delete").val(content[row]["id"]);
                $("#id_brg_pengiriman_delete").val(content[row]["id_brg_pengiriman"]);
                $("#brg_pengiriman_qty_delete").html(content[row]["qty_brg_pengiriman"]+" Pcs");
                $("#brg_penerimaan_qty_delete").val(content[row]["qty_brg_pengiriman"]);
                $("#brg_nama_delete").html(content[row]["nama_brg"]);
                $("#toko_delete").html(content[row]["nama_toko"]);
                $("#cabang_delete").html(content[row]["daerah_cabang"]);
                $("#tgl_pengiriman_delete").html(content[row]["tgl_pengiriman"]);
                $("#delete_modal").modal("show");
            }
            else if(content[row]["status"].toLowerCase() == "perjalanan"){

                $("#id_brg_pemenuhan").val(content[row]["id_brg_pemenuhan"]);
                $("#id_brg_pengiriman").val(content[row]["id_brg_pengiriman"]);
                $("#brg_pengiriman_qty").html(content[row]["qty_brg_pengiriman"]+" Pcs");
                $("#brg_penerimaan_qty").val(content[row]["qty_brg_pengiriman"]);
                $("#brg_nama").html(content[row]["nama_brg"]);
                $("#toko").html(content[row]["nama_toko"]);
                $("#cabang").html(content[row]["daerah_cabang"]);
                $("#tgl_pengiriman").html(content[row]["tgl_pengiriman"]);
                $("#register_modal").modal("show");
            }
        });
    }
</script>
<?php
$data = array(
    "page_title" => "Penerimaan Permintaan",
    "type" => $type,
    "id_tempat_penerimaan" => $id_tempat_penerimaan,
    "tipe_penerimaan" => $tipe_penerimaan,
);
?>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("penerimaan_permintaan/f-add-penerimaan_permintaan",$data);?>
<?php $this->load->view("penerimaan_permintaan/f-delete-penerimaan_permintaan",$data);?>

<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>
