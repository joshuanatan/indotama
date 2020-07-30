<?php
$page_title = "Permintaan Barang";
$breadcrumb = array(
    "Permintaan Barang"
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
                <div class = "col-lg-2">
                    <a href = "<?php echo base_url();?>permintaan/histori" class = "btn btn-primary btn-sm" style = "marign-left:10px;margin-top:20px;margin-bottom:20px">Histori</a>
                </div>
                <div class = "col-lg-10">
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
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Incoming Delivery");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <a href = "<?php echo base_url();?>penerimaan/permintaan" target = "_blank" data-toggle = "modal" class = "btn btn-primary btn-sm">Tabel Penerimaan Permintaan</a>
                                        <div class = "col-lg-12" id = "incoming_delivery_container">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Permintaan Barang Aktif");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <button type = "button" data-toggle = "modal" data-target = "#register_modal" target = "_blank" class = "btn btn-primary btn-sm">Tambah Permintaan</button>
                                        <a href = "<?php echo base_url();?>permintaan/data" class = "btn btn-primary btn-sm">Daftar Permintaan</a>
                                        <div class = "col-lg-12" id = "permintaan_barang_container">
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
    var ctrl = "permintaan";
</script>
<?php $this->load->view("_core_script/menubar_func");?>
<script>
    load_permintaan_barang_content();
    load_incoming_delivery_content();
    var content_permintaan;
    var content_id;
    var content;
    function load_permintaan_barang_content(){
        $.ajax({
            url:"<?php echo base_url();?>ws/permintaan/list_permintaan_aktif",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var data_not_exists_flag = true;
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    content_permintaan = respond["content"];
                    for(var a = 0; a<content_permintaan.length; a++){
                        if(content_permintaan[a]["status"].toLowerCase() != "selesai"){
                            console.log("Test");
                            if(data_not_exists_flag){
                                html += `<ul class="timeline timeline-container-invert">`;
                                data_not_exists_flag = false;
                            }
                            if(content_permintaan[a]["notes"] == "-") content_permintaan[a]["notes"] = "Tidak ada catatan";
                            html += `
                            <li class="timeline-inverted">
                                <div class="timeline-badge bg-yellow">
                                    <i class="icon-layers" ></i>
                                </div>
                                <div class="timeline-panel pa-30">
                                    <div class="timeline-heading mb-15">
                                        <h6 class="mb-5">Tanggal Permintaan ${content_permintaan[a]["create_date"]}</h6>
                                        <h6 class="mb-5">Deadline ${content_permintaan[a]["deadline"]}</h6>
                                    </div>
                                    <hr style = "background-color:black;border:1px solid black;opacity:0.1;margin-bottom:10px"/>
                                    <div class="timeline-body mb-15">
                                        <p class="lead head-font mb-20">${content_permintaan[a]["barang"]}</p>
                                        <h4 class="mb-5">${content_permintaan[a]["qty_pemenuhan"]} / ${content_permintaan[a]["qty"]} Pcs</h4>
                                        <p>${content_permintaan[a]["notes"]}</p>
                                        <br/><br/>
                                        <button type = 'button' onclick = "konfirmasi_selesai(${a})" style = "float:right"; class = 'btn btn-primary btn-sm col-lg-4 mr-5'>Done</button>
                                        <button type = 'button' onclick = "konfirmasi_batal(${a})" style = "float:right"; class = 'btn btn-danger btn-sm col-lg-4 mr-5'>Cancel</button>
                                    </div>
                                </div>
                            </li>
                            `;
                            if(a == content_permintaan.length-1){
                                html += `<li class="clearfix no-float"></li>`;
                                html += `</ul>`;
                            }
                        }
                    }
                }
                else{
                    if(data_not_exists_flag){
                        html = `<p align = 'center'>No Data Found</p>`;
                    }
                }
                $("#permintaan_barang_container").html(html);
            }
        })
    }
    function konfirmasi_selesai(row){
        ctrl = "permintaan";
        content = content_permintaan;
        load_selesai_content(row);
        $('#selesai_modal').modal('show');
    }
    function konfirmasi_batal(row){
        ctrl = "permintaan";
        content = content_permintaan;
        load_delete_content(row);
        $('#delete_modal').modal('show');
    }
    function load_incoming_delivery_content(){
        $.ajax({
            url:"<?php echo base_url();?>ws/penerimaan_permintaan/list_pengiriman_otw?type=cabang",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var data_not_exists_flag = true;
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    content_id = respond["content"];
                    for(var a = 0; a<content_id.length; a++){
                        if(data_not_exists_flag){
                            html += `<ul class="timeline">`;
                            data_not_exists_flag = false;
                        }
                        if(content_id[a]["note_brg_pengiriman"] == "-") content_permintaan[a]["note_brg_pengiriman"] = "Tidak ada catatan";
                        html += `
                        <li>
                            <div class="timeline-badge bg-pink">
                                <i class="icon-magnifier-add"></i>
                            </div>
                            <div class="timeline-panel pa-30">
                                <div class="timeline-heading">
                                    <h6 class="mb-5">Tanggal Pengiriman: ${content_id[a]["tgl_pengiriman"]}</h6>
                                    <h6 class="mb-5">Pengiriman: ${content_id[a]["nama_toko"]} ${content_id[a]["daerah_cabang"]}</h6>
                                </div>
                                    <hr style = "background-color:black;border:1px solid black;opacity:0.1;margin-bottom:10px"/>
                                <div class="timeline-body">
                                    <p class="lead  mb-20">${content_id[a]["nama_brg"]}</p>
                                    <h4 class=" mb-5">${content_id[a]["qty_brg_pengiriman"]} Pcs</h4>
                                    <p>${content_id[a]["note_brg_pengiriman"]}</p>
                                    <br/><br/>
                                    <button type = 'button' onclick = "konfirmasi_terima(${a})" style = "float:right"; class = 'btn btn-primary btn-sm col-lg-4 mr-5'>Done</button>
                                </div>
                            </div>
                        </li>
                        `;
                        if(a == content_id.length-1){
                            html += '<li class="clearfix no-float"></li>';
                            html += `</ul>`;
                        }
                    }
                }
                else{
                    if(data_not_exists_flag){
                        html = `<p align = 'center'>No Data Found</p>`;
                    }
                }
                $("#incoming_delivery_container").html(html);
            }
        })    
    }
    function konfirmasi_terima(row){
        ctrl = "penerimaan_permintaan";
        content = content_id;
        $("#p_id_brg_pemenuhan").val(content[row]["id_brg_pemenuhan"]);
        $("#p_id_brg_pengiriman").val(content[row]["id_brg_pengiriman"]);
        $("#p_brg_pengiriman_qty").html(content[row]["qty_brg_pengiriman"]+" Pcs");
        $("#p_brg_penerimaan_qty").val(content[row]["qty_brg_pengiriman"]);
        $("#p_brg_nama").html(content[row]["nama_brg"]);
        $("#p_toko").html(content[row]["nama_toko"]);
        $("#p_cabang").html(content[row]["daerah_cabang"]);
        $("#p_tgl_pengiriman").html(content[row]["tgl_pengiriman"]);
        $("#p_register_penerimaan_permintaan_modal").modal("show");
    }
</script>
<?php
$data = array(
    "page_title" => "Permintaan",
    "type" => "Cabang",
    "tipe_penerimaan" => "permintaan",
    "id_tempat_penerimaan" => $this->session->id_cabang
);
?>
<?php $this->load->view("_base_element/datalist_barang_cabang");?>
<script>
    load_datalist_barang_cabang();
</script>
<?php $this->load->view("brg_permintaan/f-selesai-brg-permintaan",$data);?>
<?php $this->load->view("brg_permintaan/f-delete-brg-permintaan",$data);?>
<?php $this->load->view("brg_permintaan/f-add-brg-permintaan",$data);?>
<?php $this->load->view("brg_permintaan/p-add-penerimaan-permintaan");?>


<?php $this->load->view('_notification/notif_general'); ?>
<script>
    $("#notif_selesai_error").css("display", "none");
</script>
<?php $this->load->view("req/core_script");?>