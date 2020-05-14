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
                                    <h6 class="panel-title txt-light">Warehouse</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div  class="panel-body">
                                        <div class="row mt-10 ">
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#tambah_warehouse"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Warehouse</span></button>
                                        </div>
<div class = "modal fade" id = "tambah_warehouse">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Warehouse</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/register_warehouse">
                    <div class = "form-group">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type="submit" class = "btn btn-sm btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                                                                        <th>Nama Warehouse</th>
                                                                        <th>Alamat</th>
                                                                        <th>No Telp</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                
                                                                <tbody style="font-size:10px !important">
                                                                <?php 
                                                                for($x=0; $x<count($view_warehouse); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td>
                                                                    <td><?php echo $view_warehouse[$x]['WAREHOUSE_NAMA'] ?></td>
                                                                    <td><?php echo $view_warehouse[$x]['WAREHOUSE_ALAMAT'] ?></td>
                                                                    <td><?php echo $view_warehouse[$x]['WAREHOUSE_NOTELP'] ?></td>
                                                                    <td class="text-center">
                                                                       
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_warehouse<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_warehouse<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        <a href="<?php echo base_url() ?>warehouse/warehouse_barang/<?php echo $view_warehouse[$x]['ID_PK_WAREHOUSE'] ?>" ><button class="btn btn-success btn-icon-anim btn-square" 
                                                                        ><i class="icon-eye"></i></button></a>
                                                                        
                                                                    </td>
                                                                </tr>
<div class = "modal fade" id = "edit_warehouse<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Warehouse</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/edit_warehouse">
                    <input type="hidden" name="id_pk_warehouse" value="<?php echo $view_warehouse[$x]['ID_PK_WAREHOUSE'] ?>"> 
                    <div class = "form-group">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" value="<?php echo $view_warehouse[$x]['WAREHOUSE_NAMA'] ?>" name="warehouse_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" value="<?php echo $view_warehouse[$x]['WAREHOUSE_ALAMAT'] ?>" name="warehouse_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" value="<?php echo $view_warehouse[$x]['WAREHOUSE_NOTELP'] ?>" name="warehouse_notelp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" value="<?php echo $view_warehouse[$x]['WAREHOUSE_DESC'] ?>" name="warehouse_desc" required>
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

<div class = "modal fade" id = "hapus_warehouse<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Warehouse</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>warehouse/hapus_warehouse">
                    <input type="hidden" name="id_pk_warehouse" value="<?php echo $view_warehouse[$x]['ID_PK_WAREHOUSE'] ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus warehouse dengan nama: "<b><?php echo $view_warehouse[$x]['WAREHOUSE_NAMA'] ?></b>"?</h5>
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