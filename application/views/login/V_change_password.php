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
                                        <div class="row">
                                            <?php 
                                            if($this->session->flashdata('gagal_pass')!="" || $this->session->flashdata('gagal_pass')!=null){ ?>
                                            <div class="alert alert-danger alert-dismissable col-lg-4">
                                                <i class="zmdi zmdi-alert-circle-o pr-15 pull-left"></i><p class="pull-left">Eror! <?php echo $this->session->flashdata('gagal_pass') ?></p>
                                            </div>
                                            <?php }?>
                                        </div>
                                        <form id="pass_change" method = "POST" action="<?php echo base_url() ?>login/change_password_method">
                                            <input type = "hidden" name = "id_pk_user" value = "<?php echo $_SESSION['id_user'] ?>">
                                            <div class="row">
                                                <div class = "form-group col-lg-6">
                                                    <h5>Password Sekarang</h5>
                                                    <input type = "password" class = "form-control" required name = "pass_lama" id = "pass_lama">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class = "form-group col-lg-6">
                                                    <h5>Password Baru</h5>
                                                    <input oninput="cek_pass();cek_panjang()" type = "password" class = "form-control" required name = "pass_baru" id = "pass_baru">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class = "form-group col-lg-6">
                                                    <h5>Konfirmasi Password Baru</h5>
                                                    <input oninput="cek_pass();cek_panjang()" type = "password" class = "form-control" required name = "pass_baru2" id = "pass_baru2">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class = "form-group col-lg-6">
                                                    <span id="sama" style="color:red;display:none">*Pasword baru dan password konfirmasi harus sama!</span><br>
                                                    <span id="panjang" style="color:red;display:none">*Pasword minimal 8 karakter!</span><br><br>
                                                    <input style="display:none" data-toggle = "modal" data-target = "#konfir_modal" type = "button" class = "btn btn-primary" value="Ganti Password" name = "ganti_pass" id = "ganti_pass">
                                                </div>
                                            </div>
                                         </form>
<div class = "modal fade" id = "konfir_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Konfirmasi Ganti Password</h4>
            </div>
            <div class = "modal-body">
                <h5>Apakah Anda yakin untuk mengganti password? Jika berhasil, silahkan login ulang dengan password baru!</h5>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "ganti_pass_baru()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
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
<script>
    function cek_pass(){
        var passbaru = $("#pass_baru").val();
        var passbaru2 = $("#pass_baru2").val();
        if(passbaru!=passbaru2){
            $("#ganti_pass").hide();
            $("#sama").show();
        }else{
            $("#ganti_pass").show();
            $("#sama").hide();
        }
    }

    function cek_panjang(){
        var passbaru = $("#pass_baru").val();
        var passbaru2 = $("#pass_baru2").val();
        if(passbaru.length<8){
            $("#ganti_pass").hide();
            $("#panjang").show();
        }else{
            $("#ganti_pass").show();
            $("#panjang").hide();
        }
    }

    function ganti_pass_baru(){   
        $("#pass_change").submit(); // Submit the form
    }
</script>