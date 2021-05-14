<div class="modal fade" id="register_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambah Data <?php echo ucwords($page_title); ?></h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/register_error', $notif_data); ?>
        <form id="register_form" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <h5>Customer</h5>
            <input type="text" class="form-control" required name="penawar">
          </div>
          <div class="form-group">
            <h5>Tanggal Penawaran</h5>
            <input type="date" class="form-control" required name="tgl">
          </div>
          <div class="form-group">
            <h5>Subjek Penawaran</h5>
            <input list="datalist_barang_jenis" type="text" required name="subjek" class="form-control">
          </div>
          <div class="form-group">
            <h5>Content Penawaran</h5>
            <textarea class="form-control" required name="content"></textarea>
          </div>
          <div class="form-group">
            <h5>Notes Penawaran</h5>
            <textarea class="form-control" required name="notes"></textarea>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <th>Nama Barang</th>
                <th>Jumlah Barang</th>
                <th>Harga Penawaran</th>
                <th>Notes</th>
                <th></th>
              </thead>
              <tbody>
                <tr id="btn_tambah_item_penawaran_container">
                  <td colspan="5"><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="$('#btn_tambah_item_penawaran_container').before(tambah_data_barang_penawaran_row())">Tambah Data Barang Penawaran</button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="register_func()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function tambah_data_barang_penawaran_row() {
    var counter = $(".data_barang_penawaran_row").length;
    var html = `
      <tr class = "data_barang_penawaran_row">
        <input type = "hidden" name = "check[]" value = "${counter}">
        <td><input type = "text" class = "form-control" name = "nama_barang${counter}"></td>
        <td><input type = "text" class = "form-control" name = "jumlah_barang${counter}"></td>
        <td><input type = "text" class = "form-control" name = "harga_barang${counter}"></td>
        <td><textarea class = "form-control" name = "notes_barang${counter}"></textarea></td>
        <td>
          <a href = "#" class = "text-danger"><i class = "icon md-delete"></i></a>
        </td>
      </tr>
    `;
    return html;
  }
</script>