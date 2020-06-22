
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
                        <h5>Nama User</h5>
                        <input type = "text" class = "form-control" required name = "name">
                    </div>
                    <div class = "form-group col-lg-6">
                        <h5>Password</h5>
                        <input type = "text" class = "form-control" required name = "pass">
                    </div>
                    <div class = "form-group col-lg-12">
                        <h5>Email</h5>
                        <input type = "text" class = "form-control" required name = "email">
                    </div>
                    <div class = "form-group">
                        <h5>Role</h5> 
                        <select class = "form-control" required name = "id_role" onchange = "load_hak_akses()" id = "role_list">
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
                            <tbody id = "daftar_hak_akses_container">
                            </tbody>
                        </table>
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "button" onclick = "register_func();clear_priv_list()" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function load_hak_akses(){
        var id_role = $("#role_list").val();
        $.ajax({
            url:"<?php echo base_url();?>ws/roles/hak_akses?id="+id_role,
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                if(respond["status"] == "SUCCESS"){
                    var html = "";
                    if(respond["content"].length > 0){
                        for(var a = 0; a<respond["content"].length; a++){
                            if(respond["content"][a]["status"].toLowerCase() == "aktif"){
                                html += "<tr><td>"+respond["content"][a]["menu_display"].toUpperCase()+"</td></tr>";
                            }
                        }
                    }
                    else{
                        var html = "<tr><td>No Privilege</td></tr>";
                    }
                    $("#daftar_hak_akses_container").html(html);
                }
                else{
                    var html = "<tr><td>No Privilege</td></tr>";
                    $("#daftar_hak_akses_container").html(html);
                }
            }
        })
    }
    function clear_priv_list(){
        $("#daftar_hak_akses_container").html("");
    }
</script>