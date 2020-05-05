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
                  <h6 class="panel-title txt-light">Produk</h6>
                </div>
                <div class="clearfix"></div>
              </div>
							<div class="panel-wrapper collapse in">
								<div  class="panel-body">
                  <div class="row mt-10 ">
                        <a href="add-products.php"><button class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i class="fa fa-pencil"></i><span class="btn-text">Tambah Produk</span></button></a>
                  </div>
                  <br>
									<div  class="pills-struct vertical-pills">
										<ul role="tablist" class="nav nav-pills ver-nav-pills" id="myTabs_10">
											<li class="active" role="presentation"><a aria-expanded="true"  data-toggle="tab" role="tab" id="home_tab_10" href="#home_10">Sepatu</a></li>
											<li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Coverall</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Rompi</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Bodyharness</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Security</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Helm</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Marka Jalan</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Sarung Tangan</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Jas Hujan</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Masker</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Kacamata</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Earplug Earmuff</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Pemadam</a></li>
                                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">Lainnya</a></li>

										</ul>
										<div class="tab-content" id="myTabContent_10">
											<div  id="home_10" class="tab-pane fade active in" role="tabpanel">
												<div class="table-wrap">
										              <div class="table-responsive">
											             <table id="example" class="table table-hover display  pb-30" >
												<thead>
													<tr>
														<th>Produk</th>
														<th>Kode</th>
														<th>Harga Toko</th>
                                                        <th>Harga Jual</th>
														<th>Catatan</th>
														<th>Deskripsi</th>
                                                        <th>Size</th>
                                                        <th>Warna</th>
                                                        <th>Aksi</th>
													</tr>
												</thead>

												<tbody style="font-size:10px !important">
                                                    <tr>
														<td>Red Parker</td>
														<td>RP181</td>
														<td>Rp 165.000</td>
                                                        <td>Rp 200.000</td>
														<td>Pendek Tali</td>
														<td>MM Safety Customer</td>
                                                        <td>S,M,L,XL,XXL</td>
                                                        <td>Merah, Kuning, Hijau, Biru</td>
														<td class="text-center">
                                                            <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
													        <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                        </td>
													</tr>
                                                    <tr>
														<td>Red Parker</td>
														<td>T187</td>
														<td>Rp 260.000</td>
                                                        <td>Rp 325.000</td>
														<td>PDL</td>
														<td>MM Safety Customer</td>
                                                        <td>S,M,L,XL,XXL</td>
                                                        <td>Merah, Kuning, Hijau, Biru</td>
														<td class="text-center">
                                                            <button class="btn btn-primary btn-icon-anim btn-square"><i class="fa fa-pencil"></i></button>
													        <button class="btn btn-danger btn-icon-anim btn-square"><i class="icon-trash"></i></button>
                                                        </td>
													</tr>


												</tbody>

												<tfoot>
													<tr>
														<th>Produk</th>
														<th>Kode</th>
														<th>Harga Toko</th>
                                                        <th>Harga Jual</th>
														<th>Catatan</th>
														<th>Deskripsi</th>
                                                        <th>Size</th>
                                                        <th>Warna</th>
                                                        <th>Aksi</th>
													</tr>
												</tfoot>
											</table>
										              </div>
									            </div>
											</div>
											<div  id="profile_10" class="tab-pane fade" role="tabpanel">
												<p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.</p>
											</div>
											<div  id="dropdown_19" class="tab-pane fade " role="tabpanel">
												<p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade.</p>
											</div>
											<div  id="dropdown_20" class="tab-pane fade" role="tabpanel">
												<p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party before they sold out master cleanse gluten-free squid scenester freegan cosby sweater.</p>
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
