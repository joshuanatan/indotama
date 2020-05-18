
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog modal-lg">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "register_form" method = "POST" action="<?php echo base_url()?>warehouse/register_brg_warehouse">
                    <input type="hidden" name="id_fk_Warehouse" value="<?php echo $warehouse[0]['ID_PK_WAREHOUSE'] ?>" >
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Barang</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_add">
                                <tr id = "add_brg_beli_but_container">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_brg_beli_row()">Tambah Barang Warehouse</button>
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
    var brg_count = 0;  
    function add_brg_beli_row(){
        var html = "<tr class = 'add_brg_count'><td id = 'row"+brg_count+"'><input name = 'check[]' value = "+brg_count+" type = 'hidden'><input type = 'text' list = 'daftar_barang' name = 'brg"+brg_count+"' class = 'form-control'></td><td><input type = 'text' name = 'brg_warehouse_notes"+brg_count+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_beli_but_container").before(html);
        brg_count++;    
    }
    function empty_table_form(){
        var brg_count = 0;  
        $(".add_brg_count").remove();
    }
</script>