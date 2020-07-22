<?php
$page_title = "Master Barang";
$breadcrumb = array(
    "Master","Barang"
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
                                            <div class = "d-block">
                                                <button type = "button" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" data-toggle = "modal" data-target = "#register_modal" style = "margin-right:10px">Tambah <?php echo ucwords($page_title);?></button>
                                                <a href = "<?php echo base_url();?>plugin/excel/barang" class = "btn btn-success btn-sm col-lg-2 col-sm-12" target = "_blank" style = "margin-right:10px">Download Excel</a>
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
    var ctrl = "barang";
    var url_add = "";
</script>
<?php
$data = array(
    "page_title" => "Master Barang"
);
?>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("_core_script/register_func");?>
<?php $this->load->view("_core_script/update_func");?>
<?php $this->load->view("_core_script/delete_func");?>
<?php $this->load->view("barang/f-add-barang",$data);?>
<?php $this->load->view("barang/f-update-barang",$data);?>
<?php $this->load->view("barang/f-delete-barang",$data);?>
<?php $this->load->view("barang/f-detail-barang",$data);?>
<?php $this->load->view("_base_element/datalist_barang_jenis");?>
<?php $this->load->view("_base_element/datalist_barang_merk");?>
<?php $this->load->view("_base_element/datalist_barang");?>
<?php $this->load->view("_base_element/datalist_satuan");?>
<script>
    function load_datalist(){
        load_datalist_barang_jenis();
        load_datalist_barang_merk();
        load_datalist_barang();
        load_datalist_satuan();
    }
</script>

<?php $this->load->view('_notification/notif_general'); ?>

<!-- number format -->
<script src = "<?php echo base_url();?>asset/custom/number_formatter.js"></script>