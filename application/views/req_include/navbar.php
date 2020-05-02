<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggler hamburger hamburger-close navbar-toggler-left hided" data-toggle="menubar">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-bar"></span>
        </button>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-collapse" data-toggle="collapse">
            <i class="icon md-more" aria-hidden="true"></i>
        </button>
        <div class="navbar-brand navbar-brand-center site-gridmenu-toggle" data-toggle="gridmenu">
            <span class="navbar-brand-text hidden-xs-down"> ISUPPORT</span>
        </div>
        <button type="button" class="navbar-toggler collapsed" data-target="#site-navbar-search" data-toggle="collapse">
            <span class="sr-only">Toggle Search</span>
            <i class="icon md-search" aria-hidden="true"></i>
        </button>
    </div>

    <div class="navbar-container container-fluid">
        <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
            <ul class="nav navbar-toolbar">
                <li class="nav-item hidden-float" id="toggleMenubar">
                    <a class="nav-link" data-toggle="menubar" href="#" role="button" style="height: 100%;padding-top: 60%;">
                        <i class="icon hamburger hamburger-arrow-left">
                            <span class="sr-only">Toggle menubar</span>
                            <span class="hamburger-bar"></span>
                        </i>
                    </a>
                </li>
                <li class="nav-item hidden-sm-down" id="toggleFullscreen">
                    <a class="nav-link icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                        <span class="sr-only">Toggle fullscreen</span>
                    </a>
                </li>
            </ul>
            <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right">
                <li class="nav-item dropdown">
                    <a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
                        
                        <?php echo $this->session->acc_name;?>
                    </a>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="javascript:void(0)" role="menuitem">
                            <i class="icon md-account" aria-hidden="true"></i> Profile
                        </a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a class="dropdown-item" href="<?php echo base_url();?>welcome/logout" role="menuitem">
                            <i class="icon md-power" aria-hidden="true"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>    
<div class="site-menubar">
    <div class="site-menubar-body">
        <div>
            <div>
                <ul class="site-menu" data-plugin="menu">
                    <li class="site-menu-category"></li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>welcome/dashboard">
                            <i class="site-menu-icon md-view-dashboard" aria-hidden="true"></i>
                            <span class="site-menu-title">Home</span>
                        </a>
                    </li>
                    <li class="site-menu-category">Master Data</li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>project">
                            <i class="site-menu-icon md-collection-text" aria-hidden="true"></i>
                            <span class="site-menu-title">Proyek</span>
                        </a>
                    </li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>formula">
                            <i class="site-menu-icon md-storage" aria-hidden="true"></i>
                            <span class="site-menu-title">Nama Perkerjaan</span>
                        </a>
                    </li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>attribute">
                            <i class="site-menu-icon md-plaster" aria-hidden="true"></i>
                            <span class="site-menu-title">Bahan</span>
                        </a>
                    </li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>upah">
                            <i class="site-menu-icon md-receipt" aria-hidden="true"></i>
                            <span class="site-menu-title">Upah</span>
                        </a>
                    </li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>alat">
                            <i class="site-menu-icon md-truck" aria-hidden="true"></i>
                            <span class="site-menu-title">Alat</span>
                        </a>
                    </li>
                    <li class="site-menu-item">
                        <a class="animsition-link" href="<?php echo base_url();?>supplier">
                            <i class="site-menu-icon md-store" aria-hidden="true"></i>
                            <span class="site-menu-title">Supplier</span>
                        </a>
                    </li>
                </ul>
            </div>      
        </div>
    </div>
</div>
<div class="site-menubar-footer">
    <a href="javascript: void(0);" class="fold-show" data-placement="top" data-toggle="tooltip" data-original-title="Settings">
        <span class="icon md-settings" aria-hidden="true"></span>
    </a>
    <a href="javascript: void(0);" data-placement="top" data-toggle="tooltip" data-original-title="Lock">
        <span class="icon md-eye-off" aria-hidden="true"></span>
    </a>
    <a href="javascript: void(0);" data-placement="top" data-toggle="tooltip" data-original-title="Logout">
        <span class="icon md-power" aria-hidden="true"></span>
    </a>
</div>
