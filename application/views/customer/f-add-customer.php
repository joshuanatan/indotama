
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
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="cust_name" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="cust_suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Perusahaan</h5>
                        <input type="text" class="form-control" name="cust_perusahaan" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Email</h5>
                        <input type="email" class="form-control" name="cust_email" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="cust_telp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="cust_hp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="cust_alamat" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" name="cust_keterangan" required>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>