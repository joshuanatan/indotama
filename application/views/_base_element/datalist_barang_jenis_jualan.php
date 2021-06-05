<datalist id='datalist_barang_jenis_jualan'></datalist>
<script>
  var datalist_barang_jenis_jualan;
  load_datalist_barang_jenis_jualan();

  function load_datalist_barang_jenis_jualan() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/barang_jenis/list_data_jualan",
      type: "GET",
      async: false,
      dataType: "JSON",
      success: function(respond) {
        if (respond["status"] == "SUCCESS") {
          datalist_barang_jenis_jualan = respond["content"];
          var html_datalist_barang_jenis_jualan = "";
          for (var a = 0; a < respond["content"].length; a++) {
            html_datalist_barang_jenis_jualan += "<option value = '" + respond["content"][a]["nama"] + "'>";
          }
          $("#datalist_barang_jenis_jualan").html(html_datalist_barang_jenis_jualan);
        }
      },
    });
  }
</script>