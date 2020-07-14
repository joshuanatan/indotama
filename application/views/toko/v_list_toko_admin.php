<?php
$page_title = "Toko";
$breadcrumb = array(
    "Toko"
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
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "md-wrench"></i><b> - Aktivasi Toko untuk Manajemen </b>   
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
    var ctrl = "toko";
    var url_add = "";
    var custom_contentCtrl = "list_toko_admin";
    var custom_tblHeaderCtrl = "columns_toko_admin";
    var unautorized_button = ["edit_button","delete_button","detail_button"];
    var additional_button = [
        {
            class:"md-wrench",
            style:"cursor:pointer",
            onclick:"activate_toko_manajemen()"
        }
    ];
</script>
<?php $this->load->view("_core_script/table_func");?>
<script>
    function activate_toko_manajemen(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_toko = content[row]["id"];
            if(confirm("Apakah anda ingin mengaktifkan toko "+content[row]["nama"]+"?")){
                window.location.replace("<?php echo base_url();?>toko/activate_toko_manajemen/"+id_toko);
            }
        });
    }
</script>
<?php $this->load->view('_notification/notif_general'); ?>
