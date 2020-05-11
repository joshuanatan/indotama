<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="mobile-only-brand pull-left">
        <div class="nav-header pull-left">
            <div class="logo-wrap">
                <a href="dashboard.php">
                    <span class="brand-text"
                        style="font-size:20px !important;margin-top:10px !important; color:#625BD6 !important; margin-left:20px !important">INDOTAMA</span>
                </a>
            </div>
        </div>
    </div>

    <div id="mobile_only_nav" class="mobile-only-nav pull-right">
        <ul class="nav navbar-right top-nav pull-right">
            <li>
                <a href="setting.php"><i class="zmdi zmdi-settings top-nav-icon"></i></a>
            </li>
            <li>
                <a href="complain.php"><i class="fa fa-commenting top-nav-icon"></i></a>
            </li>
            <li class="dropdown alert-drp">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="zmdi zmdi-notifications top-nav-icon"></i><span class="top-nav-icon-badge">5</span>
                </a>
                <ul class="dropdown-menu alert-dropdown" data-dropdown-in="bounceIn" data-dropdown-out="bounceOut">
                    <li>
                        <div class="notification-box-head-wrap">
                            <span class="notification-box-head pull-left inline-block">notifications</span>
                            <a class="txt-danger pull-right clear-notifications inline-block" href="javascript:void(0)">
                                clear all </a>
                            <div class="clearfix"></div>
                            <hr class="light-grey-hr ma-0" />
                        </div>
                    </li>
                    <li>
                        <div class="streamline message-nicescroll-bar">
                            <div class="sl-item">
                                <a href="javascript:void(0)">
                                    <div class="icon bg-green">
                                        <i class="zmdi zmdi-flag"></i>
                                    </div>
                                    <div class="sl-content">
                                        <span
                                            class="inline-block capitalize-font  pull-left truncate head-notifications">
                                            New subscription created</span>
                                        <span class="inline-block font-11  pull-right notifications-time">2pm</span>
                                        <div class="clearfix"></div>
                                        <p class="truncate">Your customer subscribed for the basic plan. The customer
                                            will pay $25 per month.</p>
                                    </div>
                                </a>
                            </div>
                            <hr class="light-grey-hr ma-0" />
                            <div class="sl-item">
                                <a href="javascript:void(0)">
                                    <div class="icon bg-yellow">
                                        <i class="zmdi zmdi-trending-down"></i>
                                    </div>
                                    <div class="sl-content">
                                        <span
                                            class="inline-block capitalize-font  pull-left truncate head-notifications txt-warning">Server
                                            #2 not responding</span>
                                        <span class="inline-block font-11 pull-right notifications-time">1pm</span>
                                        <div class="clearfix"></div>
                                        <p class="truncate">Some technical error occurred needs to be resolved.</p>
                                    </div>
                                </a>
                            </div>
                            <hr class="light-grey-hr ma-0" />
                            <div class="sl-item">
                                <a href="javascript:void(0)">
                                    <div class="icon bg-blue">
                                        <i class="zmdi zmdi-email"></i>
                                    </div>
                                    <div class="sl-content">
                                        <span
                                            class="inline-block capitalize-font  pull-left truncate head-notifications">2
                                            new messages</span>
                                        <span class="inline-block font-11  pull-right notifications-time">4pm</span>
                                        <div class="clearfix"></div>
                                        <p class="truncate"> The last payment for your G Suite Basic subscription
                                            failed.</p>
                                    </div>
                                </a>
                            </div>
                            <hr class="light-grey-hr ma-0" />
                            <div class="sl-item">
                                <a href="javascript:void(0)">
                                    <div class="sl-avatar">
                                        <img class="img-responsive" src="<?php echo base_url(); ?>asset/img/avatar.jpg"
                                            alt="avatar" />
                                    </div>
                                    <div class="sl-content">
                                        <span
                                            class="inline-block capitalize-font  pull-left truncate head-notifications">Sandy
                                            Doe</span>
                                        <span class="inline-block font-11  pull-right notifications-time">1pm</span>
                                        <div class="clearfix"></div>
                                        <p class="truncate">Neque porro quisquam est qui dolorem ipsum quia dolor sit
                                            amet, consectetur, adipisci velit</p>
                                    </div>
                                </a>
                            </div>
                            <hr class="light-grey-hr ma-0" />
                            <div class="sl-item">
                                <a href="javascript:void(0)">
                                    <div class="icon bg-red">
                                        <i class="zmdi zmdi-storage"></i>
                                    </div>
                                    <div class="sl-content">
                                        <span
                                            class="inline-block capitalize-font  pull-left truncate head-notifications txt-danger">99%
                                            server space occupied.</span>
                                        <span class="inline-block font-11  pull-right notifications-time">1pm</span>
                                        <div class="clearfix"></div>
                                        <p class="truncate">consectetur, adipisci velit.</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="notification-box-bottom-wrap">
                            <hr class="light-grey-hr ma-0" />
                            <a class="block text-center read-all" href="javascript:void(0)"> read all </a>
                            <div class="clearfix"></div>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="dropdown auth-drp">
                <a href="#" class="dropdown-toggle pr-0" data-toggle="dropdown"><img
                        src="<?php echo base_url(); ?>asset/img/user1.png" alt="user_auth"
                        class="user-auth-img img-circle" /><span class="user-online-status"></span></a>
                <ul class="dropdown-menu user-auth-dropdown" data-dropdown-in="flipInX" data-dropdown-out="flipOutX">
                    <li>
                        <a href="profile.html"><i class="zmdi zmdi-account"></i><span>Profile</span></a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="#"><i class="zmdi zmdi-power"></i><span>Log Out</span></a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- /Top Menu Items -->

