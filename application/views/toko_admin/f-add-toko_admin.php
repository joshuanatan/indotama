
<div class = "modal fade" id = "register_modal">
    <div class = "modal-dialog">
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
                    <input type = "hidden" name = "id_toko" value = "<?php echo $toko[0]["id_pk_toko"];?>">
                    <div class = "form-group">
                        <h5>Item Pembelian</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Username</th>
                                <th>Action</th>
                            </thead>
                            <tbody id = "daftar_brg_beli_add">
                                <tr id = "add_brg_beli_but_container">
                                    <td colspan = 6><button type = "button" class = "btn btn-primary btn-sm col-lg-12" onclick = "add_toko_admin()">Tambah Barang Pembelian</button>
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
    var toko_admin = 0;  
    function add_toko_admin(){
        var html = "<tr class = 'add_toko_admin'><td id = 'row"+toko_admin+"'><input name = 'check[]' value = "+toko_admin+" type = 'hidden'><input type = 'text' list = 'daftar_user' name = 'nama"+toko_admin+"' class = 'form-control'></td><td><i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i></td></tr>";
        $("#add_brg_beli_but_container").before(html);
        toko_admin++;    
    }
    function empty_table_form(){
        var toko_admin = 0;  
        $(".add_toko_admin").remove(); 
    }
</script>