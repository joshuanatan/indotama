
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <div class = "form-group col-lg-6">
                    <h5>Badan Usaha</h5>
                    <input type = "text" class = "form-control" name = "nama" readonly id = "sup_badan_usaha_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nama Supplier</h5>
                    <input type = "text" class = "form-control" name = "nama" readonly id = "nama_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Panggilan</h5>
                    <input type = "text" class = "form-control" name = "nama" readonly id = "suff_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nama PIC</h5>
                    <input type = "text" class = "form-control" name = "pic" readonly id = "pic_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Email</h5>
                    <input type = "text" class = "form-control" name = "email" readonly id = "email_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>No Kantor</h5>
                    <input type = "text" class = "form-control" name = "notelp" readonly id = "notelp_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>No HP</h5>
                    <input type = "text" class = "form-control" name = "nohp" readonly id = "nohp_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Keterangan</h5>
                    <input type = "text" class = "form-control" name = "keterangan" readonly id = "keterangan_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nomor NPWP</h5>
                    <input type="text" class="form-control" name="sup_npwp" readonly id = "sup_npwp_detail">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Foto NPWP</h5>
                    <img readonly id = "sup_foto_npwp_detail" style = "width:100%">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nomor Rekening</h5>
                    <input type="text" class="form-control" name="sup_rek" readonly id = "sup_rek_detail" value = "-">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Foto Kartu Nama</h5>
                    <img readonly id = "sup_krt_nama_detail" style = "width:100%">
                </div>
                <div class = "form-group">
                    <h5>Alamat</h5>
                    <textarea type = "text" class = "form-control" name = "alamat" readonly id = "alamat_detail"></textarea>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function load_detail_content(id){
        $("#d_id_edit").val(content[id]["id"]);
        $("#sup_badan_usaha_detail").val(content[id]["badan_usaha"]);
        $("#nama_detail").val(content[id]["perusahaan"]);
        $("#suff_detail").val(content[id]["suff"]);
        $("#pic_detail").val(content[id]["nama"]);
        $("#email_detail").val(content[id]["email"]);
        $("#notelp_detail").val(content[id]["telp"]);
        $("#nohp_detail").val(content[id]["hp"]);
        $("#keterangan_detail").val(content[id]["keterangan"]);
        $("#sup_npwp_detail").val(content[id]["no_npwp"]);
        $("#sup_rek_detail").val(content[id]["no_rekening"]);
        $("#alamat_detail").val(content[id]["alamat"]);

        if(content[id]["foto_npwp"] != "-"){
            $("#sup_foto_npwp_detail").attr("src","<?php echo base_url();?>asset/uploads/supplier/npwp/"+content[id]["foto_npwp"]);
        }
        else{
            $("#sup_foto_npwp_detail").attr("src","<?php echo base_url();?>asset/uploads/supplier/npwp/noimage.jpg");
        }
        if(content[id]["foto_kartu_nama"] != "-"){
            $("#sup_krt_nama_detail").attr("src","<?php echo base_url();?>asset/uploads/supplier/krt_nama/"+content[id]["foto_kartu_nama"]);
        }
        else{
            $("#sup_krt_nama_detail").attr("src","<?php echo base_url();?>asset/uploads/supplier/krt_nama/noimage.jpg");
        }
    }
</script>
