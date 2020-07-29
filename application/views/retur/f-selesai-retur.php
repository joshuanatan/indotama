
<div class = "modal fade" id = "selesai_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Konfirmasi Selesai <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <input type = 'hidden' name = 'id' id = "s_id_edit">
                <div class = "form-group col-lg-6">
                    <h5>Nomor Retur</h5>
                    <input readonly type = "text" class = "form-control" list = "datalist_penjualan" required id = "s_no_retur_edit" name = "no_retur">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nomor Penjualan</h5>
                    <input readonly type = "text" class = "form-control" readonly required id = "s_no_penjualan_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Tanggal Retur</h5>
                    <input readonly type = "date" class = "form-control" required id = "s_tgl_retur_edit" name = "tgl_retur">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Opsi Pengembalian</h5>
                    <input type="radio" disabled class = "d_tipe_retur_edit" name = "tipe_retur" checked value="UANG">&nbsp;UANG
                    &nbsp;&nbsp;
                    <input type="radio" disabled class = "d_tipe_retur_edit" name = "tipe_retur" value="BARANG">&nbsp;BARANG
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
                        </thead>
                        <tbody id = "s_daftar_brg_retur">
                            <tr id = "s_add_brg_retur_but_container_edit">
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class = "form-group" id = "s_barang_kembali_container_edit" style = "display:none">
                    <h5>Barang Kembali</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Harga Final</th>
                            <th>Notes</th>
                        </thead>
                        <tbody id = "s_daftar_brg_kembali">
                            <tr id = "s_add_brg_kembali_but_container_edit">
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" onclick = "selesai_retur()">Selesai Retur</button>
                </div>  
            </div>
        </div>
    </div>
</div> 
<script>
    function load_selesai_content(row){
        $("#s_id_edit").val(content[row]["id"]);
        $("#s_no_retur_edit").val(content[row]["no"]);
        $("#s_no_penjualan_edit").val(content[row]["nomor_penj"]);
        $("#s_tgl_retur_edit").val(content[row]["tgl"].split(" ")[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/retur/brg_retur?id_retur="+content[row]["id"],
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".s_brg_retur_counter_edit").remove();
                $(".s_brg_retur_counter").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 's_brg_retur_counter_edit'>
                            <td id = 's_brg_retur_counter_edit${a}'>
                                <input readonly name = 'brg_retur_check_edit[]' value = ${a} type = 'hidden'>
                                <input readonly type = 'hidden' id = 'id_brg_retur_edit${a}' name = 'id_brg_retur_edit${a}' value = '${respond["content"][a]["id"]}'>
                                <input readonly name = 'brg_retur_edit${a}' value = '${respond["content"][a]["nama_brg"]}' type = 'text' class = 'form-control' list = 'datalist_barang_cabang'>
                            </td>
                            <td>${respond["content"][a]["terkirim"]} ${respond["content"][a]["satuan_terkirim"]} / ${respond["content"][a]["beli"]} ${respond["content"][a]["satuan_beli"]}</td>
                            <td>
                                <input readonly name = 'brg_retur_jumlah_edit${a}' value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <input readonly name = 'brg_retur_notes_edit${a}' value = '${respond["content"][a]["notes"]}' type = 'text' class = 'form-control'>
                            </td>
                        </tr>`;
                    }
                    $("#s_add_brg_retur_but_container_edit").before(html);
                }
            }
        });

        if(content[row]["tipe"].toLowerCase() == "barang"){
            $(".s_tipe_retur_edit[value='BARANG']").prop("checked",true);
            $('#s_barang_kembali_container_edit').show();

            $.ajax({
                url:"<?php echo base_url();?>ws/retur/brg_kembali?id_retur="+content[row]["id"],
                type:"GET",
                dataType:"JSON",
                success:function(respond){
                    $(".s_brg_kembali_row_edit").remove();
                    $(".s_add_brg_kembali_row").remove();
                    if(respond["status"] == "SUCCESS"){
                        var html = "";
                        for(var a = 0; a<respond["content"].length; a++){
                            html += `
                            <tr class = 's_brg_kembali_row_edit'>
                                <td id = 's_brg_kembali_row_edit${a}'>
                                    <input readonly name = 'brg_kembali_check_edit[]' value = ${a} type = 'hidden'>
                                    <input readonly type = 'hidden' id = 's_id_brg_kembali_edit${a}'name = 'id_brg_kembali_edit${a}' value = '${respond["content"][a]["id"]}'>
                                    <input readonly type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang(${a})' value = '${respond["content"][a]["nama_brg"]}'id = 'brg${a}' name = 'brg_edit${a}' class = 'form-control'>
                                </td>
                                <td>
                                    <input readonly value = '${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}' name = 'brg_qty_edit${a}' type = 'text' class = 'form-control'>
                                </td>
                                <td>
                                    <input readonly value = '${respond["content"][a]["harga_brg"]}' type = 'text' readonly id = 's_harga_barang_jual${a}' class = 'form-control'>
                                </td>
                                <td>
                                    <input readonly value = '${respond["content"][a]["harga"]}' type = 'text' name = 'brg_price_edit${a}' class = 'form-control'>
                                </td>
                                <td>
                                    <input readonly value = '${respond["content"][a]["note"]}' type = 'text' name = 'brg_notes_edit${a}' class = 'form-control'>
                                </td>
                            </tr>`;
                        }
                        $("#s_add_brg_kembali_but_container_edit").before(html);
                    }
                }
            });
        }
        else if(content[row]["tipe"] == "UANG"){
            $(".s_tipe_retur_edit[value='UANG']").prop("checked",true);
            $('#s_barang_kembali_container_edit').hide();
            
        }
    }
</script>
<script>
    function selesai_retur(){
        var id = $("#s_id_edit").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/selesai?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"].toLowerCase() == "success"){
                    $("#selesai_modal").modal("hide");
                    if(typeof(refresh) != "undefined"){
                        refresh(page);
                    }
                }
            }
        })
    }
</script>