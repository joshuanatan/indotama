<?php
$page_title = "Katalog Barang";
$breadcrumb = array(
    "Katalog Barang"
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
                                            <div class = "form-group">
                                                <h5>Search Data Here</h5>
                                                <input id = "search_box" placeholder = "Search data here..." type = "text" class = "form-control input-sm " onkeyup = "search()" style = "width:25%">
                                            </div>
                                            <nav aria-label="Page navigation example">
                                                <ul class="pagination justify-content-center pagination_container">
                                                </ul>
                                            </nav>
                                            <div id = "catalog_content_container"></div>
                                            <div class = "clearfix"></div>

                                            <nav aria-label="Page navigation example">
                                                <ul class="pagination justify-content-center pagination_container">
                                                </ul>
                                            </nav>
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
    var ctrl = "barang";
    var url_add = "";
</script>
<?php
$data = array(
    "page_title" => "Master Barang"
);
?>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view('_core_script/menubar_func'); ?>
<script>
    var searchKey = "";
    var page = 1;
    var content = [];
    $(document).ready(function(){
        menubar();
        refresh(1);
    });
    function refresh(req_page = 1) {
        $.ajax({
            url: "<?php echo base_url();?>ws/barang_cabang/content?orderBy=0&orderDirection=ASC&page="+page+"&searchKey="+searchKey+"&id_cabang=<?php echo $this->session->id_cabang;?>",
            type: "GET",
            dataType: "JSON",
            success: function(respond) {
                $("#catalog_content_container").html("");
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    content = respond["content"];
                    for(var a = 0; a<content.length; a++){
                        html += `
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default card-view pa-0">
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body pa-0">
                                        <div class="sm-data-box">
                                            <div class="container-fluid">
                                                <div class="row">
                                                    <div class="col-xs-12 text-center pl-0 pr-0 data-wrap-left">
                                                        <div style = 'width:90%;height:90%;margin:auto'>
                                                            <img src = "<?php echo base_url();?>asset/uploads/barang/${content[a]["image_barang"]}" style = "width:100%;padding:20px">
                                                       
                                                            <table class = "table table-bordered" style = "width:100%;">
                                                                <tr>
                                                                    <th>Nama Produk</th>
                                                                    <td>${content[a]["nama_brg"]}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Harga</th>
                                                                    <td>${content[a]["harga"]}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Stok</th>
                                                                    <td>${content[a]["qty"]} ${content[a]["satuan_brg"]}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Jenis Barang</th>
                                                                    <td>${content[a]["jenis"]}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Merk Barang</th>
                                                                    <td>${content[a]["merk"]}</td>
                                                                </tr>
                                                            </table> 
                                                        </div>
                                                    </div>
                                                </div>	
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    }
                    $("#catalog_content_container").html(html);
                    pagination(respond["page"]);
                }
            }
        });
        function pagination(page_rules){
            html = "";
            if(page_rules["previous"]){
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["before"])+')"><</a></li>';
            }
            else{
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
            }
            if(page_rules["first"]){
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["first"])+')">'+(page_rules["first"])+'</a></li>';
                html += '<li class="page-item"><a class="page-link">...</a></li>';
            }
            if(page_rules["before"]){
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["before"])+')">'+page_rules["before"]+'</a></li>';
            }
            html += '<li class="page-item active"><a class="page-link" onclick = "refresh('+(page_rules["current"])+')">'+page_rules["current"]+'</a></li>';
            if(page_rules["after"]){
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["after"])+')">'+page_rules["after"]+'</a></li>';
            }
            if(page_rules["last"]){
                html += '<li class="page-item"><a class="page-link">...</a></li>';
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["last"])+')">'+page_rules["last"]+'</a></li>';
            }
            if(page_rules["next"]){
                html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(page_rules["after"])+')">></a></li>';
            }
            else{
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
            }
            $(".pagination_container").html(html);
        }
    }
    function test(){
        console.log(content);
    }
    function search(){
        searchKey = $("#search_box").val();
        refresh();
    }
</script>