<?php
$page_title = "Warehouse";
$breadcrumb = array(
    "Warehouse"
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
                                        <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?> - Cabang "<?= $cabang[0]['cabang_nama']?>"</h6>
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
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-eye"></i><b> - Details </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-store"></i><b> - Stok Gudang </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-warning md-assignment-account"></i><b> - Admin Gudang </b>
                                            </div>
                                            <br/>
                                            <?php
                                                $data = array(
                                                    "ctrl_model" => "m_warehouse",
                                                    "excel_title" => "Daftar Warehouse"
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
<?php 
$data = array(
    "page_title" => "Warehouse"
);
?>
<?php $this->load->view('warehouse_cabang/f-add-warehouse',$data);?>
<?php $this->load->view('warehouse_cabang/f-update-warehouse',$data);?>
<?php $this->load->view('warehouse_cabang/f-detail-warehouse',$data);?>
<?php $this->load->view('warehouse_cabang/f-delete-warehouse',$data);?>

<script>
    function redirect_brg_warehouse(){
        $('table').find('tr').click( function(){
            var row = $(this).index();
            var id_warehouse = content[row]["id"];
            window.location.href = "<?php echo base_url();?>warehouse/brg_warehouse/"+id_warehouse;
        });
    }
    function redirect_admin_cabang(){
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_cabang = content[row]["id"];
            window.location.href= "<?php echo base_url();?>warehouse/admin/"+id_cabang;
        });
    }
</script>
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>
<?php $this->load->view("_base_element/datalist_cabang");?>

<script>
    load_datalist_cabang_all();



    var ctrl = "warehouse";
    var contentCtrl = "content_warehouse_cabang";
    var tblHeaderCtrl = "columns_warehouse_cabang";
    var colCount = 6; //ragu either 1/0
    var orderBy = 0;
    var orderDirection = "ASC";
    var searchKey = "";
    var page = 1;
    var url_add = "id_cabang=<?php echo $cabang[0]["id_pk_cabang"];?>";

    refresh();
    function refresh(req_page = 1) {
        page = req_page;
        $.ajax({
            url: "<?php echo base_url();?>ws/"+ctrl+"/"+contentCtrl+"?orderBy="+orderBy+"&orderDirection="+orderDirection+"&page="+page+"&searchKey="+searchKey+"&"+url_add,
            type: "GET",
            dataType: "JSON",
            success: function(respond) {
                if(respond["status"] == "SUCCESS"){
                    content = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        var html_status = "";
                        switch(respond["content"][a]["status"].toLowerCase()){
                            case "aktif":
                            html_status += `<td class = 'align-middle text-center'><span class="badge badge-success align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                            break;
                            case "konfirmasi":
                            html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                            break;
                            case "selesai":
                            html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                            break;
                            case "diterima":
                            html_status += `<td class = 'align-middle text-center'><span class="badge badge-primary align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                            break;
                            default:
                            html_status += `<td class = 'align-middle text-center'><span class="badge badge-danger align-top" id = "orderDirection">${respond["content"][a]["status"].toUpperCase()}</span></td>`;
                            break;
                        }
                        html += `
                            <tr>
                                <td>${respond["content"][a]["nama"]}</td>
                                <td>${respond["content"][a]["alamat"]}</td>
                                <td>${respond["content"][a]["notelp"]}</td>
                                <td>${respond["content"][a]["desc"]}</td>
                                ${html_status}
                                <td>${respond["content"][a]["last_modified"]}</td>
                                <td>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></i>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content(${a})'></i>  
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></i>

                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-success md-store' data-target = '#delete_modal' onclick = 'redirect_brg_warehouse(${a})'></i>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-warning md-assignment-account' data-target = '#delete_modal' onclick = 'redirect_admin_cabang(${a})'></i>
                                </td>
                            </tr>
                        `;
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
    }
    
</script>

<?php $this->load->view("_core_script/core");?>