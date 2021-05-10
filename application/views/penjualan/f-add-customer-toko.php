
<div class = "modal fade" id = "register_modal_cust_toko">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Tambah Data Customer </h4>
            </div>
            <div class = "modal-body" style="display:flex">
                <div class="alert alert-danger alert-dismissable col-lg-12" id="notif_register_error_cust" style="position: fixed; bottom:10%;z-index:100;left: 50%; margin-left: -50%;">
                    <i class="zmdi zmdi-alert-circle-o pr-15 pull-left"></i><p class="pull-left">Register Customer gagal! <span id="regis_error_msg_cust"></span></p>
                    
                    <div class="clearfix"></div>
                </div>
                <form id = "register_form_cust_toko" method = "POST">
                    <div class = "form-group col-lg-6">
                        <h5>Panggilan</h5>
                        <select name="cust_suff" class="form-control">
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
                        <h5>Nama Lengkap</h5>
                        <input type="text" class="form-control" name="cust_name" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Badan Usaha</h5>
                        <select name="cust_badan_usaha" class="form-control">
                            <option value="0" disabled>Pilih Badan Usaha</option>
                            <option value="Toko">Toko</option>
                            <option value="CV">CV</option>
                            <option value="PT">PT</option>
                            <option value="Unit Dagang">Unit Dagang</option>
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
                        <h5>No Kantor</h5>
                        <input type="text" class="form-control" name="cust_telp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>No HP</h5>
                        <input type="text" class="form-control" name="cust_hp" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Keterangan</h5>
                        <input type="text" class="form-control" name="cust_keterangan" required>
                    </div>
                    
                    <div class = "form-group col-lg-6">
                        <h5>Nomor NPWP</h5>
                        <input type="text" class="form-control" name="cust_npwp" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto NPWP</h5>
                        <input type="file" class="form-control" name="cust_foto_npwp" required>
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Nomor Rekening</h5>
                        <input type="text" class="form-control" name="cust_rek" required value = "-">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Foto Kartu Nama</h5>
                        <input type="file" class="form-control" name="cust_krt_nama" required>
                    </div>
                    <div class = "form-group col-lg-12">
                        <h5>Alamat</h5>
                        <textarea class="form-control" name="cust_alamat" required></textarea>
                    </div>
                    <input type="hidden" class="form-control" name="id_fk_toko" value="<?php echo $this->session->id_toko ?>"" required>
                    <div class = "form-group col-lg-12">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func_cust()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$("#notif_register_success_cust").css("display", "none");
$("#notif_register_error_cust").css("display", "none");
function register_func_cust(id_register_form = "register_form_cust_toko",id_register_modal = "register_modal_cust_toko"){
        if(typeof(nf_reformat_all) != "undefined"){
            nf_reformat_all();
        }
        var form = $(`#${id_register_form}`)[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/customer/register",
            type:"POST",
            dataType:"JSON",
            data:data,
            async:false,
            processData:false,
            contentType:false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    load_datalist_customer();
                    $('#notif_register_success_cust').show(1).delay(2000).hide(1);
                    $(`#${id_register_modal}`).modal("hide");
                    //notification
                }

                if(respond["status"] == "ERROR"){
                    $('#regis_error_msg_cust').empty();
                    $('#regis_error_msg_cust').append(respond["msg"]);
                    $('#notif_register_error_cust').show(1).delay(2000).hide(1);
                }
            },
            error:function(){
                //notification
                $('#regis_error_msg_cust').empty();
                $('#regis_error_msg_cust').append(respond["msg"]);
                $('#notif_register_error_cust').show(1).delay(2000).hide(1);
            }
        });
    }

</script>