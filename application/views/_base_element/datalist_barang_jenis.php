<datalist id='datalist_barang_jenis'></datalist>
<script>
  var datalist_barang_jenis;
  load_datalist_barang_jenis();

  function load_datalist_barang_jenis() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang_jenis/list_data",
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          datalist_barang_jenis = respond["content"];
          var html_datalist_barang_jenis = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_barang_jenis += "<option value = '" + respond["content"][a]["nama"] + "'>";
          }
          $("#datalist_barang_jenis").html(html_datalist_barang_jenis);
        }
      },
    });
  }
</script>