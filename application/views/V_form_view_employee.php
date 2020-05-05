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
                    <!-- Title -->
                    <div class="row heading-bg">
                        <!-- Breadcrumb -->
                        <div class="col-lg-12 pull-right">
                            <ol class="breadcrumb">
                                <li><a href="<?php echo base_url();?>Master?tab=tabEmp"><span>Master
                                            Employee</span></a></li>
                                <li>Detail Employee</li>
                            </ol>
                        </div>
                        <!-- /Breadcrumb -->
                    </div>
                    <!-- /Title -->

                    <!-- Row -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="form-wrap">
                                            <form action="#">
                                                <h5 class="txt-dark capitalize-font"><i
                                                        class="fa fa-info-circle inline-block mr-10"></i> Personal
                                                    Information</h5>
                                                <hr class="light-grey-hr" />
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Suffix Employee</label>
                                                            <select class="form-control" data-placeholder="Pilih Suffix"
                                                                tabindex="1" name="emp_suf" readonly>
                                                                <option value="Mr.">Mr.</option>
                                                                <option value="Mrs.">Mrs.</option>
                                                                <option value="Ms.">Ms.</option>
                                                                <option value="Ibu">Ibu</option>
                                                                <option value="Bapak">Bapak</option>
                                                                <option value="Nona">Nona</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Full Name</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Nama Lengkap" name="emp_name"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!-- Row -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Gender</label>
                                                            <select class="form-control" data-placeholder="Pilih Gender"
                                                                tabindex="1" name="emp_gender" readonly>
                                                                <option value="Perempuan">Perempuan</option>
                                                                <option value="Laki-Laki">Laki-Laki</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Toko</label>
                                                            <select class="form-control" data-placeholder="Pilih Toko"
                                                                tabindex="1" name="emp_toko" readonly>
                                                                <option value="Toko 1">Toko 1</option>
                                                                <option value="Toko 2">Toko 2</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!--/row-->
                                                <!-- Row -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Mobile</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Mobile Phone" name="emp_phone"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Email</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Email" name="emp_email" readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!--/row-->
                                                <!-- Row -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">NPWP</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Nomor NPWP" name="emp_npwp" readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">KTP</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Nomor KTP" name="emp_ktp" readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!--/row-->
                                                <!-- Row -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Jabatan</label>
                                                            <select class="form-control"
                                                                data-placeholder="Pilih Jabatan" tabindex="1"
                                                                name="emp_jabatan" readonly>
                                                                <option value="Kepala Gudang">Kepala Gudang</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Gaji</label>
                                                            <div class="input-group">
                                                                <div class="input-group-addon"><i class="ti-money"></i>
                                                                </div>
                                                                <input type="text" class="form-control"
                                                                    id="exampleInputuname"
                                                                    placeholder="Input Nominal Gaji" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!--/row-->
                                                <!-- Row -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Kode Pos</label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Input Kode Pos" name="emp_kodePos"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="control-label mb-10">Alamat</label>
                                                            <textarea class="form-control" rows="4" name="emp_addr"
                                                                readonly>Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. but the majority have suffered alteration in some form, by injected humour</textarea>
                                                        </div>
                                                    </div>
                                                    <!--/span-->
                                                </div>
                                                <!--/row-->

                                                <div class="seprator-block"></div>
                                                <h5 class="txt-dark capitalize-font"><i
                                                        class="fa fa-info-circle inline-block mr-10"></i> Image or File
                                                    Upload</h5>
                                                <hr class="light-grey-hr" />

                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <ul id="portfolio_1" class="portf project-gallery"
                                                            style="width:200px;height:200px;">
                                                            <li class=" item tall"
                                                                data-src="<?php echo base_url();?>asset/images/img_default_card.jpg">
                                                                <a href="">
                                                                    <img class="img-responsive"
                                                                        src="<?php echo base_url();?>asset/images/img_default_card.jpg"
                                                                        alt="upload_img">
                                                                    <span class="hover-cap">Profile Picture</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <ul id="portfolio_2" class="portf project-gallery"
                                                            style="width:200px;height:200px;">
                                                            <li class=" item tall"
                                                                data-src="<?php echo base_url();?>asset/images/img_default_profile.jpg">
                                                                <a href="">
                                                                    <img class="img-responsive"
                                                                        src="<?php echo base_url();?>asset/images/img_default_profile.jpg"
                                                                        alt="upload_img">
                                                                    <span class="hover-cap">KTP</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <ul id="portfolio_3" class="portf project-gallery"
                                                            style="width:200px;height:200px;">
                                                            <li class=" item tall"
                                                                data-src="<?php echo base_url();?>asset/images/img_default_npwp.jpg">
                                                                <a href="">
                                                                    <img class="img-responsive"
                                                                        src="<?php echo base_url();?>asset/images/img_default_npwp.jpg"
                                                                        alt="upload_img">
                                                                    <span class="hover-cap">NPWP</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <ul id="portfolio_1" class="portf project-gallery" data-col="4"
                                                            style="width:200px;height:200px;">
                                                            <li class="item tall"
                                                                data-src="<?php echo base_url();?>asset/images/img_default_etc.jpg">
                                                                <a href="">
                                                                    <img class="img-responsive"
                                                                        src="<?php echo base_url();?>asset/images/img_default_etc.jpg"
                                                                        alt="upload_img">
                                                                    <span class="hover-cap">Other File</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <button type="button" class="btn btn-warning pull-left"
                                                        onclick="window.location.href='<?php echo base_url();?>Master?tab=tabEmp';">CANCEL</button>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </form>
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
        <script>
        $(function() {
            $(' a').imageLightbox();
        });
        </script>
    </body>

</html>