<!-- Left Sidebar Menu -->
<div class="fixed-sidebar-left">
    <ul class="nav navbar-nav side-nav nicescroll-bar">
        <!-- User Profile -->
        <li>
            <div class="user-profile text-center">
                <img src="<?php echo base_url(); ?>asset/img/user1.png" alt="user_auth"
                    class="user-auth-img img-circle" />
                <div class="dropdown mt-5">
                    <a href="#" class="dropdown-toggle pr-0 bg-transparent" data-toggle="dropdown">Andre Octo</a>
                </div>
            </div>
        </li>
        <!-- /User Profile -->

        <li>
            <a href="<?php echo base_url();?>HQ/login">
                <div class="pull-left"><i data-icon="a" class="linea-icon linea-basic"></i><span class="right-nav-text"
                        style="margin-left:20px">DASHBOARD</span></div>
                <div class="clearfix"></div>
            </a>
        </li>

        <li>
            <a href="<?php echo base_url();?>Request">
                <div class="pull-left"><i data-icon="&#xe017;" class="linea-icon linea-basic"></i><span
                        class="right-nav-text" style="margin-left:20px">REQUEST BARANG</span></div>
                <div class="clearfix"></div>
            </a>
        </li>

        <li>
            <a href="<?php echo base_url();?>Penjualan">
                <div class="pull-left"><i data-icon="m" class="linea-icon linea-ecommerce"></i><span
                        class="right-nav-text" style="margin-left:20px">PENJUALAN</span></div>
                <div class="clearfix"></div>
            </a>
        </li>

        <li>
            <a href="pembelian.php">
                <div class="pull-left"><i data-icon="a" class="linea-icon linea-ecommerce"></i><span
                        class="right-nav-text" style="margin-left:20px">PEMBELIAN</span></div>
                <div class="clearfix"></div>
            </a>
        </li>

        <li>
            <hr class="light-grey-hr mb-10" />
        </li>

        <li>
            <a href="javascript:void(0);" data-toggle="collapse" data-target="#menu_idn">
                <div class="pull-left"><i data-icon="u"
                        class="linea-icon linea-ecommerce"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BARANG</div>
                <div class="pull-right"><span class="label label-primary">3</span></div>
                <div class="clearfix"></div>
            </a>
            <ul id="menu_idn" class="collapse collapse-level-1">
                <li>
                    <a href=""></a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>Katalog">KATALOG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>Product">BARANG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>Stock">STOK</a>
                </li>
            </ul>
        </li>

        <li>
            <hr class="light-grey-hr mb-10" />
        </li>

        <li>
            <a href="<?php echo base_url();?>Master">
                <div class="pull-left"><i data-icon="M" class="linea-icon linea-basic"></i><span class="right-nav-text"
                        style="margin-left:20px">MASTER</span></div>
                <div class="clearfix"></div>
            </a>
        </li>

        <li>
            <a href="javascript:void(0);" data-toggle="collapse" data-target="#menu_report">
                <div class="pull-left"><i data-icon="u"
                        class="linea-icon linea-basic"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MASTER</div>
                <div class="pull-right"><span class="label label-primary">7</span></div>
                <div class="clearfix"></div>
            </a>
            <ul id="menu_report" class="collapse collapse-level-1">
                <li>
                    <a href=""></a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>jabatan">JABATAN</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>employee">KARYAWAN</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>toko">TOKO</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>barang_jenis">JENIS BARANG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>barang_merk">MERK BARANG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>barang">BARANG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>supplier">SUPPLIER</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Stock">CUSTOMER</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Keuangan">GUDANG</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Keuangan">NAMA AKUN</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Keuangan">JENIS PRODUCT</a>
                </li>
            </ul>
        </li>

        <li>
            <hr class="light-grey-hr mb-10" />
        </li>

        <li>
            <a href="javascript:void(0);" data-toggle="collapse" data-target="#menu_report">
                <div class="pull-left"><i data-icon="u"
                        class="linea-icon linea-ecommerce"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LAPORAN</div>
                <div class="pull-right"><span class="label label-primary">4</span></div>
                <div class="clearfix"></div>
            </a>
            <ul id="menu_report" class="collapse collapse-level-1">
                <li>
                    <a href=""></a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Pembelian">LAPORAN PEMBELIAN</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Penjualan">LAPORAN PENJUALAN</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Stock">LAPORAN STOK</a>
                </li>
                <li>
                    <a href="<?php echo base_url();?>ReportMM/Keuangan">LAPORAN KEUANGAN</a>
                </li>
            </ul>
        </li>

        <li>
            <hr class="light-grey-hr mb-10" />
        </li>
        <li class="navigation-header">
            <span>System</span>
            <i class="zmdi zmdi-account"></i>
        </li>
        <li>
            <a href="<?php echo base_url();?>Setting">
                <div class="pull-left"><i data-icon="O" class="linea-icon linea-basic"></i><span class="right-nav-text"
                        style="margin-left:20px">Setting User</span></div>
                <div class="clearfix"></div>
            </a>
        </li>
    </ul>
</div>
<!-- /Left Sidebar Menu -->