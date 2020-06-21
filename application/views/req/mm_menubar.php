
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="mobile-only-brand pull-left">
        <div class="nav-header pull-left">
            <div class="logo-wrap">
                <a href="<?php echo base_url();?>dashboard">
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
                    <li>
                        <a href="<?php echo base_url() ?>login/change_password"><i class="zmdi zmdi-account"></i><span>Change Password</span></a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?php echo base_url() ?>login/logout"><i class="zmdi zmdi-power"></i><span>Log Out</span></a>
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
        <?php $this->load->view("req/menu/general");?>
        <?php $this->load->view("req/menu/warehouse");?>
        <?php $this->load->view("req/menu/toko"); ?>
        <?php $this->load->view("req/menu/cabang"); ?>
    </ul>
</div>
<!-- /Left Sidebar Menu -->