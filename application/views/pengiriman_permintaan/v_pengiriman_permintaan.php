<?php
$page_title = "Pengiriman Permintaan";
$breadcrumb = array(
    "Pengiriman Permintaan"
);
$notif_data = array(
    "page_title"=>$page_title
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
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "md-truck"></i><b> - Kirim Barang </b>
                                            </div>
                                            <br/>
                                            <?php $this->load->view("_base_element/table",$excel);?>
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
    var ctrl = "pengiriman_permintaan";  
    var url_add = "type=<?php echo $type;?>";
    var unautorized_button = ["edit_button","delete_button","detail_button"];
    var additional_button = [
        {
            class:"md-truck",
            style:"cursor:pointer",
            onclick:"open_kirim_barang_modal()"
        },
        {
            style:'cursor:pointer;font-size:large',
            class:'text-info md-print',
            onclick:'redirect_print_pdf()'
        }
    ]
</script>
<script>
    function redirect_print_pdf(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_pengiriman = content[row]["id"];
            window.open("<?php echo base_url();?>pdf/surat_jalan/permintaan/"+id_pengiriman,"_blank");
        });
    }
    var delete_params = "";
    function open_kirim_barang_modal(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            
            if(content[row]["pemenuhan_status_brg"].toLowerCase() == "perjalanan"){
                delete_params = "&id_brg="+content[row]["id"];
                $("#id_delete").val(content[row]["id_pengiriman"]);
                $("#id_brg_pemenuhan_delete").val(content[row]["id"]);
                $("#brg_nama_delete").html(content[row]["nama_brg"]);
                $("#brg_pemenuhan_qty_delete").html(content[row]["pemenuhan_qty_brg"]);
                $("#toko_delete").html(content[row]["nama_toko"]);
                $("#cabang_delete").html(content[row]["daerah_cabang"]);
                $("#tgl_pengiriman_delete").html(content[row]["tgl_pengiriman"]);
                $("#delete_modal").modal("show");
            }
            else if(content[row]["pemenuhan_status_brg"].toLowerCase() == "aktif"){
                $("#id_brg_pemenuhan").val(content[row]["id"]);
                $("#brg_nama").html(content[row]["nama_brg"]);
                $("#brg_pemenuhan_qty").html(content[row]["pemenuhan_qty_brg"]);
                $("#brg_pengiriman_qty").val(content[row]["pemenuhan_qty_brg"]);
                $("#toko").html(content[row]["nama_toko"]);
                $("#cabang").html(content[row]["daerah_cabang"]);
                $("#register_modal").modal("show");
            }
        });
    }
</script>
<?php
$data = array(
    "page_title" => "Pengiriman Permintaan",
    "type" => $type,
    "id_tempat_pengiriman" => $id_tempat_pengiriman,
    "tipe_pengiriman" => $tipe_pengiriman,
);
?>
<?php $this->load->view("_core_script/table_func");?>

<?php $this->load->view("pengiriman_permintaan/f-add-pengiriman_permintaan",$data);?>
<?php $this->load->view("pengiriman_permintaan/f-delete-pengiriman_permintaan",$data);?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>