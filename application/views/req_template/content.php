<div class = "col-lg-12">
    <button type = "button" class = "btn btn-primary btn-sm" data-toggle = "modal" data-target = "#register_dialog" style = "margin-right:10px">add_button_label</button>
    <div class = "align-middle text-center">
        <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-primary md-edit"></i><b> - Edit </b>   
        <i style = "cursor:pointer;font-size:large;margin-left:10px" class = "text-danger md-delete"></i><b> - Delete </b>
    </div>
    <br/>
    <div class = "table-responsive ">
        <div class = "form-group">
            <h5>Search Data Here</h5>
            <input type = "text" class = "form-control form-control-sm col-lg-3 col-sm-12">
        </div>
        <table class = "table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th style = "cursor:pointer" onclick = "sort('table_name')" class = "text-center align-middle">Coloumn 1 <span class="badge badge-light align-top">ASC</span></th>
                    <th style = "cursor:pointer" onclick = "sort('table_name')" class = "text-center align-middle">Coloumn 2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class = "align-middle"></td>
                    <td class = "align-middle text-center">
                        <i style = "cursor:pointer;font-size:large" class = "text-primary md-edit"></i> | 
                        <i style = "cursor:pointer;font-size:large" class = "text-danger md-delete"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center" id = "pagination_container">
        </ul>
    </nav>
</div>
<div class = "modal fade" id = "register_dialog">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">modal_form_title</h4>
            </div>
            <div class = "modal-body">
                <form action = "" method = "POST">
                    <div class = "form-group">
                        <h5>form_data_label</h5>
                        <input type = "text" class = "form-control" required name = "">
                    </div>
                    <div class = "form-group">
                        <button type = "button" class = "btn btn-sm btn-danger" data-dismiss = "modal">Cancel</button>
                        <button type = "submit" class = "btn btn-sm btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class = "modal fade" id = "delete_dialog">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">modal_delete_confirmation_title</h4>
            </div>
            <div class = "modal-body">
                <form action = "<?php echo base_url();?>alat/delete" method = "POST">
                <input type = "hidden" name = "attr_id" value = "" id = "attr_id_delete">
                    <h4 align = "center">confirmation_question</h4>
                    <table class = "table table-bordered table-striped table-hover">
                    </table>
                    <div class = "row">
                        <button type = "button" class = "btn btn-sm btn-primary col-lg-3 col-sm-12 offset-lg-3" data-dismiss = "modal">Cancel</button>
                        <button type = "submit" class = "btn btn-sm btn-danger col-lg-3">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function refresh(page = 1) {
        $.ajax({
            url: "<?php echo base_url() ?>",
            type: "GET",
            dataType: "JSON",
            success: function(respond) {
                var html = "";
                if(respond["page"]["previous"]){
                    html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(respond["page"]["before"])+')"><</a></li>';
                }
                else{
                    html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
                }
                if(respond["page"]["before"]){
                    html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(respond["page"]["before"])+')">'+respond["page"]["before"]+'</a></li>';
                }
                html += '<li class="page-item active"><a class="page-link" onclick = "refresh('+(respond["page"]["current"])+')">'+respond["page"]["current"]+'</a></li>';
                if(respond["page"]["after"]){
                    html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(respond["page"]["after"])+')">'+respond["page"]["after"]+'</a></li>';
                }
                if(respond["page"]["next"]){
                    html += '<li class="page-item"><a class="page-link" onclick = "refresh('+(respond["page"]["after"])+')">></a></li>';
                }
                else{
                    html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
                }
                $("#pagination_container").html(html);
            },
            error: function(){
                var html = "";
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed"><</a></li>';
                html += '<li class="page-item"><a class="page-link" style = "cursor:not-allowed">></a></li>';
                $("#pagination_container").html(html);
            }
        });
    }
    function sort(col_name){

    }
</script>
<script>
    window.onload = function(){
        refresh();
    }
</script>