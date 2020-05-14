<div class = "modal fade" id = "tambah_customer">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Customer</h4>
            </div>
            <div class = "modal-body">
                <form method="POST" action="<?php echo base_url() ?>customer/register_customer">
                    <div class = "form-group">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="cust_name" required>
                    </div>
                    <div class = "form-group">
                        <h5>Perusahaan</h5>
                        <input type="text" class="form-control" name="cust_perusahaan" required>
                    </div>
                    <div class = "form-group">
                        <h5>Email</h5>
                        <input type="email" class="form-control" name="cust_email" required>
                    </div>
                    <div class = "form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="cust_telp" required>
                    </div>
                    <div class = "form-group">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="cust_hp" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="cust_alamat" required>
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" name="cust_keterangan" required>
                    </div>
                    <div class = "form-group">
                        <h5>Toko</h5>
                        <select class="form-control" name="id_fk_toko">
                            <option value="0" disabled>Pilih Toko</option>
                            <?php for($p=0 ; $p<count($toko); $p++){ ?>
                                <option value="<?php echo $toko[$p]['ID_PK_TOKO'] ?>"><?php echo $toko[$p]['TOKO_NAMA']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <input type="submit" class = "btn btn-sm btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>