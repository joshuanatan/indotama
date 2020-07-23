<?php
$page_title = "Pengiriman";
$breadcrumb = array(
    "Pengiriman"
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
                                            <div class = "form-inline">
                                                <select class = "form-control form-sm" id = "tipe_pengiriman" style = "width:20%">
                                                    <option value = "pembelian">Pengiriman Penjualan</option>
                                                    <option value = "retur">Pengiriman Retur</option>
                                                </select>
                                                <button type = "button" onclick = "redirect_tipe_pengiriman()" class = "btn btn-primary btn-sm">Buka</button>
                                            </div>
                                            <br/>
                                            <div class = "d-block">
                                                <button type = "button" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" data-toggle = "modal" data-target = "#register_modal" style = "margin-right:10px">Tambah <?php echo ucwords($page_title);?></button>
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-eye"></i><b> - Details </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
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
    var ctrl = "pengiriman";
    var url_add = "type=<?php echo $type;?>";
</script>
<?php
$data = array(
    "page_title" => "Pengiriman",
    "type" => $type,
    "id_tempat_pengiriman" => $id_tempat_pengiriman
);
?>
<?php $this->load->view("_core_script/table_func");?>

<?php $this->load->view("pengiriman/f-add-pengiriman",$data);?>
<?php $this->load->view("pengiriman/f-update-pengiriman",$data);?>
<?php $this->load->view("pengiriman/f-delete-pengiriman",$data);?>
<?php $this->load->view("pengiriman/f-detail-pengiriman",$data);?>


<?php $this->load->view("_base_element/datalist_penjualan");?>
<?php $this->load->view("_base_element/datalist_satuan");?>
<script>
    function load_datalist(){
        load_datalist_penjualan();
        load_datalist_satuan();
    }
</script>
<script>
    function redirect_tipe_pengiriman(){
        var tipe_pengiriman = $("#tipe_pengiriman").val();
        window.location.href = "<?php echo base_url();?>pengiriman/"+tipe_pengiriman;
    }
</script>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>