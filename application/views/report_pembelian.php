<!DOCTYPE html>
<html lang="en">
<head>
	<?php $this->load->view('mm_css.php');?>
</head>

<body>
	<!-- Preloader -->
	<div class="preloader-it">
		<div class="la-anim-1"></div>
	</div>
	<!-- /Preloader -->
    <div class="wrapper theme-1-active pimary-color-pink">

    <?php $this->load->view('mm_menubar.php');?>

    <!-- Main Content -->
		<div class="page-wrapper">
        <div class="container-fluid pt-25">
    				<!-- Row -->
    				<div class="row">
    					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    						<div class="panel panel-default card-view pt-0 bg-gradient">
    							<div class="panel-wrapper collapse in">
    								<div class="panel-body pa-0">
    									<div class="sm-data-box bg-white">
    										<div class="container-fluid">
    											<div class="row">
    												<div class="col-xs-6 text-left pl-0 pr-0 data-wrap-left">
    													<span class="txt-light block counter"><span>LAPORAN</span></span>
    													<span class="block"><span class="weight-500 uppercase-font txt-light font-13">Pembelian Berdasarkan Jumlah</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
    												</div>
                            <div class="col-xs-6 text-left  pl-0 pr-0 pt-25 data-wrap-right">
													        <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-eye"></i><span class="btn-text">Lihat Laporan</span></button></a>
												    </div>
    											</div>
    										</div>
    									</div>
    								</div>
    							</div>
    						</div>
    						<div class="panel panel-default card-view pt-0 bg-gradient1">
    							<div class="panel-wrapper collapse in">
    								<div class="panel-body pa-0">
    									<div class="sm-data-box bg-white">
    										<div class="container-fluid">
                          <div class="row">
    												<div class="col-xs-6 text-left pl-0 pr-0 data-wrap-left">
    													<span class="txt-light block counter"><span>LAPORAN</span></span>
    													<span class="block"><span class="weight-500 uppercase-font txt-light font-13">Pembelian Berdasarkan Supplier</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
    												</div>
                            <div class="col-xs-6 text-left  pl-0 pr-0 pt-25 data-wrap-right">
													        <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-eye"></i><span class="btn-text">Lihat Laporan</span></button></a>
												    </div>
    											</div>
    										</div>
    									</div>
    								</div>
    							</div>
    						</div>
    					</div>
    				</div>
    				<!-- Row -->
            <!-- Row -->
    				<div class="row">
    					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    						<div class="panel panel-default card-view pt-0 bg-gradient2">
    							<div class="panel-wrapper collapse in">
    								<div class="panel-body pa-0">
    									<div class="sm-data-box bg-white">
    										<div class="container-fluid">
                          <div class="row">
                            <div class="col-xs-6 text-left pl-0 pr-0 data-wrap-left">
                              <span class="txt-light block counter"><span>LAPORAN</span></span>
                              <span class="block"><span class="weight-500 uppercase-font txt-light font-13">Pembelian Berdasarkan Quantity</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                            </div>
                            <div class="col-xs-6 text-left  pl-0 pr-0 pt-25 data-wrap-right">
                                  <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-eye"></i><span class="btn-text">Lihat Laporan</span></button></a>
                            </div>
                          </div>
    										</div>
    									</div>
    								</div>
    							</div>
    						</div>
    						<div class="panel panel-default card-view pt-0 bg-gradient3">
    							<div class="panel-wrapper collapse in">
    								<div class="panel-body pa-0">
    									<div class="sm-data-box bg-white">
    										<div class="container-fluid">
                          <div class="row">
                            <div class="col-xs-6 text-left pl-0 pr-0 data-wrap-left">
                              <span class="txt-light block counter"><span>LAPORAN</span></span>
                              <span class="block"><span class="weight-500 uppercase-font txt-light font-13">Rincian Pembelian Barang</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                            </div>
                            <div class="col-xs-6 text-left  pl-0 pr-0 pt-25 data-wrap-right">
                                  <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-eye"></i><span class="btn-text">Lihat Laporan</span></button></a>
                            </div>
                          </div>
    										</div>
    									</div>
    								</div>
    							</div>
    						</div>
    					</div>
    				</div>
    				<!-- Row -->

			  </div>

		<?php $this->load->view('mm_footer.php');?>


		</div>
        <!-- /Main Content -->

    </div>
    <!-- /#wrapper -->

	<!-- JavaScript -->

	<?php $this->load->view('mm_js.php');?>
</body>

</html>
