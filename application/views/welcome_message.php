<?php
$page_title = "Dashboard";
$breadcrumb = array(
    "Dashboard"
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">

        <?php $this->load->view('req/mm_menubar');?>

<!-- Main Content -->
<div class="page-wrapper">

<div class="container-fluid pt-25">
    <div class="col-lg-12 col-sm-12">
        <div class="panel panel-default card-view">
            <div class="panel-heading bg-gradient">
                <div class="pull-left">
                    <h6 class="panel-title txt-light">Dashboard</h6>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class = "panel-body">
                <?php $this->load->view("home/v_daftar_permintaan");?>
            </div>
            <div class="panel-wrapper collapse in">
                <div class="panel-body" style="margin-top:-50px">
                    <div  class="pills-struct mt-40">
                        <ul role="tablist" class="nav nav-pills" id="myTabs_6">
                            <li class="active" role="presentation"><a aria-expanded="true"  data-toggle="tab" role="tab" id="home_tab_6" href="#home_6">Dashboard Owner</a></li>
                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_6" role="tab" href="#profile_6" aria-expanded="false">Dashboard Penjualan</a></li>
                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_7" role="tab" href="#profile_7" aria-expanded="false">Dashboard Pembelian</a></li>
                            <li role="presentation" class=""><a  data-toggle="tab" id="profile_tab_8" role="tab" href="#profile_8" aria-expanded="false">Dashboard Keuangan</a></li>
                        </ul>
                        <div class="tab-content" id="myTabContent_6">
                            <div  id="home_6" class="tab-pane fade active in" role="tabpanel">
                            <!-- Panel -->
                                <div class="col-lg-12">
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                        <div class="panel panel-info card-view">
                                            <div class="panel-heading">
                                                <div class="pull-left">
                                                <h6 class="panel-title txt-light">Total Penjualan</h6>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div  class="panel-wrapper collapse in">
                                                <div  class="panel-body">
                                                <h5 class="text-center">RP <?php echo number_format($total_penjualan) ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                        <div class="panel panel-primary card-view">
                                            <div class="panel-heading">
                                                <div class="pull-left">
                                                <h6 class="panel-title txt-light">Total Pembelian</h6>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div  class="panel-wrapper collapse in">
                                                <div  class="panel-body">
                                                <h5 class="text-center">RP <?php echo number_format($total_pembelian) ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                        <div class="panel panel-warning card-view">
                                            <div class="panel-heading">
                                                <div class="pull-left">
                                                <h6 class="panel-title txt-light">Total Customer</h6>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div  class="panel-wrapper collapse in">
                                                <div  class="panel-body">
                                                <h5 class="text-center"><?php echo number_format($total_customer) ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                        <div class="panel panel-danger card-view">
                                            <div class="panel-heading">
                                                <div class="pull-left">
                                                <h6 class="panel-title txt-light">Total Produk</h6>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div  class="panel-wrapper collapse in">
                                                <div  class="panel-body">
                                                <h5 class="text-center"><?php echo number_format($total_produk) ?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- Tutup Panel -->
                            <!-- Laba  -->
                                <div class="col-lg-12">
                                    <div class="col-lg-6 col-md-6 col-xs-12">
                                        <div class="panel panel-default card-view pt-0 bg-gradient1">
                                        <div class="panel-wrapper collapse in">
                                            <div class="panel-body pa-0">
                                            <div class="sm-data-box bg-white">
                                                <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                    <span class="txt-dark block counter">Rp <?php echo number_format($laba_bulan_ini) ?></span>
                                                    <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Laba Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-xs-12">
                                        <div class="panel panel-default card-view pt-0 bg-gradient2">
                                            <div class="panel-wrapper collapse in">
                                            <div class="panel-body pa-0">
                                                <div class="sm-data-box bg-white">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                    <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                        <span class="txt-dark block counter">Rp <?php echo number_format($laba_tahun_ini) ?></span>
                                                        <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Laba Tahun Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                    </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- Tutup Laba -->
                            <!-- Top Produk Terjual dan Penjualan Kemarin -->
                            <div class="col-lg-12">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="panel panel-default card-view">
                                      <div class="panel-heading bg-gradient3">
                                        <div class="pull-left">
                                          <h6 class="panel-title txt-dark">Top Produk Terjual</h6>
                                        </div>
                                        <div class="clearfix"></div>
                                      </div>
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body">
                                          <div class="flot-container" style="height:250px">
                                            <div id="flot_pie_chart" class="demo-placeholder"></div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                      <div class="panel panel-primary card-view">
                                          <div class="panel-heading">
                                            <div class="pull-left">
                                              <h6 class="panel-title txt-light">Penjualan Kemarin</h6>
                                            </div>
                                            <div class="clearfix"></div>
                                          </div>
                                          <div  class="panel-wrapper collapse in">
                                            <div  class="panel-body">
                                                <h5 class="pl-0">5 September 2019</h5>
                                                <div class="table-wrap mt-20">
                                                    <div class="table-responsive">
                                                      <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                  <th>Deskripsi</th>
                                                                  <th>Jumlah</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr class="active">
                                                                  <th scope="row">Jumlah Transaksi</th>
                                                                  <td>189 </td>
                                                                </tr>
                                                                <tr class="success">
                                                                  <th scope="row">Nilai Omset</th>
                                                                  <td>Rp 209.898.999</td>
                                                                </tr>
                                                                <tr class="info">
                                                                  <th scope="row">Jumlah Barang</th>
                                                                  <td>650</td>
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
                            <!-- Tutup  Top Produk Terjual dan Penjualan Kemarin-->
                            <!-- Hutang dan Piutang -->
                            <div class="col-lg-12">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="panel panel-primary card-view">
                                        <div class="panel-heading">
                                          <div class="pull-left">
                                            <h6 class="panel-title txt-light">Piutang Pelanggan</h6>
                                          </div>
                                          <div class="clearfix"></div>
                                        </div>
                                        <div  class="panel-wrapper collapse in">
                                          <div  class="panel-body">
                                              <div class="table-wrap">
                                                  <div class="table-responsive">
                                                    <table class="table mb-0">
                                                          <thead>
                                                              <tr>
                                                                <th>Nama Perusahaan</th>
                                                                <th>Jumlah Piutang</th>
                                                              </tr>
                                                          </thead>
                                                          <tbody>
                                                              <tr class="active">
                                                                <th scope="row">PT. Sari Multigirya Sentosa</th>
                                                                <td>Rp 225.000.000 </td>
                                                              </tr>
                                                              <tr class="success">
                                                                <th scope="row">PT. Harapan Inti Persada</th>
                                                                <td>Rp 209.898.999</td>
                                                              </tr>
                                                              <tr class="info">
                                                                <th scope="row">PT. Hakiki Persada</th>
                                                                <td>Rp 156.000.850</td>
                                                              </tr>
                                                              <tr class="warning">
                                                                <th scope="row">PT. Samudera Safety</th>
                                                                <td>Rp 125.000.850</td>
                                                              </tr>
                                                              <tr class="primary">
                                                                <th scope="row">PT. Redo Sejati</th>
                                                                <td>Rp 169.000.000</td>
                                                              </tr>
                                                        </tbody>
                                                    </table>
                                                  </div>
                                              </div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="panel panel-danger card-view">
                                        <div class="panel-heading">
                                          <div class="pull-left">
                                            <h6 class="panel-title txt-light">Top 5 Pelanggan Bulan Ini </h6>
                                          </div>
                                          <div class="clearfix"></div>
                                        </div>
                                        <div  class="panel-wrapper collapse in">
                                          <div class="panel-body mt-80">
                                                <div class="col-sm-7">
                                                     <canvas id="chart_7" height="120px"></canvas>
                                                </div>
                                                <div class="col-sm-5 pr-50 pt-25">
                                                  <div class="label-chatrs">
                                                    <div class="mb-5">
                                                      <span class="clabels inline-block bg-yellow mr-5"></span>
                                                      <span class="clabels-text font-12 inline-block txt-dark capitalize-font">PT. BNN Nasional</span>
                                                    </div>
                                                    <div class="mb-5">
                                                      <span class="clabels inline-block bg-pink mr-5"></span>
                                                      <span class="clabels-text font-12 inline-block txt-dark capitalize-font">PT. KAI Indonesia</span>
                                                    </div>
                                                    <div class="mb-5">
                                                      <span class="clabels inline-block bg-blue mr-5"></span>
                                                      <span class="clabels-text font-12 inline-block txt-dark capitalize-font">Persija Jakarta</span>
                                                    </div>
                                                    <div class="mb-5">
                                                      <span class="clabels inline-block bg-red mr-5"></span>
                                                      <span class="clabels-text font-12 inline-block txt-dark capitalize-font">Wooding Hijab</span>
                                                    </div>
                                                    <div class="">
                                                      <span class="clabels inline-block bg-green mr-5"></span>
                                                      <span class="clabels-text font-12 inline-block txt-dark capitalize-font">PT. Yanjkung</span>
                                                    </div>
                                                  </div>
                                                </div>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Hutang dan Piutang -->
                          </div>
                                                <div  id="profile_6" class="tab-pane fade" role="tabpanel">
                            <!-- Total Penjualan Bulan dan Tahun  -->
                            <div class="col-lg-12">
                                <div class="col-lg-6 col-md-6 col-xs-12 pl-0">
                                    <div class="panel panel-default card-view pt-0 bg-gradient3">
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body pa-0">
                                          <div class="sm-data-box bg-white">
                                            <div class="container-fluid">
                                              <div class="row">
                                                <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                  <span class="txt-dark block counter">Rp 2.453.659.541</span>
                                                  <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Penjualan Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                               </div>
                               <div class="col-lg-6 col-md-6 col-xs-12 pl-0">
                                   <div class="panel panel-default card-view pt-0 bg-gradient2">
                                     <div class="panel-wrapper collapse in">
                                       <div class="panel-body pa-0">
                                         <div class="sm-data-box bg-white">
                                           <div class="container-fluid">
                                             <div class="row">
                                               <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                 <span class="txt-dark block counter">Rp 658.000.000</span>
                                                 <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Penjualan Tahun Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                               </div>
                                             </div>
                                           </div>
                                         </div>
                                       </div>
                                     </div>
                                   </div>
                              </div>
                            </div>
                            <!-- Tutup Total Penjualan Bulan dan Tahun -->
                            <!-- Total Produk dan Piutang -->
                            <div class="col-lg-12">
                                <div class="col-lg-3 col-md-12 col-xs-12 pl-0" >
                                    <div class="panel panel-default card-view pt-0" style="background-color:#9cc4d9 !important">
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body pa-0">
                                          <div class="sm-data-box bg-white">
                                            <div class="container-fluid">
                                              <div class="row">
                                                <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                  <span class="counter-anim txt-dark block counter">265</span>
                                                  <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Invoice Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12 col-xs-12 pl-0" >
                                    <div class="panel panel-default card-view pt-0" style="background-color:#b19cd9 !important">
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body pa-0">
                                          <div class="sm-data-box bg-white">
                                            <div class="container-fluid">
                                              <div class="row">
                                                <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                  <span class="counter-anim txt-dark block counter">1650</span>
                                                  <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Barang Terjual Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12 col-xs-12 pl-0" >
                                    <div class="panel panel-default card-view pt-0" style="background-color:#d9b19c !important">
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body pa-0">
                                          <div class="sm-data-box bg-white">
                                            <div class="container-fluid">
                                              <div class="row">
                                                <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                  <span class="txt-dark block counter">Rp 105.350.000.000</span></span>
                                                  <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Piutang Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tutup Total Penjualan Bulan dan Tahun -->
                                                </div>
                          <div  id="profile_7" class="tab-pane fade" role="tabpanel">
                            <!-- Total Penjualan Bulan dan Tahun  -->
                            <div class="col-lg-12">
                                <div class="col-lg-6 col-md-6 col-xs-12 pl-0">
                                    <div class="panel panel-default card-view pt-0 bg-gradient3">
                                      <div class="panel-wrapper collapse in">
                                        <div class="panel-body pa-0">
                                          <div class="sm-data-box bg-white">
                                            <div class="container-fluid">
                                              <div class="row">
                                                <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                  <span class="txt-dark block counter">Rp 356.000.000</span>
                                                  <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Pembelian Bulan Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                               </div>
                               <div class="col-lg-6 col-md-6 col-xs-12 pl-0">
                                   <div class="panel panel-default card-view pt-0 bg-gradient2">
                                     <div class="panel-wrapper collapse in">
                                       <div class="panel-body pa-0">
                                         <div class="sm-data-box bg-white">
                                           <div class="container-fluid">
                                             <div class="row">
                                               <div class="col-xs-12 text-left pl-0 pr-0 data-wrap-left">
                                                 <span class="txt-dark block counter">Rp 1.256.400.000</span>
                                                 <span class="block"><span class="weight-500 uppercase-font txt-grey font-13" style="color:black !important">Pembelian Tahun Ini</span><i class="zmdi zmdi-caret-down txt-danger font-21 ml-5 vertical-align-middle"></i></span>
                                               </div>
                                             </div>
                                           </div>
                                         </div>
                                       </div>
                                     </div>
                                   </div>
                              </div>
                            </div>
                            <!-- Tutup Total Penjualan Bulan dan Tahun -->
                            <!-- Hutang Usaha -->
                            <div class="col-lg-12">
                                           <div class="panel panel-default card-view">
                                              <div class="panel-heading">
                                                  <div class="pull-left">
                                                      <h6 class="panel-title txt-dark">Hutang Pertahun</h6>
                                                  </div>
                                                  <div class="clearfix"></div>
                                              </div>
                                              <div class="panel-wrapper collapse in">
                                                  <div class="panel-body">
                                                      <canvas id="chart_2" height="200"></canvas>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                            <!-- Tutup Hutang Usaha -->
                          </div>
                          <div  id="profile_8" class="tab-pane fade" role="tabpanel">
                            <!-- Panel -->
                            <div class="col-lg-12">
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 pl-0">
                                      <div class="panel panel-info card-view">
                                          <div class="panel-heading" style="background-color:#fd9696 !important">
                                            <div class="pull-left">
                                              <h6 class="panel-title txt-light" >Keuntungan Bulan Ini</h6>
                                            </div>
                                            <div class="clearfix"></div>
                                          </div>
                                          <div  class="panel-wrapper collapse in">
                                            <div  class="panel-body">
                                              <h5 class="text-center">RP 1.250.982.000</h5>
                                            </div>
                                          </div>
                                      </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                      <div class="panel card-view" >
                                          <div class="panel-heading" style="background-color:#fdfd96 !important">
                                            <div class="pull-left">
                                              <h6 class="panel-title txt-light" style="color:black !important">Penjualan Bulan Ini</h6>
                                            </div>
                                            <div class="clearfix"></div>
                                          </div>
                                          <div  class="panel-wrapper collapse in">
                                            <div  class="panel-body">
                                              <h5 class="text-center">RP 5.950.123.000</h5>
                                            </div>
                                          </div>
                                     </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                      <div class="panel panel-warning card-view">
                                          <div class="panel-heading" style="background-color:#fdca96 !important">
                                            <div class="pull-left">
                                              <h6 class="panel-title txt-light" >Piutang Bulan Ini</h6>
                                            </div>
                                            <div class="clearfix"></div>
                                          </div>
                                          <div  class="panel-wrapper collapse in">
                                            <div  class="panel-body">
                                              <h5 class="text-center">Rp 150.300.000</h5>
                                            </div>
                                          </div>
                                     </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                      <div class="panel panel-danger card-view">
                                          <div class="panel-heading" style="background-color:#9696fd !important">
                                            <div class="pull-left">
                                              <h6 class="panel-title txt-light" style="color:black !important">Hutang Bulan Ini</h6>
                                            </div>
                                            <div class="clearfix"></div>
                                          </div>
                                          <div  class="panel-wrapper collapse in">
                                            <div  class="panel-body">
                                              <h5 class="text-center">Rp 546.201.000<</h5>
                                            </div>
                                          </div>
                                     </div>
                                </div>
                            </div>
                            <!-- Tutup Panel -->
                            <!-- Beban Perusahaan -->
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                  <div class="panel panel-default card-view panel-refresh">
                                                <div class="panel-heading">
                                                     <h5>Beban Perusahaan Bulan Ini</h5>
                                                </div>
                                                <div class="panel-wrapper collapse in">
                                                            <div class="panel-body">
                                                                  <div>
                                                                      <canvas id="chart_6" height="180"></canvas>
                                                                  </div>
                                                                  <hr class="light-grey-hr row mt-10 mb-15"/>
                                                                  <div class="label-chatrs">
                                                                      <div class="">
                                                                          <span class="clabels clabels-lg inline-block bg-blue mr-10 pull-left"></span>
                                                                          <span class="clabels-text font-12 inline-block txt-dark capitalize-font pull-left"><span class="block font-15 weight-500 mb-5">9.930.000</span><span class="block txt-grey">Biaya Gaji, Lembur, THR</span></span>
                                                                          <div id="sparkline_1" class="sp-small-chart pull-right"  ></div>
                                                                          <div class="clearfix"></div>
                                                                      </div>
                                                                  </div>
                                                                  <hr class="light-grey-hr row mt-10 mb-15"/>
                                                                  <div class="label-chatrs">
                                                                      <div class="">
                                                                          <span class="clabels clabels-lg inline-block bg-green mr-10 pull-left"></span>
                                                                          <span class="clabels-text font-12 inline-block txt-dark capitalize-font pull-left"><span class="block font-15 weight-500 mb-5">250.000.000</span><span class="block txt-grey">Biaya Sewa Gudang</span></span>
                                                                          <div id="sparkline_2" class="sp-small-chart pull-right" ></div>
                                                                          <div class="clearfix"></div>
                                                                      </div>
                                                                  </div>
                                                                  <hr class="light-grey-hr row mt-10 mb-15"/>
                                                                  <div class="label-chatrs">
                                                                      <div class="">
                                                                          <span class="clabels clabels-lg inline-block bg-yellow mr-10 pull-left"></span>
                                                                          <span class="clabels-text font-12 inline-block txt-dark capitalize-font pull-left"><span class="block font-15 weight-500 mb-5">2.334.300</span><span class="block txt-grey">Biaya Bensin, Tol, Parkir</span></span>
                                                                          <div id="sparkline_3" class="sp-small-chart pull-right" ></div>
                                                                          <div class="clearfix"></div>
                                                                      </div>
                                                                  </div>
                                                         </div>
                                                </div>
                                            </div>
                                      </div>
                            <!-- Tutup Beban Perusahaan -->
                            <!-- List Invoice -->
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                                <div class="panel panel-primary card-view">
                                    <div class="panel-heading">
                                      <div class="pull-left">
                                        <h6 class="panel-title txt-light">Invoice Bulan Ini</h6>
                                      </div>
                                      <div class="clearfix"></div>
                                    </div>
                                    <div  class="panel-wrapper collapse in">
                                      <div  class="panel-body">
                                                      <div class="table-wrap">
                                                                  <div class="table-responsive">
                                                                      <table id="example" class="table table-hover display  pb-30" >
                                                                          <thead>
                                                                              <tr>
                                                                                  <th>Tanggal</th>
                                                                                  <th>Invoice</th>
                                                                                  <th>Pelanggan</th>
                                                                                  <th>Total</th>
                                                                                  <th>Status</th>
                                                                                  <th>Faktur</th>
                                                                              </tr>
                                                                          </thead>

                                                                          <tbody>
                                                                              <tr>
                                                                                  <td>26 September 2019</td>
                                                                                  <td>MM-102-2039</td>
                                                                                  <td>Edinburgh</td>
                                                                                  <td>$320,800</td>
                                                                                  <td>2011/04/25</td>
                                                                                  <td>$320,800</td>
                                                                              </tr>
                                                                              <tr>
                                                                                  <td>28 September 2019</td>
                                                                                  <td>MM-103-2040</td>
                                                                                  <td>Tokyo</td>
                                                                                  <td>$170,750</td>
                                                                                  <td>2011/07/25</td>
                                                                                  <td>$170,750</td>
                                                                              </tr>
                                                    <tr>
                                                                                  <td>26 September 2019</td>
                                                                                  <td>MM-102-2039</td>
                                                                                  <td>Edinburgh</td>
                                                                                  <td>$320,800</td>
                                                                                  <td>2011/04/25</td>
                                                                                  <td>$320,800</td>
                                                                              </tr>
                                                                              <tr>
                                                                                  <td>28 September 2019</td>
                                                                                  <td>MM-103-2040</td>
                                                                                  <td>Tokyo</td>
                                                                                  <td>$170,750</td>
                                                                                  <td>2011/07/25</td>
                                                                                  <td>$170,750</td>
                                                                              </tr>
                                                    <tr>
                                                                                  <td>26 September 2019</td>
                                                                                  <td>MM-102-2039</td>
                                                                                  <td>Edinburgh</td>
                                                                                  <td>$320,800</td>
                                                                                  <td>2011/04/25</td>
                                                                                  <td>$320,800</td>
                                                                              </tr>
                                                                              <tr>
                                                                                  <td>28 September 2019</td>
                                                                                  <td>MM-103-2040</td>
                                                                                  <td>Tokyo</td>
                                                                                  <td>$170,750</td>
                                                                                  <td>2011/07/25</td>
                                                                                  <td>$170,750</td>
                                                                              </tr>
                                                    <tr>
                                                                                  <td>26 September 2019</td>
                                                                                  <td>MM-102-2039</td>
                                                                                  <td>Edinburgh</td>
                                                                                  <td>$320,800</td>
                                                                                  <td>2011/04/25</td>
                                                                                  <td>$320,800</td>
                                                                              </tr>
                                                                              <tr>
                                                                                  <td>28 September 2019</td>
                                                                                  <td>MM-103-2040</td>
                                                                                  <td>Tokyo</td>
                                                                                  <td>$170,750</td>
                                                                                  <td>2011/07/25</td>
                                                                                  <td>$170,750</td>
                                                                              </tr>
                                                    <tr>
                                                                                  <td>26 September 2019</td>
                                                                                  <td>MM-102-2039</td>
                                                                                  <td>Edinburgh</td>
                                                                                  <td>$320,800</td>
                                                                                  <td>2011/04/25</td>
                                                                                  <td>$320,800</td>
                                                                              </tr>
                                                                              <tr>
                                                                                  <td>28 September 2019</td>
                                                                                  <td>MM-103-2040</td>
                                                                                  <td>Tokyo</td>
                                                                                  <td>$170,750</td>
                                                                                  <td>2011/07/25</td>
                                                                                  <td>$170,750</td>
                                                                              </tr>
                                                                          </tbody>
                                                                      </table>
                                                                  </div>
                                                      </div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End List Invoice -->
                          </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                    </div>
            </div>

      </div>

</div>

            <div class="page-wrapper">
                <div class="container-fluid">
                    <div class="row mt-20">
                        
                    </div>


                    <?php $this->load->view('req/mm_footer.php');?>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>
<?php $this->load->view("_core_script/table_func");?>
<script>
window.onload = function() {
    if(!window.location.hash) {
        window.location = window.location + '#loaded';
        window.location.reload();
    }
}
</script>