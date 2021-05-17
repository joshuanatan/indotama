
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body" style="display:flow-root">
            
                






                <div class="panel-group accordion-struct accordion-style-1" id="accordion_cust_detail" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading_1">
                            <a role="button" data-toggle="collapse" data-parent="#accordion_cust_detail" href="#collapse_1" aria-expanded="false" class="collapsed">
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Customer
                            </a> 
                        </div>
                        <div id="collapse_1" class="panel-collapse collapse" role="tabpanel">
                            <div style = "margin:10px" class = "panel-body">
                                <!-- isi konten -->
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
                                    <div class = "form-group col-lg-6">
                                        <h5>Toko</h5>
                                        <input type="text" class="form-control" name="id_fk_toko" id = "cust_nama_toko" required readonly>
                                    </div>
                            </div>
                        </div>

                    </div>



                    <div class="panel panel-default">
                        <div class="panel-heading activestate" role="tab" id="heading_2">
                            <a role="button" data-toggle="collapse" data-parent="#accordion_cust_detail" href="#collapse_2" aria-expanded="true">
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Penjualan produk
                            </a> 
                        </div>
                        <div id="collapse_2" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
                            <div style = "margin:10px" class = "panel-body">
                                <!-- isi konten -->
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                            <th>Harga Jual</th>
                                            <th>Notes</th>
                                            <th>Tanggal Penjualan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_brg_jual_customer">
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>





                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading_3">
                            <a role="button" data-toggle="collapse" data-parent="#accordion_cust_detail" href="#collapse_3" aria-expanded="false" class="collapsed">
                                <div class="icon-ac-wrap pr-20">
                                    <span class="plus-ac"><i class="ti-plus"></i></span>
                                    <span class="minus-ac"><i class="ti-minus"></i></span>
                                </div>
                                Detail Transaksi
                            </a> 
                        </div>
                        <div id="collapse_3" class="panel-collapse collapse" role="tabpanel" aria-expanded="false">
                            <div style = "margin:10px" class = "panel-body">
                                <!-- isi konten -->
                                sdsdsd
                        </div>

                    </div>

                    <br><br>
                    <div class = "form-group col-lg-12">
                        <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                    </div>
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
        $("#cust_nama_toko").val(content[id]["nama_toko"]);
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


        $.ajax({
            url:"<?php echo base_url();?>customer/detail_brg_penjualan_customer/" + content[id]["id"],
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                console.log(respond["customer_brg_penjualan"]);
                var html_cust_brg_penj = '';
                if(respond["customer_brg_penjualan"].length >0){
                    for(var a = 0; a<respond["customer_brg_penjualan"].length; a++){
                        html_cust_brg_penj = html_cust_brg_penj + '<tr><td>' + respond["customer_brg_penjualan"][a]["brg_nama"] + '</td><td>'+respond["customer_brg_penjualan"][a]["brg_penjualan_qty"] + " " + respond["customer_brg_penjualan"][a]["brg_penjualan_satuan"] + '</td><td>'+respond["customer_brg_penjualan"][a]["brg_penjualan_harga"]+'</td><td>'+respond["customer_brg_penjualan"][a]["brg_penjualan_note"]+'</td><td>'+respond["customer_brg_penjualan"][a]["penj_tgl"]+'</td></tr>';
                    }
                }else{
                    html_cust_brg_penj = `<td colspan="5">No Data</td>`;
                }
                $("#detail_brg_jual_customer").html(html_cust_brg_penj);
            }
        })

    }
</script>
