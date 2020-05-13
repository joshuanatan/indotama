
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
                        <input type = "text" class = "form-control" required name = "nomor">
                    </div>
                    <div class = "form-group">
                        <h5>Tanggal Pembelian</h5>
                        <input type = "date" class = "form-control" required name = "tgl">
                    </div>
                    <div class = "form-group">
                        <h5>Supplier</h5>
                        <input type = 'text' class = "form-control" list = "daftar_supplier" required name = "supplier">
                    </div>
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_add">
                                <tr id = "add_brg_beli_but_container">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_beli_row()">Tambah Barang Pembelian</button>
                                    </td>
                                </tr>
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
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_tambahan_beli_add">
                                <tr id = "add_tambahan_beli_but_container">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_tambahan_beli_row()">Tambah Barang Pembelian</button>
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
    var brg_beli_row = 0;  
    function add_brg_beli_row(){
        var html = "<tr class = 'add_brg_beli_row'><td id = 'row"+brg_beli_row+"'><input name = 'check[]' value = "+brg_beli_row+" type = 'hidden'><input type = 'text' list = 'daftar_barang' name = 'brg"+brg_beli_row+"' class = 'form-control'></td><td><input name = 'brg_qty"+brg_beli_row+"' type = 'text' class = 'form-control'></td><td><input type = 'text' name = 'brg_price"+brg_beli_row+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_notes"+brg_beli_row+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_beli_but_container").before(html);
        brg_beli_row++;    
    }
    var tambahan_beli_row = 0;
    function add_tambahan_beli_row(){
        var html = "<tr class = 'add_tambahan_beli_row'><td><input name = 'tambahan[]' value = "+tambahan_beli_row+" type = 'hidden'><input name = 'tmbhn"+tambahan_beli_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_jumlah"+tambahan_beli_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_harga"+tambahan_beli_row+"' type = 'text' class = 'form-control'></td><td><input name = 'tmbhn_notes"+tambahan_beli_row+"' type = 'text' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_tambahan_beli_but_container").before(html);
        tambahan_beli_row++;        
    }
    function empty_table_form(){
        var brg_beli_row = 0;  
        var tambahan_beli_row = 0;
        $(".add_brg_beli_row").remove();
        $(".add_tambahan_beli_row").remove();
    }
</script>