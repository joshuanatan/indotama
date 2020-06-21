<?php
$page_title = "Admin Gudang";
$breadcrumb = array(
    "Master",$warehouse[0]["warehouse_nama"],"Admin"
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

            <?php $this->load->view('req/mm_menubar');?>

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
                                                <a href = "<?php echo base_url();?>warehouse" style = "margin-right:10px" class = "btn btn-danger btn-sm col-lg-2 col-sm-12">Kembali ke Daftar Gudang</a>
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
    var ctrl = "warehouse_admin";
    var url_add = "id_warehouse=<?php echo $warehouse[0]["id_pk_warehouse"];?>";
    var unautorized_button = ["detail_button"];
</script>
<?php 
$data = array(
    "page_title" => "Admin Gudang",
    "warehouse" => $warehouse
);
?>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("_core_script/register_func");?>
<?php $this->load->view("_core_script/update_func");?>
<?php $this->load->view("_core_script/delete_func");?>
<?php $this->load->view('warehouse_admin/f-add-warehouse_admin',$data);?>
<?php $this->load->view('warehouse_admin/f-update-warehouse_admin',$data);?>
<?php $this->load->view('warehouse_admin/f-delete-warehouse_admin',$data);?>

<datalist id = 'daftar_user'></datalist>
<script>
    window.onload = function(){
        $.ajax({
            url:"<?php echo base_url();?>ws/user/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html+="<option value = '"+respond['content'][a]["name"]+"'></option>";
                    }
                    $("#daftar_user").html(html);
                }
            }
        });
    }
    window.onfocus = function(){
        $.ajax({
            url:"<?php echo base_url();?>ws/user/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html+="<option value = '"+respond['content'][a]["name"]+"'></option>";
                    }
                    $("#daftar_user").html(html);
                }
            }
        });
    }
</script>
<?php $this->load->view('_notification/notif_general'); ?>