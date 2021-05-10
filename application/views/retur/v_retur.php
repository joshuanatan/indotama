<?php
$page_title = "Retur";
$breadcrumb = array(
    "Retur"
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
                                            <div class = "d-block">
                                                <button type = "button" class = "btn btn-primary btn-sm col-lg-2 col-sm-12" data-toggle = "modal" data-target = "#register_modal" style = "margin-right:10px">Tambah <?php echo ucwords($page_title);?></button>
                                            </div>
                                            <br/>
                                            <br/>
                                            <div class = "align-middle text-center d-block">
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-eye"></i><b> - Details </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-info md-print"></i><b> - Print Surat Jalan </b>
                                                <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-success md-check"></i><b> - Selesai Retur </b>
                                            </div>
                                            <br/>
                                            <?php
                                                $data = array(
                                                    "ctrl_model" => "m_retur",
                                                    "excel_title" => "Retur Barang"
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
    var ctrl = "retur";
    var additional_button = [
        {
            style:'cursor:pointer;font-size:large',
            class:'text-info md-print',
            onclick:'redirect_print_pdf()'
        },
        {
            class:"text-secondary md-check",
            onclick:"open_konfirmasi_selesai_modal()",
            style:"cursor:pointer"
        }
    ];
</script>
<?php
$data = array(
    "page_title" => "Retur"
);
?>
<?php $this->load->view("retur/f-add-retur",$data);?>
<?php $this->load->view("retur/f-update-retur",$data);?>
<?php $this->load->view("retur/f-detail-retur",$data);?>
<?php $this->load->view("retur/f-delete-retur",$data);?>
<?php $this->load->view("retur/f-selesai-retur",$data);?>


<?php $this->load->view("_base_element/datalist_penjualan");?>
<?php $this->load->view("_base_element/datalist_satuan");?>
<?php $this->load->view("_base_element/datalist_barang_cabang_jualan");?>
<script>
    function redirect_print_pdf(){
        var is_opened = false;
        $('body table').find('tr').click( function(){
            var row = $(this).index();
            var id_retur = content[row]["id"];
            $(this).find(".action_column").click(function(){
                $(this).find("i.text-info.md-print").click(function()  {
                    if(!is_opened){
                        window.open("<?php echo base_url();?>pdf/retur/index/"+id_retur,"_blank");
                        is_opened = true;
                    }
                })
            })
        });
    }
    load_datalist();

    function load_datalist(){
        load_datalist_penjualan();
        load_datalist_satuan();
        load_datalist_barang_cabang_jualan();
    }
</script>   
<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script");?>
<script>
    function open_konfirmasi_selesai_modal(){
        $("body table").find("tr").click(function(){
            var row = $(this).index();
            $(this).find(".action_column").click(function(){
                $(this).find("i.text-secondary.md-check").click(function()  {
                    if(content[row]["status"].toLowerCase() != "aktif"){
                        alert("Data retur sedang menunggu konfirmasi");
                    }
                    else{
                        load_selesai_content(row);
                        $("#selesai_modal").modal("show");
                    }
                })
            })
        })
    }
</script>



<script src = "<?php echo base_url();?>asset/custom/number_formatter.js"></script>
<script>
    var ctrl = "retur";
    var contentCtrl = "content";
    var tblHeaderCtrl = "columns";
    var colCount = 7; //ragu either 1/0
    var orderBy = 0;
    var orderDirection = "ASC";
    var searchKey = "";
    var page = 1;
    var url_add = "";

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
                                <td>${respond["content"][a]["no"]}</td>
                                <td>${respond["content"][a]["tgl"]}</td>
                                <td>${respond["content"][a]["tipe"]}</td>
                                ${html_status}
                                <td>${respond["content"][a]["last_modified"]}</td>
                                <td>${respond["content"][a]["confirm_date"]}</td>
                                <td>${respond["content"][a]["konfirmasi_user"]}</td>
                                <td>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'detail_button text-success md-eye' data-target = '#detail_modal' onclick = 'load_detail_content(${a})'></i>
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'text-primary md-edit' data-target = '#update_modal' onclick = 'load_edit_content(${a})'></i>  
                                    <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' data-target = '#delete_modal' onclick = 'load_delete_content(${a})'></i>
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
<script>
    function load_harga_barang(row){
        var nama_barang = $("#brg"+row).val();
        var hrg_brg_dsr = $("#datalist_barang_cabang_jualan option[value='"+nama_barang+"']").attr("data-baseprice");
        var hrg_brgtoko = $("#datalist_barang_cabang_jualan option[value='"+nama_barang+"']").attr("data-hargatoko");
        var hrg_brggrosir = $("#datalist_barang_cabang_jualan option[value='"+nama_barang+"']").attr("data-hargagrosir");
        $("#harga_barang_jual"+row).val(hrg_brg_dsr);
        $("#harga_barang_toko"+row).val(hrg_brgtoko);
        $("#harga_barang_grosir"+row).val(hrg_brggrosir);
    }
</script>
<?php $this->load->view("_core_script/core");?>