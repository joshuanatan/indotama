<?php
$page_title = "Penjualan";
$breadcrumb = array(
    "Penjualan","Ubah Penjualan",$detail[0]["penj_nomor"]
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
            <?php $this->load->view('req/mm_menubar.php');?>
            <div class="page-wrapper">
                <?php $this->load->view('_notification/register_success',$notif_data); ?>
                <?php $this->load->view('_notification/update_success',$notif_data); ?>
                <?php $this->load->view('_notification/delete_success',$notif_data); ?>
                <?php $this->load->view('_notification/register_error',$notif_data); ?>
                <?php $this->load->view('_notification/update_error',$notif_data); ?>
                <?php $this->load->view('_notification/delete_error',$notif_data); ?>
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
                                                    <input type = "text" class = "form-control" required name = "nomor" value = "<?php echo $detail[0]["penj_nomor"];?>">
                                                </div>
                                                <div class = "form-group col-lg-6">
                                                    <h5>Customer</h5>
                                                    <input type = 'text' class = "form-control" list = "datalist_customer" required name = "customer" value = "<?php echo $detail[0]["cust_perusahaan"];?>">
                                                </div>
                                                <div class = "form-group col-lg-6">
                                                    <h5>Tanggal Penjualan</h5>
                                                    <input type = "date" class = "form-control" required name = "tgl" value = '<?php echo explode(" ",$detail[0]["penj_tgl"])[0];?>'>
                                                </div>
                                                <div class = "form-group col-lg-6">
                                                    <h5>Dateline</h5>
                                                    <input type = "date" class = "form-control" required name = "dateline" value = '<?php echo explode(" ",$detail[0]["penj_dateline_tgl"])[0];?>'>
                                                </div>
                                                <div class = "form-group col-lg-6">
                                                    <h5>Jenis Penjualan</h5>
                                                    <input checked type="radio" name="jenis_penjualan" value="OFFLINE" onclick = "$('#online_info_container').hide()">&nbsp;OFFLINE
                                                    &nbsp;&nbsp;
                                                    <input <?php if(strtolower($detail[0]["penj_jenis"]) == "online") echo "checked";?> type="radio" name="jenis_penjualan" value="ONLINE" onclick = "$('#online_info_container').show()">&nbsp;ONLINE
                                                </div>
                                                <div class = "form-group col-lg-6" style = "display:none">
                                                    <h5>Jenis Pembayaran</h5>
                                                    <input checked type="radio" name="jenis_pembayaran" value="FULL PAYMENT">&nbsp;FULL PAYMENT
                                                    &nbsp;&nbsp;
                                                    <input type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "DP") echo 'checked';?> name="jenis_pembayaran" value="DP">&nbsp;DP
                                                    &nbsp;&nbsp;
                                                    <input type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "TEMPO") echo 'checked';?> name="jenis_pembayaran" value="TEMPO">&nbsp;TEMPO
                                                    &nbsp;&nbsp;
                                                    <input type="radio" <?php if(strtoupper($detail[0]["penj_tipe_pembayaran"]) == "KEEP") echo 'checked';?> name="jenis_pembayaran" value="KEEP">&nbsp;KEEP
                                                </div>
                                                <div id = "online_info_container" style = "display:none">
                                                    <div class = "clearfix"></div>
                                                    <div class = "form-group col-lg-6">
                                                        <h5>Marketplace</h5>
                                                        <select id = "marketplace" class = "form-control" id = "marketplace" required name = "marketplace"></select>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                    <div class = "form-group col-lg-6">
                                                        <h5>Kurir</h5>
                                                        <input type = "text" class = "form-control" id = "kurir" required value = "" name = "kurir">
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                    <div class = "form-group col-lg-6">
                                                        <h5>No Resi</h5>
                                                        <input type = "text" class = "form-control" id = "no_resi" required value = "" name = "no_resi">
                                                    </div>
                                                </div>
                                                <div class = "form-group col-lg-12">
                                                    <h5>Custom</h5>
                                                    <button type = "button" class = "btn btn-primary btn-sm" data-toggle = "modal" data-target = "#custom_produk_modal">Custom Produk</button>
                                                </div>
                                                <div class = "form-group col-lg-12">
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
                                                
                                                <div class = "form-group col-lg-12">
                                                    <h5>Item Penjualan</h5>
                                                    <table class = "table table-striped table-bordered">
                                                        <thead>
                                                            <th>Barang</th>
                                                            <th>Jumlah</th>
                                                            <th>Jumlah Markup</th>
                                                            <th>Harga</th>
                                                            <th>Harga Jual</th>
                                                            <th>Harga Final</th>
                                                            <th>Notes</th>
                                                            <th>Action</th>
                                                        </thead>
                                                        <tbody id = "daftar_brg_jual_add">
                                                            <?php for($a = 0; $a<count($item); $a++):?>
                                                            <tr class = 'add_brg_jual_row_edit' id = "add_brg_jual_row_edit<?php echo $a;?>">
                                                                <td id = 'row<?php echo $a;?>'>
                                                                    <input type = 'hidden' id = 'id_brg_jual_edit<?php echo $a;?>' name = 'id_brg_jual_edit<?php echo $a;?>' value = "<?php echo $item[$a]["id_pk_brg_penjualan"];?>">
                                                                    <input name = 'check_edit[]' value = "<?php echo $a;?>" type = 'hidden'>
                                                                    <input type = 'text' value = "<?php echo $item[$a]["brg_nama"];?>" list = 'datalist_barang_cabang' name = 'brg_edit<?php echo $a;?>' class = 'form-control'>
                                                                    <a href = '<?php echo base_url();?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
                                                                </td>
                                                                <td><input type = 'text' value = "<?php echo number_format($item[$a]["brg_penjualan_qty_real"],2,",",".")." ". $item[$a]["brg_penjualan_satuan_real"];?>" name = 'brg_qty_real_edit<?php echo $a;?>' class = 'form-control nf-input'></td>
                                                                <td><input type = 'text' value = "<?php echo number_format($item[$a]["brg_penjualan_qty"],2,",",".")." ". $item[$a]["brg_penjualan_satuan"];?>" name = 'brg_qty_edit<?php echo $a;?>' class = 'form-control nf-input'></td>
                                                                <td><input type = 'text' value = "<?php echo number_format($item[$a]["brg_harga"],0,",",".");?>" class = 'form-control nf-input' readonly></td>
                                                                <td><input type = 'text' value = "<?php echo number_format($item[$a]["brg_penjualan_harga"],0,",",".");?>" name = 'brg_price_edit<?php echo $a;?>' class = 'form-control nf-input'></td>
                                                                <td><input readonly type = 'text' value = "<?php echo number_format($item[$a]["brg_penjualan_harga"]*$item[$a]["brg_penjualan_qty"],2,",",".");?>" id = 'harga_brg_final_edit<?php echo $a;?>' class = 'form-control'></td>
                                                                <td><input type = 'text' value = "<?php echo $item[$a]["brg_penjualan_note"];?>" name = 'brg_notes_edit<?php echo $a;?>' class = 'form-control'></td>
                                                                <td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_brg_penjualan(<?php echo $a;?>);'></i></td></tr>
                                                            <?php endfor;?>
                                                            <tr id = "add_brg_jual_but_container">
                                                                <td colspan = 8><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_jual_row()">Tambah Barang Penjualan</button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class = "form-group col-lg-12">
                                                    <h5>Tambahan Penjualan</h5>
                                                    <table class = "table table-striped table-bordered">
                                                        <thead>
                                                            <th>Tambahan</th>
                                                            <th>Jumlah</th>
                                                            <th>Harga</th>
                                                            <th>Harga Final</th>
                                                            <th>Notes</th>
                                                            <th>Action</th>
                                                        </thead>
                                                        <tbody id = "daftar_tambahan_jual_add">
                                                            <?php for($a = 0; $a<count($tambahan); $a++):?>
                                                            <tr class = 'add_tambahan_jual_row_edit' id = "add_tambahan_jual_row_edit<?php echo $a;?>">
                                                                <td>
                                                                    <input name = 'tambahan_edit[]' value = '<?php echo $a;?>' type = 'hidden'>
                                                                    <input id = 'id_tmbhn_edit<?php echo $a;?>' name = 'id_tmbhn_edit<?php echo $a;?>' value = '<?php echo $tambahan[$a]["id_pk_tmbhn"];?>' type = 'hidden'>
                                                                    <input name = 'tmbhn_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn"];?>" type = 'text' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <input name = 'tmbhn_jumlah_edit<?php echo $a;?>' value = "<?php echo number_format($tambahan[$a]["tmbhn_jumlah"],2,",",".")." ".$tambahan[$a]["tmbhn_satuan"];?>" type = 'text' class = 'form-control nf-input'>
                                                                </td>
                                                                <td>
                                                                    <input name = 'tmbhn_harga_edit<?php echo $a;?>' value = "<?php echo number_format($tambahan[$a]["tmbhn_harga"],0,",",".");?>" type = 'text' class = 'form-control nf-input'>
                                                                </td>
                                                                <td>
                                                                    <input readonly value = "<?php echo number_format($tambahan[$a]["tmbhn_harga"]*$tambahan[$a]["tmbhn_jumlah"],0,",",".");?>" type = 'text' id = 'harga_tambahan_final_edit<?php echo $a;?>' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <input name = 'tmbhn_notes_edit<?php echo $a;?>' value = "<?php echo $tambahan[$a]["tmbhn_notes"];?>" type = 'text' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_tmbhn_penjualan(<?php echo $a;?>);'></i>
                                                                </td>
                                                            </tr>
                                                            <?php endfor;?>
                                                            <tr id = "add_tambahan_jual_but_container">
                                                                <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_tambahan_jual_row()">Tambah Tambahan Penjualan</button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class = "form-group col-lg-12">
                                                    <h5>Total Price</h5>
                                                    <input type = "text" class = "form-control" required readonly onclick = "count_total_price()" id = "total_price">
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
                                                            <th>Action</th>
                                                        </thead>
                                                        <tbody id = "daftar_pembayaran_add">
                                                            <?php for($a = 0; $a<count($pembayaran); $a++):?>
                                                            <tr class = 'add_pembayaran_row_edit' id = "add_pembayaran_row_edit<?php echo $a;?>">
                                                                <td id = 'row<?php echo $a;?>'>
                                                                    <input name = 'pembayaran_edit[]' value = <?php echo $a;?> type = 'hidden'>
                                                                    <input id = 'id_pembayaran_edit<?php echo $a;?>' name = 'id_pembayaran_edit<?php echo $a;?>' value = <?php echo $pembayaran[$a]["id_pk_penjualan_pembayaran"];?> type = 'hidden'>
                                                                    <input type = 'text' value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_nama"];?>" name = 'pmbyrn_nama_edit<?php echo $a;?>' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <input value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_persen"];?>" name = 'pmbyrn_persen_edit<?php echo $a;?>' type = 'text' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <input type = 'text' onfocus = "count_nominal_persentase('_edit',<?php echo $a;?>)" value = "<?php echo number_format($pembayaran[$a]["penjualan_pmbyrn_nominal"],0,",",".");?>" name = 'pmbyrn_nominal_edit<?php echo $a;?>' class = 'form-control nf-input'>
                                                                </td>
                                                                <td>
                                                                    <input type = 'text' value = "<?php echo $pembayaran[$a]["penjualan_pmbyrn_notes"];?>" name = 'pmbyrn_notes_edit<?php echo $a;?>' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <input type = 'date' value = "<?php echo explode(" ",$pembayaran[$a]["penjualan_pmbyrn_dateline"])[0];?>" name = 'pmbyrn_dateline_edit<?php echo $a;?>' class = 'form-control'>
                                                                </td>
                                                                <td>
                                                                    <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = 'delete_pembayaran_penjualan(<?php echo $a;?>);'></i>
                                                                </td>
                                                            </tr>
                                                            <?php endfor;?>
                                                            <tr id = "add_pembayaran_but_container">
                                                                <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_pembayaran_row()">Tambah Tahap Pembayaran</button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class = "form-group col-lg-12" style = "width:50%">
                                                    <button type = "button" class = "btn btn-sm btn-danger" onclick = "close_window()">Cancel</button>
                                                    <button type = "button" onclick = "update_func();location.reload();" class = "btn btn-sm btn-primary">Submit</button>
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
        </div>
        <?php $this->load->view('req/mm_js.php');?>
    </body>
