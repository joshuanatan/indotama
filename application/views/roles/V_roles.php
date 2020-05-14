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
                                    <h6 class="panel-title txt-light">Jabatan</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div  class="panel-body">
                                        <div class="row mt-10 ">
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#tambah_jabatan"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Jabatan</span></button>
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
<?php
$this->load->view("roles/f-add-roles");
for($x=0; $x<count($view_jabatan); $x++){
    $data = array(
        "x" => $x,
        "ID_PK_JABATAN" => $view_jabatan[$x]["ID_PK_JABATAN"],
        "JABATAN_NAMA" => $view_jabatan[$x]["JABATAN_NAMA"],
    );
    $this->load->view("roles/f-update-roles",$data);
    $this->load->view("roles/f-delete-roles",$data);
}
?>