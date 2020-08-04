
<datalist id = 'datalist_employee'></datalist>
<script>
    function load_datalist_employee(){
        $.ajax({
            url:"<?php echo base_url();?>ws/employee/list_employee",
            type:"GET",
            dataType:"JSON",
            async:false,
            success:function(respond){
                var html_datalist_employee = "";
                if(respond["status"] == "SUCCESS"){
                    for(var a = 0; a<respond["content"].length; a++){
                        html_datalist_employee+="<option value = '"+respond['content'][a]["nama"]+"'></option>";
                    }
                    $("#datalist_employee").html(html_datalist_employee);
                }
            }
        });
    }
</script>