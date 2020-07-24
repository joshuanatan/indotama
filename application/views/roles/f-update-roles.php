
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
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Nama Jabatan</h5>
                        <input type="text" class="form-control" name="jabatan_nama" id = "jabatan_nama_edit" required>
                    </div>
                    <div class = "form-group">
                        <h5>Daftar Menu</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Daftar Menu</th>
                                <th>Check</th>
                            </thead>
                            <tbody id = "daftar_hak_akses_container_edit">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func();menubar()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function load_edit_content(id){
        $("#id_edit").val(content[id]["id"]);
        $("#jabatan_nama_edit").val(content[id]["nama"]);
        load_hak_akses_edit(content[id]["id"]);
    }
    function load_hak_akses_edit(id){
        $.ajax({
            url:"<?php echo base_url();?>ws/roles/hak_akses?id="+id,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                for(var a = 0; a<respond["content"].length; a++){
                    if(respond["content"][a]["status"].toUpperCase() == "NONAKTIF"){
                        html+=`
                        <tr>
                            <td>${respond["content"][a]["menu_display"]} / ${respond["content"][a]["kategori"]}</td>
                            <td><input type = 'checkbox' value = '${respond["content"][a]["id_menu"]}' name = 'check[]'></td>
                        </tr>`;
                    }
                    else{
                        html+=`
                        <tr>
                            <td>${respond["content"][a]["menu_display"]} / ${respond["content"][a]["kategori"]}</td>
                            <td><input type = 'checkbox' checked value = '${respond["content"][a]["id_menu"]}' name = 'check[]'></td>
                        </tr>`;
                    }
                }
                $("#daftar_hak_akses_container_edit").html(html);
            }
        })
    }
</script>
