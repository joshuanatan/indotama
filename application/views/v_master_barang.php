<?php
$page_title = "Master Barang";
$breadcrumb = array(
    "Master","Barang"
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
                                            <div class = "d-block">
                                                <button type = "button" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" data-toggle = "modal" data-target = "#register_modal" style = "margin-right:10px">Tambah <?php echo ucwords($page_title);?></button>
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
                                            </div>
                                            <br/>
                                            <div class = "form-group">
                                                <h5>Search Data Here</h5>
                                                <input id = "search_box" placeholder = "Search data here..." type = "text" class = "form-control input-sm " onkeyup = "search()" style = "width:25%">
                                            </div>
                                            <div class = "table-responsive">
                                                <table class = "table table-bordered table-hover table-striped">
                                                    <thead id = "col_title_container">
                                                    </thead>
                                                    <tbody id = "content_container">
                                                    </tbody>
                                                </table>
                                            </div>
                                            <nav aria-label="Page navigation example">
                                                <ul class="pagination justify-content-center" id = "pagination_container">
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
<datalist id = "list_merk" ></datalist>
<datalist id = "list_jenis" ></datalist>
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "register_form" method = "POST" enctype = "multipart/form-data">
                    <div class = "form-group">
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Barang</h5>
                        <input list = "list_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan">
                    </div>
                    <div class = "form-group">
                        <h5>Merk Barang</h5>
                        <input list = "list_merk" type = "text"  required name = "id_brg_merk" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control" required name = "minimal">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan">
                    </div>
                    <div class = "form-group">
                        <h5>Gambar</h5>
                        <input type = "file" required name = "gambar">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "update_form" method = "POST" enctype = "multipart/form-data">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode" id = "kode_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Barang</h5>
                        <input list = "list_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control" id = "id_brg_jenis_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan" id = "keterangan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Merk Barang</h5>
                        <input list = "list_merk" type = "text"  required name = "id_brg_merk" class = "form-control" id = "id_brg_merk_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control" required name = "minimal" id = "minimal_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan" id = "satuan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Gambar</h5>
                        <input type = "hidden" id = "gambar_edit" name = "gambar_current">
                        <input type = "file" class = "form-control" required name = "gambar">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <input type = "hidden" id = "id_delete" name = "id">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Kode Barang</td>
                            <td id = "kode_delete"></td>
                        </tr>
                        <tr>
                            <td>Nama Barang</td>
                            <td id = "nama_delete"></td>
                        </tr>
                        <tr>
                            <td>Jenis Barang</td>
                            <td id = "jenis_delete"></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td id = "keterangan_delete"></td>
                        </tr>
                        <tr>
                            <td>Merk barang</td>
                            <td id = "merk_delete"></td>
                        </tr>
                        <tr>
                            <td>Stok Minimal</td>
                            <td id = "minimal_delete"></td>
                        </tr>
                        <tr>
                            <td>Satuan</td>
                            <td id = "satuan_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var ctrl = "barang";
    var colCount = 1; //ragu either 1/0
    var orderBy = 0;
    var orderDirection = "ASC";
    var searchKey = "";
    var page = 1;
    var content = [];
    function refresh(req_page = 1) {
        page = req_page;
        $.ajax({
            url: "<?php echo base_url();?>ws/"+ctrl+"/content?orderBy="+orderBy+"&orderDirection="+orderDirection+"&page="+page+"&searchKey="+searchKey,
            type: "GET",
            dataType: "JSON",
            success: function(respond) {
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    content = respond["content"];
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr>";
                        for(var b = 0; b<respond["key"].length; b++){
                            html += "<td class = 'align-middle text-center' id = '"+respond["key"][b]+""+respond["content"][a]["id"]+"'>"+respond["content"][a][respond["key"][b]]+"</td>";
                        }
                        html += "<td class = 'align-middle text-center'><i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content("+a+")'></i> | <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content("+a+")'></i></td>";
                        html += "</tr>";
                    }
                }
                else{
                    html += "<tr>";
                    html += "<td colspan = "+colCount+" class = 'align-middle text-center'>No Records Found</td>";
                    html += "</tr>";
                }
                $("#content_container").html(html);
                pagination(respond["page"]);
                
            },
            error: function(){
                var html = "";
                html += "<tr>";
                html += "<td colspan = "+colCount+" class = 'align-middle text-center'>No Records Found</td>";
                html += "</tr>";
                $("#content_container").html(html);
                
                html = "";
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
                $("#pagination_container").html(html);
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
            $("#pagination_container").html(html);
        }
    }
    function sort(colNum){
        if(parseInt(colNum) != orderBy){
            orderBy = colNum; 
            orderDirection = "ASC";
            var orderDirectionHtml = ' <span class="badge badge-primary align-top" id = "orderDirection">ASC</span>';
            $("#orderDirection").remove();
            $("#col"+colNum).append(orderDirectionHtml);
        }
        else{
            var direction = $("#orderDirection").text();
            if(direction == "ASC"){
                orderDirection = "DESC";
            }
            else{
                orderDirection = "ASC";
            }
            $("#orderDirection").text(orderDirection);
        }
        refresh();
    }
    function search(){
        searchKey = $("#search_box").val();
        refresh();
    }
    function tblheader(){
        $.ajax({
            url: "<?php echo base_url();?>ws/"+ctrl+"/columns",
            type: "GET",
            dataType: "JSON",
            async:false,
            success: function(respond) {
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    colCount = respond["content"].length+1; //sama col action
                    html += "<tr>";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<th id = 'col"+a+"' style = 'cursor:pointer' onclick = 'sort("+a+")' class = 'text-center align-middle'>"+respond["content"][a]["col_name"];
                        if(a == 0){
                            html += " <span class='badge badge-primary align-top' id = 'orderDirection'>ASC</span>";
                        }
                        html += "</th>";
                    }
                    html += "<th class = 'text-center align-middle'>Action</th>";
                    html += "</tr>";
                }
                else{
                    html += "<tr>";
                    html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
                    html += "</tr>";
                }
                $("#col_title_container").html(html);
                
            },
            error: function(){
                var html = "<tr>";
                html += "<th class = 'align-middle text-center'>Columns is not defined</th>";
                html += "</tr>";
                $("#col_title_container").html(html);
            }
        });
    }
    window.onload = function(){
        tblheader();
        refresh();
        load_brg_jenis();
        load_brg_merk();
    }
