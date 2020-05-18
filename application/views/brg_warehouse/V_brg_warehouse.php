<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('req/mm_css.php');?>
</head>

<body>
	<!--Preloader-->
	<div class="preloader-it">
		<div class="la-anim-1"></div>
	</div>
	<!--/Preloader-->
    <div class="wrapper theme-1-active pimary-color-pink">

        <!-- Menu Bar -->
        <?php $this->load->view('req/mm_menubar.php');?>
        <!-- /Menu Bar -->

		<!-- Main Content -->
		<div class="page-wrapper">
			<div class="container-fluid">
				<!-- Row -->
				<div class="row mt-30">
					<div class="col-sm-12">
                        <div class="panel panel-default card-view">
                                <div class="panel-heading" style="background-color:black !important;">
                                    <div class="pull-left">
                                    <h6 class="panel-title txt-light">Daftar Barang<br>'<?php echo $warehouse[0]['WAREHOUSE_NAMA'] ?>'</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div  class="panel-body">
                                        <div class="row mt-10 ">
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#register_modal"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Daftar Barang</span></button>
                                        </div>

                                        <br>
                                        <div  class="pills-struct vertical-pills">
                                            <div class="tab-content" id="myTabContent_10">
                                                <div  id="home_10" class="tab-pane fade active in" role="tabpanel">
                                                    <div class="table-wrap">
                                                        <div class="table-responsive">
                                                            <table id="example" class="table table-hover display  pb-30">
                                                                <thead>
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Nama barang</th>
                                                                        <th>Jenis Barang</th>
                                                                        <th>Merek Barang</th>
                                                                        <th>Notes</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                
                                                                <tbody style="font-size:10px !important">
                                                                <?php 
                                                                for($x=0; $x<count($view_barang_wh); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['BRG_NAMA'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['BRG_JENIS_NAMA'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['BRG_MERK_NAMA'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['BRG_WAREHOUSE_NOTES'] ?></td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_brg_warehouse<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_brg_warehouse<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        
                                                                    </td>
                                                                </tr>
<div class = "modal fade" id = "edit_brg_warehouse<?php echo $x+1 ?>">
<div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Employee</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/edit_brg_warehouse">
                    <input type="hidden" name="id_pk_brg_warehouse" value="$view_brg_wh[$x]['ID_PK_BRG_WAREHOUSE'] ?>"> 
                    <datalist class="form-control">
                        <option value="Edge">
                        <option value="Firefox">
                        <option value="Chrome">
                        <option value="Opera">
                        <option value="Safari">
                    </datalist>
                   
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class = "modal fade" id = "hapus_brg_warehouse<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Customer</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/hapus_brg_warehouse">
                    <input type="hidden" name="id_pk_brg_warehouse" value="<?php echo $view_brg_wh[$x]['ID_PK_BRG_WAREHOUSE'] ?>"> 
                    <input type="hidden" name="id_fk_warehouse" value="<?php echo $view_brg_wh[$x]['ID_FK_WAREHOUSE'] ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus barang dari warehouse '<?php echo $warehouse[0]['WAREHOUSE_NAMA'] ?>' dengan nama: "<b><?php echo $view_brg_wh[$x]['BRG_NAMA'] ?></b>"?</h5>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Yakin">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
                                                                <?php } ?>
                                                                </tbody>
                                                                <tfoot>
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Nama barang</th>
                                                                        <th>Jenis Barang</th>
                                                                        <th>Merek Barang</th>
                                                                        <th>Notes</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
        </div>
    </div>
    <!-- /Row -->
</div>

			<!-- Footer -->
			<?php $this->load->view('req/mm_footer.php');?>
			<!-- /Footer -->

		</div>
		<!-- /Main Content -->

    </div>
    <!-- /#wrapper -->

	<!-- JavaScript -->

	<?php $this->load->view('req/mm_js.php');?>

</body>

</html>

<script>
    var ctrl = "barang_warehouse";
    var url_add = "";
</script>
<?php
$data = array(
    "page_title" => "Barang Warehouse"
);
?>
<?php $this->load->view("_core_script/register_func");?>
<?php $this->load->view("brg_warehouse/f-add-brg_warehouse",$data);?>

<datalist id = 'daftar_barang'></datalist>
<script>
    window.onfocus = function(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html+="<option value = '"+respond['content'][a]["nama"]+"'></option>";
                    }
                    $("#daftar_barang").html(html);
                }
            }
        });
    }
</script>