</html>

<script>
    
    var ctrl = "penjualan";

    var brg_jual_row = 0;  
    function add_brg_jual_row(){
        var html = `
        <tr class = 'add_brg_jual_row'>
            <td id = 'row${brg_jual_row}'>
                <input name = 'check[]' value = ${brg_jual_row} type = 'hidden'>
                <input type = 'text' list = 'datalist_barang_cabang' onchange = 'load_harga_barang(${brg_jual_row})' id = 'brg${brg_jual_row}' name = 'brg${brg_jual_row}' class = 'form-control'>
                <a href = '<?php echo base_url();?>toko/brg_cabang' class = 'btn btn-primary btn-sm col-lg-12' target = '_blank'>Tambah Barang Cabang</a>
            </td>
            <td>
                <input name = 'brg_qty_real${brg_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'brg_qty${brg_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' readonly id = 'harga_barang_jual${brg_jual_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_price${brg_jual_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_brg_final${brg_jual_row}'>
            </td>
            <td>
                <input type = 'text' name = 'brg_notes${brg_jual_row}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_brg_jual_but_container").before(html);
        init_nf();
        brg_jual_row++;    
    }
    var tambahan_jual_row = 0;
    function add_tambahan_jual_row(){
        var html = `
        <tr class = 'add_tambahan_jual_row'>
            <td>
                <input name = 'tambahan[]' value = ${tambahan_jual_row} type = 'hidden'>
                <input name = 'tmbhn${tambahan_jual_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input name = 'tmbhn_jumlah${tambahan_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input name = 'tmbhn_harga${tambahan_jual_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <input readonly type = 'text' class = 'form-control nf-input' id = 'harga_tambahan_final${tambahan_jual_row}'>
            </td>
            <td>
                <input name = 'tmbhn_notes${tambahan_jual_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_tambahan_jual_but_container").before(html);
        init_nf();
        tambahan_jual_row++;        
    }
    var pembayaran_row = 0;  
    function add_pembayaran_row(){
        var html = `
        <tr class = 'add_pembayaran_row'>
            <td id = 'row${pembayaran_row}'>
                <input name = 'pembayaran[]' value = ${pembayaran_row} type = 'hidden'>
                <input type = 'text' name = 'pmbyrn_nama${pembayaran_row}' class = 'form-control'>
            </td>
            <td>
                <input name = 'pmbyrn_persen${pembayaran_row}' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input onfocus = 'count_nominal_persentase("",${pembayaran_row})' type = 'text' name = 'pmbyrn_nominal${pembayaran_row}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'pmbyrn_notes${pembayaran_row}' class = 'form-control'>
            </td>
            <td>
                <input type = 'date' name = 'pmbyrn_dateline${pembayaran_row}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_pembayaran_but_container").before(html);
        init_nf();
        pembayaran_row++;    
    }
    var custom_produk_row = 0;  
    function add_custom_produk_row(){
        var html = `
        <tr class = 'add_custom_produk_row'>
            <td>
                <input name = 'custom[]' value = ${custom_produk_row} type = 'hidden'>
                <input name = 'custom_brg_awal${custom_produk_row}' list = 'datalist_barang_cabang' type = 'text' class = 'form-control'>
                <a href = '<?php echo base_url();?>toko/brg_cabang' class = 'btn btn-primary btn-sm' target = '_blank'>Tambah Barang Cabang</a>
            </td>
            <td>
                <input name = 'custom_brg_akhir${custom_produk_row}' list = 'datalist_barang_cabang' type = 'text' class = 'form-control'>
            </td>
            <td>
                <input name = 'custom_brg_qty${custom_produk_row}' type = 'text' class = 'form-control nf-input'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
        $("#add_custom_produk_but_container").before(html);
        init_nf();
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
<?php $this->load->view("_base_element/datalist_marketplace");?>
<script>
    load_datalist();
    function load_datalist(){
        load_datalist_customer();
        load_datalist_barang_cabang();
        load_datalist_marketplace();
        $('#marketplace').html(html_option_marketplace);
        <?php
        if($online):?>
        $("#online_info_container").show();
        $('#marketplace').val('<?php echo $online[0]["penj_on_marketplace"];?>');
        $("#kurir").val('<?php echo $online[0]["penj_on_kurir"];?>');
        $("#no_resi").val('<?php echo $online[0]["penj_on_no_resi"];?>');
        <?php endif;?>
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
<script>
    function count_total_price(){
        var total = 0;
        for(var a = 0; a < brg_jual_row; a++){
            var qty = deformatting_func($("input[name='brg_qty"+a+"']").val().split(" ")[0]);
            var price = deformatting_func($("input[name='brg_price"+a+"']").val().split(" ")[0]);
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                $("#harga_brg_final"+a).val("0");
                total += 0;
            }
            else{
                var count_result = Math.round(parseFloat(qty.split(" ")[0])*parseInt(price));
                $("#harga_brg_final"+a).val(formatting_func(count_result));
                total += count_result;
            }
        }
        for(var a = 0; a < tambahan_jual_row; a++){
            var qty = deformatting_func($("input[name='tmbhn_jumlah"+a+"'").val().split(" ")[0]);
            var price = deformatting_func($("input[name='tmbhn_harga"+a+"'").val().split(" ")[0]);
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                $("#harga_tambahan_final"+a).val("0");
                total += 0;
            }
            else{
                var count_result = Math.round(parseFloat(qty.split(" ")[0])*parseInt(price));
                $("#harga_tambahan_final"+a).val(formatting_func(count_result));
                total += count_result;
            }
        }
        var brg_jual_edit_row = $(".add_brg_jual_row_edit").length; 
        for(var a = 0; a < brg_jual_edit_row; a++){
            var qty = deformatting_func($("input[name='brg_qty_edit"+a+"'").val().split(" ")[0]);
            var price = deformatting_func($("input[name='brg_price_edit"+a+"'").val().split(" ")[0]);
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                $("#harga_brg_final_edit"+a).val("0");
                total += 0;
            }
            else{
                var count_result = Math.round(parseFloat(qty.split(" ")[0])*parseInt(price));
                $("#harga_brg_final_edit"+a).val(formatting_func(count_result));
                total += count_result;
            }
        }
        var tambahan_jual_edit_row = $(".add_tambahan_jual_row_edit").length;
        for(var a = 0; a < tambahan_jual_edit_row; a++){
            var qty = deformatting_func($("input[name='tmbhn_jumlah_edit"+a+"'").val().split(" ")[0]);
            var price = deformatting_func($("input[name='tmbhn_harga_edit"+a+"'").val().split(" ")[0]);
            if(typeof(qty) == 'undefined' || typeof(price) == 'undefined' || !price || !qty){
                $("#harga_tambahan_final_edit"+a).val("0");
                total += 0;
            }
            else{
                var count_result = Math.round(parseFloat(qty.split(" ")[0])*parseInt(price));
                $("#harga_tambahan_final_edit"+a).val(formatting_func(count_result));
                total += count_result;
            }
        }
        $("#total_price").val(formatting_func(total));
    }
    function count_nominal_persentase(type,row){
        var total = deformatting_func($("#total_price").val());
        var persen = $("input[name='pmbyrn_persen"+type+row+"']").val();
        if(typeof(persen) == 'undefined' || !persen){
            nominal = 0;
        }
        else{
            nominal = Math.round(parseFloat(persen.split("%")[0])/100*total);
        }
        $("input[name='pmbyrn_nominal"+type+row+"']").val(formatting_func(nominal));
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
                        html += `
                        <tr>
                            <td>
                                <input type = 'hidden' name = 'brg_custom[]' value = '${a}'>
                                <input type = 'hidden' name = 'id_brg_custom${a}' value = '${respond["content"][a]["id_brg_pindah"]}'>${respond["content"][a]["nama_brg_awal"]}
                            </td>
                            <td>${respond["content"][a]["nama_brg_akhir"]}</td>
                            <td>${respond["content"][a]["qty"]}</td>
                        </tr>`;
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

<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("_core_script/menubar_func");?>
<?php $this->load->view("req/core_script");?>