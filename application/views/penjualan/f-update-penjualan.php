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
                        <h5>Nomor Penjualan</h5>
                        <input type = "text" class = "form-control" required name = "nomor" id = "nomor_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penjualan</h5>
                        <input type = "date" class = "form-control" required name = "tgl" id = "tgl_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Dateline</h5>
                        <input type = "date" class = "form-control" required name = "dateline" id = "dateline_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Customer</h5>
                        <input type = 'text' class = "form-control" list = "daftar_customer" required name = "customer" id = "customer_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Penjualan</h5>
                        <input checked type="radio" name="jenis_penjualan" id = 'OFFLINE_edit' value="OFFLINE" onclick = "$('#online_info_container_edit').hide()">&nbsp;OFFLINE
                        &nbsp;&nbsp;
                        <input type="radio" name="jenis_penjualan" id = 'ONLINE_edit' value="ONLINE" onclick = "$('#online_info_container_edit').show()">&nbsp;ONLINE
                    </div>
                    <div id = "online_info_container_edit" style = "display:none">
                        <div class = "form-group">
                            <h5>Kurir</h5>
                            <input type = "text" class = "form-control" required>
                        </div>
                        <div class = "form-group">
                            <h5>No Resi</h5>
                            <input type = "text" class = "form-control" required>
                        </div>
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Pembayaran</h5>
                        <input checked type="radio" name="jenis_pembayaran" value="FULL PAYMENT">&nbsp;FULL PAYMENT
                        &nbsp;&nbsp;
                        <input type="radio" name="jenis_pembayaran" value="DP">&nbsp;DP
                        &nbsp;&nbsp;
                        <input type="radio" name="jenis_pembayaran" value="TEMPO">&nbsp;TEMPO
                        &nbsp;&nbsp;
                        <input type="radio" name="jenis_pembayaran" value="KEEP">&nbsp;KEEP
                    </div>
                    <div id = "online_info_container" style = "display:none">
                        <div class = "form-group">
                            <h5>Kurir</h5>
                            <input type = "text" class = "form-control" required>
                        </div>
                        <div class = "form-group">
                            <h5>No Resi</h5>
                            <input type = "text" class = "form-control" required>
                        </div>
                    </div>
                    <div class = "form-group">
                        <h5>Item Pemjualan</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_jual_edit">
                                <tr id = "add_brg_jual_but_container_edit">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_edit_brg_jual_row()">Tambah Barang Pemjualan</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Tambahan Pemjualan</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Tambahan</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_tambahan_jual_edit">
                                <tr id = "add_tambahan_jual_but_container_edit">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_edit_tambahan_jual_row()">Tambah Barang Pemjualan</button>
                                    </td>
                                </tr>
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
        $("#id_edit").val(content[row]["id"]);
        $("#nomor_edit").val(content[row]["nomor"]);
        $("#tgl_edit").val(content[row]["tgl"]);
        $("#dateline_edit").val(content[row]["dateline_tgl"]);
        $("#customer_edit").val(content[row]["perusahaan_cust"]);

        $("input[type='radio'][value='"+content[row]["jenis"]+"']").attr("checked",true);
        if(content[row]["jenis"] == "ONLINE"){
            $("#online_info_container_edit").show();
        }
        else{
            $("#online_info_container_edit").hide();
        }   

        $("input[type='radio'][value='"+content[row]["tipe_pembayaran"]+"']").attr("checked",true);

        $(".brg_penjualan_row_edit_add").remove();
        $(".tmbhn_penjualan_row_edit_add").remove();

        load_brg_penjualan(content[row]["id"]);
        load_tambahan_penjualan(content[row]["id"]);
    }
    var content_brg_penjualan = [];
    function load_brg_penjualan(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/brg_penjualan?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".brg_penjualan_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_penjualan = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'brg_penjualan_row_edit' id = 'brg_penjualan_row_edit"+a+"'><td><input name = 'brg_pem_edit[]' value = "+a+" type = 'hidden'><input type = 'hidden' name = 'id_brg_pem_edit"+a+"' value = '"+respond["content"][a]["id"]+"'><input type = 'text' list = 'daftar_barang' name = 'brg_edit"+a+"' value = '"+respond["content"][a]["nama_brg"]+"' class = 'form-control'></td><td><input name = 'brg_qty_edit"+a+"' type = 'text' class = 'form-control' value = '"+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"]+"'></td><td><input type = 'text' name = 'brg_price_edit"+a+"' class = 'form-control' value = '"+respond["content"][a]["harga"]+"'></td><td><input type = 'text' name = 'brg_notes_edit"+a+"' class = 'form-control' value = '"+respond["content"][a]["note"]+"'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_penjualan("+a+");'></i></td></tr>";
                    }
                    $("#add_brg_jual_but_container_edit").before(html);
                }
            }
        });
    }
    function add_edit_brg_jual_row(){
        var html = "<tr class = 'brg_penjualan_row_edit_add'><td><input name = 'check[]' value = "+brg_jual_row+" type = 'hidden'><input type = 'text' list = 'daftar_barang' name = 'brg"+brg_jual_row+"' class = 'form-control'></td><td><input name = 'brg_qty"+brg_jual_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_notes"+brg_jual_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_jual_but_container_edit").before(html);
        brg_jual_row++;    
    }
    function delete_brg_penjualan(row){
        var id_brg_penjualan = content_brg_penjualan[row]["id"];
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/remove_brg_penjualan?id="+id_brg_penjualan,
            type:"DELETE",
            dataType:"JSON",
            success:function(){
                $("#brg_penjualan_row_edit"+row).remove();
            }
        });
    }
    var content_tmbhn_penjualan = [];
    function load_tambahan_penjualan(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/tmbhn_penjualan?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                $(".tmbhn_penjualan_row_edit").remove();
                if(respond["status"] == "SUCCESS"){
                    content_tmbhn_penjualan = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'tmbhn_penjualan_row_edit' id = 'tmbhn_penjualan_row_edit"+a+"'><td><input name = 'tambahan_edit[]' value = "+a+" type = 'hidden'><input type = 'hidden' name = 'id_tmbhn_pem_edit"+a+"' value = '"+respond["content"][a]["id"]+"'><input value = '"+respond["content"][a]["tmbhn"]+"' name = 'tmbhn_edit"+a+"' type = 'text' class = 'form-control'></td><td><input value = '"+respond["content"][a]["jumlah"]+" "+respond["content"][a]["satuan"]+"' name = 'tmbhn_jumlah_edit"+a+"' type = 'text' class = 'form-control'></td><td><input value = '"+respond["content"][a]["harga"]+"' name = 'tmbhn_harga_edit"+a+"' type = 'text' class = 'form-control'></td><td><input value = '"+respond["content"][a]["notes"]+"' name = 'tmbhn_notes_edit"+a+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_tmbhn_penjualan("+a+");'></i></td></tr>";
                    }
                    $("#add_tambahan_jual_but_container_edit").before(html);
                }
            }
        });
    }
    var tambahan_jual_row = 0;
    function add_edit_tambahan_jual_row(){
        var html = "<tr class = 'tmbhn_penjualan_row_edit_add'><td><input name = 'tambahan[]' value = "+tambahan_jual_row+" type = 'hidden'><input name = 'tmbhn"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_jumlah"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_harga"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_notes"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_tambahan_jual_but_container_edit").before(html);
        tambahan_jual_row++;        
    }
    function delete_tmbhn_penjualan(row){
        var id_brg_penjualan = content_tmbhn_penjualan[row]["id"];
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/remove_tmbhn_penjualan?id="+id_brg_penjualan,
            type:"DELETE",
            dataType:"JSON",
            success:function(){
                $("#tmbhn_penjualan_row_edit"+row).remove();
            }
        });
    }
</script>