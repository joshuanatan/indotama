<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            ?>
                <input type = "hidden" name = "id" id = "d_id_edit">
                <div class = "form-group">
                    <h5>Nomor Retur</h5>
                    <input type = "text" class = "form-control" list = "datalist_retur_pengiriman" required id = "d_no_retur_edit" readonly>
                </div>
                <div class = "form-group">
                    <h5>Tanggal Penerimaan</h5>
                    <input type = "date" class = "form-control" required name = "tgl_pengiriman" id = "d_tgl_penerimaan_edit" readonly>
                </div>
                <div class = "form-group">
                    <h5>Detail Retur</h5>
                    <table class = "table table-striped table-bordered">
                        <tr>
                            <th>No Penjualan</th>
                            <td id = "d_nomor_penjualan_edit"></td>    
                        </tr>
                        <tr>
                            <th>Tanggal Penjualan</th>
                            <td id = "d_tgl_penjualan_edit"></td>    
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td id = "d_customer_edit"></td>    
                        </tr>
                        <tr>
                            <th>Kontak</th>
                            <td id = "d_contact_edit"></td>    
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td id = "d_alamat_edit"></td>    
                        </tr>
                    </table>
                </div>
                <div class = "form-group">
                    <h5>Item Retur</h5>
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
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){
        var satuan_opt = "";
        $("#d_id_edit").val(content[row]["id"]);
        $("#d_no_retur_edit").val(content[row]["retur_no"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#d_tgl_penerimaan_edit").val(tgl[0]);

        var no_retur = $("#d_no_retur_edit").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/detail/"+no_retur,
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    detail_retur = respond["content"];
                    $("#d_nomor_penjualan_edit").html(respond["content"][0]["nomor_penj"]);
                    $("#d_tgl_penjualan_edit").html(respond["content"][0]["tgl_penj"]);
                    $("#d_customer_edit").html(respond["content"][0]["perusahaan_cust"]+" ("+respond["content"][0]["suff_cust"]+" "+respond["content"][0]["name_cust"]+")");
                    $("#d_contact_edit").html(respond["content"][0]["email_cust"]+" / "+respond["content"][0]["telp_cust"]+" / "+respond["content"][0]["hp_cust"]);
                    $("#d_alamat_edit").html(respond["content"][0]["alamat_cust"]);
                }
            }
        });
        var satuan_opt = "";
        <?php for($a = 0; $a<count($satuan); $a++):?>
            satuan_opt += "<option value = '<?php echo $satuan[$a]["id_pk_satuan"];?>'><?php echo $satuan[$a]["satuan_nama"]." - ".$satuan[$a]["satuan_rumus"];?></option>";
        <?php endfor;?>
        $.ajax({
            url:"<?php echo base_url();?>ws/pengiriman/brg_pengiriman_retur?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_pembelian_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_pembelian_row_edit'>
                            <input type = 'hidden' name = 'check[]' value = '${a}'>
                            <input type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg_kirim${a}'>
                            <td>
                                ${respond["content"][a]["nama_brg"]}<br/>
                                Notes:${respond["content"][a]["brg_notes_retur"]}
                            </td>
                            <td>
                                ${respond["content"][a]["brg_qty_retur"]} ${respond["content"][a]["brg_satuan_retur"]}
                            </td>
                            <td>${respond["content"][a]["note"]}
                            </td>
                            <td>
                                <div style = 'display:inline-block'>
                                    <input disabled value = '${respond["content"][a]["qty"]}' type = 'text' class = 'form-control' style = 'width:50%; display:inline-block' name = 'qty_kirim${a}'>
                                    <select disabled class = 'form-control' style = 'width:50%; display:inline-block' id = 'd_id_satuan_edit${a}' name = 'id_satuan${a}'>${satuan_opt}</select>
                                </div>
                            </td>
                        </tr>`;
                    }
                    $("#d_daftar_brg_beli_edit").html(html);
                    for(var a = 0; a<respond["content"].length; a++){
                        $("#d_id_satuan_edit"+a).val(respond["content"][a]["id_satuan"]);
                    }
                }
            }
        });
    }
</script>