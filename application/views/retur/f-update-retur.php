
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
                    <input type = 'hidden' name = 'id' id = "id_edit">
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Retur</h5>
                        <input type = "text" class = "form-control" list = "datalist_penjualan" required id = "no_retur_edit" name = "no_retur" readonly>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Penjualan</h5>
                        <input type = "text" class = "form-control" readonly required id = "no_penjualan_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Tanggal Retur</h5>
                        <input type = "date" class = "form-control" required id = "tgl_retur_edit" name = "tgl_retur">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Opsi Pengembalian</h5>
                        <input type="radio" class = "tipe_retur_edit" name = "tipe_retur" checked value="UANG" onclick = "$('#barang_kembali_container_edit').hide()">&nbsp;UANG
                        &nbsp;&nbsp;
                        <input type="radio" class = "tipe_retur_edit" name = "tipe_retur" value="BARANG" onclick = "$('#barang_kembali_container_edit').show()">&nbsp;BARANG
                    </div>
                    <div class = "clearfix"></div>
                    <div class = "form-group">
                        <h5>Barang Retur</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah Dikirim / Dipesan</th>
                                <th>Jumlah Kembali</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_retur">
                                <tr id = "add_brg_retur_but_container_edit">
                                    <td colspan = 5><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_retur_edit()">Tambah Barang Retur</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group" id = "barang_kembali_container_edit" style = "display:none">
                        <h5>Barang Kembali</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Harga Final</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_kembali">
                                <tr id = "add_brg_kembali_but_container_edit">
                                    <td colspan = 7><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_kembali_edit()">Tambah Barang Pengembalian</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group" id = 'update_btn_container'>
                        <button type = "button" id = "cancel_update_btn" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" id = "submit_update_btn" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script>
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#no_retur_edit").val(content[row]["no"]);
        $("#no_penjualan_edit").val(content[row]["nomor_penj"]);
        $("#tgl_retur_edit").val(content[row]["tgl"].split(" ")[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/retur/brg_retur?id_retur="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_retur_counter_edit").remove();
                $(".brg_retur_counter").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_retur_counter_edit'>
                            <td id = 'brg_retur_counter_edit${a}'>
                                <input name = 'brg_retur_check_edit[]' value = ${a} type = 'hidden'>
                                <input type = 'hidden' id = 'id_brg_retur_edit${a}' name = 'id_brg_retur_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input name = 'brg_retur_edit${a}' value = '${respond["content"][a]["nama_brg"]}' type = 'text' class = 'form-control' list = 'datalist_barang_cabang'>
                            </td>
                            <td>-</td>
                            <td>
                                <input name = 'brg_retur_jumlah_edit${a}' value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}' type = 'text' class = 'form-control nf-input'>
                            </td>
                            <td>
                                <input name = 'brg_retur_notes_edit${a}' value = '${respond["content"][a]["notes"]}' type = 'text' class = 'form-control'>
                            </td>
                            <td>`;
                                if(content[row]["status"].toLowerCase() == "menunggu konfirmasi"){
                                    html += `<i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_retur(${a})'></i>`;
                                }
                            html += `
                            </td>
                        </tr>`;
                    }
                    $("#add_brg_retur_but_container_edit").before(html);
                    init_nf();
                }
            }
        });

        if(content[row]["tipe"] == "BARANG"){
            $(".tipe_retur_edit[value='BARANG']").prop("checked",true);
            $('#barang_kembali_container_edit').show();

            $.ajax({
                url:"<?php echo base_url();?>ws/retur/brg_kembali?id_retur="+content[row]["id"],
                type:"GET",
                async:false,
                dataType:"JSON",
                success:function(respond){
                    $(".brg_kembali_row_edit").remove();
                    $(".add_brg_kembali_row").remove();
                    if(respond["status"] == "SUCCESS"){
                        var html = "";
                        for(var a = 0; a<respond["content"].length; a++){
                            html += `
                            <tr class = 'brg_kembali_row_edit'>
                                <td id = 'brg_kembali_row_edit${a}'>
                                    <input name = 'brg_kembali_check_edit[]' value = ${a} type = 'hidden'>
                                    <input type = 'hidden' id = 'id_brg_kembali_edit${a}'name = 'id_brg_kembali_edit${a}' value = '${respond["content"][a]["id"]}'>
                                    <input type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang(${a})' value = '${respond["content"][a]["nama_brg"]}'id = 'brg${a}' name = 'brg_edit${a}' class = 'form-control'>
                                </td>
                                <td>
                                    <input value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}' name = 'brg_qty_edit${a}' type = 'text' class = 'form-control nf-input'>
                                </td>
                                <td>
                                    <input value = '${respond["content"][a]["harga_brg"]}' type = 'text' readonly id = 'harga_barang_jual${a}' class = 'form-control nf-input'>
                                </td>
                                <td>
                                    <input value = '${respond["content"][a]["harga"]}' type = 'text' name = 'brg_price_edit${a}' class = 'form-control nf-input'>
                                </td>
                                <td>
                                    <input value = '${respond["content"][a]["note"]}' type = 'text' name = 'brg_notes_edit${a}' class = 'form-control'>
                                </td>
                                <td>`;
                                if(content[row]["status"].toLowerCase() == "menunggu konfirmasi"){
                                    html += `<i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_kembali(${a})'></i>`;
                                }
                                html += 
                                `</td>
                            </tr>`;
                        }
                        $("#add_brg_kembali_but_container_edit").before(html);
                        init_nf();
                    }
                }
            });
        }
        else if(content[row]["tipe"] == "UANG"){
            $(".tipe_retur_edit[value='UANG']").prop("checked",true);
            $('#barang_kembali_container_edit').hide();
            
        }
        if(content[row]["status"].toLowerCase() != "menunggu konfirmasi"){
            $("#add_brg_retur_but_container_edit").empty();
            $("#add_brg_kembali_but_container_edit").empty();
            $("#submit_update_btn").remove();
            $("#update_form input").attr("disabled",true);
        }
        else{
            $("#add_brg_retur_but_container_edit").html(`<td colspan = 5><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_retur_edit()">Tambah Barang Retur</button></td>`);
            $("#add_brg_kembali_but_container_edit").html(` <td colspan = 7><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_kembali_edit()">Tambah Barang Pengembalian</button></td>`);
            $("#update_btn_container").html(`
                        <button type = "button" id = "cancel_update_btn" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" id = "submit_update_btn" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>`);
            $("#update_form input").attr("disabled",false);
        }
    }
    function delete_brg_retur(row){
        var id = $("#id_brg_retur_edit"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/delete_brg_retur?id="+id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#brg_retur_counter_edit"+row).parent().remove();
                }
            }
        })
    }
    function delete_brg_kembali(row){
        var id = $("#id_brg_kembali_edit"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/delete_brg_kembali?id="+id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#brg_kembali_row_edit"+row).parent().remove();
                }
            }
        })
    }
    function add_brg_retur_edit(){
        var count = $(".brg_retur_counter").length;
        var html = `
        <tr class = 'brg_retur_counter'>
            <td id = 'brg_retur_counter${count}'>
                <input name = 'brg_retur_check[]' value = ${count} type = 'hidden'>
                <input name = 'brg_retur${count}' type = 'text' class = 'form-control' list = 'datalist_barang_cabang'>
            </td>
            <td>-</td>
            <td>
                <input name = 'brg_retur_jumlah${count}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'brg_retur_notes${count}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_retur_but_container_edit").before(html);
        init_nf();
    }
    var brg_kembali_row = 0;  
    function add_brg_kembali_edit(){
        var html = `
        <tr class = 'add_brg_kembali_row'>
            <td id = 'brg_kembali_counter${brg_kembali_row}'>
                <input name = 'brg_kembali_check[]' value = ${brg_kembali_row} type = 'hidden'>
                <input type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang(${brg_kembali_row})' id = 'brg${brg_kembali_row}' name = 'brg${brg_kembali_row}' class = 'form-control'>
            </td>
            <td>
                <input name = 'brg_qty${brg_kembali_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' readonly id = 'harga_barang_jual${brg_kembali_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_price${brg_kembali_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_notes${brg_kembali_row}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_kembali_but_container_edit").before(html);
        brg_kembali_row++;    
        init_inf();
    }
</script>
