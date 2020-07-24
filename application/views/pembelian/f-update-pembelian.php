<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Pembelian</h5>
                        <input type = "text" class = "form-control" required name = "nomor" id = "nomor_edit" readonly>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Tanggal Pembelian</h5>
                        <input type = "date" class = "form-control" required name = "tgl" id = "tgl_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Supplier</h5>
                        <input type = 'text' class = "form-control" list = "datalist_supplier" required name = "supplier" id = "supplier_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_add">
                                <tr id = "add_brg_beli_but_container_edit">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_edit_brg_beli_row()">Tambah Barang Pembelian</button>
                                    </td>
                                </tr>
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
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_tambahan_beli_add">
                                <tr id = "add_tambahan_beli_but_container_edit">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_edit_tambahan_beli_row()">Tambah Barang Pembelian</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
<script>
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#nomor_edit").val(content[row]["nomor"]);
        $("#tgl_edit").val(content[row]["tgl"]);
        $("#supplier_edit").val(content[row]["supplier"]);
        $(".brg_pembelian_row_edit_add").remove();
        $(".tmbhn_pembelian_row_edit_add").remove();
        if("a"+is_brg_pembelian_loaded != "a"+content[row]["id"]){
            is_brg_pembelian_loaded = false;
        }
        if("a"+is_tambahan_pembelian_loaded != "a"+content[row]["id"]){
            is_tambahan_pembelian_loaded = false;
        }
        load_brg_pembelian(content[row]["id"]);
        load_tambahan_pembelian(content[row]["id"]);
    }
    var content_brg_pembelian = [];
    var is_brg_pembelian_loaded = false;
    function load_brg_pembelian(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/brg_pembelian?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".brg_pembelian_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_pembelian_row_edit' id = 'brg_pembelian_row_edit${a}'>
                            <td>
                                <input name = 'brg_pem_edit[]' value = ${a} type = 'hidden'>
                                <input type = 'hidden' name = 'id_brg_pem_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input type = 'text' list = 'datalist_barang_cabang' name = 'brg_edit${a}' value = '${respond["content"][a]["nama_brg"]}' class = 'form-control'>
                                <a href = '<?php echo base_url();?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
                            </td>
                            <td>
                                <input name = 'brg_qty_edit${a}' type = 'text' class = 'form-control nf-input' value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}'>
                            </td>
                            <td>
                                <input type = 'text' name = 'brg_price_edit${a}' class = 'form-control nf-input' value = '${respond["content"][a]["harga"]}'>
                            </td>
                            <td>
                                <input type = 'text' name = 'brg_notes_edit${a}' class = 'form-control' value = '${respond["content"][a]["note"]}'>
                            </td>
                            <td>
                                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_pembelian(${a});'></i>
                            </td>
                        </tr>`;
                    }
                    $("#add_brg_beli_but_container_edit").before(html);
                    init_nf();
                    is_brg_pembelian_loaded = id;
                }
            }
        });
    }
    function add_edit_brg_beli_row(){
        var html = `
        <tr class = 'brg_pembelian_row_edit_add'>
            <td>
                <input name = 'check[]' value = ${brg_beli_row} type = 'hidden'>
                <input type = 'text' list = 'datalist_barang_cabang' name = 'brg${brg_beli_row}' class = 'form-control'>
                <a href = '<?php echo base_url();?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
            </td>
            <td>
                <input name = 'brg_qty${brg_beli_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_price${brg_beli_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_notes${brg_beli_row}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_beli_but_container_edit").before(html);
        init_nf();
        brg_beli_row++;    
    }
    function delete_brg_pembelian(row){
        var id_brg_pembelian = content_brg_pembelian[row]["id"];
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/remove_brg_pembelian?id="+id_brg_pembelian,
            type:"DELETE",
            dataType:"JSON",
            success:function(){
                $("#brg_pembelian_row_edit"+row).remove();
            }
        });
    }
    var content_tmbhn_pembelian = [];
    var is_tambahan_pembelian_loaded = false;
    function load_tambahan_pembelian(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/tmbhn_pembelian?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".tmbhn_pembelian_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_tmbhn_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'tmbhn_pembelian_row_edit' id = 'tmbhn_pembelian_row_edit${a}'>
                            <td>
                                <input name = 'tambahan_edit[]' value = ${a} type = 'hidden'>
                                <input type = 'hidden' name = 'id_tmbhn_pem_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input value = '${respond["content"][a]["tmbhn"]}' name = 'tmbhn_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <input value = '${respond["content"][a]["jumlah"]} ${respond["content"][a]["satuan"]}' name = 'tmbhn_jumlah_edit${a}' type = 'text' class = 'form-control nf-input'>
                            </td>
                            <td>
                                <input value = '${respond["content"][a]["harga"]}' name = 'tmbhn_harga_edit${a}' type = 'text' class = 'form-control nf-input'>
                            </td>
                            <td>
                                <input value = '${respond["content"][a]["notes"]}' name = 'tmbhn_notes_edit${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_tmbhn_pembelian(${a});'></i>
                            </td>
                        </tr>`;
                    }
                    $("#add_tambahan_beli_but_container_edit").before(html);
                    init_nf();
                    is_tambahan_pembelian_loaded = id;
                }
            }
        });
    }
    var tambahan_beli_row = 0;
    function add_edit_tambahan_beli_row(){
        var html = `
        <tr class = 'tmbhn_pembelian_row_edit_add'>
            <td>
                <input name = 'tambahan[]' value = ${tambahan_beli_row} type = 'hidden'>
                <input name = 'tmbhn${tambahan_beli_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input name = 'tmbhn_jumlah${tambahan_beli_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'tmbhn_harga${tambahan_beli_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'tmbhn_notes${tambahan_beli_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_tambahan_beli_but_container_edit").before(html);
        init_nf();       
        tambahan_beli_row++; 
    }
    function delete_tmbhn_pembelian(row){
        var id_brg_pembelian = content_tmbhn_pembelian[row]["id"];
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/remove_tmbhn_pembelian?id="+id_brg_pembelian,
            type:"DELETE",
            dataType:"JSON",
            success:function(){
                $("#tmbhn_pembelian_row_edit"+row).remove();
            }
        });
    }
</script>