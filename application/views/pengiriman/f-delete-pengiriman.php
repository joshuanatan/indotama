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
                            <th>Tanggal Pengiriman</th>
                            <td id = "tgl_pengiriman_delete"></td>    
                        </tr>
                        <tr>
                            <th>Nomor Penjualan</th>
                            <td id = "nomor_penj_delete"></td>    
                        </tr>
                        <tr>
                            <th>Perusahaan Customer</th>
                            <td id = "perusahaan_cust_delete"></td>    
                        </tr>
                        <tr>
                            <th>Contact Person</th>
                            <td id = "cp_cust_delete"></td>    
                        </tr>
                        <tr>
                            <th>Email / No HP</th>
                            <td id = "contact_cust_delete"></td>    
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <h5>Item Pembelian</h5>
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
        $("#nomor_penj_delete").html(content[row]["nomor"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#tgl_pengiriman_delete").html(tgl[0]);
        $("#perusahaan_cust_delete").html(content[row]["perusahaan_cust"]);
        $("#cp_cust_delete").html(content[row]["suff_cust"]+". "+content[row]["name_cust"]);
        content[row]["email_cust"] == 'null' ? content[row]["email_cust"] = content[row]["email_cust"] : content[row]["email_cust"] = "-";
        content[row]["telp_cust"] == 'null' ? content[row]["telp_cust"] = content[row]["telp_cust"] : content[row]["telp_cust"] = "-";
        content[row]["hp_cust"] == 'null' ? content[row]["hp_cust"] = content[row]["hp_cust"]  : content[row]["hp_cust"] = "-";
        $("#contact_cust_delete").html(content[row]["email_cust"]+" / "+content[row]["telp_cust"]+" / "+content[row]["hp_cust"]);
        $.ajax({
            url:"<?php echo base_url();?>ws/pengiriman/brg_pengiriman?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_pengiriman_row").remove();
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'brg_pengiriman_row'><td>"+respond["content"][a]["nama_brg"]+"<br/>Notes:"+respond["content"][a]["note"]+"</td><td>"+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"]+"</td><td>"+respond["content"][a]["note"]+"</td></tr>";
                    }
                    $("#daftar_brg_beli_delete").html(html);
                }
            }
        });
    }
</script>