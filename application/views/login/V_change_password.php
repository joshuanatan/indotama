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

            <?php $this->load->view('req/mm_menubar.php');?>

            <div class="page-wrapper">
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-lg-12 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light">Change Password</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">Home</li>
                                        <li class="breadcrumb-item active">Change Password</li>
                                    </ol>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <form method = "POST" action="<?php echo base_url() ?>login/change_password_method">
                                            <input type = "hidden" name = "id_pk_user" value = "<?php echo $_SESSION['id_user'] ?>">
                                            <div class = "form-group col-lg-12">
                                                <h5>Password Seakrang</h5>
                                                <input type = "password" class = "form-control" required name = "pass_lama">
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Password Baru</h5>
                                                <input type = "password" class = "form-control" required name = "pass_baru">
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Konfirmasi Password Baru</h5>
                                                <input type = "password" class = "form-control" required name = "pass_baru2">
                                            </div>
                                         </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('req/mm_footer.php');?>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>