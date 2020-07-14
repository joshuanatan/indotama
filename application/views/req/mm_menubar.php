<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="mobile-only-brand pull-left">
        <div class="nav-header pull-left">
            <div class="logo-wrap">
                <a href="<?php echo base_url();?>dashboard">
                    <span class="brand-text">INDOTAMA</span>
                </a>
            </div>
        </div>	
        <a id="toggle_nav_btn" class="toggle-left-nav-btn inline-block ml-20 pull-left" href="javascript:void(0);">
            <i class="zmdi zmdi-menu"></i>
        </a>
        <a id="toggle_mobile_nav" class="mobile-only-view" href="javascript:void(0);">
            <i class="zmdi zmdi-more"></i>
        </a>
    </div>
    <div id="mobile_only_nav" class="mobile-only-nav pull-right">
        <ul class="nav navbar-right top-nav pull-right">
            <li class="dropdown auth-drp">
                <a href="#" class="dropdown-toggle pr-0" data-toggle="dropdown">
                    <img src="<?php echo base_url(); ?>asset/img/user1.png" alt="user_auth" class="user-auth-img img-circle" />
                    <span class="user-online-status"></span>
                </a>
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
<div class="fixed-sidebar-left" style = "background-image:url('<?php echo base_url();?>asset/images/sidebar1.jpg');background-size:cover">
    <ul class="nav navbar-nav side-nav nicescroll-bar">
        <li>
            <div class="user-profile text-center">
                <img src="<?php echo base_url(); ?>asset/img/user1.png" alt="user_auth" class="user-auth-img img-circle" />
                <div class="dropdown mt-5">
                    <a href="#" class="dropdown-toggle pr-0 bg-transparent" data-toggle="dropdown"><?php echo ucwords($this->session->user_name);?></a>
                </div>
            </div>
        </li>
            <!-- /User Profile -->
        <?php $this->load->view("req/menu/general");?>
        <?php $this->load->view("req/menu/cabang");?>
        <?php $this->load->view("req/menu/toko");?>
        <?php $this->load->view("req/menu/warehouse");?>
    </ul>
</div>