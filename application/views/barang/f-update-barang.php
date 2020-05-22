
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
            <?php 
                $notif_data = array(
                    "page_title"=>$page_title
                );
                $this->load->view('_notification/update_error',$notif_data); ?>
                <form id = "update_form" method = "POST" enctype = "multipart/form-data">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Kode Barang</h5>
                        <input type = "text" class = "form-control" required name = "kode" id = "kode_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Jenis Barang</h5>
                        <input list = "list_jenis" type = "text"  required name = "id_brg_jenis" class = "form-control" id = "id_brg_jenis_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Nama Barang</h5>
                        <input type = "text" class = "form-control" required name = "nama" id = "nama_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Keterangan</h5>
                        <input type = "text" class = "form-control" required name = "keterangan" id = "keterangan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Merk Barang</h5>
                        <input list = "list_merk" type = "text"  required name = "id_brg_merk" class = "form-control" id = "id_brg_merk_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Minimal Stok</h5>
                        <input type = "text" class = "form-control" required name = "minimal" id = "minimal_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Satuan</h5>
                        <input type = "text" class = "form-control" required name = "satuan" id = "satuan_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Ukuran</h5>
                        <input type = "text" class = "form-control" required name = "ukuran" id = "ukuran_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Gambar</h5>
                        <input type = "hidden" id = "gambar_edit" name = "gambar_current">
                        <input type = "file" class = "form-control" required name = "gambar">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_edit_content(row){
        $("#id_edit").val(content[row]["id"]);
        $("#kode_edit").val(content[row]["kode"]);
        $("#nama_edit").val(content[row]["nama"]);
        $("#id_brg_jenis_edit").val(content[row]["jenis"]);
        $("#keterangan_edit").val(content[row]["ket"]);
        $("#id_brg_merk_edit").val(content[row]["merk"]);
        $("#minimal_edit").val(content[row]["minimal"]);
        $("#satuan_edit").val(content[row]["satuan"]);
        $("#ukuran_edit").val(content[row]["ukuran"]);
        $("#gambar_edit").val(content[row]["image"]);
    }
    function update_func(){
        var form = $("#update_form")[0];
        var data = new FormData(form);
        $.ajax({
            url:"<?php echo base_url();?>ws/"+ctrl+"/update",
            type:"POST",
            dataType:"JSON",
            data:data,
            processData: false,
            contentType: false,
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    $("#update_form :input").val("");
                    $("#update_modal").modal("hide");
                    refresh(page);
                }
            }
        });
    }
</script>