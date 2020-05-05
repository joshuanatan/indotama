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
                                        <h6 class="panel-title txt-light">Master</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="tab-struct custom-tab-1">
                                            <ul role="tablist" class="nav nav-tabs" id="myTabs_7">
                                                <li role="presentation">
                                                    <a aria-expanded="true" data-toggle="tab" role="tab" id="home_tab_7" href="#home_7" onclick ="alert('cabang');">
                                                        <i class="fa fa-building"></i>
                                                        <span class="right-nav-text" style="margin-left:20px">Cabang</span>
                                                    </a>
                                                </li>
                                                <li role="presentation">
                                                    <a data-toggle="tab" id="profile_tab_9" role="tab" href="#profile_9" aria-expanded="false">
                                                        <i class="fa fa-truck"></i>
                                                        <span class="right-nav-text" style="margin-left:20px">Supplier</span>
                                                    </a>
                                                </li>
                                                <li role="presentation" class="">
                                                    <a data-toggle="tab" id="profile_tab_10" role="tab" href="#profile_10" aria-expanded="false">
                                                        <i class="fa fa-users"></i>
                                                        <span class="right-nav-text" style="margin-left:20px">Customer</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="myTabContent_7">
                                                <div class = "col-lg-12">
                                                    <button type = "button" class = "btn btn-primary btn-sm" data-toggle = "modal" data-target = "#addAttribute" style = "margin-right:10px">Tambah Attribute</button>
                                                    <div class = "align-middle text-center">
                                                        <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                        <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
                                                    </div>
                                                    <br/>
                                                    <div class = "table-responsive ">
                                                        <div class = "form-group">
                                                            <h5>Search Data Here</h5>
                                                            <input id = "search_box" placeholder = "Search data here..." type = "text" class = "form-control form-control-sm col-lg-3 col-sm-12" onkeyup = "search()">
                                                        </div>
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
                        </div>
                    </div>
                    <?php $this->load->view('req/mm_footer.php');?>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>
<div class = "modal fade" id = "addAttribute">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data Bahan</h4>
            </div>
            <div class = "modal-body">
                <form id = "register_form" method = "POST">
                    <input type = "hidden" name = "attr_type" value = "BAHAN">
                    <div class = "form-group">
                        <h5>Nama Bahan</h5>
                        <input type = "text" class = "form-control" required name = "attr_name">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan Bahan</h5>
                        <input type = "text" required name = "attr_unit" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <h5>Harga Satuan</h5>
                        <input type = "text" required name = "attr_price" class = "form-control">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_attr()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class = "modal fade" id = "editAttribute">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data Bahan</h4>
            </div>
            <div class = "modal-body">
                <form id = "edit_form" method = "POST">
                    <input type = "hidden" name = "attr_id" id = "attr_id_edit">
                    <div class = "form-group">
                        <h5>Nama Bahan</h5>
                        <input type = "text" class = "form-control" required name = "attr_name" id = "attr_name_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan Bahan</h5>
                        <input type = "text" required name = "attr_unit" class = "form-control" id = "attr_unit_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Harga Satuan</h5>
                        <input type = "text" required name = "attr_price" class = "form-control" id = "attr_price_edit">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_attr()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class = "modal fade" id = "deleteAttribute">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Bahan</h4>
            </div>
            <div class = "modal-body">
            <input type = "hidden" name = "attr_id" value = "" id = "attr_id_delete">
                <h4 align = "center">Apakah anda yakin akan menghapus data bahan di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Nama Bahan</td>
                            <td id = "attr_name_delete"></td>
                        </tr>
                        <tr>
                            <td>Satuan Bahan</td>
                            <td id = "attr_unit_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "row">
                    <button type = "button" class = "btn btn-sm btn-primary col-lg-3 col-sm-12 offset-lg-3" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_attr()" class = "btn btn-sm btn-danger col-lg-3">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var jenis = "bahan";
    var orderBy = 0;
    var orderDirection = "ASC";
    var searchKey = "";
    var page = 1;
    function refresh(req_page = 1) {
        page = req_page;
        $.ajax({
            url: "<?php echo base_url();?>ws/attribute/list?orderBy="+orderBy+"&orderDirection="+orderDirection+"&page="+page+"&searchKey="+searchKey+"&jenis="+jenis,
            type: "GET",
            dataType: "JSON",
            success: function(respond) {
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr>";
                        html += "<td class = 'align-middle text-center' id = 'name"+a+"'>"+respond["content"][a]["name"]+"</td>";
                        html += "<td class = 'align-middle text-center' id = 'satuan"+a+"'>"+respond["content"][a]["satuan"]+"</td>";
                        html += "<td class = 'align-middle text-center' id = 'harga_satuan"+a+"'>"+respond["content"][a]["harga_satuan"]+"</td>";
                        html += "<td class = 'align-middle text-center' id = 'status"+a+"'>"+respond["content"][a]["status"]+"</td>";
                        html += "<td class = 'align-middle text-center' id = 'last_modified"+a+"'>"+respond["content"][a]["last_modified"]+"</td>";
                        html += "<td class = 'align-middle text-center'><i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#editAttribute' onclick = 'load_content("+respond["content"][a]["id"]+")'></i> | <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-danger md-delete' data-target = '#deleteAttribute' onclick = 'load_delete_content("+respond["content"][a]["id"]+")'></i></td>";
                        html += "</tr>";
                    }
                }
                else{
                    html += "<tr>";
                    html += "<td colspan = 6 class = 'align-middle text-center'>No Records Found</td>";
                    html += "</tr>";
                }
                $("#content_container").html(html);
                pagination(respond["page"]);
                
            },
            error: function(){
                var html = "";
                html += "<tr>";
                html += "<td colspan = 6 class = 'align-middle text-center'>No Records Found</td>";
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
            var orderDirectionHtml = '<span class="badge badge-light align-top" id = "orderDirection">ASC</span>';
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
</script>
<script>
    window.onload = function(){
        refresh();
    }
</script>
<script>
    function register_attr(){
        var form = $("#register_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/attribute/register",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData:false,
            contentType:false,
            success:function(respond){
                $("#addAttribute").modal("hide");
                refresh(page);
            }
        });
    }
    function update_attr(){
        var form = $("#edit_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/attribute/update",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData: false,
            contentType: false,
            success:function(respond){
                $("#editAttribute").modal("hide");
                refresh(page);
            }
        });
    }
    function delete_attr(){
        var attr_id = $("#attr_id_delete").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/attribute/delete/"+attr_id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                $("#deleteAttribute").modal("hide");
                refresh(page);
            }
        });
    }
</script>
<script>
    function load_content(id_submit_attr){
        $.ajax({
            url:"<?php echo base_url();?>ws/attribute/get_attribute/"+id_submit_attr,
            dataType:"JSON",
            type:"GET",
            success:function(respond){
                $("#attr_name_edit").val(respond["attr"]["attr_name"]);
                $("#attr_unit_edit").val(respond["attr"]["attr_unit"]);
                $("#attr_id_edit").val(respond["attr"]["attr_id"]);
                $("#attr_price_edit").val(respond["attr"]["attr_price"]);
            }
        });
    }
    function load_delete_content(id_submit_attr){
        $.ajax({
            url:"<?php echo base_url();?>ws/attribute/get_attribute/"+id_submit_attr,
            dataType:"JSON",
            type:"GET",
            success:function(respond){
                $("#attr_name_delete").html(respond["attr"]["attr_name"]);
                $("#attr_unit_delete").html(respond["attr"]["attr_unit"]);
                $("#attr_id_delete").val(respond["attr"]["attr_id"]);
            }
        });
    }
</script>