
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
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
                <form id = "update_form" method = "POST" enctype = "multipart/form-data">
                    <input type = "hidden" name = "id" id = "id_edit">
                    
                    <div class = "form-group col-lg-6">
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode" id = "kode_edit">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Jenis Barang</h5>
                        <input list = "datalist_barang_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control" id = "id_brg_jenis_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Merk Barang</h5>
                        <input list = "datalist_barang_merk" type = "text"  required name = "id_brg_merk" class = "form-control" id = "id_brg_merk_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan" id = "keterangan_edit">
                    </div>
                    
                    
                    <div class = "form-group col-lg-6">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control nf-input" required name = "minimal" id = "minimal_edit">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan" id = "satuan_edit" list = "datalist_satuan">
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Harga Satuan</h5>
                        <input type = "text" class = "form-control nf-input" required name = "harga" id = "harga_edit">
                    </div>

                    <div class = "form-group col-lg-6">
                        <h5>Harga Toko</h5>
                        <input type = "text" class = "form-control nf-input" required name = "harga_toko" id = "harga_toko_edit">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Harga Grosir</h5>
                        <input type = "text" class = "form-control nf-input" required name = "harga_grosir" id = "harga_grosir_edit">
                    </div>
                    
                    <div class = "form-group" id = "kombinasi_barang_container">
                        <h5>Kombinasi Barang</h5>
                        <input type="radio" class = "kombinasi_barang_edit" name = "tipe" value="nonkombinasi" onclick = "$('#barang_kombinasi_container_edit').hide()">&nbsp;TIDAK KOMBINASI
                        &nbsp;&nbsp;
                        <input type="radio" class = "kombinasi_barang_edit" name = "tipe" value="kombinasi" onclick = "$('#barang_kombinasi_container_edit').show()">&nbsp;KOMBINASI
                    </div>
                    
                    <table class = "table table-striped table-bordered" id = "barang_kombinasi_container_edit">
                        <thead>
                            <th>Nama Barang</th>
                            <th>Qty (Pcs)</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <tr id = "btn_tambah_baris_barang_container_edit">
                                <td colspan = 3>
                                    <button type = "button" onclick = "tambah_baris_barang_edit()" class = "btn btn-primary btn-sm col-lg-12">Tambah Barang</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class = "form-group col-lg-12">
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
<script>
    var barang_kombinasi_list;
    function load_edit_content(row){
        $(".row_brg_edit").remove();

        $("#id_edit").val(content[row]["id"]);
        $("#kode_edit").val(content[row]["kode"]);
        $("#nama_edit").val(content[row]["nama"]);
        $("#id_brg_jenis_edit").val(content[row]["jenis"]);
        $("#keterangan_edit").val(content[row]["ket"]);
        $("#id_brg_merk_edit").val(content[row]["merk"]);
        $("#minimal_edit").val(content[row]["minimal"]);
        $("#satuan_edit").val(content[row]["satuan"]);
        $("#harga_edit").val(content[row]["harga"]);
        $("#harga_toko_edit").val(content[row]["harga_toko"]);
        $("#harga_grosir_edit").val(content[row]["harga_grosir"]);
        $("#gambar_edit").val(content[row]["image"]);

        console.log(content[row]["dalam_kombinasi"]);
        if(content[row]["dalam_kombinasi"]){
            $("#kombinasi_barang_container").hide();
        }
        else{
            $("#kombinasi_barang_container").show();
        }
        if(!(content[row]["tipe"].toLowerCase() == "kombinasi")){
            $("#barang_kombinasi_container_edit").hide();
            $(".kombinasi_barang_edit[type='radio'][value='nonkombinasi']").prop("checked",true);
            $(".kombinasi_barang_edit[type='radio'][value='kombinasi']").prop("checked",false);
        }
        else{
            $("#barang_kombinasi_container_edit").show();
            $(".kombinasi_barang_edit[type='radio'][value='kombinasi']").prop("checked",true);
            $(".kombinasi_barang_edit[type='radio'][value='nonkombinasi']").prop("checked",false);
            
            $.ajax({
                url:"<?php echo base_url();?>ws/barang/barang_kombinasi?id_barang="+content[row]["id"],
                type:"GET",
                dataType:"JSON",
                success:function(respond){
                    if(respond["status"].toLowerCase() == "success"){
                        var html = "";
                        for(var a = 0; a<respond["content"].length; a++){
                            html += `
                            <tr class = 'row_brg_edit' id = 'id_brg_edit${a}'>
                                <input type = 'hidden' id = 'id_barang_kombinasi${a}' name = 'id_barang_kombinasi${a}' value = '${respond["content"][a]["id"]}'>
                                <input type = 'hidden' name = 'edit[]' value = '${a}'>
                                <td>
                                    <input type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang_edit${a}' value = '${respond["content"][a]["barang"]}'>
                                </td>
                                <td>
                                    <input type = 'text' class = 'form-control' name = 'qty_edit${a}' value = '${respond["content"][a]["qty"]}'>
                                </td>
                                <td>
                                    <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_barang_edit(${a})'></i>
                                </td>
                            </tr>`;
                        }
                        $("#btn_tambah_baris_barang_container_edit").before(html);
                    }
                }
            });
        }
    }
    var baris_barang_counter_edit = 0;
    function tambah_baris_barang_edit(){
        var html = `
        <tr class = 'row_brg_edit'>
            <input type = 'hidden' name = 'check[]' value = '${baris_barang_counter_edit}'>
            <td>
                <input type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang${baris_barang_counter_edit}'>
            </td>
            <td>
                <input type = 'text' class = 'form-control' name = 'qty${baris_barang_counter_edit}'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#btn_tambah_baris_barang_container_edit").before(html);
        baris_barang_counter_edit++;
    }
    function delete_barang_edit(row){
        var id_brg_kombinasi = $("#id_barang_kombinasi"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/barang/remove_barang_kombinasi?id_brg_kombinasi="+id_brg_kombinasi,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                $("#id_brg_edit"+row).remove();
            }
        })
    }
</script>