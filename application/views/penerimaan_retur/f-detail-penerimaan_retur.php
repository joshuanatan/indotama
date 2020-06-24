<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                    <input type = "hidden" name = "id" id = "d_id_edit">
                    <div class = "form-group">
                        <h5>Nomor Pembelian</h5>
                        <input readonly type = "text" class = "form-control" list = "list_pembelian" required id = "d_no_pembelian_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penerimaan</h5>
                        <input readonly type = "date" class = "form-control" required name = "tgl_penerimaan" id = "d_tgl_penerimaan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Detail Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <tr>
                                <th>Cabang</th>
                                <td id = "d_detail_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Alamat Cabang</th>
                                <td id = "d_detail_alamat_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>No Telp Cabang</th>
                                <td id = "d_detail_notelp_cabang_edit"></td>    
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td id = "d_detail_supplier_edit"></td>    
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
                            <tbody id = "d_daftar_brg_beli_edit">
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
                            <tbody id = "d_daftar_tambahan_beli_edit">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){

        $("#d_id_edit").val(content[row]["id"]);
        $("#d_no_pembelian_edit").val(content[row]["pem_pk_nomor"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#d_tgl_penerimaan_edit").val(tgl[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/detail/"+content[row]["pem_pk_nomor"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#d_detail_cabang_edit").html(respond["data"][0]["nama_toko"]+" - "+respond["data"][0]["daerah_cabang"])
                    $("#d_detail_alamat_cabang_edit").html(respond["data"][0]["alamat_cabang"])
                    $("#d_detail_notelp_cabang_edit").html(respond["data"][0]["notelp_cabang"])
                    $("#d_detail_supplier_edit").html(respond["data"][0]["supplier"])
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penerimaan/brg_penerimaan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".d_brg_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'd_brg_pembelian_row'><input type = 'hidden' name = 'check[]' value = '"+a+"'><input type = 'hidden' value = '"+respond["content"][a]["id"]+"' name = 'id_brg_terima"+a+"'><td>"+respond["content"][a]["nama_brg"]+"<br/>Notes:"+respond["content"][a]["pem_note"]+"</td><td>"+respond["content"][a]["pem_qty"]+" "+respond["content"][a]["pem_satuan"]+"</td><td><input disabled type = 'text' class = 'form-control' name = 'notes"+a+"' value = '"+respond["content"][a]["note"]+"'></td><td><div style = 'display:inline-block'><input value = '"+respond["content"][a]["qty"]+"' disabled type = 'text' class = 'form-control' style = 'width:50%; display:inline-block' name = 'qty_terima"+a+"'><select disabled class = 'form-control' style = 'width:50%; display:inline-block' id = 'd_id_satuan_edit"+a+"' name = 'id_satuan"+a+"'>"+satuan_opt+"</select></div></td></tr>";
                    }
                    $("#d_daftar_brg_beli_edit").html(html);
                    for(var a = 0; a<respond["content"].length; a++){
                        $("#d_id_satuan_edit"+a).val(respond["content"][a]["id_satuan"]);
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
                $(".d_tmbhn_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'd_tmbhn_pembelian_row'><td>"+respond["content"][a]["tmbhn"]+"</td><td>"+respond["content"][a]["jumlah"]+" "+respond["content"][a]["satuan"]+"</td><td>"+respond["content"][a]["notes"]+"</td></tr>";
                    }
                    $("#d_daftar_tambahan_beli_edit").html(html);
                }
            }
        });
    }
</script>