
<div class = "modal fade" id = "delete_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Hapus Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            $this->load->view('_notification/delete_error',$notif_data); ?>
                <form method="POST" action="<?php echo base_url() ?>warehouse/hapus_warehouse">
                <input type = "hidden" id = "id_delete">
                    <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                    <table class = "table table-bordered table-striped table-hover">
                        <tbody>
                            <tr>
                                <td>Nama Warehouse</td>
                                <td id = "warehouse_nama_delete"></td>
                            </tr>
                            <tr>
                                <td>Alamat</td>
                                <td id = "warehouse_alamat_delete"></td>
                            </tr>
                            <tr>
                                <td>No Telp</td>
                                <td id = "warehouse_notelp_delete"></td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td id = "warehouse_desc_delete"></td>
                            </tr>
                            <tr>
                                <td>Cabang</td>
                                <td id = "warehouse_nama_cabang_delete"></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                    </div>  
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function load_delete_content(id){
        $("#id_delete").val(content[id]["id"]);
        $("#warehouse_nama_delete").html(content[id]["nama"]);
        $("#warehouse_alamat_delete").html(content[id]["alamat"]);
        $('#warehouse_notelp_delete').html(content[id]["notelp"]);
        $('#warehouse_desc_delete').html(content[id]["desc"]);
        $('#warehouse_nama_cabang_delete').html(content[id]["nama_cabang"]);
    }
</script>