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
                    <img src="<?php echo base_url(); ?>asset/images/employee/foto/<?php echo $this->session->foto;?>" alt="user_auth" class="user-auth-img img-circle" />
                    <span class="user-online-status"></span>
                </a>
                <ul class="dropdown-menu user-auth-dropdown" data-dropdown-in="flipInX" data-dropdown-out="flipOutX">
                    <li>
                        <a onclick="view_profile()" style="cursor:pointer" data-toggle = 'modal' data-target = '#view_profile'><i class="zmdi zmdi-account"></i><span>Profile</span></a>
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
                <img src="<?php echo base_url(); ?>asset/images/employee/foto/<?php echo $this->session->foto;?>" alt="user_auth" class="user-auth-img img-circle" />
                <div class="dropdown mt-5">
                    <a href="#" class="dropdown-toggle pr-0 bg-transparent"><?php echo ucwords($this->session->user_name);?></a>
                    <br/>
                    <a style = "font-size:13px;color:lightgrey" href="#" class="dropdown-toggle pr-0 bg-transparent"><?php echo ucwords($this->session->disp_nama_toko_cabang);?></a>
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
<input type="hidden" name="id_user_profile" value="<?php echo $this->session->id_user ?>" id="id_user_profile">

<div class = "modal fade" id = "view_profile">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">My Profile</h4>
            </div>
            <div class = "modal-body">
                <img id="foto_profile" width="150px">
                <table>
                    <tr>
                        <td style="padding-right:50px;">Nama</td>
                        <td>: <span id="panggilan_profile"></span> <span id="nama_profile"></span></td>
                    </tr>
                    <tr>
                        <td style="padding-right:50px;">Email</td>
                        <td>: <span id="email_profile"></span></td>
                    </tr>
                    <tr>
                        <td style="padding-right:50px;">Jabatan</td>
                        <td>: <span id="role_profile"></span></td>
                    </tr>

                    <tr>
                        <td style="padding-right:50px;">Jenis Kelamin</td>
                        <td>: <span id="gender_profile"></span></td>
                    </tr>
                    <tr>
                        <td style="padding-right:50px;">Toko</td>
                        <td>: <span id="toko_profile"></span></td>
                    </tr>
                </table>
                <br>
                <a href="<?php echo base_url() ?>dashboard/edit_profile_view/<?php echo $this->session->id_user ?>" class = "btn btn-sm btn-primary">Edit Profile</a>
               
            </div>
        </div>
    </div>
</div>
<script>
    function view_profile(){
        var id_user = $("#id_user_profile").val();
        $.ajax({
            url: "<?= base_url() ?>dashboard/view_profile",
            type: "POST",
            dataType: "JSON",
            data: {
                id_user:id_user
            },
            success:function(respond){
                $("#panggilan_profile").html(respond['panggilan_profile']);
                $("#nama_profile").html(respond['nama_profile']);
                $("#email_profile").html(respond['email_profile']);
                $("#role_profile").html(respond['role_profile']);
                $("#gender_profile").html(respond['gender_profile']);
                $("#toko_profile").html(respond['toko_profile']);
                $("#foto_profile").attr("src",respond['foto_profile']);
            }
        });
    }
    
</script>