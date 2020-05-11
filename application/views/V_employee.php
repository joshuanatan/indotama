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
                <form method="POST" action="<?php echo base_url() ?>employee/register_employee"  enctype="multipart/form-data">
                    <div class = "form-group">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="emp_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>NPWP</h5>
                        <input type="text" class="form-control" name="emp_npwp" required>
                    </div>
                    <div class = "form-group">
                        <h5>KTP</h5>
                        <input type="text" class="form-control" name="emp_ktp" required>
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="emp_hp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="emp_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>Kode Pos</h5>
                        <input type="number" class="form-control" name="emp_kode_pos" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="emp_foto_npwp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto KTP</h5>
                        <input type="file" class="form-control" name="emp_foto_ktp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto Lain</h5>
                        <input type="file" class="form-control" name="emp_foto_lain" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto</h5>
                        <input type="file" class="form-control" name="emp_foto" required>
                    </div>
                    <div class = "form-group">
                        <h5>Gaji Karyawan</h5>
                        <input type="number" class="form-control" name="emp_gaji" required>
                    </div>
                    <div class = "form-group">
                        <h5>Mulai Bekerja</h5>
                        <input type="date" class="form-control" name="emp_startdate" required>
                    </div>
                    <div class = "form-group">
                        <h5>Akhir Bekerja</h5>
                        <input type="radio" name="radio_enddate" id="no_enddate" value="MASIH" checked>Masih Bekerja
                        <br><input type="radio" value="TIDAK" name="radio_enddate" id="yes_enddate">Tidak bekerja sejak:
                        <input type="date" style="display:none" class="form-control" id="emp_enddate" name="emp_enddate" required>
                    </div>
                    <div class = "form-group">
                        <h5>Rekening Bank</h5>
                        <input type="text" class="form-control" name="emp_rek" required>
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Kelamin</h5>
                        <input type="radio" name="emp_gender" value="PRIA" checked>Pria 
                        <input type="radio" name="emp_gender" value="WANITA">Wanita
                    </div>
                    <div class = "form-group">
                        <h5>Panggilan</h5>
                        <select name="emp_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <h5>Toko</h5>
                        <select class="form-control">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($x=0 ; $x<count($toko); $x++){ ?>
                                <option value="<?php echo $toko[$x]['ID_PK_TOKO'] ?>"><?php echo $toko[$x]['TOKO_NAMA']?></option>
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
                                                                        <th>Foto</th>
                                                                        <th>Nama</th>
                                                                        <th>Toko</th>
                                                                        <th>HP</th>
                                                                        <th>Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody style="font-size:10px !important">
                                                                <?php for($x=0; $x<count($view_employee); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td>
                                                                    <img width="100px" src="<?php echo base_url() ?>asset/images/employee/foto/<?php echo $view_employee[$x]['EMP_FOTO'] ?>">
                                                                    </td>
                                                                    <td><?php echo $view_employee[$x]['EMP_NAMA'] ?></td>
                                                                    <td>
                                                                    <?php for($y=0; $y<count($toko_all); $y++){ 
                                                                        if($toko_all[$y]['ID_PK_TOKO']==$view_employee[$x]['ID_FK_TOKO']){
                                                                            ?>
                                                                            <?php echo $toko_all[$y]['TOKO_NAMA'] ?>
                                                                    <?php }?>
                                                                    <?php } ?>
                                                                    </td>
                                                                    <td><?php echo $view_employee[$x]['EMP_HP'] ?></td>
                                                                    <td class="text-center">
                                                                       
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_employee<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_jabatan<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        
                                                                    </td>
                                                                </tr>
<div class = "modal fade" id = "edit_employee<?php echo $x+1 ?>">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Edit Employee</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>jabatan/edit_jabatan">
                    <input type="hidden" name="id_pk_employee" value="<?php echo $view_employee[$x]['ID_PK_EMPLOYEE'] ?>"> 
                    <div class = "form-group">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" value="<?php echo $view_employee[$x]['EMP_NAMA'] ?>" name="emp_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>NPWP</h5>
                        <input type="text" class="form-control" value="<?php echo $view_employee[$x]['EMP_NPWP'] ?>"  name="emp_npwp" required>
                    </div>
                    <div class = "form-group">
                        <h5>KTP</h5>
                        <input type="text" value="<?php echo $view_employee[$x]['emp_ktp'] ?>"  class="form-control" name="EMP_KTP" required>
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" value="<?php echo $view_employee[$x]['EMP_HP'] ?>"  name="emp_hp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" value="<?php echo $view_employee[$x]['EMP_ALAMAT'] ?>" name="emp_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>Kode Pos</h5>
                        <input type="number" class="form-control" value="<?php echo $view_employee[$x]['EMP_KODE_POS'] ?>" name="emp_kode_pos" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="emp_foto_npwp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto KTP</h5>
                        <input type="file" class="form-control" name="emp_foto_ktp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto Lain</h5>
                        <input type="file" class="form-control" name="emp_foto_lain" required>
                    </div>
                    <div class = "form-group">
                        <h5>Foto</h5>
                        <input type="file" class="form-control" name="emp_foto" required>
                    </div>
                    <div class = "form-group">
                        <h5>Gaji Karyawan</h5>
                        <input type="number" class="form-control" value="<?php echo $view_employee[$x]['EMP_GAJI'] ?>" name="emp_gaji" required>
                    </div>
                    <div class = "form-group">
                        <h5>Mulai Bekerja</h5>
                        <input type="date"  value="<?php echo $view_employee[$x]['EMP_STARTDATE'] ?>"  class="form-control" name="emp_startdate" required>
                    </div>
                    <div class = "form-group">
                        <h5>Akhir Bekerja</h5>
                        <input type="radio" name="radio_enddate" id="no_enddate" value="MASIH" checked>Masih Bekerja
                        <br><input type="radio" value="TIDAK" name="radio_enddate" id="yes_enddate">Tidak bekerja sejak:
                        <input type="date" style="display:none" class="form-control" id="emp_enddate" name="emp_enddate" required>
                    </div>
                    <div class = "form-group">
                        <h5>Rekening Bank</h5>
                        <input type="text" class="form-control" name="emp_rek" required>
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Kelamin</h5>
                        <input type="radio" name="emp_gender" value="PRIA" checked>Pria 
                        <input type="radio" name="emp_gender" value="WANITA">Wanita
                    </div>
                    <div class = "form-group">
                        <h5>Panggilan</h5>
                        <select name="emp_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <h5>Toko</h5>
                        <select class="form-control">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($x=0 ; $x<count($toko); $x++){ ?>
                                <option value="<?php echo $toko[$x]['ID_PK_TOKO'] ?>"><?php echo $toko[$x]['TOKO_NAMA']?></option>
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
<script>
$('#yes_enddate').click(function() {
   $("#emp_enddate").show();
});
$('#no_enddate').click(function() {
    $("#emp_enddate").hide();
});
</script>