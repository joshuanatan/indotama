<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <input type = "hidden" id = "id_delete">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Tanggal Penerimaan</td>
                            <td id = "tgl_delete"></td>
                        </tr>
                        <tr>
                            <td>Nomor Retur</td>
                            <td id = "no_retur_delete"></td>
                        </tr>
                        <tr>
                            <th>No Penjualan</th>
                            <td id = "nomor_penjualan_delete"></td>    
                        </tr>
                        <tr>
                            <th>Tanggal Penjualan</th>
                            <td id = "tgl_penjualan_delete"></td>    
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td id = "customer_delete"></td>    
                        </tr>
                        <tr>
                            <th>Kontak</th>
                            <td id = "contact_delete"></td>    
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td id = "alamat_delete"></td>    
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <h5>Item Penerimaan</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Notes</th>
                        </thead>
                        <tbody id = "daftar_brg_beli_delete">
                        </tbody>
                    </table>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(row){
        $("#id_delete").val(content[row]["id"]);
        $("#no_retur_delete").html(content[row]["retur_no"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#tgl_delete").html(tgl[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/retur/detail/"+content[row]["retur_no"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    detail_retur = respond["content"];
                    $("#nomor_penjualan_delete").html(respond["content"][0]["nomor_penj"]);
                    $("#tgl_penjualan_delete").html(respond["content"][0]["tgl_penj"]);
                    $("#customer_delete").html(respond["content"][0]["perusahaan_cust"]+" ("+respond["content"][0]["suff_cust"]+" "+respond["content"][0]["name_cust"]+")");
                    $("#contact_delete").html(respond["content"][0]["email_cust"]+" / "+respond["content"][0]["telp_cust"]+" / "+respond["content"][0]["hp_cust"]);
                    $("#alamat_delete").html(respond["content"][0]["alamat_cust"]);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penerimaan/brg_penerimaan_retur?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr><td>"+respond["content"][a]["nama_brg"]+"<br/>Notes:"+respond["content"][a]["brg_notes_retur"]+"</td><td>"+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"]+"</td><td>"+respond["content"][a]["note"]+"</td></tr>";
                    }
                    $("#daftar_brg_beli_delete").html(html);
                }
            }
        });
    }
</script>