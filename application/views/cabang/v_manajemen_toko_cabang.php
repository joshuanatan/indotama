<?php
#dibuat untuk view yang dari manajemen cabang di manajemen toko bukan yang dari master
$page_title = "Cabang";
$breadcrumb = array(
    "Nama Toko: ".$toko[0]["toko_nama"],"Daftar Cabang"
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
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?> <?php echo $toko[0]["toko_nama"];?></h6>
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
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
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
    var ctrl = "cabang";
    var url_add = "id_toko=<?php echo $toko[0]["id_pk_toko"];?>";
    var additional_button = [
        {
            style:'cursor:pointer;font-size:large',
            class:'text-success md-store',
            onclick:'open_list_barang()'
        },
        {
            style:'cursor:pointer;font-size:large',
            class:'text-warning md-assignment-account',
            onclick:'redirect_admin_cabang()'
        }
    ];
</script>
<?php
$data = array(
    "page_title" => "Cabang",
    "toko" => $toko
);
?>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("_core_script/register_func");?>
<?php $this->load->view("_core_script/update_func");?>
<?php $this->load->view("_core_script/delete_func");?>
<?php $this->load->view("cabang/f-add-cabang",$data);?>
<?php $this->load->view("cabang/f-update-cabang",$data);?>
<?php $this->load->view("cabang/f-delete-cabang",$data);?>

<script>
    function open_list_barang(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_cabang = content[row]["id"];
            window.open("<?php echo base_url();?>toko/brg_cabang_toko/"+id_cabang);
        });
    }
    function redirect_admin_cabang(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_cabang = content[row]["id"];
            window.location.replace("<?php echo base_url();?>toko/admin_cabang_toko/"+id_cabang);
        });
    }
</script>

<?php $this->load->view('_notification/notif_general'); ?>