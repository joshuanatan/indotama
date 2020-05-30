<?php
$page_title = "Penjualan";
$breadcrumb = array(
    "Penjualan","Tambah Penjualan"
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
                                        <form id = "register_form" method = "POST">
                                            <input type = "hidden" name = "id_cabang" value = "<?php echo $this->session->id_cabang;?>">
                                            <div class = "form-group col-lg-6">
                                                <h5>Nomor Penjualan</h5>
                                                <input type = "text" class = "form-control" required name = "nomor">
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Customer</h5>
                                                <input type = 'text' class = "form-control" list = "datalist_customer" required name = "customer">
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Tanggal Penjualan</h5>
                                                <input type = "date" class = "form-control" required name = "tgl">
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Dateline</h5>
                                                <input type = "date" class = "form-control" required name = "dateline">
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Jenis Penjualan</h5>
                                                <input checked type="radio" name="jenis_penjualan" value="OFFLINE" onclick = "$('#online_info_container').hide()">&nbsp;OFFLINE
                                                &nbsp;&nbsp;
                                                <input type="radio" name="jenis_penjualan" value="ONLINE" onclick = "$('#online_info_container').show()">&nbsp;ONLINE
                                            </div>
                                            <div id = "online_info_container" class = "col-lg-12" style = "display:none">
                                                <div class = "form-group">
                                                    <h5>Marketplace</h5>
                                                    <input type = "text" class = "form-control" required name = "marketplace">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>Kurir</h5>
                                                    <input type = "text" class = "form-control" required name = "no_resi">
                                                </div>
                                                <div class = "form-group">
                                                    <h5>No Resi</h5>
                                                    <input type = "text" class = "form-control" required name = "kurir">
                                                </div>
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Custom</h5>
                                                <button type = "button" class = "btn btn-primary btn-sm" data-toggle = "modal" data-target = "#custom_produk_modal">Custom Produk</button>
                                            </div>
                                            
                                            <div class = "form-group col-lg-6">
                                                <h5>Item Penjualan</h5>
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Barang</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                        <th>Harga Final</th>
                                                        <th>Notes</th>
                                                        <th>Action</th>
                                                    </thead>
                                                    <tbody id = "daftar_brg_jual_add">
                                                        <tr id = "add_brg_jual_but_container">
                                                            <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_jual_row()">Tambah Barang Penjualan</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-6">
                                                <h5>Tambahan Penjualan</h5>
                                                <table class = "table table-striped table-bordered">
                                                    <thead>
                                                        <th>Tambahan</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                        <th>Notes</th>
                                                        <th>Action</th>
                                                    </thead>
                                                    <tbody id = "daftar_tambahan_jual_add">
                                                        <tr id = "add_tambahan_jual_but_container">
                                                            <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_tambahan_jual_row()">Tambah Barang Penjualan</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Total Price</h5>
                                                <input style = "width:50%" type = "text" class = "form-control" required readonly>
                                            </div>
                                            <div class = "form-group col-lg-12">
                                                <h5>Jenis Pembayaran</h5>
                                                <input checked type="radio" name="jenis_pembayaran" value="FULL PAYMENT">&nbsp;FULL PAYMENT
                                                &nbsp;&nbsp;
                                                <input type="radio" name="jenis_pembayaran" value="DP">&nbsp;DP
                                                &nbsp;&nbsp;
                                                <input type="radio" name="jenis_pembayaran" value="TEMPO">&nbsp;TEMPO
                                                &nbsp;&nbsp;
                                                <input type="radio" name="jenis_pembayaran" value="KEEP">&nbsp;KEEP
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
                                                        <tr id = "add_pembayaran_but_container">
                                                            <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_pembayaran_row()">Tambah Tahap Pembayaran</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class = "form-group col-lg-12" style = "width:50%">
                                                <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                                                <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
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
        var html = "<tr class = 'add_brg_jual_row'><td id = 'row"+brg_jual_row+"'><input name = 'check[]' value = "+brg_jual_row+" type = 'hidden'><input type = 'text' list = 'datalist_barang_cabang' name = 'brg"+brg_jual_row+"' class = 'form-control'></td><td><input name = 'brg_qty"+brg_jual_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_notes"+brg_jual_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
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
        var html = "<tr class = 'add_pembayaran_row'><td id = 'row"+pembayaran_row+"'><input name = 'pembayaran[]' value = "+pembayaran_row+" type = 'hidden'><input type = 'text' name = 'pmbyrn_nama"+pembayaran_row+"' class = 'form-control'></td><td><input name = 'pmbyrn_persen"+pembayaran_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' name = 'pmbyrn_nominal"+pembayaran_row+"' class = 'form-control'></td><td><input type = 'text' name = 'pmbyrn_notes"+pembayaran_row+"' class = 'form-control'></td><td><input type = 'date' name = 'pmbyrn_dateline"+pembayaran_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_pembayaran_but_container").before(html);
        pembayaran_row++;    
    }
    var custom_produk_row = 0;  
    function add_custom_produk_row(){
        var html = "<tr class = 'add_custom_produk_row'><td><input name = 'custom[]' value = "+custom_produk_row+" type = 'hidden'><input name = 'custom"+custom_produk_row+"' type = 'text' class = 'form-control'></td><td><input name = 'custom_jumlah"+custom_produk_row+"' type = 'text' class = 'form-control'></td><td><input name = 'custom_harga"+custom_produk_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_custom_produk_but_container").before(html);
        custom_produk_row++;    
    }
    function empty_table_form(){
        var brg_jual_row = 0;  
        var tambahan_jual_row = 0;
        $(".add_brg_jual_row").remove();
        $(".add_tambahan_jual_row").remove();
    }
</script>
<?php $this->load->view("_core_script/table_func");?>
<?php $this->load->view("_base_element/datalist_customer");?>
<?php $this->load->view("_base_element/datalist_barang_cabang");?>
<script>
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
                        <div class = "form-group col-lg-12" style = "width:50%">
                            <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                            <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                        </div>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view("_core_script/register_func");?>