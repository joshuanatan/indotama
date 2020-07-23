<?php
$page_title = "Barang Cabang";
$breadcrumb = array(
    "Master","Nama Toko: <b>".$toko[0]["toko_nama"]."</b>","Daerah: <b>".$cabang[0]["cabang_daerah"]."</b>","Stok"
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
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?> <?php echo $cabang[0]["cabang_daerah"];?></h6>
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
                                            <div class = "d-block">
                                                <a href = "<?php echo base_url();?>toko/cabang_toko" style = "margin-right:10px" class = "btn btn-danger btn-sm col-lg-2 col-sm-12">Kembali ke Daftar Cabang</a>
                                            </div>
                                            <br/>
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
    var ctrl = "barang_cabang";
    var url_add = "id_cabang=<?php echo $cabang[0]["id_pk_cabang"];?>";
    var unautorized_button = ["edit_button","detail_button","delete_button"];
</script>

<?php
$data = array(
    "page_title" => "Daftar Barang Cabang",
    "cabang" => $cabang
);
?>
<?php $this->load->view("brg_cabang/f-add-brg_cabang",$data);?>
<?php $this->load->view("brg_cabang/f-update-brg_cabang",$data);?>
<?php $this->load->view("brg_cabang/f-delete-brg_cabang",$data);?>
<?php $this->load->view("brg_cabang/f-detail-brg_cabang",$data);?>

<?php $this->load->view("_base_element/datalist_barang");?>
<script>
    function load_datalist(){
        load_datalist_barang();
    }
</script>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>
<?php $this->load->view("_core_script/table_func");?>