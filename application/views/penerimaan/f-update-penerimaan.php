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
                    <div class = "form-group">
                        <h5>Nomor Pembelian</h5>
                        <input readonly type = "text" class = "form-control" list = "datalist_pembelian_toko" required id = "no_pembelian_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penerimaan</h5>
                        <input type = "date" class = "form-control" required name = "tgl_penerimaan" id = "tgl_penerimaan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Detail Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <tr>
                                <th>Cabang</th>
                                <td id = "detail_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Alamat Cabang</th>
                                <td id = "detail_alamat_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>No Telp Cabang</th>
                                <td id = "detail_notelp_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td id = "detail_supplier_edit"></td>    
                            </tr>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Notes</th>
                                <th style = "width:30%">Penerimaan</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_edit">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Tambahan Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Tambahan</th>
                                <th>Jumlah</th>
                                <th>Notes</th>
                            </thead>
                            <tbody id = "daftar_tambahan_beli_edit">
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
        var satuan_opt = "";
        <?php for($a = 0; $a<count($satuan); $a++):?>
            satuan_opt += "<option value = '<?php echo $satuan[$a]["id_pk_satuan"];?>'><?php echo $satuan[$a]["satuan_nama"]." - ".$satuan[$a]["satuan_rumus"];?></option>";
        <?php endfor;?>

        $("#id_edit").val(content[row]["id"]);
        $("#no_pembelian_edit").val(content[row]["pem_pk_nomor"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#tgl_penerimaan_edit").val(tgl[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/detail/"+content[row]["pem_pk_nomor"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#detail_cabang_edit").html(respond["data"][0]["nama_toko"]+" - "+respond["data"][0]["daerah_cabang"])
                    $("#detail_alamat_cabang_edit").html(respond["data"][0]["alamat_cabang"])
                    $("#detail_notelp_cabang_edit").html(respond["data"][0]["notelp_cabang"])
                    $("#detail_supplier_edit").html(respond["data"][0]["supplier"])
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penerimaan/brg_penerimaan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_pembelian_row'>
                            <input type = 'hidden' name = 'check[]' value = '${a}'>
                            <input type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg_terima${a}'>
                            <td>
                                ${respond["content"][a]["nama_brg"]}<br/>
                                Notes:${respond["content"][a]["pem_note"]}
                            </td>
                            <td>
                                ${respond["content"][a]["pem_qty"]} ${respond["content"][a]["pem_satuan"]}
                            </td>
                            <td>
                                <input type = 'text' class = 'form-control' name = 'notes${a}' value = '${respond["content"][a]["note"]}'>
                            </td>
                            <td>
                                <div style = 'display:inline-block'>
                                    <input value = '${respond["content"][a]["qty"]}' type = 'text' class = 'form-control nf-input' style = 'width:50%; display:inline-block' name = 'qty_terima${a}'>
                                    <select class = 'form-control' style = 'width:50%; display:inline-block' id = 'id_satuan_edit${a}' name = 'id_satuan${a}'>${satuan_opt}</select>
                                </div>
                            </td>
                        </tr>`;
                    }
                    $("#daftar_brg_beli_edit").html(html);
                    init_nf();
                    for(var a = 0; a<respond["content"].length; a++){
                        $("#id_satuan_edit"+a).val(respond["content"][a]["id_satuan"]);
                    }
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/tmbhn_pembelian?id="+content[row]["id_pembelian"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".tmbhn_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'tmbhn_pembelian_row'>
                            <td>
                                ${respond["content"][a]["tmbhn"]}
                            </td>
                            <td>
                                ${respond["content"][a]["jumlah"]} ${respond["content"][a]["satuan"]}
                            </td>
                            <td>
                                ${respond["content"][a]["notes"]}
                            </td>
                        </tr>`;
                    }
                    $("#daftar_tambahan_beli_edit").html(html);
                }
            }
        });
    }
</script>