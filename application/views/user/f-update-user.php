
<div class = "modal fade" id = "update_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Ubah Data <?php echo ucwords($page_title);?></h4>
            </div>
            <div class = "modal-body">
                <form id = "update_form" method = "POST">
                    <input type = "hidden" name = "id" id = "id_edit">
                    <div class = "form-group">
                        <h5>Nama User</h5>
                        <input type = "text" class = "form-control" required name = "name" id = "name_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email" id = "email_edit">
                    </div>
                    <div class = "form-group">
                        <h5>Role</h5> 
                        <select class = "form-control" required name = "id_role" id = "role_list_edit" onchange = "load_hak_akses_edit()">
                            <option>Pilih Role</option>
                            <?php for($a = 0; $a<count($roles); $a++):?>
                            <option value = '<?php echo $roles[$a]["id_pk_jabatan"];?>'><?php echo $roles[$a]["jabatan_nama"];?></option>
                            <?php endfor;?>
                        </select>
                        <h5>Hak Akses</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Menu Tersedia</th>
                            </thead>
                            <tbody id = "daftar_hak_akses_container_edit">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "update_func();" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function load_edit_content(id){
        $("#id_edit").val(content[id]["id"]);
        $("#name_edit").val(content[id]["name"]);
        $("#email_edit").val(content[id]["email"]);
        $('#role_list_edit').val(content[id]["id_role"]);
        load_hak_akses_edit();
    }
    function load_hak_akses_edit(){
        var id_role = $("#role_list_edit").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/roles/hak_akses?id="+id_role,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    if(respond["content"].length > 0){
                        for(var a = 0; a<respond["content"].length; a++){
                            if(respond["content"][a]["status"] == "AKTIF"){
                                html += "<tr><td>"+respond["content"][a]["menu_display"].toUpperCase()+"</td></tr>";
                            }
                        }
                    }
                    else{
                        var html = "<tr><td>No Privilege</td></tr>";
                    }
                    $("#daftar_hak_akses_container_edit").html(html);
                }
                else{
                    var html = "<tr><td>No Privilege</td></tr>";
                    $("#daftar_hak_akses_container_edit").html(html);
                }
            }
        })
    }
</script>
