
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
                    <input type = "hidden" name = "id_cabang" value = "<?php echo $this->session->id_cabang;?>">
                    <div class = "form-group">
                        <h5>Nomor Penjualan</h5>
                        <input type = "text" class = "form-control" required name = "nomor">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Penjualan</h5>
                        <input type = "date" class = "form-control" required name = "tgl">
                    </div>
                    <div class = "form-group">
                        <h5>Dateline</h5>
                        <input type = "date" class = "form-control" required name = "dateline">
                    </div>
                    <div class = "form-group">
                        <h5>Customer</h5>
                        <input type = 'text' class = "form-control" list = "daftar_customer" required name = "customer">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Penjualan</h5>
                        <input checked type="radio" name="jenis_penjualan" value="OFFLINE" onclick = "$('#online_info_container').hide()">&nbsp;OFFLINE
                        &nbsp;&nbsp;
                        <input type="radio" name="jenis_penjualan" value="ONLINE" onclick = "$('#online_info_container').show()">&nbsp;ONLINE
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
                        <h5>Item Penjualan</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
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
                    <div class = "form-group">
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
    var brg_jual_row = 0;  
    function add_brg_jual_row(){
        var html = "<tr class = 'add_brg_jual_row'><td id = 'row"+brg_jual_row+"'><input name = 'check[]' value = "+brg_jual_row+" type = 'hidden'><input type = 'text' list = 'daftar_barang' name = 'brg"+brg_jual_row+"' class = 'form-control'></td><td><input name = 'brg_qty"+brg_jual_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_jual_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_notes"+brg_jual_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_jual_but_container").before(html);
        brg_jual_row++;    
    }
    var tambahan_jual_row = 0;
    function add_tambahan_jual_row(){
        var html = "<tr class = 'add_tambahan_jual_row'><td><input name = 'tambahan[]' value = "+tambahan_jual_row+" type = 'hidden'><input name = 'tmbhn"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_jumlah"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_harga"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_notes"+tambahan_jual_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_tambahan_jual_but_container").before(html);
        tambahan_jual_row++;        
    }
    function empty_table_form(){
        var brg_jual_row = 0;  
        var tambahan_jual_row = 0;
        $(".add_brg_jual_row").remove();
        $(".add_tambahan_jual_row").remove();
    }
</script>