
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "register_form" method = "POST">
                    <div class = "form-group">
                        <h5>Nomor Pembelian</h5>
                        <input type = "text" class = "form-control" required name = "nomor" id = "no_pembelian">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-primary btn-sm" style = "width:20%" onclick = "load_detail_pembelian()">Load Data Barang</button>
                    </div>
                    <div class = "form-group">
                        <h5>Detail Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <tr>
                                <th>Cabang</th>
                                <td id = "detail_cabang"></td>    
                            </tr>
                            <tr>
                                <th>Alamat Cabang</th>
                                <td id = "detail_alamat_cabang"></td>    
                            </tr>
                            <tr>
                                <th>No Telp Cabang</th>
                                <td id = "detail_notelp_cabang"></td>    
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td id = "detail_supplier"></td>    
                            </tr>
                        </table>
                    </div>
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
                            <tbody id = "daftar_brg_beli">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <h5>Tambahan Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Tambahan</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                            </thead>
                            <tbody id = "daftar_tambahan_beli">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func();empty_table_form()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script>
    function load_detail_pembelian(){
        var satuan_opt = "";
        <?php for($a = 0; $a<count($satuan); $a++):?>
            satuan_opt += "<option value = '<?php echo $satuan[$a]["id_pk_satuan"];?>'><?php echo $satuan[$a]["satuan_nama"]." - ".$satuan[$a]["satuan_rumus"];?></option>";
        <?php endfor;?>
        var no_pembelian = $("#no_pembelian").val();
        var detail_pembelian;
        var content_brg_pembelian;
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/detail/"+no_pembelian,
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    detail_pembelian = respond["data"];
                    $("#detail_cabang").html(respond["data"][0]["nama_toko"]+" - "+respond["data"][0]["daerah_cabang"])
                    $("#detail_alamat_cabang").html(respond["data"][0]["alamat_cabang"])
                    $("#detail_notelp_cabang").html(respond["data"][0]["notelp_cabang"])
                    $("#detail_supplier").html(respond["data"][0]["supplier"])
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/brg_pembelian?id="+detail_pembelian[0]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'brg_pembelian_row' id = 'brg_pembelian_row"+a+"'><input type = 'hidden' name = 'check[]' value = '"+a+"'><input type = 'hidden' value = '"+respond["content"][a]["id"]+"' name = 'id_brg"+a+"'><td>"+respond["content"][a]["nama_brg"]+"</td><td>"+respond["content"][a]["qty"]+" "+respond["content"][a]["satuan"]+"</td><td>"+respond["content"][a]["harga"]+"</td><td>"+respond["content"][a]["note"]+"</td><td><div style = 'display:inline-block'><input type = 'text' class = 'form-control' style = 'width:50%; display:inline-block' name = 'qty_terima"+a+"'><select class = 'form-control' style = 'width:50%; display:inline-block' name = 'qty_terima"+a+"'>"+satuan_opt+"</select></div></td></tr>";
                    }
                    $("#daftar_brg_beli").html(html);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/pembelian/tmbhn_pembelian?id="+detail_pembelian[0]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".tmbhn_pembelian_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_pembelian = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr class = 'tmbhn_pembelian_row'><td>"+respond["content"][a]["tmbhn"]+"</td><td>"+respond["content"][a]["jumlah"]+" "+respond["content"][a]["satuan"]+"</td><td>"+respond["content"][a]["harga"]+"</td><td>"+respond["content"][a]["notes"]+"</td></tr>";
                    }
                    $("#daftar_tambahan_beli").html(html);
                }
            }
        });
    }
</script>