</script>
<script>
    function register_func(){
        var form = $("#register_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/register",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData:false,
            contentType:false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#register_modal").modal("hide");
                    $("#register_form :input").val("");
                    refresh(page);
                }
            }
        });
    }
    function update_func(){
        var form = $("#update_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/update",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData: false,
            contentType: false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#update_form :input").val("");
                    $("#update_modal").modal("hide");
                    refresh(page);
                }
            }
        });
    }
    function delete_func(){
        var id = $("#id_delete").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/delete?id="+id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#delete_modal").modal("hide");
                    refresh(page);
                }
            }
        });
    }
</script>
<script>
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#kode_edit").val(content[row]["kode"]);
        $("#nama_edit").val(content[row]["nama"]);
        $("#id_brg_jenis_edit").val(content[row]["jenis"]);
        $("#keterangan_edit").val(content[row]["ket"]);
        $("#id_brg_merk_edit").val(content[row]["merk"]);
        $("#minimal_edit").val(content[row]["minimal"]);
        $("#satuan_edit").val(content[row]["satuan"]);
        $("#gambar_edit").val(content[row]["image"]);
    }
    function load_delete_content(row){
        $("#id_delete").val(content[row]["id"]);
        $("#kode_delete").html(content[row]["kode"]);
        $("#nama_delete").html(content[row]["nama"]);
        $("#jenis_delete").html(content[row]["jenis"]);
        $("#keterangan_delete").html(content[row]["ket"]);
        $("#merk_delete").html(content[row]["merk"]);
        $("#minimal_delete").html(content[row]["minimal"]);
        $("#satuan_delete").html(content[row]["satuan"]);
    }
</script>
<script>
    $("button[data-toggle='modal']").click(function(){
        load_brg_jenis();
        load_brg_merk();
    });
    function load_brg_jenis(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang_jenis/list",
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<option value = '"+respond["content"][a]["nama"]+"'>";
                    }
                    $("#list_jenis").html(html);
                }
            },
            error:function(){

            }
        });
    }
    function load_brg_merk(){
        $.ajax({
            url:"<?php echo base_url();?>ws/barang_merk/list",
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<option value = '"+respond["content"][a]["nama"]+"'>";
                    }
                    $("#list_merk").html(html);
                }
            },
            error:function(){

            }
        });
    }
</script>