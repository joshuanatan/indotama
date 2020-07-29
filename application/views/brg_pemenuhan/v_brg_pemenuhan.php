<?php
$page_title = "Permintaan Cabang Lain";
$breadcrumb = array(
    "Permintaan Cabang Lain"
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
                                               <a href = "<?php echo base_url();?>permintaan" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" id="pemenuhan_saya" style = "margin-right:10px">Lihat Permintaan Saya</a>
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-eye"></i><b> - Details </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-plus"></i><b> - Berikan Barang </b>   
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
    var ctrl = "pemenuhan";
    var url_add = "type=<?php echo $type;?>";
    var unautorized_button = ["edit_button","delete_button"];
    var additional_button = [
        {
            data_toggle:'modal',
            data_target:'#register_modal',
            style:'cursor:pointer;font-size:large',
            class:'text-primary md-plus',
            onclick: 'load_edit_content()'
        }
    ];
</script>
<?php
$data = array(
    "page_title" => "Pemberian",
    "type" => $type
);
?>
<?php $this->load->view("brg_pemenuhan/f-insert-brg-pemenuhan",$data);?>
<?php $this->load->view("brg_pemenuhan/f-detail-brg-pemenuhan",$data);?>

<?php $this->load->view("req/core_script");?>
<?php $this->load->view('_notification/notif_general'); ?>

<?php $this->load->view("_core_script/table_func");?>