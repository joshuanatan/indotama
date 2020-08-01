
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Pemberian <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <table class = "table table-striped table-bordered">
                    <thead>
                        <tr>
                            <td>Tanggal Pemberian</td>
                            <td>Jumlah Pemberian</td>
                            <td>Status Pemberian</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody id = "pemberian_list_container">
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var pemenuhan_detail_content;
    function load_detail_content(row){
        $.ajax({
            url:"<?php echo base_url();?>ws/pemenuhan/list_data_pemenuhan?id_brg_permintaan="+content[row]["id"],
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                
                $("#pemberian_list_container").html(`<tr><td colspan = 4>No Data</td></tr>`);
                if(respond["status"] == "SUCCESS"){
                    pemenuhan_detail_content = respond["content"];
                    var html = "";
                    for(var a = 0; a < respond["content"].length; a++){
                        html += `
                        <tr id = 'pemenuhan_list${a}'>
                            <td>${respond["content"][a]["last_modified"]}</td>
                            <td>${respond["content"][a]["qty"]} Pcs</td>
                            <td>${respond["content"][a]["status"]}</td>
                            <td>
                                <i style = 'cursor:pointer;font-size:large' data-toggle = 'modal' class = 'delete_button text-danger md-delete' onclick = 'hapus_pemberian(${a})'></i>
                            </td>
                        </tr>`;
                    }
                    $("#pemberian_list_container").html(html);
                }
            }
        })
    }
    function hapus_pemberian(row){
        $.ajax({
            url:"<?php echo base_url();?>ws/pemenuhan/hapus_pemberian?id_pemenuhan="+pemenuhan_detail_content[row]["id"],
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                $("#pemenuhan_list"+row).remove();
                refresh(page);
                $("#detail_modal").modal("hide");
            }
        })
    }
</script>