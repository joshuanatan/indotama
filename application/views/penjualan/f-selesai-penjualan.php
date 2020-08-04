<div class = "modal fade" id = "selesai_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Konfirmasi Selesai <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <div class="panel-group accordion-struct accordion-style-1" id="s_accordion_2" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading activestate" role="tab" id="s_heading_10">
                            <a role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_10" aria-expanded="true">
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Transaksi
                            </a> 
                        </div>
                        <div id="s_collapse_10" class="panel-collapse collapse in" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <div class = "form-group col-lg-6">
                                    <h5>Nomor Penjualan</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_nomor_detail" >
                                </div>
                                <div class = "form-group col-lg-6">
                                    <h5>Customer</h5>
                                    <input disabled type = 'text' class = "form-control" required id = "s_customer_detail">
                                </div>
                                <div class = "form-group col-lg-6">
                                    <h5>Tanggal Penjualan</h5>
                                    <input disabled type = "date" class = "form-control" required id = "s_tgl_detail">
                                </div>
                                <div class = "form-group col-lg-6">
                                    <h5>Dateline</h5>
                                    <input disabled type = "date" class = "form-control" required id = "s_dateline_detail">
                                </div>
                                <div class = "form-group col-lg-12">
                                    <h5>Jenis Penjualan</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_jenis_penjualan_detail">
                                </div>
                                <div class = "form-group col-lg-12">
                                    <h5>Jenis Pembayaran</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_jenis_pembayaran_detail">
                                </div>
                                <div class = "form-group col-lg-12">
                                    <h5>Total Price</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_total_price_detail">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="s_heading_11">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_11" aria-expanded="false" >
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Penjualan Online 
                            </a>
                        </div>
                        <div id="s_collapse_11" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <div class = "form-group">
                                    <h5>Marketplace</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_marketplace_detail">
                                </div>
                                <div class = "form-group">
                                    <h5>Kurir</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_kurir_detail">
                                </div>
                                <div class = "form-group">
                                    <h5>No Resi</h5>
                                    <input disabled type = "text" class = "form-control" required id = "s_no_resi_detail">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="s_heading_12">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_12" aria-expanded="false">
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Barang Custom
                            </a>
                        </div>
                        <div id="s_collapse_12" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <table class = "table table-striped table-bordered">
                                    <thead>
                                        <th>Barang Awal</th>
                                        <th>Barang Pindah</th>
                                        <th>Jumlah</th>
                                    </thead>
                                    <tbody id = "s_daftar_brg_custom_detail">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="s_heading_12">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_13" aria-expanded="false" >
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Barang
                            </a>
                        </div>
                        <div id="s_collapse_13" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <table class = "table table-striped table-bordered">
                                    <thead>
                                        <th>Barang</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Harga Markup</th>
                                        <th>Notes</th>
                                    </thead>
                                    <tbody id = "s_daftar_brg_jual_detail">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="s_heading_13">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_14" aria-expanded="false" >
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Tambahan Penjualan
                            </a>
                        </div>
                        <div id="s_collapse_14" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <table class = "table table-striped table-bordered">
                                    <thead>
                                        <th>Tambahan</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Notes</th>
                                    </thead>
                                    <tbody id = "s_daftar_tambahan_jual_detail">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="s_heading_13">
                            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s_accordion_2" href="#s_collapse_15" aria-expanded="false" >
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Pembayaran
                            </a>
                        </div>
                        <div id="s_collapse_15" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <table class = "table table-striped table-bordered">
                                    <thead>
                                        <th>Pembayaran #</th>
                                        <th>Persentase</th>
                                        <th>Jumlah</th>
                                        <th>Notes</th>
                                        <th>Dateline Pembayaran</th>
                                        <th>Status Pembayaran</th>
                                    </thead>
                                    <tbody id = "s_daftar_pembayaran_detail">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" onclick = "selesai_penjualan_func()">Penjualan Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var id_penjualan;
    function load_selesai_content(row){
        id_penjualan = content[row]["id"];
        $("#s_nomor_detail").val(content[row]["nomor"]);
        $("#s_customer_detail").val(content[row]["cust_display"]);
        $("#s_tgl_detail").val(content[row]["tgl"]);
        $("#s_dateline_detail").val(content[row]["dateline_tgl"]);
        $("#s_jenis_penjualan_detail").val(content[row]["jenis"]);
        $("#s_jenis_pembayaran_detail").val(content[row]["tipe_pembayaran"]);

        var s_total = 0;
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/penjualan_online?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    $("#s_marketplace_detail").val(respond["content"][0]["marketplace"]);
                    $("#s_kurir_detail").val(respond["content"][0]["kurir"]);
                    $("#s_no_resi_detail").val(respond["content"][0]["no_resi"]);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/brg_penjualan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr>
                            <td>${respond["content"][a]["nama_brg"]}</td>
                            <td>${formatting_func(respond["content"][a]["qty"])} ${respond["content"][a]["satuan"]}</td>
                            <td>${formatting_func(respond["content"][a]["harga_stok"])}</td>
                            <td>${formatting_func(respond["content"][a]["harga"])}</td>
                            <td>${respond["content"][a]["note"]}</td>
                        </tr>`;
                        s_total += respond["content"][a]["qty"]*respond["content"][a]["harga"];
                    }
                    $("#s_daftar_brg_jual_detail").html(html);
                }
                else{
                    html = `<tr><td colspan = 6>No Data</td></tr>`;
                    $("#s_daftar_brg_jual_detail").html(html);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/tmbhn_penjualan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr>
                            <td>${respond["content"][a]["tmbhn"]}</td>
                            <td>${formatting_func(respond["content"][a]["jumlah"])} ${respond["content"][a]["satuan"]}</td>
                            <td>${formatting_func(respond["content"][a]["harga"])} Pcs</td>
                            <td>${respond["content"][a]["notes"]}</td>
                        </tr>`;
                        s_total += respond["content"][a]["jumlah"]*respond["content"][a]["harga"];
                    }
                    $("#s_daftar_tambahan_jual_detail").html(html);
                }
                else{
                    html = `<tr><td colspan = 4>No Data</td></tr>`;
                    $("#s_daftar_tambahan_jual_detail").html(html);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/brg_pindah_penjualan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr>
                            <td>${respond["content"][a]["brg_awal"]}</td>
                            <td>${respond["content"][a]["brg_akhir"]}</td>
                            <td>${formatting_func(respond["content"][a]["brg_pindah_qty"])} Pcs</td>
                        </tr>`;
                    }
                    $("#s_daftar_brg_custom_detail").html(html);
                }
                else{
                    html = `<tr><td colspan = 3>No Data</td></tr>`;
                    $("#s_daftar_brg_custom_detail").html(html);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/pembayaran_penjualan?id="+content[row]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"].toLowerCase() == "success"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr>
                            <td>${respond["content"][a]["nama"]}</td>
                            <td>${respond["content"][a]["persen"]}</td>
                            <td>${formatting_func(respond["content"][a]["nominal"])}</td>
                            <td>${respond["content"][a]["notes"]}</td>
                            <td>${respond["content"][a]["dateline"]}</td>
                            <td>${respond["content"][a]["status"]}</td>
                        </tr>`;
                    }
                    $("#s_daftar_pembayaran_detail").html(html);
                }
                else{
                    html = `<tr><td colspan = 6>No Data</td></tr>`;
                    $("#s_daftar_pembayaran_detail").html(html);
                }
            }
        });
        $("#s_total_price_detail").val(formatting_func(s_total));
    }
    function selesai_penjualan_func(){
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/selesai?id="+id_penjualan,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                refresh(page);
                $("#selesai_modal").modal("hide");
            }
        })
    }
</script>