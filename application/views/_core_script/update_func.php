<script>
  var default_update_form_html = $("#update_form").html();

  function reset_update_form() {
    $("#update_form").html(default_update_form_html);
    if (typeof(init_nf) != "undefined") {
      init_nf();
    }
    if (typeof(emp_edit_form_script_init) != "undefined") {
      emp_edit_form_script_init();
    }
  }
</script>
<script>
  function update_func() {
    if (typeof(nf_reformat_all) != "undefined") {
      nf_reformat_all();
    }
    var form = $("#update_form")[0];
    var data = new FormData(form);
    $.ajax({
      url: "<?php echo base_url(); ?>ws/" + ctrl + "/update",
      type: "POST",
      dataType: "JSON",
      data: data,
      async: false,
      processData: false,
      contentType: false,
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          $('#notif_update_success').show(1).delay(2000).hide(1);
          $("#update_modal").modal("hide");
          if (typeof(refresh) != "undefined") {
            refresh(page);
          }
          if (typeof(reset_update_form) != "undefined") {
            /*reset_update_form();*/
          }
        }

        if (respond["status"] == "ERROR") {
          $('#update_error_msg').empty();
          $('#update_error_msg').append(respond["msg"]);
          $('#notif_update_error').show(1).delay(2000).hide(1);
        }
      },
      error: function() {
        //notification
        $('#update_error_msg').empty();
        $('#update_error_msg').append(respond["msg"]);
        $('#notif_update_error').show(1).delay(2000).hide(1);
      }
    });
  }
</script>