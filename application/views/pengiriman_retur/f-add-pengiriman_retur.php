
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
                    <input type = 'hidden' name = 'id_reff' id = 'id_retur'>
                    <input type = 'hidden' name = 'type' value = '<?php echo $type;?>'>
                    <input type = 'hidden' name = 'tipe_pengiriman' value = '<?php echo $tipe_pengiriman;?>'>
                    <input type = 'hidden' name = 'id_tempat_pengiriman' value = '<?php echo $id_tempat_pengiriman;?>'>
                    <div class = "form-group">
                        <h5>Nomor Retur</h5>
                        <input type = "text" class = "form-control" list = "datalist_retur_pengiriman" required id = "no_retur">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-primary btn-sm" style = "width:20%" onclick = "load_detail_retur()">Load Data Barang</button>
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Pengiriman</h5>
                        <input type = "date" class = "form-control" required name = "tgl_pengiriman">
                    </div>
                    <div class = "form-group">
                        <h5>Detail Retur</h5>
                        <table class = "table table-striped table-bordered">
                            <tr>
                                <th>No Penjualan</th>
                                <td id = "nomor_penjualan"></td>    
                            </tr>
                            <tr>
                                <th>Tanggal Penjualan</th>
                                <td id = "tgl_penjualan"></td>    
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td id = "customer"></td>    
                            </tr>
                            <tr>
                                <th>Kontak</th>
                                <td id = "contact"></td>    
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td id = "alamat"></td>    
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
                                <th style = "width:30%">Pengiriman</th>
                            </thead>
                            <tbody id = "dftr_brg_retur">
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
    function load_detail_retur(){
        var no_retur = $("#no_retur").val();
        var detail_retur;
        var content_brg_retur;
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/detail/"+no_retur,
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    detail_retur = respond["content"];
                    $("#id_retur").val(respond["content"][0]["id"]);
                    $("#nomor_penjualan").html(respond["content"][0]["nomor_penj"]);
                    $("#tgl_penjualan").html(respond["content"][0]["tgl_penj"]);
                    $("#customer").html(respond["content"][0]["perusahaan_cust"]+" ("+respond["content"][0]["suff_cust"]+" "+respond["content"][0]["name_cust"]+")");
                    $("#contact").html(respond["content"][0]["email_cust"]+" / "+respond["content"][0]["telp_cust"]+" / "+respond["content"][0]["hp_cust"]);
                    $("#alamat").html(respond["content"][0]["alamat_cust"]);
                }
            }
        });
        var satuan_opt = "";
        <?php for($a = 0; $a<count($satuan); $a++):?>
            satuan_opt += "<option value = '<?php echo $satuan[$a]["id_pk_satuan"];?>'><?php echo $satuan[$a]["satuan_nama"]." - ".$satuan[$a]["satuan_rumus"];?></option>";
        <?php endfor;?>
        $.ajax({
            url:"<?php echo base_url();?>ws/retur/brg_kembali?id_retur="+detail_retur[0]["id"],
            type:"GET",
            async:false,
            dataType:"JSON",
            success:function(respond){
                $(".brg_retur_row").remove();
                if(respond["status"] == "SUCCESS"){
                    content_brg_retur = respond["content"];
                    var html = "";
                    for(var a = 0; a<respond["content"].length; a++){
                        html += `
                        <tr class = 'brg_retur_row'>
                            <input type = 'hidden' name = 'check[]' value = '${a}'>
                            <input type = 'hidden' value = '${respond["content"][a]["id"]}' name = 'id_brg${a}'>
                            <td>
                                ${respond["content"][a]["nama_brg"]}<br/>
                                Notes:${respond["content"][a]["notes"]}
                            </td>
                            <td>
                                ${respond["content"][a]["qty"]} ${respond["content"][a]["satuan"]}
                            </td>
                            <td>
                                <input type = 'text' class = 'form-control' name = 'notes${a}'>
                            </td>
                            <td>
                                <div style = 'display:inline-block'>
                                    <input type = 'text' class = 'form-control nf-input' style = 'width:50%; display:inline-block' name = 'qty_kirim${a}'>
                                    <select class = 'form-control' style = 'width:50%; display:inline-block' name = 'id_satuan${a}'>${satuan_opt}</select>
                                </div>
                            </td>
                        </tr>`;
                    }
                    $("#dftr_brg_retur").html(html);
                    init_nf();
                }
            }
        });
    }
</script>

