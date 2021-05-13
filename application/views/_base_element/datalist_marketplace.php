<datalist id='datalist_marketplace'></datalist>
<script>
  var html_option_marketplace = "";
  load_datalist_marketplace();
  function load_datalist_marketplace() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/marketplace/list_data",
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_marketplace = "";
        if (respond["status"] == "SUCCESS") {
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_marketplace += "<option value = '" + respond['content'][a]["nama"] + "'></option>";
            html_option_marketplace += "<option value = '" + respond['content'][a]["id"] + "'>" + respond['content'][a]["nama"] + "</option>";
          }
          $("#datalist_marketplace").html(html_datalist_marketplace);
        }
      }
    });
  }
</script>