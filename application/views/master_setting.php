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
                <div class="col-lg-12 col-sm-12 mt-20">
                  <div class="panel panel-default card-view">
                    <div class="panel-heading" style="background-color:black !important;">
                      <div class="pull-left">
                        <h6 class="panel-title txt-light">System Setting</h6>
                      </div>
                      <div class="clearfix"></div>
                    </div>
                    <div class="panel-wrapper collapse in">
                      <div  class="panel-body">
                        <div  class="tab-struct vertical-tab custom-tab-1 mt-40">
                          <ul role="tablist" class="nav nav-tabs ver-nav-tab" id="myTabs_8">
                            <li class="active" role="presentation"><a aria-expanded="true"  data-toggle="tab" role="tab" id="home_tab_8" href="#user_mng">User Management</a></li>
                            <li class="dropdown" role="presentation">
                              <a  data-toggle="dropdown" class="dropdown-toggle" id="myTabDrop_8" href="#" aria-expanded="false">Sales Template<span class="caret"></span></a>
                              <ul id="myTabDrop_8_contents"  class="dropdown-menu">
                                <li class=""><a  data-toggle="tab" id="dropdown_15_tab" role="tab" href="#dropdown_15" aria-expanded="true">Invoice</a></li>
                                <li class=""><a  data-toggle="tab" id="dropdown_16_tab" role="tab" href="#dropdown_16" aria-expanded="false">Penawaran</a></li>
                                <li class=""><a  data-toggle="tab" id="dropdown_16_tab" role="tab" href="#dropdown_17" aria-expanded="false">Order</a></li>
                              </ul>
                            </li>
                          </ul>
                          <div class="tab-content" id="myTabContent_8">
                            <div  id="user_mng" class="tab-pane fade active in" role="tabpanel">
                                  <div class="row">
                                      <a href="add-user-mng.php"><button class="btn btn-warning btn-anim pull-right" style="margin-left:10px !important"><i class="fa fa-pencil"></i><span class="btn-text">Tambah List User</span></button></a>
                                  </div>
                                  <br>
                									<div class="table-wrap">
                										<div class="table-responsive">
                											<table id="datable_1" class="table table-hover display pb-30" >
                												<thead>
                													<tr>
                														<th>Nama</th>
                														<th>Email</th>
                														<th>Roles</th>
                														<th>Status</th>
                                            <th>Aksi</th>
                													</tr>
                												</thead>
                												<tbody>
                                            <tr>
                  														<td>Andre</td>
                  														<td>andre.msm@gmail.com</td>
                  														<td>Owner</td>
                  														<td>Active</td>
                  														<td class="text-center">
                                                   <button class="btn btn-info btn-icon-anim btn-square" data-toggle="modal" data-target="#exampleModal"><i class="fa fa-eye"></i></button>
                                                   <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
                                              </td>
                													</tr>
                												</tbody>
                											</table>
                										</div>
                									</div>
                                  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
											                  <div class="modal-dialog" role="document">
                    												<div class="modal-content">
                                              <div class="modal-header">
                      														<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      														<h5>User Management Detail</h5>
													                    </div>
                    													<div class="modal-body">
                                                <div class="form-wrap">
                                                     <form class="form-horizontal" role="form">
                                                          <div class="form-body">
                                                              <div class="row">
                                                                  <div class="col-md-12">
                                                                    <div class="form-group">
                                                                      <label class="control-label col-md-4 pull-left">Nama</label>
                                                                      <div class="col-md-8">
                                                                          <p class="form-control-static">Andre</p>
                                                                      </div>
                                                                    </div>
                                                                  </div>
                                                              </div>
                                                              <div class="row">
                                                                 <div class="col-md-12">
                                                                   <div class="form-group">
                                                                     <label class="control-label col-md-4 pull-left">Email</label>
                                                                     <div class="col-md-8">
                                                                         <p class="form-control-static">andre.msm@gmail.com</p>
                                                                     </div>
                                                                   </div>
                                                                 </div>
                                                              </div>
                                                              <div class="row">
                                                                 <div class="col-md-12">
                                                                   <div class="form-group">
                                                                     <label class="control-label col-md-4 pull-left">Roles</label>
                                                                     <div class="col-md-8">
                                                                         <p class="form-control-static">Owner</p>
                                                                     </div>
                                                                   </div>
                                                                 </div>
                                                              </div>
                                                              <div class="row">
                                                                 <div class="col-md-12">
                                                                   <div class="form-group">
                                                                     <label class="control-label col-md-4 pull-left">Status</label>
                                                                     <div class="col-md-8">
                                                                         <p class="form-control-static">Active</p>
                                                                     </div>
                                                                   </div>
                                                                 </div>
                                                              </div>
                                                              <div class="row">
                                                                 <div class="col-md-12">
                                                                   <div class="form-group">
                                                                     <label class="control-label col-md-4 pull-left">Access Right</label>
                                                                     <div class="col-md-8">
                                                                         <p class="form-control-static">Penjualan, Pembelian, Dashboard Owner</p>
                                                                     </div>
                                                                   </div>
                                                                 </div>
                                                              </div>
                    													            </div>
                                                    </form>
                                                </div>
                    												  </div>
                    									     </div>
										                    </div>
                                  </div>
                            </div>
                            <div  id="dropdown_15" class="tab-pane fade" role="tabpanel">
                              <p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.</p>
                            </div>
                            <div  id="dropdown_16" class="tab-pane fade" role="tabpanel">
                              <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party before they sold out master cleanse gluten-free squid scenester freegan cosby sweater.</p>
                            </div>
                            <div  id="dropdown_17" class="tab-pane fade" role="tabpanel">
                              <p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party before they sold out master cleanse gluten-free squid scenester freegan cosby sweater.</p>
                            </div>
                          </div>
                         </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>

      				<!-- Footer -->
      				<?php $this->load->view('mm_footer.php');?>
      				<!-- /Footer -->
		</div>
	</div>
        <!-- /Main Content -->

    <!-- /#wrapper -->

	<!-- JavaScript -->
  <?php $this->load->view('mm_js.php');?>


</body>

</html>
