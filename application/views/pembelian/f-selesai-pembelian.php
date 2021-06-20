<div class = "modal fade" id = "selesai_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Konfirmasi Selesai Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <input type = "hidden" name = "id" id = "s_id_edit">
                <div class = "form-group col-lg-6">
                    <h5>Nomor Pembelian</h5>
                    <input type = "text" class = "form-control" required name = "nomor" disabled id = "s_nomor_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Tanggal Pembelian</h5>
                    <input type = "date" class = "form-control" required name = "tgl" disabled id = "s_tgl_edit">
                </div>
                <div class = "form-group">
                    <h5>Supplier</h5>
                    <input type = 'text' class = "form-control" list = "daftar_supplier" required name = "supplier" disabled id = "s_supplier_edit">
                </div>
                <div class = "form-group">
                    <h5>Item Pembelian</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Notes</th>
                        </thead>
                        <tbody id = "s_daftar_brg_beli_add">
                        </tbody>
                    </table>
                </div>
                <div class = "form-group">
                    <h5>Tambahan Pembelian</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Tambahan</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Notes</th>
                        </thead>
                        <tbody id = "s_daftar_tambahan_beli_add">
                        </tbody>
                    </table>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" onclick = "selesai_pembelian_func()">Pembelian Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_selesai_content(row){
        $("#s_id_edit").val(content[row]["id"]);
        $("#s_nomor_edit").val(content[row]["nomor"]);
        $("#s_tgl_edit").val(content[row]["tgl"]);
        $("#s_supplier_edit").val(content[row]["supplier"]);
        $(".s_brg_pembelian_row_edit_add").remove();
        $(".s_tmbhn_pembelian_row_edit_add").remove();
        if("a"+s_is_brg_pembelian_loaded != "a"+content[row]["id"]){
            s_is_brg_pembelian_loaded = false;
        }
        if("a"+s_is_tambahan_pembelian_loaded != "a"+content[row]["id"]){
            s_is_tambahan_pembelian_loaded = false;
        }
        s_load_brg_pembelian(content[row]["id"]);
        s_load_tambahan_pembelian(content[row]["id"]);
    }
    var content_brg_pembelian = [];
    var s_is_brg_pembelian_loaded = false;
    function s_load_brg_pembelian(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/brg_pembelian?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".s_brg_pembelian_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 's_brg_pembelian_row_edit' id = 's_brg_pembelian_row_edit${a}'>
                            <td>
                                <input disabled name = 'brg_pem_edit[]' value = ${a} type = 'hidden'>
                                <input disabled type = 'hidden' name = 'id_brg_pem_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input disabled type = 'text' list = 'datalist_barang_cabang' name = 'brg_edit${a}' value = '${respond["content"][a]["nama_brg"]}' class = 'form-control'>
                            </td>
                            <td>
                                <input disabled name = 'brg_qty_edit${a}' type = 'text' class = 'form-control' value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}'>
                            </td>
                            <td>
                                <input disabled type = 'text' name = 'brg_price_edit${a}' class = 'form-control' value = '${respond["content"][a]["harga"]}'>
                            </td>
                            <td>
                                <input disabled type = 'text' name = 'brg_notes_edit${a}' class = 'form-control' value = '${respond["content"][a]["note"]}'>
                            </td>
                        </tr>`;
                    }
                    $("#s_daftar_brg_beli_add").html(html);
                    s_is_brg_pembelian_loaded = id;
                }
                if(respond['status']=="ERROR"){
                    var html = "";
                    html+="<td align='center' colspan='4'>No Data</td>";
                    $("#s_daftar_brg_beli_add").html(html);
                    s_is_brg_pembelian_loaded = id;
                }
            }
        });
    }
    var content_tmbhn_pembelian = [];
    var s_is_tambahan_pembelian_loaded = false;
    function s_load_tambahan_pembelian(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/tmbhn_pembelian?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".d_tmbhn_pembelian_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_tmbhn_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'tmbhn_pembelian_row_edit' id = 'd_tmbhn_pembelian_row_edit${a}'>
                            <td>
                                <input disabled name = 'tambahan_edit[]' value = ${a} type = 'hidden'>
                                <input type = 'hidden' name = 'id_tmbhn_pem_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input disabled value = '${respond["content"][a]["tmbhn"]}' name = 'tmbhn_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <input disabled value = '${respond["content"][a]["jumlah"]} ${respond["content"][a]["satuan"]}' name = 'tmbhn_jumlah_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <input disabled value = '${respond["content"][a]["harga"]}' name = 'tmbhn_harga_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <input disabled value = '${respond["content"][a]["notes"]}' name = 'tmbhn_notes_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                        </tr>`;
                    }
                    
                    $("#s_daftar_tambahan_beli_add").html(html);
                    s_is_tambahan_pembelian_loaded = id;
                }
                if(respond["status"]=="ERROR"){
                    var html = "";
                    html+="<tr><td align='center' colspan='4'>No Data</td></tr>";
                    $("#s_daftar_tambahan_beli_add").html(html);
                    s_is_tambahan_pembelian_loaded = id;
                }
            }
        });
    }
    function selesai_pembelian_func(){
        var id = $("#s_id_edit").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/selesai?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $("#selesai_modal").modal("hide");
                if(typeof(refresh) != 'undefined'){
                    refresh(page);
                }
            }
        })
    }
</script>