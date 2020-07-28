<?php
$page_title = "Penjualan";
$breadcrumb = array(
    "Penjualan"
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
            <?php $this->load->view('_notification/register_success',$notif_data); ?>
            <?php $this->load->view('_notification/update_success',$notif_data); ?>
            <?php $this->load->view('_notification/delete_success',$notif_data); ?>
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
                                    <div class="panel-body">
                                        <div class = "col-lg-12">
                                            <div class = "form-inline">
                                                <select class = "form-control form-sm" id = "tipe_pembayaran" style = "width:20%">
                                                    <option value = "all">Semua</option>
                                                    <option value = "FULL PAYMENT">Full Payment</option>
                                                    <option value = "DP">DP</option>
                                                    <option value = "TEMPO">Tempo</option>
                                                    <option value = "KEEP">Keep</option>
                                                </select>
                                                <button type = "button" onclick = "redirect_tipe_pembayaran()" class = "btn btn-primary btn-sm">Buka</button>
                                            </div>
                                            <br/>
                                            <div class = "d-block">
                                                <a target = "_blank" href = "<?php echo base_url();?>penjualan/tambah" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" style = "margin-right:10px">Tambah <?php echo ucwords($page_title);?></a>
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-eye"></i><b> - Details </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-info md-print"></i><b> - Invoice </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-default md-print"></i><b> - Invoice Copy </b>
                                            </div>
                                            <br/>
                                            <?php
                                                $data = array(
                                                    "ctrl_model" => "m_penjualan",
                                                    "excel_title" => "Daftar Penjualan"
                                                );
                                            ?>
                                            <?php $this->load->view("_base_element/table",$data);?>
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
    var ctrl = "penjualan";
    var tipe_pemb = $("#tipe_pembayaran").val();
    var url_add = "id_cabang=<?php echo $this->session->id_cabang;?>&tipe_pemb="+tipe_pemb;
    var unautorized_button = ["edit_button"];
    var additional_button = [
        {
            style:'cursor:pointer;font-size:large',
            class:'text-primary md-edit',
            onclick:'redirect_edit_penjualan()'
        },
        {
            style:'cursor:pointer;font-size:large',
            class:'text-info md-print',
            onclick:'redirect_print_pdf()'
        },
        {
            style:'cursor:pointer;font-size:large',
            class:'text-default md-print',
            onclick:'redirect_print_pdf_copy()'
        },
    ];
</script>
<?php
$data = array(
    "page_title" => "Penjualan"
);
?>

<script>
    function redirect_print_pdf(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_penjualan = content[row]["id"];
            window.open("<?php echo base_url();?>pdf/invoice/index/"+id_penjualan,"_blank");
        });
    }
    function redirect_print_pdf_copy(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_penjualan = content[row]["id"];
            window.open("<?php echo base_url();?>pdf/invoice/copy/"+id_penjualan,"_blank");
        });
    }
    function redirect_edit_penjualan(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_penjualan = content[row]["id"];
            window.open("<?php echo base_url();?>penjualan/update/"+id_penjualan,"_blank");
        });
    }

    function redirect_detail_penjualan(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_penjualan = content[row]["id"];
            window.open("<?php echo base_url();?>penjualan/detail/"+id_penjualan,"_blank");
        });
    }
    function redirect_tipe_pembayaran(){
        var tipe_pemb = $("#tipe_pembayaran").val();
        url_add = "id_cabang=<?php echo $this->session->id_cabang;?>&tipe_pemb="+tipe_pemb;
        refresh(page);
    }
</script>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("penjualan/f-delete-penjualan");?>
<?php $this->load->view("penjualan/f-detail-penjualan");?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>