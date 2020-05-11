<script>
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