
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
                    <input type = 'hidden' name = 'id' id = 'id_brg_pengiriman'>
                    <input type = 'hidden' name = 'id_brg_pemenuhan' id = 'id_brg_pemenuhan'>
                    <input type = 'hidden' name = 'type' value = '<?php echo $type;?>'>
                    <input type = 'hidden' name = 'tipe_penerimaan' value = '<?php echo $tipe_penerimaan;?>'>
                    <input type = 'hidden' name = 'id_tempat_penerimaan' value = '<?php echo $id_tempat_penerimaan;?>'>
                    <input type = 'hidden' name = 'brg_penerimaan_qty' id = 'brg_penerimaan_qty'>
                    <table class = "table table-striped table-bordered">
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "brg_nama"></td>
                        </tr>
                        <tr>
                            <th>Jumlah Pengiriman</th>
                            <td id = "brg_pengiriman_qty"></td>
                        </tr>
                        <tr>
                            <th>Toko Pengirim</th>
                            <td id = "toko"></td>
                        </tr>
                        <tr>
                            <th>Cabang Pengirim</th>
                            <td id = "cabang"></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengiriman</th>
                            <td id = "tgl_pengiriman"></td>
                        </tr>
                    </table>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" id = "penerimaan_permintaan_tambah_button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
