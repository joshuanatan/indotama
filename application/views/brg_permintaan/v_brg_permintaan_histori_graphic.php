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
                <div class="container-fluid">
                    <div class="row mt-20">
                        <div class="col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-4">
                                            <div class = "form-group">
                                                <h5>Tanggal Histori</h5>    
                                                <input type = "date" class = "form-control" id = "tgl_histori">
                                            </div>
                                        </div>
                                        <div class = "col-lg-2">
                                            <div class = "form-group">
                                                <h5>&nbsp;</h5>    
                                                <button type = "button" onclick = "search()" class = "btn btn-primary btn-sm">Lihat Histori</button>
                                            </div>
                                        </div>
                                        <div class = "col-lg-6">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Histori Pengiriman");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class = "col-lg-12" id = "histori_pengiriman_container">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light"><?php echo ucwords("Histori Permintaan");?></h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
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

    var content_permintaan;
    var content_pengiriman;
    var content;
    function search(){
        var tgl = $("#tgl_histori").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/permintaan/histori_tgl?tgl_buat_permintaan="+tgl,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var data_not_exists_flag = true;
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    content_permintaan = respond["content"];
                    for(var a = 0; a<content_permintaan.length; a++){
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
                                    
                                    <button type = 'button' style = "float:right"; class = 'btn btn-${content_permintaan[a]["status_code"]} btn-sm'>${content_permintaan[a]["status"]}</button>
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
                else{
                    if(data_not_exists_flag){
                        html = `<p align = 'center'>No Data Found</p>`;
                    }
                }
                $("#permintaan_barang_container").html(html);
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/pengiriman_permintaan/histori_tgl?tgl_buat_permintaan="+tgl,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var data_not_exists_flag = true;
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    content_pengiriman = respond["content"];
                    for(var a = 0; a<content_pengiriman.length; a++){
                        if(data_not_exists_flag){
                            html += `<ul class="timeline">`;
                            data_not_exists_flag = false;
                        }
                        if(content_pengiriman[a]["notes"] == "-") content_pengiriman[a]["notes"] = "Tidak ada catatan";
                        html += `
                        <li>
                            <div class="timeline-badge bg-pink">
                                <i class="icon-layers" ></i>
                            </div>
                            <div class="timeline-panel pa-30">
                                <div class="timeline-heading mb-15">
                                    <h6 class="mb-5">Tanggal Pengiriman ${content_pengiriman[a]["tgl_pengiriman"]}</h6>
                                </div>
                                <hr style = "background-color:black;border:1px solid black;opacity:0.1;margin-bottom:10px"/>
                                <div class="timeline-body mb-15">
                                    <p class="lead head-font mb-20">${content_pengiriman[a]["nama_brg"]}</p>
                                    <h4 class="mb-5">${content_pengiriman[a]["pemenuhan_qty_brg"]} Pcs</h4>
                                    <p>${content_pengiriman[a]["notes"]}</p>
                                    <br/><br/>
                                    
                                    <button type = 'button' style = "float:right"; class = 'btn btn-${content_pengiriman[a]["status_code"]} btn-sm'>${content_pengiriman[a]["status"]}</button>
                                </div>
                            </div>
                        </li>
                        `;
                        if(a == content_pengiriman.length-1){
                            html += `<li class="clearfix no-float"></li>`;
                            html += `</ul>`;
                        }
                    }
                }
                else{
                    if(data_not_exists_flag){
                        html = `<p align = 'center'>No Data Found</p>`;
                    }
                }
                $("#histori_pengiriman_container").html(html);
            }
        })
    }
</script>
