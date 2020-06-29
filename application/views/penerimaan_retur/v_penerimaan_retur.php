<?php
$page_title = "Penerimaan Retur";
$breadcrumb = array(
    "Penerimaan Retur"
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
                                            <div class = "form-inline">
                                                <select class = "form-control form-sm" id = "tipe_penerimaan" style = "width:20%">
                                                    <option value = "retur">Penerimaan Retur</option>
                                                    <option value = "pembelian">Penerimaan Pembelian</option>
                                                </select>
                                                <button type = "button" onclick = "redirect_tipe_penerimaan()" class = "btn btn-primary btn-sm">Buka</button>
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
    var ctrl = "penerimaan"; 
    var custom_tblHeaderCtrl = "columns?tipe_penerimaan=<?php echo $tipe_penerimaan;?>"; 
    var url_add = "type=<?php echo $type;?>&tipe_penerimaan=<?php echo $tipe_penerimaan;?>";
</script>
<script>
    function redirect_tipe_penerimaan(){
        var tipe_penerimaan = $("#tipe_penerimaan").val();
        window.location.href = "<?php echo base_url();?>penerimaan/"+tipe_penerimaan;
    }
</script>
<?php
$data = array(
    "page_title" => "Penerimaan Retur"
);
?>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("_core_script/register_func");?>
<?php $this->load->view("_core_script/update_func");?>
<?php $this->load->view("_core_script/delete_func");?>
<?php $this->load->view("penerimaan_retur/f-add-penerimaan_retur",$data);?>
<?php $this->load->view("penerimaan_retur/f-update-penerimaan_retur",$data);?>
<?php $this->load->view("penerimaan_retur/f-detail-penerimaan_retur",$data);?>
<?php $this->load->view("penerimaan_retur/f-delete-penerimaan_retur",$data);?>

<?php $this->load->view('_notification/notif_general'); ?>

<?php $this->load->view("_base_element/datalist_retur");?>
<?php $this->load->view("_base_element/datalist_satuan");?>
<script>
    function load_datalist(){
        load_datalist_retur();
        load_datalist_satuan();
    }
</script>