
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
                <form id = "delete_form" method = "POST">
                    <input type = 'hidden' name = 'id' id = 'id_delete'>
                    <input type = 'hidden' name = 'type' value = '<?php echo $type;?>'>
                    <input type = 'hidden' name = 'tipe_penerimaan' value = '<?php echo $tipe_penerimaan;?>'>
                    <input type = 'hidden' name = 'id_tempat_penerimaan' value = '<?php echo $id_tempat_penerimaan;?>'>
                    <input type = 'hidden' name = 'brg_pengiriman_qty' id = 'brg_penerimaan_qty_delete'>
                    <table class = "table table-striped table-bordered">
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "brg_nama_delete"></td>
                        </tr>
                        <tr>
                            <th>Jumlah Pengiriman</th>
                            <td id = "brg_pengiriman_qty_delete"></td>
                        </tr>
                        <tr>
                            <th>Toko Pengirim</th>
                            <td id = "toko_delete"></td>
                        </tr>
                        <tr>
                            <th>Cabang Pengirim</th>
                            <td id = "cabang_delete"></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengiriman</th>
                            <td id = "tgl_pengiriman_delete"></td>
                        </tr>
                    </table>
                    <div class = "form-group">
                        <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger" style = "width:100%">Batalkan Penerimaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
