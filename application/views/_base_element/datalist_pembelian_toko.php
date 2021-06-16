<!-- datalist ini dipake untuk gduang karena gudang udah melekat ke toko -->
<datalist id="datalist_pembelian_toko"></datalist>

<script>
  load_datalist_pembelian_toko();
  function load_datalist_pembelian_toko() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/pembelian/list_pembelian_toko",
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        var html = "";
        if (respond["status"].toUpperCase() == "SUCCESS") {
          for (var a = 0; a < respond["content"].length; a++) {
            html += "<option value = '" + respond["content"][a]["nomor"] + "'>" + respond["content"][a]["nama_toko"] + " - " + respond["content"][a]["daerah_cabang"] + " / Supplier: " + respond["content"][a]["perusahaan_sup"] + "</option>";
          }
          $("#datalist_pembelian_toko").html(html);
        }
      }
    })
  }
</script>