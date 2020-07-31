
<div class = "modal fade" id = "detail_modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Detail Log</h4>
            </div>
            <div class = "modal-body">
                    
                <div class = "form-group col-lg-12">
                    <h5>Log Date</h5>
                    <span id = "d_log_date"></span>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Username</h5>
                    <span id = "d_log_username"></span>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Log Message</h5>
                    <span id = "d_log_msg"></span>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Log Database Related</h5>
                    <span id = "d_log_it"></span>
                </div>
                <div class = "form-group col-lg-12">
                    <h5>Log Data Changes</h5>
                    <span id = "d_log_data_changes"></span>
                </div>
                <div class = "form-group">
                    <button type = "button" class = "btn btn-sm btn-primary" data-dismiss = "modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function load_detail_content(row){
        $("#d_log_date").html(content[row]["date"]);
        $("#d_log_username").html(content[row]["user"]);
        $("#d_log_msg").html(content[row]["msg"]);
        $("#d_log_it").html(content[row]["it"]);
        $("#d_log_data_changes").html(content[row]["data"]);
    }
</script>