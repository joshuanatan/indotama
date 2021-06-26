<div class="modal fade" id="selesai_modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Konfirmasi Selesai <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <h5>Apakah yakin akan menyelesaikan penjualan ini?</h5>
        <div class="form-group">
          <button type="button" class="btn btn-sm btn-primary" onclick="selesai_penjualan_func()">Penjualan Selesai</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  var id_penjualan;

  function load_selesai_content(row) {
    console.log(row);
    console.log(content[row]["id_pk_penjualan"]);
    id_penjualan = content[row]["id_pk_penjualan"];
  }
  function selesai_penjualan_func() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/penjualan/selesai?id=" + id_penjualan,
      type: "GET",
      dataType: "JSON",
      success: function(respond) {
        refresh(page);
        $("#selesai_modal").modal("hide");
      }
    })
  }
</script>