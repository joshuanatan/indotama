<script>
  function delete_func() {
    var id = $("#id_delete").val();
    var url = "";
    if (typeof(delete_params) == 'undefined') {
      url = "<?php echo base_url(); ?>ws/" + ctrl + "/delete?id=" + id;
    } else {
      console.log("test");
      url = "<?php echo base_url(); ?>ws/" + ctrl + "/delete?id=" + id + delete_params;
    }
    $.ajax({
      url: url,
      type: "DELETE",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        if (respond["status"].toUpperCase() == "SUCCESS") {
          $('#notif_delete_success').show(1).delay(2000).hide(1);
          $("#delete_modal").modal("hide");
          if (typeof(refresh) != "undefined") {
            refresh(page);
          }
            console.log("Test2");
          //notification
        }

        if (respond["status"].toUpperCase() == "ERROR") {
          $('#delete_error_msg').empty();
          $('#delete_error_msg').append(respond["msg"]);
          $('#notif_delete_error').show(1).delay(2000).hide(1);
        }

      },
      error: function() {
        //notification
        $('#delete_error_msg').empty();
        $('#delete_error_msg').append(respond["msg"]);
        $('#notif_delete_error').show(1).delay(2000).hide(1);
      }
    })
  }
</script>