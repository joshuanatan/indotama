
<div class = "modal fade" id = "p_register_penerimaan_permintaan_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Konfirmasi Penerimaan Barang</h4>
            </div>
            <div class = "modal-body">
                <form id = "p_register_form" method = "POST">
                    <input type = 'hidden' name = 'id' id = 'p_id_brg_pengiriman'>
                    <input type = 'hidden' name = 'id_brg_pemenuhan' id = 'p_id_brg_pemenuhan'>
                    <input type = 'hidden' name = 'type' value = '<?php echo $type;?>'>
                    <input type = 'hidden' name = 'tipe_penerimaan' value = '<?php echo $tipe_penerimaan;?>'>
                    <input type = 'hidden' name = 'id_tempat_penerimaan' value = '<?php echo $id_tempat_penerimaan;?>'>
                    <input type = 'hidden' name = 'brg_penerimaan_qty' class = "nf-input" id = 'p_brg_penerimaan_qty'>
                    <table class = "table table-striped table-bordered">
                        <tr>
                            <th>Nama Barang</th>
                            <td id = "p_brg_nama"></td>
                        </tr>
                        <tr>
                            <th>Jumlah Pengiriman</th>
                            <td id = "p_brg_pengiriman_qty"></td>
                        </tr>
                        <tr>
                            <th>Toko Pengirim</th>
                            <td id = "p_toko"></td>
                        </tr>
                        <tr>
                            <th>Cabang Pengirim</th>
                            <td id = "p_cabang"></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengiriman</th>
                            <td id = "p_tgl_pengiriman"></td>
                        </tr>
                    </table>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" id = "p_penerimaan_permintaan_tambah_button" onclick = "register_func('p_register_form','p_register_penerimaan_permintaan_modal');load_incoming_delivery_content()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 