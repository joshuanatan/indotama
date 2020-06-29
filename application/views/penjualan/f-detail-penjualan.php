<?php
$page_title = "Penjualan";
$breadcrumb = array(
    "Penjualan","Detail Penjualan",$detail[0]["penj_nomor"]
);
$notif_data = array(
    "page_title"=>$page_title
);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <div class="wrapper theme-1-active pimary-color-pink">
            <div class="container-fluid">
                <div class="row mt-20">
                    <div class="col-lg-12 col-sm-12">
                        <div class="panel panel-default card-view">
                            <div class="panel-heading bg-gradient">
                                <div class="pull-left">
                                    <h6 class="panel-title txt-light"><?php echo ucwords($page_title);?></h6>
                                </div>
                                <div class="clearfix"></div>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">Home</a></li>
                                    <?php for($a = 0; $a<count($breadcrumb); $a++):?>
                                    <?php if($a+1 != count($breadcrumb)):?>
                                    <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]);?></a></li>
                                    <?php else:?>
                                    <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]);?></li>
                                    <?php endif;?>
                                    <?php endfor;?>
                                </ol>
                            </div>
                            <div class="panel-wrapper collapse in">
                                <div class="panel-body" style = "background-color:white">
                                    <div class = "col-lg-12">
                                        <form id = "update_form" method = "POST">
                                            <input type = "hidden" name = "id_penjualan" value = "<?php echo $detail[0]["id_pk_penjualan"];?>">
                                            <div class = "form-group col-lg-6">
                                                <h5>Nomor Penjualan</h5>
                                                <input type = "text" class = "form-control" required name = "nomor" disabled value = "<?php echo $detail[0]["penj_nomor"];?>">
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Customer</h5>
                                                <input disabled type = 'text' class = "form-control" list = "datalist_customer" required name = "customer" value = "<?php echo $detail[0]["cust_perusahaan"];?>">
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Tanggal Penjualan</h5>
                                                <input disabled type = "date" class = "form-control" required name = "tgl" value = '<?php echo explode(" ",$detail[0]["penj_tgl"])[0];?>'>
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Dateline</h5>
                                                <input disabled type = "date" class = "form-control" required name = "dateline" value = '<?php echo explode(" ",$detail[0]["penj_dateline_tgl"])[0];?>'>
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Jenis Penjualan</h5>
                                                <input disabled checked type="radio" name="jenis_penjualan" value="OFFLINE" onclick = "$('#online_info_container').hide()">&nbsp;OFFLINE
                                                &nbsp;&nbsp;
                                                <input <?php if(strtolower($detail[0]["penj_jenis"]) == "online") echo "checked";?> type="radio" name="jenis_penjualan" value="ONLINE" onclick = "$('#online_info_container').show()">&nbsp;ONLINE
                                            </div>
                                            <?php if(strtolower($detail[0]["penj_jenis"]) == "online" && $online):?>
                                            <div id = "online_info_container" class = "col-lg-6">
                                                <div class = "form-group">
                                                    <h5>Marketplace</h5>
                                                    <input disabled type = "text" class = "form-control" required value = "<?php echo $online[0]["penj_on_marketplace"];?>" name = "marketplace">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Kurir</h5>
                                                    <input disabled type = "text" class = "form-control" required value = "<?php echo $online[0]["penj_on_kurir"];?>" name = "kurir">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>No Resi</h5>
                                                    <input disabled type = "text" class = "form-control" required value = "<?php echo $online[0]["penj_on_no_resi"];?>" name = "no_resi">
                                                </div>
                                            </div>
                                            <?php endif;?>
                                            <div class = "form-group col-lg-12">
                                                <h5>Custom</h5>
                                                
                                                <h5>Produk Custom</h5>
                                                <table class = "table table-striped table-bordered" style = "width:50%">
                                                    <thead>
                                                        <th>Barang Awal</th>
                                                        <th>Barang Pindah</th>
                                                        <th>Jumlah</th>
                                                    </thead>
                                                    <tbody id = "daftar_brg_custom_container">
                                                        <?php for($a = 0; $a<count($brg_custom); $a++):?>
                                                        <tr>
                                                            <td><?php echo $brg_custom[$a]["brg_awal"];?></td>
                                                            <td><?php echo $brg_custom[$a]["brg_akhir"];?></td>
                                                            <td><?php echo $brg_custom[$a]["brg_pindah_qty"];?></td>
                                                        </tr>
                                                        <?php endfor;?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class = "form-group col-lg-8">
                                                <h5>Item Penjualan</h5>
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Barang</th>
                                                        <th>Jumlah</th>
                                                        <th>Jumlah Markup</th>
                                                        <th>Harga</th>
                                                        <th>Harga Markup</th>
                                                        <th>Notes</th>
                                                    </thead>
                                                    <tbody id = "daftar_brg_jual_add">
                                                        <?php for($a = 0; $a<count($item); $a++):?>
                                                        <tr class = 'add_brg_jual_row_edit' id = "add_brg_jual_row_edit<?php echo $a;?>">
                                                            <td id = 'row<?php echo $a;?>'>
                                                                <input type = 'hidden' id = 'id_brg_jual_edit<?php echo $a;?>' name = 'id_brg_jual_edit<?php echo $a;?>' value = "<?php echo $item[$a]["id_pk_brg_penjualan"];?>">
                                                                <input disabled name = 'check_edit[]' value = "<?php echo $a;?>" type = 'hidden'>
                                                                <input disabled type = 'text' value = "<?php echo $item[$a]["brg_nama"];?>" list = 'datalist_barang_cabang' name = 'brg_edit<?php echo $a;?>' class = 'form-control'>
                                                            </td>
                                                            <td><input disabled type = 'text' value = "<?php echo $item[$a]["brg_penjualan_qty_real"]." ". $item[$a]["brg_penjualan_satuan_real"];?>" name = 'brg_qty_real_edit<?php echo $a;?>' class = 'form-control'></td>
                                                            <td><input disabled type = 'text' value = "<?php echo $item[$a]["brg_penjualan_qty"]." ". $item[$a]["brg_penjualan_satuan"];?>" name = 'brg_qty_edit<?php echo $a;?>' class = 'form-control'></td>
                                                            <td><input disabled type = 'text' value = "<?php echo $item[$a]["brg_harga"];?>" class = 'form-control' readonly></td>
                                                            <td><input disabled type = 'text' value = "<?php echo $item[$a]["brg_penjualan_harga"];?>" name = 'brg_price_edit<?php echo $a;?>' class = 'form-control'></td>
                                                            <td><input disabled type = 'text' value = "<?php echo $item[$a]["brg_penjualan_note"];?>" name = 'brg_notes_edit<?php echo $a;?>' class = 'form-control'></td>
                                                        </tr>
                                                        <?php endfor;?>
                                                        <tr id = "add_brg_jual_but_container">
                                                            <td colspan = 7>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-8">
                                                <h5>Tambahan Penjualan</h5>
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Tambahan</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                        <th>Notes</th>
                                                    </thead>
                                                    <tbody id = "daftar_tambahan_jual_add">
                                                        <?php for($a = 0; $a<count($tambahan); $a++):?>
                                                        <tr class = 'add_tambahan_jual_row_edit' id = "add_tambahan_jual_row_edit<?php echo $a;?>">
                                                            <td>
                                                                <input disabled name = 'tambahan_edit[]' value = '<?php echo $a;?>' type = 'hidden'>
                                                                <input disabled id = 'id_tmbhn_edit<?php echo $a;?>' name = 'id_tmbhn_edit<?php echo $a;?>' value = '<?php echo $tambahan[$a]["id_pk_tmbhn"];?>' type = 'hidden'>
                                                                <input disabled name = 'tmbhn_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn"];?>" type = 'text' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled name = 'tmbhn_jumlah_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn_jumlah"]." ".$tambahan[$a]["tmbhn_satuan"];?>" type = 'text' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled name = 'tmbhn_harga_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn_harga"];?>" type = 'text' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled name = 'tmbhn_notes_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn_notes"];?>" type = 'text' class = 'form-control'>
                                                            </td>
                                                        </tr>
                                                        <?php endfor;?>
                                                        <tr id = "add_tambahan_jual_but_container">
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Total Price</h5>
                                                <input disabled style = "width:50%" type = "text" class = "form-control" required readonly onclick = "count_total_price()" id = "total_price">
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Jenis Pembayaran</h5>
                                                <input checked type="radio" name="jenis_pembayaran" value="FULL PAYMENT">&nbsp;FULL PAYMENT
                                                &nbsp;&nbsp;
                                                <input disabled type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "DP") echo 'checked';?> name="jenis_pembayaran" value="DP">&nbsp;DP
                                                &nbsp;&nbsp;
                                                <input disabled type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "TEMPO") echo 'checked';?> name="jenis_pembayaran" value="TEMPO">&nbsp;TEMPO
                                                &nbsp;&nbsp;
                                                <input disabled type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "KEEP") echo 'checked';?> name="jenis_pembayaran" value="KEEP">&nbsp;KEEP
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Tahapan Pembayaran</h5>
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Pembayaran #</th>
                                                        <th>Persentase</th>
                                                        <th>Jumlah</th>
                                                        <th>Notes</th>
                                                        <th>Dateline Bayar</th>
                                                    </thead>
                                                    <tbody id = "daftar_pembayaran_add">
                                                        <?php for($a = 0; $a<count($pembayaran); $a++):?>
                                                        <tr class = 'add_pembayaran_row_edit' id = "add_pembayaran_row_edit<?php echo $a;?>">
                                                            <td id = 'row<?php echo $a;?>'>
                                                                <input disabled name = 'pembayaran_edit[]' value = <?php echo $a;?> type = 'hidden'>
                                                                <input disabled id = 'id_pembayaran_edit<?php echo $a;?>' name = 'id_pembayaran_edit<?php echo $a;?>' value = <?php echo $pembayaran[$a]["id_pk_penjualan_pembayaran"];?> type = 'hidden'>
                                                                <input disabled type = 'text' value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_nama"];?>" name = 'pmbyrn_nama_edit<?php echo $a;?>' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_persen"];?>" name = 'pmbyrn_persen_edit<?php echo $a;?>' type = 'text' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled type = 'text' onfocus = "count_nominal_persentase('edit',<?php echo $a;?>)" value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_nominal"];?>" name = 'pmbyrn_nominal_edit<?php echo $a;?>' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled type = 'text' value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_notes"];?>" name = 'pmbyrn_notes_edit<?php echo $a;?>' class = 'form-control'>
                                                            </td>
                                                            <td>
                                                                <input disabled type = 'date' value = "<?php echo explode(" ",$pembayaran[$a]["penjualan_pmbyrn_dateline"])[0];?>" name = 'pmbyrn_dateline_edit<?php echo $a;?>' class = 'form-control'>
                                                            </td>
                                                        </tr>
                                                        <?php endfor;?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-12" style = "width:50%">
                                                <button type = "button" class = "btn btn-sm btn-primary" onclick = "close_window()">OK</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>

