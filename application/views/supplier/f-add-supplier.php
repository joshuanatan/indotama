
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
                        <h5>Badan Usaha</h5>
                        <select name="sup_badan_usaha" class="form-control">
                            <option value="0" disabled>Pilih Badan Usaha</option>
                            <option value="Toko">Toko</option>
                            <option value="CV">CV</option>
                            <option value="PT">PT</option>
                            <option value="Unit Dagang">Unit Dagang</option>
                        </select>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama Supplier</h5>
                        <input type = "text" class = "form-control" required name = "nama">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="suff" class="form-control">
                            <option value="0" disabled>Pilih Panggilan</option>
                            <option value="Tn">Tn</option>
                            <option value="MR">Mr</option>
                            <option value="MRS">Mrs</option>
                            <option value="MS">Ms</option>
                            <option value="BAPAK">Bpk</option>
                            <option value="IBU">Ibu</option>
                            <option value="NONA">Nona</option>
                        </select>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nama PIC</h5>
                        <input type = "text" class = "form-control" required name = "pic">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No Kantor</h5>
                        <input type = "text" class = "form-control" required name = "notelp">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type = "text" class = "form-control" required name = "nohp">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor NPWP</h5>
                        <input type="text" class="form-control" name="sup_npwp" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="sup_foto_npwp" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Rekening</h5>
                        <input type="text" class="form-control" name="sup_rek" required value = "-">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto Kartu Nama</h5>
                        <input type="file" class="form-control" name="sup_krt_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Alamat</h5>
                        <textarea type = "text" class = "form-control" required name = "alamat"></textarea>
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