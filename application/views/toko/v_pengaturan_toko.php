<?php
$page_title = "Toko";
$breadcrumb = array(
    "Toko","Pengaturan Toko"
);
$notif_data = array(
    "page_title"=>$page_title
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
            <?php $this->load->view('req/mm_menubar.php');?>
            <div class="page-wrapper">
                <?php $this->load->view('_notification/update_success',$notif_data); ?>
                <?php $this->load->view('_notification/update_error',$notif_data); ?>
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-lg-12 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">Home</a></li>
                                        <?php for($a = 0; $a<count($breadcrumb); $a++):?>
                                        <?php if($a+1 != count($breadcrumb)):?>
                                        <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]);?></a></li>
                                        <?php else:?>
                                        <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]);?></li>
                                        <?php endif;?>
                                        <?php endfor;?>
                                    </ol>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body" style = "background-color:white">
                                        <div class = "col-lg-12">
                                            <form id = "update_form" method = "POST">
                                                <input type = "hidden" name = "id" id = "id_edit">
                                                <div class = "form-group">
                                                    <h5>Nama Toko</h5>
                                                    <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Kode Toko</h5>
                                                    <input type = "text" class = "form-control" required name = "kode" id = "kode_edit">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Logo Toko</h5>
                                                    <a target = "_blank" id = "logo_download" class = "col-lg-3 btn btn-primary btn-sm">Download Logo Toko</a>
                                                    <br/>
                                                    <br/>
                                                    <input type = "hidden" class = "form-control" name = "logo_current" id = "logo_current">
                                                    <input type = "file" class = "form-control" name = "logo">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Kop Surat</h5>
                                                    <a target = "_blank" id = "kop_surat_download" class = "col-lg-3 btn btn-primary btn-sm">Download Kop Surat</a>
                                                    <br/>
                                                    <br/>
                                                    <input type = "file" class = "form-control" name = "kop_surat">
                                                    <input type = "hidden" name = "kop_surat_current" id = "kop_surat_current">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Surat Non PKP</h5>
                                                    <a target = "_blank" id = "nonpkp_download" class = "col-lg-3 btn btn-primary btn-sm">Download Surat Non PKP</a>
                                                    <br/>
                                                    <br/>
                                                    <input type = "file" class = "form-control" name = "nonpkp">
                                                    <input type = "hidden" name = "nonpkp_current" id = "nonpkp_current">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Surat Pernyataan Nomor Rekening</h5>
                                                    <a target = "_blank" id = "pernyataan_rek_download" class = "col-lg-3 btn btn-primary btn-sm">Download Pernyataan Nomor Rekening</a>
                                                    <br/>
                                                    <br/>
                                                    <input type = "file" class = "form-control" name = "pernyataan_rek">
                                                    <input type = "hidden" name = "pernyataan_rek_current" id = "pernyataan_rek_current">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Tanda Tangan Digital</h5>
                                                    <a target = "_blank" id = "ttd_download" class = "col-lg-3 btn btn-primary btn-sm">Download Tanda Tangan Digital</a>
                                                    <br/>
                                                    <br/>
                                                    <input type = "file" class = "form-control" name = "ttd">
                                                    <input type = "hidden" name = "ttd_current" id = "ttd_current">
                                                </div>
                                                <div class = "form-group">
                                                    <button type = "button" onclick = "update_func();update_id_toko();location.reload()" class = "btn btn-sm btn-primary">Submit</button>
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
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>

<script>
    var ctrl = "toko";
</script>
<?php $this->load->view("_core_script/table_func");?>
<script>
    $.ajax({
        url:"<?php echo base_url();?>ws/toko/pengaturan",
        type:"GET",
        dataType:"JSON",
        success:function(respond){
            if(respond["status"].toLowerCase() == "success"){
                $("#id_edit").val(respond["content"][0]["id"]);
                $("#nama_edit").val(respond["content"][0]["nama"]);
                $("#kode_edit").val(respond["content"][0]["kode"]);

                if(respond["content"][0]["logo"] != "noimage.jpg" && respond["content"][0]["logo"] != "null" && respond["content"][0]["logo"] != null && respond["content"][0]["logo"] != "-"){
                    $("#logo_download").attr("href","<?php echo base_url();?>asset/uploads/toko/logo/"+respond["content"][0]["logo"]);
                }
                else{
                    $("#logo_download").attr("class","btn btn-sm btn-danger col-lg-3");
                    $("#logo_download").html("Tidak ada data");
                }

                if(respond["content"][0]["kop_surat"] != "noimage.jpg" && respond["content"][0]["kop_surat"] != "null" && respond["content"][0]["kop_surat"] != null && respond["content"][0]["kop_surat"] != "-"){
                    $("#kop_surat_download").attr("href","<?php echo base_url();?>asset/uploads/toko/kop_surat/"+respond["content"][0]["kop_surat"]);
                }
                else{
                    $("#kop_surat_download").attr("class","btn btn-sm btn-danger col-lg-3");
                    $("#kop_surat_download").html("Tidak ada data");
                }

                if(respond["content"][0]["nonpkp"] != "noimage.jpg" && respond["content"][0]["nonpkp"] != "null" && respond["content"][0]["nonpkp"] != null && respond["content"][0]["nonpkp"] != "-"){
                    $("#nonpkp_download").attr("href","<?php echo base_url();?>asset/uploads/toko/nonpkp/"+respond["content"][0]["nonpkp"]);
                }
                else{
                    $("#nonpkp_download").attr("class","btn btn-sm btn-danger col-lg-3");
                    $("#nonpkp_download").html("Tidak ada data");
                }

                if(respond["content"][0]["pernyataan_rek"] != "noimage.jpg" && respond["content"][0]["pernyataan_rek"] != "null" && respond["content"][0]["pernyataan_rek"] != null && respond["content"][0]["pernyataan_rek"] != "-"){
                    $("#pernyataan_rek_download").attr("href","<?php echo base_url();?>asset/uploads/toko/pernyataan_rek/"+respond["content"][0]["pernyataan_rek"]);
                }
                else{
                    $("#pernyataan_rek_download").attr("class","btn btn-sm btn-danger col-lg-3");
                    $("#pernyataan_rek_download").html("Tidak ada data");
                }

                if(respond["content"][0]["ttd"] != "noimage.jpg" && respond["content"][0]["ttd"] != "null" && respond["content"][0]["ttd"] != null && respond["content"][0]["ttd"] != "-"){
                    $("#ttd_download").attr("href","<?php echo base_url();?>asset/uploads/toko/ttd/"+respond["content"][0]["ttd"]);
                }
                else{
                    $("#ttd_download").attr("class","btn btn-sm btn-danger col-lg-3");
                    $("#ttd_download").html("Tidak ada data");
                }


                $("#logo_current").val(respond["content"][0]["logo"]);
                $("#kop_surat_current").val(respond["content"][0]["kop_surat"]);
                $("#nonpkp_current").val(respond["content"][0]["nonpkp"]);
                $("#pernyataan_rek_current").val(respond["content"][0]["pernyataan_rek"]);
                $("#ttd_current").val(respond["content"][0]["ttd"]);
            }
        }
    });
    function update_id_toko(){
        $.ajax({
            url:"<?php echo base_url();?>ws/toko/refresh_id_toko",
            type:"GET",
            async:false,
            dataType:"JSON"
        });
    }
</script>


<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>