<script>
    var ctrl = "penjualan";

    var brg_jual_row = 0;  
    function add_brg_jual_row(){
        var html = "<tr class = 'add_brg_jual_row'><td id = 'row"+brg_jual_row+"'><input name = 'check[]' value = "+brg_jual_row+" type = 'hidden'><input type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang("+brg_jual_row+")' id = 'brg"+brg_jual_row+"' name = 'brg"+brg_jual_row+"' class = 'form-control'></td><td><input name = 'brg_qty_real"+brg_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'brg_qty"+brg_jual_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' readonly id = 'harga_barang_jual"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_notes"+brg_jual_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_jual_but_container").before(html);
        brg_jual_row++;    
    }
    var tambahan_jual_row = 0;
    function add_tambahan_jual_row(){
        var html = "<tr class = 'add_tambahan_jual_row'><td><input name = 'tambahan[]' value = "+tambahan_jual_row+" type = 'hidden'><input name = 'tmbhn"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_jumlah"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_harga"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_notes"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_tambahan_jual_but_container").before(html);
        tambahan_jual_row++;        
    }
    var pembayaran_row = 0;  
    function add_pembayaran_row(){
        var html = "<tr class = 'add_pembayaran_row'><td id = 'row"+pembayaran_row+"'><input name = 'pembayaran[]' value = "+pembayaran_row+" type = 'hidden'><input type = 'text' name = 'pmbyrn_nama"+pembayaran_row+"' class = 'form-control'></td><td><input name = 'pmbyrn_persen"+pembayaran_row+"' type = 'text' class = 'form-control'></td><td><input onfocus = 'count_nominal_persentase('',"+pembayaran_row+")' type = 'text' name = 'pmbyrn_nominal"+pembayaran_row+"' class = 'form-control'></td><td><input type = 'text' name = 'pmbyrn_notes"+pembayaran_row+"' class = 'form-control'></td><td><input type = 'date' name = 'pmbyrn_dateline"+pembayaran_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_pembayaran_but_container").before(html);
        pembayaran_row++;    
    }
    var custom_produk_row = 0;  
    function add_custom_produk_row(){
        var html = "<tr class = 'add_custom_produk_row'><td><input name = 'custom[]' value = "+custom_produk_row+" type = 'hidden'><input name = 'custom_brg_awal"+custom_produk_row+"' list = 'datalist_barang_cabang' type = 'text' class = 'form-control'></td><td><input name = 'custom_brg_akhir"+custom_produk_row+"' list = 'datalist_barang_cabang' type = 'text' class = 'form-control'></td><td><input name = 'custom_brg_qty"+custom_produk_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_custom_produk_but_container").before(html);
        custom_produk_row++;    
    }
    function load_harga_barang(row){
        var nama_barang = $("#brg"+row).val();
        var hrg_brg_dsr = $("#datalist_barang_cabang option[value='"+nama_barang+"']").attr("data-baseprice");
        $("#harga_barang_jual"+row).val(hrg_brg_dsr);
    }
