<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('mm_css.php');?>
</head>

<body>
	<!--Preloader-->
	<div class="preloader-it">
		<div class="la-anim-1"></div>
	</div>
	<!--/Preloader-->
    <div class="wrapper theme-1-active pimary-color-pink">

    <?php $this->load->view('mm_menubar.php');?>

		<!-- Main Content -->
		<div class="page-wrapper">
            <div class="container-fluid">
              <!-- Row -->
        				<div class="row mt-20">
        					<div class="col-lg-12 col-sm-12">
        						<div class="panel panel-default card-view">
        							<div class="panel-heading" style="background-color:black !important;">
        								<div class="pull-left">
        									<h6 class="panel-title txt-light">Stok</h6>
        								</div>
        								<div class="clearfix"></div>
        							</div>
        							<div class="panel-wrapper collapse in">
        								<div class="panel-body">
        									<div  class="tab-struct custom-tab-1">
        										<ul role="tablist" class="nav nav-tabs" id="myTabs_7">
        											<li class="active" role="presentation"><a aria-expanded="true"  data-toggle="tab" role="tab" id="home_tab_7" href="#home_7"><i class="fa fa-truck"></i><span class="right-nav-text" style="margin-left:20px">Jenis Produk</span></a></li>
                              <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_9" role="tab" href="#profile_9" aria-expanded="false"><i class="fa fa-truck"></i><span class="right-nav-text" style="margin-left:20px">Stok Opname</span></a></li>
                              <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false"><i class="fa fa-truck"></i><span class="right-nav-text" style="margin-left:20px">Mutasi Stok</span></a></li>
        										</ul>
        										<div class="tab-content" id="myTabContent_7">
        											<div  id="home_7" class="tab-pane fade active in" role="tabpanel">
                                      <!-- Row Jenis Produk -->
                                      <div class="row">
                                            <div class="col-sm-12">
                                              <div class="panel panel-default card-view">
                                                <div class="panel-heading">
                                                  <div class="row mt-10 ">
                                                        <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Jenis Produk</span></button></a>
                                                  </div>
                                                </div>
                                                <div class="panel-wrapper collapse in">
                                                  <div class="panel-body">
                                                    <div class="table-wrap">
                                                      <div class="table-responsive">
                                                        <table id="example" class="table table-hover display  pb-30" >
                                                          <thead>
                                                            <tr>
                                                                <th>Jenis Produk</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                          </thead>
                                                          <tbody>
                                                            <tr>
                                                              <td>Coverall</td>
                                                              <td class="text-center">
                                                                    <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
                                                                    <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>Sepatu</td>
                                                              <td class="text-center">
                                                                    <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
                                                                    <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>Rompi</td>
                                                              <td class="text-center">
                                                                    <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
                                                                    <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                              </td>
                                                            </tr>
                                                            <tr>
                                                              <td>Bodyharness</td>
                                                              <td class="text-center">
                                                                    <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
                                                                    <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                      </div>
                                      <!-- End Row Jenis Produk-->
        											</div>
                              <div  id="profile_9" class="tab-pane fade" role="tabpanel">
                                <!-- Row -->
                                  <div class="row">
                                    <div class="col-sm-12">
                                      <div class="panel panel-default card-view">
                                        <div class="panel-heading">
                                          <div class="row mt-10 ">
                                                <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Stok Opname</span></button></a>
                                          </div>
                                        </div>
                                        <div class="panel-wrapper collapse in">
                                          <div class="panel-body">
                                            <div class="table-wrap">
                                              <div class="table-responsive">
                                                <table id="datable_1" class="table table-hover display  pb-30" >
                                                  <thead >
                                                    <tr>
                                                      <th>Tanggal</th>
                                                      <th>Kode Opname</th>
                                                      <th>Gudang</th>
                                                      <th>PIC</th>
                                                      <th>Status</th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-012-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-success">Disetujui</span> </td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-013-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span> </td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-014-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-015-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-015-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>OP-015-2202</td>
                                                      <td>LTC</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <!-- /Row -->
        											</div>
                              <div  id="profile_10" class="tab-pane fade" role="tabpanel">
                                  <!-- Row -->
                                  <div class="row">
                                    <div class="col-sm-12">
                                      <div class="panel panel-default card-view">
                                        <div class="panel-heading">
                                          <div class="row mt-10 ">
                                                <a href="#"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Stok Mutasi</span></button></a>
                                          </div>
                                        </div>
                                        <div class="panel-wrapper collapse in">
                                          <div class="panel-body">
                                            <div class="table-wrap">
                                              <div class="table-responsive">
                                                <table id="datable_1" class="table table-hover display  pb-30" >
                                                  <thead>
                                                    <tr>
                                                      <th>Tanggal</th>
                                                      <th>Kode Mutasi</th>
                                                      <th>Gudang Asal</th>
                                                      <th>Pengirim</th>
                                                      <th>Gudang Tujuan</th>
                                                      <th>Penerima</th>
                                                      <th>Status</th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-012-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-success">Selesai</span> </td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-013-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-warning">Proses</span> </td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-014-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-014-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-015-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                    <tr>
                                                      <td>26-08-2019</td>
                                                      <td>MUT-014-2202</td>
                                                      <td>LTC</td>
                                                      <td>Amin</td>
                                                      <td>Benhil</td>
                                                      <td>Santo</td>
                                                      <td class="text-center"><span class="label label-danger">Ditolak</span></td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <!-- /Row -->
        											</div>
        										</div>
        									</div>
        								</div>
        							</div>
        						</div>
        					</div>
        				</div>
        				<!-- /Row -->

                <!-- Footer -->
          			<?php $this->load->view('mm_footer.php');?>
          			<!-- /Footer -->
			     </div>
		</div>
        <!-- /Main Content -->

    </div>
    <!-- /#wrapper -->

    <!-- JavaScript -->

    <?php $this->load->view('mm_js.php');?>

</body>

</html>
