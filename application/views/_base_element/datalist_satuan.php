<datalist id='datalist_satuan'></datalist>
<script>
  var datalist_satuan;
  load_datalist_satuan();

  function load_datalist_satuan() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/satuan/list_data",
      type: "GET",
      dataType: "JSON",
      async: false,
      success: function(respond) {
        var html_datalist_satuan = "";
        if (respond["status"] == "SUCCESS") {
          console.log(respond["content"]);
          datalist_satuan = respond['content'];
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_satuan += "<option value = '" + respond['content'][a]["nama"] + "'>" + respond["content"][a]["nama"].toString().toUpperCase() + " / Rumus: " + respond["content"][a]["rumus"] + "</option>";
          }
          $("#datalist_satuan").html(html_datalist_satuan);
        }
      }
    });
  }
</script>