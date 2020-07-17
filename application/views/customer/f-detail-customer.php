
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
            $notif_data = array(
                "page_title"=>$page_title
            );
            ?>
                
                <div class = "form-group col-lg-6">
                    <h5>Panggilan</h5>
                    <input type = "text" readonly id = "cust_suff_detail" class="form-control">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nama Lengkap</h5>
                    <input type="text" class="form-control" id = "cust_name_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Badan Usaha</h5>
                    <input type = 'text' readonly id = "cust_badan_usaha_detail" class="form-control">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Perusahaan</h5>
                    <input type="text" class="form-control" id = "cust_perusahaan_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Email</h5>
                    <input type="email" class="form-control" id = "cust_email_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>No Kantor</h5>
                    <input type="text" class="form-control" id = "cust_telp_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>No HP</h5>
                    <input type="text" class="form-control" id = "cust_hp_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Keterangan</h5>
                    <input type="text" class="form-control" id = "cust_keterangan_detail" disabled>
                </div>
                
                <div class = "form-group col-lg-6">
                    <h5>Nomor NPWP</h5>
                    <input type="text" class="form-control" id = "cust_npwp_detail" disabled>
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Foto NPWP</h5>
                    <img id = "cust_foto_npwp_detail" style = "width:100%">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Nomor Rekening</h5>
                    <input type="text" class="form-control" id = "cust_rek_detail" disabled value = "-">
                </div>
                <div class = "form-group col-lg-6">
                    <h5>Foto Kartu Nama</h5>
                    <img id = "cust_krt_nama_detail" style = "width:100%">
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Alamat</h5>
                    <textarea class="form-control" id = "cust_alamat_detail" disabled></textarea>
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
        $("#id_detail").val(content[id]["id"]);
        $("#cust_suff_detail").val(content[id]["suff"]);
        $("#cust_name_detail").val(content[id]["name"]);
        $("#cust_badan_usaha_detail").val(content[id]["badan_usaha"]);
        $("#cust_perusahaan_detail").val(content[id]["perusahaan"]);
        $("#cust_email_detail").val(content[id]["email"]);
        $("#cust_telp_detail").val(content[id]["telp"]);
        $("#cust_hp_detail").val(content[id]["hp"]);
        $("#cust_keterangan_detail").val(content[id]["keterangan"]);
        $("#cust_npwp_detail").val(content[id]["no_npwp"]);
        if(content[id]["foto_npwp"] != "-"){
            $("#cust_foto_npwp_detail").attr("src","<?php echo base_url();?>asset/uploads/customer/npwp/"+content[id]["foto_npwp"]);
        }
        else{
            $("#cust_foto_npwp_detail").attr("src","<?php echo base_url();?>asset/uploads/customer/npwp/noimage.jpg");
        }
        $("#cust_rek_detail").val(content[id]["no_rekening"]);
        if(content[id]["foto_kartu_nama"] != "-"){
            $("#cust_krt_nama_detail").attr("src","<?php echo base_url();?>asset/uploads/customer/krt_nama/"+content[id]["foto_kartu_nama"]);
        }
        else{
            $("#cust_krt_nama_detail").attr("src","<?php echo base_url();?>asset/uploads/customer/krt_nama/noimage.jpg");
        }
        $("#cust_alamat_detail").val(content[id]["alamat"]);
    }
</script>
