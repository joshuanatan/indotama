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
                <input type = "hidden" id = "id_delete" name = "id">
                <h4 align = "center">Apakah anda yakin akan menghapus data di bawah ini?</h4>
                <table class = "table table-bordered table-striped table-hover">
                    <tbody>
                        <tr>
                            <td>Kode Barang</td>
                            <td id = "kode_delete"></td>
                        </tr>
                        <tr>
                            <td>Nama Barang</td>
                            <td id = "nama_delete"></td>
                        </tr>
                        <tr>
                            <td>Jenis Barang</td>
                            <td id = "jenis_delete"></td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td id = "keterangan_delete"></td>
                        </tr>
                        <tr>
                            <td>Merk barang</td>
                            <td id = "merk_delete"></td>
                        </tr>
                        <tr>
                            <td>Stok Minimal</td>
                            <td id = "minimal_delete"></td>
                        </tr>
                        <tr>
                            <td>Satuan</td>
                            <td id = "satuan_delete"></td>
                        </tr>
                        <tr>
                            <td>Ukuran</td>
                            <td id = "ukuran_delete"></td>
                        </tr>
                    </tbody>
                </table>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">Cancel</button>
                    <button type = "button" onclick = "delete_func()" class = "btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_delete_content(row){
        $("#id_delete").val(content[row]["id"]);
        $("#kode_delete").html(content[row]["kode"]);
        $("#nama_delete").html(content[row]["nama"]);
        $("#jenis_delete").html(content[row]["jenis"]);
        $("#keterangan_delete").html(content[row]["ket"]);
        $("#merk_delete").html(content[row]["merk"]);
        $("#minimal_delete").html(content[row]["minimal"]);
        $("#satuan_delete").html(content[row]["satuan"]);
        $("#ukuran_delete").val(content[row]["ukuran"]);
    }
    function delete_func(){
        var id = $("#id_delete").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/delete?id="+id,
            type:"DELETE",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#delete_modal").modal("hide");
                    refresh(page);
                }
            }
        });
    }
</script>