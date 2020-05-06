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
                                    <h6 class="panel-title txt-light">Karyawan</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div  class="panel-body">
                                        <div class="row mt-10 ">
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#tambah_jabatan"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Karyawan</span></button>
                                        </div>
<div class = "modal fade" id = "tambah_jabatan">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Karyawan</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>jabatan/register_jabatan">
                    <div class = "form-group">
                        <h5>Nama Jabatan</h5>
                        <input type="text" class="form-control" name="jabatan_nama" required>
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
                                                                        <th>Jabatan</th>
                                                                        <th>Status Jabatan</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody style="font-size:10px !important">
                                                                <?php for($x=0; $x<count($view_jabatan); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td><?php echo $view_jabatan[$x]['JABATAN_NAMA'] ?></td>
                                                                    <td><?php echo $view_jabatan[$x]['JABATAN_STATUS'] ?></td>
                                                                    <td class="text-center">
                                                                        <?php if($view_jabatan[$x]['JABATAN_STATUS']=="AKTIF"){ ?>
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_jabatan<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_jabatan<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        <?php } ?>
                                                                    </td>
                                                                </tr>
<div class = "modal fade" id = "edit_jabatan<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Jabatan</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>jabatan/edit_jabatan">
                    <input type="hidden" name="id_pk_jabatan" value="<?php echo $view_jabatan[$x]['ID_PK_JABATAN'] ?>"> 
                    <div class = "form-group">
                        <h5>Nama Jabatan</h5>
                        <input type="text" class="form-control" name="jabatan_nama" value="<?php echo $view_jabatan[$x]['JABATAN_NAMA'] ?>" required>
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

<div class = "modal fade" id = "hapus_jabatan<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <b><h4 class = "modal-title">Hapus Jabatan</h4></b>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>jabatan/hapus_jabatan">
                    <input type="hidden" name="id_pk_jabatan" value="<?php echo $view_jabatan[$x]['ID_PK_JABATAN'] ?>"> 
                    <div class = "form-group">
                        <h5 style="text-align:center">Apakah anda yakin akan menghapus jabatan dengan nama: "<b><?php echo $view_jabatan[$x]['JABATAN_NAMA'] ?></b>"?</h5>
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
                                                                        <th>Jabatan</th>
                                                                        <th>Status Jabatan</th>
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
