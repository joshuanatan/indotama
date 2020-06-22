
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
                    <div class = "form-group">
                        <h5>Nama Role</h5>
                        <input type="text" class="form-control" name="jabatan_nama" required>
                    </div>
                    <div class = "form-group">
                        <h5>Daftar Menu</h5>
                        <table class = "table table-striped table-bordered">
                            <thead>
                                <th>Daftar Menu</th>
                                <th>Check</th>
                            </thead>
                            <tbody id = "daftar_menu_container_add">
                            </tbody>
                        </table>
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
<script>
    function load_list_menu(){
        $.ajax({
            url:"<?php echo base_url();?>ws/menu/list",
            type:"GET",
            dataType:"JSON",
            success:function(respond){
                var html = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html+="<tr><td>"+respond["content"][a]["display"]+"</td><td><input type = 'checkbox' value = '"+respond["content"][a]["id"]+"' name = 'check[]'></td></tr>";
                    }
                    $("#daftar_menu_container_add").html(html);
                }
            }
        });
    }
</script>