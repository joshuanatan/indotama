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
                                                                        <th>Nama</th>
                                                                        <th>Perusahaan</th>
                                                                        <th>Email</th>
                                                                        <th>No Telp</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                
                                                                <tbody style="font-size:10px !important">
                                                                <?php 
                                                                for($x=0; $x<count($view_barang_wh); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td>
                                                                    <td><?php echo $view_barang_wh[$x]['CUST_NAME'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['CUST_PERUSAHAAN'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['CUST_EMAIL'] ?></td>
                                                                    <td><?php echo $view_barang_wh[$x]['CUST_TELP'] ?></td>
                                                                    <td class="text-center">
                                                                       
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_customer<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_customer<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        
                                                                    </td>
                                                                </tr>
<div class = "modal fade" id = "edit_customer<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Employee</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>customer/edit_customer">
                    <input type="hidden" name="id_pk_cust" value="<?php echo $view_barang_wh[$x]['ID_PK_CUST'] ?>"> 
                    <div class = "form-group">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_NAME'] ?>" name="cust_name" required>
                    </div>
                    <div class = "form-group">
                        <h5>Perusahaan</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_PERUSAHAAN'] ?>" name="cust_perusahaan" required>
                    </div>
                    <div class = "form-group">
                        <h5>Email</h5>
                        <input type="email" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_EMAIL'] ?>" name="cust_email" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_TELP'] ?>" name="cust_telp" required>
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_HP'] ?>" name="cust_hp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_ALAMAT'] ?>" name="cust_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" value="<?php echo $view_barang_wh[$x]['CUST_KETERANGAN'] ?>" name="cust_keterangan" required>
                    </div>
                    <div class = "form-group">
                        <h5>Toko</h5>
                        <select class="form-control" name="id_fk_toko">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($p=0 ; $p<count($toko); $p++){ ?>
                                <option value="<?php echo $toko[$p]['ID_PK_TOKO'] ?>"  <?php  if($view_barang_wh[$x]['ID_FK_TOKO']==$toko[$p]['ID_PK_TOKO']){echo "selected";} ?>><?php echo $toko[$p]['TOKO_NAMA']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type = "submit" class = "btn btn-sm btn-primary" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class = "modal fade" id = "hapus_customer<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Customer</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>customer/hapus_customer">
                    <input type="hidden" name="id_pk_cust" value="<?php echo $view_barang_wh[$x]['ID_PK_CUST'] ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus customer dengan nama: "<b><?php echo $view_barang_wh[$x]['CUST_NAME'] ?></b>"?</h5>
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
                                                                        <th>Foto</th>
                                                                        <th>Nama</th>
                                                                        <th>Toko</th>
                                                                        <th>HP</th>
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