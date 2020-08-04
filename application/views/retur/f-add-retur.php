
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/register_error',$notif_data); ?>
                <form id = "register_form" method = "POST">
                
                    <input type = "hidden" id = "penomoran_otomatis_cb" name = "generate_pem_no[]" value = 1 checked onclick = "toggle_nomor_retur()"> 
                    <!--
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Retur</h5>
                        <input id = "nomor" readonly value = "-" type = "text" class = "form-control" required id = "no_retur" name = "no_retur">
                    </div>
                    -->
                    <div class = "form-group col-lg-12">
                        <h5>Nomor Penjualan</h5>
                        <input type = "text" class = "form-control" list = "datalist_penjualan" required id = "no_penjualan" name = "no_penjualan">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Tanggal Retur</h5>
                        <input type = "date" class = "form-control" required name = "tgl_retur">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Opsi Pengembalian</h5>
                        <input type="radio" name = "tipe_retur" checked value="UANG" onclick = "$('#barang_kembali_container').hide()">&nbsp;UANG
                        &nbsp;&nbsp;
                        <input type="radio" name = "tipe_retur" value="BARANG" onclick = "$('#barang_kembali_container').show()">&nbsp;BARANG
                    </div>
                    <div class = "form-group col-lg-12">
                        <button type = "button" class = "btn btn-primary btn-sm" style = "width:20%" onclick = "load_detail_penjualan()">Load Data Barang</button>
                    </div>
                    <div class = "form-group">
                        <h5>Barang Retur</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah Dikirim / Dipesan</th>
                                <th>Jumlah Kembali</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_retur">
                                <tr id = "add_brg_retur_but_container">
                                    <td colspan = 5><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_retur()">Tambah Barang Retur</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group" id = "barang_kembali_container" style = "display:none">
                        <h5>Barang Kembali</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Harga Final</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_kembali">
                                <tr id = "add_brg_kembali_but_container">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_kembali()">Tambah Barang Pengembalian</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
<script>
    function load_detail_penjualan(){
        var satuan_opt = "";
        var no_penjualan = $("#no_penjualan").val();
        var detail_penjualan;
        var content_brg_penjualan;
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/detail/"+no_penjualan,
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    detail_penjualan = respond["data"];
                    $("#id_penjualan").val(respond["data"][0]["id"]);
                    $("#perusahaan_cust").html(respond["data"][0]["cust_perusahaan"]);
                    $("#cp_cust").html(respond["data"][0]["suff_cust"]+". "+respond["data"][0]["name_cust"]);
                    respond["data"][0]["email_cust"] == 'null' ? respond["data"][0]["email_cust"] = respond["data"][0]["email_cust"] : respond["data"][0]["email_cust"] = "-";
                    respond["data"][0]["telp_cust"] == 'null' ? respond["data"][0]["telp_cust"] = respond["data"][0]["telp_cust"] : respond["data"][0]["telp_cust"] = "-";
                    respond["data"][0]["hp_cust"] == 'null' ? respond["data"][0]["hp_cust"] = respond["data"][0]["hp_cust"]  : respond["data"][0]["hp_cust"] = "-";
                    $("#contact_cust").html(respond["data"][0]["email_cust"]+" / "+respond["data"][0]["telp_cust"]+" / "+respond["data"][0]["hp_cust"]);
                }
            }
        });
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/brg_penjualan?id="+detail_penjualan[0]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_penjualan_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_penjualan = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_retur_counter'>
                            <td>
                                <input name = 'brg_retur_check[]' value = ${a} type = 'hidden'>
                                <input readonly type = 'text' class = 'form-control' name = 'brg_retur${a}' value = '${respond["content"][a]["nama_brg"]}' >
                            </td>
                            <td>
                                ${respond["content"][a]["jmlh_terkirim"]} / ${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}
                            </td>
                            <td>
                                <input type = 'text' class = 'form-control nf-input' name = 'brg_retur_jumlah${a}'>
                            </td>
                            <td>
                                <input name = 'brg_retur_notes${a}' type = 'text' class = 'form-control'>
                            </td>
                            <td>
                                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
                            </td>
                        </tr>`;
                    }
                    $("#daftar_brg_retur").html(html);
                    init_nf();
                    var html_datalist_satuan = "";
                    for(var a = 0; a<datalist_satuan.length; a++){
                        html_datalist_satuan += `
                        <option value = '${datalist_satuan[a]["id"]}'>
                            ${datalist_satuan[a]["nama"].toString().toUpperCase()} / Rumus: ${datalist_satuan[a]["rumus"]}
                        </option>`;
                    }
                    $(".satuan_opt").html(html_datalist_satuan);
                }
            }
        });
    }
    function add_brg_retur(){
        var count = $(".brg_retur_counter").length;
        var html = `
        <tr class = 'brg_retur_counter'>
            <td id = 'brg_retur_counter${count}'>
                <input name = 'brg_retur_check[]' value = ${count} type = 'hidden'>
                <input name = 'brg_retur${count}' type = 'text' class = 'form-control' list = 'datalist_barang_cabang'>
            </td>
            <td>-</td>
            <td>
                <input name = 'brg_retur_jumlah${count}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'brg_retur_notes${count}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_retur_but_container").before(html);
        init_nf();
    }
    var brg_kembali_row = 0;  
    function add_brg_kembali(){
        var html = `
        <tr class = 'add_brg_kembali_row'>
            <td id = 'brg_kembali_counter${brg_kembali_row}'>
                <input name = 'brg_kembali_check[]' value = ${brg_kembali_row} type = 'hidden'>
                <input type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang(${brg_kembali_row})' id = 'brg${brg_kembali_row}' name = 'brg${brg_kembali_row}' class = 'form-control'>
            </td>
            <input name = 'brg_qty_real${brg_kembali_row}' value = '0' type = 'hidden' class = 'form-control nf-input'>
            <td>
                <input name = 'brg_qty${brg_kembali_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' readonly id = 'harga_barang_jual${brg_kembali_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_price${brg_kembali_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_notes${brg_kembali_row}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_kembali_but_container").before(html);
        init_nf();
        brg_kembali_row++;    
    }
    
    function toggle_nomor_retur(){
        if($("#penomoran_otomatis_cb").prop("checked")){
            $("#penomoran_otomatis_cb").prop("checked",true);
            $("#nomor").prop("readonly",true);
            $("#nomor").val("-");
        }
        else{
            $("#penomoran_otomatis_cb").prop("checked",false);
            $("#nomor").prop("readonly",false);
            $("#nomor").val("");
        }
    }
</script>