</script>
<?php $this->load->view("_base_element/datalist_customer");?>
<?php $this->load->view("_base_element/datalist_barang_cabang");?>
<script>
    load_datalist();
    function load_datalist(){
        load_datalist_customer();
        load_datalist_barang_cabang();
    }
</script>
<div class = "modal fade" id = "custom_produk_modal">
    <div class = "modal-dialog modal-center">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4>Custom Produk</h4>
            </div>
            <div class = "modal-body">
                <form method = "POST" id = "register_brg_pindah_form">
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Produk Asal</th>
                            <th>Produk Custom</th>
                            <th>Qty (Pcs)</th>
                            <th>Action</th>
                        </thead>
                        <tbody id = "daftar_custom_produk_add">
                            <tr id = "add_custom_produk_but_container">
                                <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_custom_produk_row()">Tambah Barang Penjualan</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_brg_pindah()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function close_window() {
        if (confirm("Close Window?")) {
            close();
        }
    }
</script>
<?php $this->load->view("_core_script/update_func");?>
<script>
    function count_total_price(){
        var total = 0;
        for(var a = 0; a < brg_jual_row; a++){
            var qty = $("input[name='brg_qty"+a+"'").val();
            var price = $("input[name='brg_price"+a+"'").val();
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                total += 0;
            }
            else{
                total += parseFloat(qty.split(" ")[0])*parseInt(price);
            }
        }
        for(var a = 0; a < tambahan_jual_row; a++){
            var qty = $("input[name='tmbhn_jumlah"+a+"'").val();
            var price = $("input[name='tmbhn_harga"+a+"'").val();
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                total += 0;
            }
            else{
                total += parseFloat(qty.split(" ")[0])*parseInt(price);
            }
        }
        var brg_jual_edit_row = $(".add_brg_jual_row_edit").length; 
        for(var a = 0; a < brg_jual_edit_row; a++){
            var qty = $("input[name='brg_qty_edit"+a+"'").val();
            var price = $("input[name='brg_price_edit"+a+"'").val();
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                total += 0;
            }
            else{
                total += parseFloat(qty.split(" ")[0])*parseInt(price);
            }
        }
        var tambahan_jual_edit_row = $(".add_tambahan_jual_row_edit").length;
        for(var a = 0; a < tambahan_jual_edit_row; a++){
            var qty = $("input[name='tmbhn_jumlah_edit"+a+"'").val();
            var price = $("input[name='tmbhn_harga_edit"+a+"'").val();
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                total += 0;
            }
            else{
                total += parseFloat(qty.split(" ")[0])*parseInt(price);
            }
        }
        $("#total_price").val(total);
    }
    function count_nominal_persentase(type,row){
        var total = $("#total_price").val();
        var persen = $("input[name='pmbyrn_persen_"+type+row+"']").val();
        if(typeof(persen) == 'undefined' || !persen){
            nominal = 0;
        }
        else{
            nominal = parseFloat(persen.split("%")[0])/100*total;
        }
        $("input[name='pmbyrn_nominal_"+type+row+"']").val(nominal);
    }
    function register_brg_pindah(){
        var form = $("#register_brg_pindah_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/barang_pindah/register?sumber=penjualan&id_ref=<?php echo $detail[0]["id_pk_penjualan"];?>",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData:false,
            contentType:false,
            success:function(respond){
                html = "";
                if(respond["content"]){
                    for(var a = 0; a<respond["content"].length; a++){
                        html += "<tr><td><input type = 'hidden' name = 'brg_custom[]' value = '"+a+"'><input type = 'hidden' name = 'id_brg_custom"+a+"' value = '"+respond["content"][a]["id_brg_pindah"]+"'>"+respond["content"][a]["nama_brg_awal"]+"</td><td>"+respond["content"][a]["nama_brg_akhir"]+"</td><td>"+respond["content"][a]["qty"]+"</td></tr>";
                    }
                }
                else{
                    html = "<tr><td colspan = 3 class = 'align-middle text-center'>No Records Found</td></tr>";
                }
                $("#daftar_brg_custom_container").append(html);
            },
            error:function(){
            }
        });
    }
    
    function delete_brg_penjualan(row){
        var id_brg_jual = $("#id_brg_jual_edit"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/remove_brg_penjualan?id="+id_brg_jual,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#add_brg_jual_row_edit"+row).remove();
                }
            }
        });
    }

    function delete_tmbhn_penjualan(row){
        var id_tmbhn = $("#id_tmbhn_edit"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/remove_tmbhn_penjualan?id="+id_tmbhn,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#add_tambahan_jual_row_edit"+row).remove();
                }
            }
        });
    }

    function delete_pembayaran_penjualan(row){
        var id_pembayaran = $("#id_pembayaran_edit"+row).val();
        $.ajax({
            url:"<?php echo base_url();?>ws/penjualan/remove_pembayaran_penjualan?id="+id_pembayaran,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#add_pembayaran_row_edit"+row).remove();
                }
            }
        });
    }
</script>