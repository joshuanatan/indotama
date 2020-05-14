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
                                    <h6 class="panel-title txt-light">Customer</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div  class="panel-body">
                                        <div class="row mt-10 ">
                                            <button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important" data-toggle = "modal" data-target = "#tambah_customer"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Customer</span></button>
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
                                                                for($x=0; $x<count($view_customer); $x++){ ?>
                                                                <tr>
                                                                    <td><?php echo $x+1 ?></td>
                                                                    <td>
                                                                    <td><?php echo $view_customer[$x]['CUST_NAME'] ?></td>
                                                                    <td><?php echo $view_customer[$x]['CUST_PERUSAHAAN'] ?></td>
                                                                    <td><?php echo $view_customer[$x]['CUST_EMAIL'] ?></td>
                                                                    <td><?php echo $view_customer[$x]['CUST_TELP'] ?></td>
                                                                    <td class="text-center">
                                                                       
                                                                        <button class="btn btn-primary btn-icon-anim btn-square"  data-toggle = "modal" data-target = "#edit_customer<?php echo $x+1 ?>"><i class="fa fa-pencil"></i></button>
                                                                        <button class="btn btn-danger btn-icon-anim btn-square" data-toggle = "modal" data-target = "#hapus_customer<?php echo $x+1 ?>"><i class="icon-trash"></i></button>
                                                                        
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
<?php
$this->load->view("customer/f-add-customer");
for($x=0; $x<count($view_customer); $x++){
    $data = array(
        "toko" => $toko,
        "x" => $x,
        "ID_PK_CUST" => $view_customer[$x]["ID_PK_CUST"],
        "CUST_NAME" => $view_customer[$x]["CUST_NAME"],
        "CUST_PERUSAHAAN" => $view_customer[$x]["CUST_PERUSAHAAN"],
        "CUST_EMAIL" => $view_customer[$x]["CUST_EMAIL"],
        "CUST_TELP" => $view_customer[$x]["CUST_TELP"],
        "CUST_HP" => $view_customer[$x]["CUST_HP"],
        "CUST_ALAMAT" => $view_customer[$x]["CUST_ALAMAT"],
        "CUST_KETERANGAN" => $view_customer[$x]["CUST_KETERANGAN"],
        "ID_FK_TOKO" => $view_customer[$x]["ID_FK_TOKO"],
    );
    $this->load->view("customer/f-update-customer",$data);
    $this->load->view("customer/f-delete-customer",$data);
}
?>