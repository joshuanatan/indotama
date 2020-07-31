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
                            <td>Nomor Pembelian</td>
                            <td id = "nomor_delete"></td>
                        </tr>
                        <tr>
                            <th>Cabang</th>
                            <td id = "detail_cabang_delete"></td>    
                        </tr>
                        <tr>
                            <th>Alamat Cabang</th>
                            <td id = "detail_alamat_cabang_delete"></td>    
                        </tr>
                        <tr>
                            <th>No Telp Cabang</th>
                            <td id = "detail_notelp_cabang_delete"></td>    
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td id = "detail_supplier_delete"></td>    
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <h5>Item Pembelian</h5>
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Notes</th>
                            <th style = "width:30%">Penerimaan</th>
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
        $("#nomor_delete").html(content[row]["pem_pk_nomor"]);
        var tgl = content[row]["tgl"].split(" ");
        $("#tgl_delete").html(tgl[0]);

        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/detail/"+content[row]["pem_pk_nomor"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#detail_cabang_delete").html(respond["data"][0]["nama_toko"]+" - "+respond["data"][0]["daerah_cabang"])
                    $("#detail_alamat_cabang_delete").html(respond["data"][0]["alamat_cabang"])
                    $("#detail_notelp_cabang_delete").html(respond["data"][0]["notelp_cabang"])
                    $("#detail_supplier_delete").html(respond["data"][0]["supplier"])
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
                        html += "<tr class = 'brg_pembelian_row'><td>"+respond["content"][a]["nama_brg"]+"<br/>Notes:"+respond["content"][a]["pem_note"]+"</td><td>"+respond["content"][a]["pem_qty"]+" "+respond["content"][a]["pem_satuan"]+"</td><td>"+respond["content"][a]["pem_harga"]+"</td><td>"+respond["content"][a]["note"]+"</td><td><div style = 'display:inline-block'>"+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"]+"</div></td></tr>";
                    }
                    $("#daftar_brg_beli_delete").html(html);
                }
            }
        });
    }
</script>