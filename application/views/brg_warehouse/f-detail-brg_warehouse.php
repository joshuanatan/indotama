<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <div class = "form-group col-lg-6">
                    <h5>Kode Barang</h5>
                    <input type = "text" class = "form-control" readonly required name = "kode" id = "d_kode_edit">
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Nama Barang</h5>
                    <input type = "text" class = "form-control" readonly required name = "nama" id = "d_nama_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Jenis Barang</h5>
                    <input list = "datalist_barang_jenis" type = "text"  readonly required name = "id_brg_jenis" class = "form-control" id = "d_id_brg_jenis_edit">
                </div>
                
                
                <div class = "form-group col-lg-6">
                    <h5>Merk Barang</h5>
                    <input list = "datalist_barang_merk" type = "text"  readonly required name = "id_brg_merk" class = "form-control" id = "d_id_brg_merk_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Keterangan</h5>
                    <input type = "text" class = "form-control" readonly required name = "keterangan" id = "d_keterangan_edit">
                </div>
                
                
                <div class = "form-group col-lg-6">
                    <h5>Minimal Stok</h5>
                    <input type = "text" class = "form-control" readonly required name = "minimal" id = "d_minimal_edit">
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Satuan</h5>
                    <input type = "text" class = "form-control" readonly required name = "satuan" id = "d_satuan_edit">
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Harga Satuan</h5>
                    <input type = "text" class = "form-control" readonly required name = "harga" id = "d_harga_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Status Kombinasi Barang</h5>
                    <input type = "text" class = "form-control" readonly required name = "harga" id = "d_tipe_edit">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Gambar</h5>
                    <img width="200px" id = "d_gambar_edit" name = "gambar_current">
                </div>
                <div  id = "d_barang_kombinasi_container_edit">
                    <h5>Daftar Kombinasi Barang</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Nama Barang</th>
                            <th>Qty (Pcs)</th>
                        </thead>
                        <tbody>
                            <tr id = "d_btn_tambah_baris_barang_container_edit">
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class = "form-group">
                    <h5>Stok</h5>
                    <input disabled type = "text" class = "form-control" required name = "stok" id = "d_stok_edit">
                </div>
                <div class = "form-group">
                    <h5>Notes</h5>
                    <input disabled type = 'text' class = "form-control" required name = "notes" id = "d_notes_edit">
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){
        $("#d_stok_edit").val(content[row]["qty"]);
        $("#d_notes_edit").val(content[row]["notes"]);

        $("#d_kode_edit").val(content[row]["kode_brg"]);
        $("#d_nama_edit").val(content[row]["nama_brg"]);
        $("#d_id_brg_jenis_edit").val(content[row]["jenis"]);
        $("#d_keterangan_edit").val(content[row]["ket_brg"]);
        $("#d_id_brg_merk_edit").val(content[row]["merk"]);
        $("#d_minimal_edit").val(content[row]["minimal_brg"]);
        $("#d_satuan_edit").val(content[row]["satuan_brg"]);
        $("#d_harga_edit").val(content[row]["harga"]);
        $("#d_tipe_edit").val(content[row]["tipe"]);

        $("#d_gambar_edit").attr("src","<?php echo base_url() ?>asset/uploads/barang/" + content[row]["image_brg"]);

        if(!(content[row]["tipe"].toLowerCase() == "kombinasi")){
            $("#d_barang_kombinasi_container_edit").hide();
        }
        else{
            $("#d_barang_kombinasi_container_edit").show();
            $.ajax({
                url:"<?php echo base_url();?>ws/barang/barang_kombinasi?id_barang="+content[row]["id_brg"],
                type:"GET",
                dataType:"JSON",
                success:function(respond){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'row_brg_edit' id = 'd_id_brg_edit${a}'>
                            <input readonly type = 'hidden' id = 'd_id_barang_kombinasi${a}' name = 'id_barang_kombinasi${a}' value = '${respond["content"][a]["id"]}'>
                            <input readonly type = 'hidden' name = 'edit[]' value = '${a}'>
                            <td>
                                <input readonly type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang_edit${a}' value = '${respond["content"][a]["barang"]}'>
                            </td>
                            <td>
                                <input readonly type = 'text' class = 'form-control' name = 'qty_edit${a}' value = '${respond["content"][a]["qty"]}'>
                            </td>
                        </tr>`;
                    }
                    $(".row_brg_edit").remove();
                    $("#d_btn_tambah_baris_barang_container_edit").before(html);
                }
            });
        }
    }
</script>