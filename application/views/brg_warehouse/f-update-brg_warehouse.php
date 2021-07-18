<div class="modal fade" id="update_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Ubah Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/update_error', $notif_data); ?>
        <form id="update_form" method="POST">
          <input type="hidden" name="id" id="id_edit">
          <div class="form-group">
            <h5>Nama Barang</h5>
            <input type='text' class='form-control' readonly list='datalist_barang' name='brg' id="brg_edit">
          </div>
          <div class="form-group">
            <h5>Stok</h5>
            <input type="text" class="form-control nf-input" readonly required name="stok" id="stok_edit">
          </div>
          <div class="form-group">
            <h5>Notes</h5>
            <input type='text' class="form-control" required name="notes" id="notes_edit">
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="update_func()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function load_edit_content(row) {
    $("#id_edit").val(content[row]["id"]);
    $("#brg_edit").val(content[row]["nama_brg"]);
    $("#stok_edit").val(content[row]["qty"]);
    $("#notes_edit").val(content[row]["notes"]);
  }
</script>