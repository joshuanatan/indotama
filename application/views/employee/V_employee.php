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
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#tambah_employee"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Karyawan</span></button>
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
                                                                <?php 
                                                                for($x=0; $x<count($view_employee); $x++){ ?>
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
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_employee<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        
                                                                    </td>
                                                                </tr>
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
$('#yes_enddate').click(function() {
   $("#emp_enddate").show();
   $("#emp_enddate").prop('required',true);
});
$('#no_enddate').click(function() {
    $("#emp_enddate").hide();
    $("#emp_enddate").prop('required',false);
});

$('#yes_enddate2').click(function() {
   $("#emp_enddate2").show();
   $("#emp_enddate2").prop('required',true);
});
$('#no_enddate2').click(function() {
    $("#emp_enddate2").hide();
    $("#emp_enddate2").prop('required',false);
});
</script>
<?php
$this->load->view("employee/f-add-employee");
for($x=0; $x<count($view_employee); $x++){
    $data = array(
        "x" => $x,
        "ID_PK_EMPLOYEE" => $view_employee[$x]["ID_PK_EMPLOYEE"],
        "EMP_NAMA" => $view_employee[$x]["EMP_NAMA"],
        "EMP_NPWP" => $view_employee[$x]["EMP_NPWP"],
        "EMP_KTP" => $view_employee[$x]["EMP_KTP"],
        "EMP_HP" => $view_employee[$x]["EMP_HP"],
        "EMP_ALAMAT" => $view_employee[$x]["EMP_ALAMAT"],
        "EMP_KODE_POS" => $view_employee[$x]["EMP_KODE_POS"],
        "EMP_GAJI" => $view_employee[$x]["EMP_GAJI"],
        "EMP_STARTDATE" => $view_employee[$x]["EMP_STARTDATE"],
        "EMP_ENDDATE" => $view_employee[$x]["EMP_ENDDATE"],
        "EMP_REK" => $view_employee[$x]["EMP_REK"],
        "EMP_GENDER" => $view_employee[$x]["EMP_GENDER"],
        "EMP_SUFF" => $view_employee[$x]["EMP_SUFF"],
        "ID_FK_TOKO" => $view_employee[$x]["ID_FK_TOKO"],
    );
    $this->load->view("employee/f-update-employee",$data);
    $this->load->view("employee/f-delete-employee",$data);
}
?>