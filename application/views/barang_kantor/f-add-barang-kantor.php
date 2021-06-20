<div class="modal fade" id="register_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambah Data <?php echo ucwords($page_title); ?> Jual</h4>
      </div>
      <div class="modal-body">
        <?php
        $notif_data = array(
          "page_title" => $page_title
        );
        $this->load->view('_notification/register_error', $notif_data); ?>
        <form id="register_form" method="POST" enctype="multipart/form-data">
          <div class="form-group col-lg-6">
            <h5>Kode Barang</h5>
            <input type="text" class="form-control" required name="kode">
          </div>
          <div class="form-group col-lg-6">
            <h5>Nama Barang</h5>
            <input type="text" class="form-control" required name="nama">
          </div>
          <div class="form-group col-lg-6">
            <h5>Jenis Barang</h5>
            <input id="tambah_id_brg_jenis_btn" list="datalist_barang_jenis_jualan" type="text" required name="id_brg_jenis" class="form-control" value = "BARANG KANTOR" readonly>
          </div>
          <div class="form-group col-lg-6">
            <h5>Merk Barang</h5>
            <input list="datalist_barang_merk" type="text" required name="id_brg_merk" class="form-control">
          </div>
          <div class="form-group col-lg-6">
            <h5>Keterangan</h5>
            <input type="text" class="form-control" required name="keterangan">
          </div>
          <div class="form-group col-lg-6">
            <h5>Minimal Stok</h5>
            <input type="text" class="form-control nf-input" required name="minimal">
          </div>
          <div class="form-group col-lg-6">
            <h5>Satuan</h5>
            <input type="text" class="form-control" required name="satuan" list="datalist_satuan">
          </div>
          <div class="form-group">
            <h5>Gambar</h5>
            <input type="file" required name="gambar">
          </div>
          <div class="form-group col-lg-12">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="register_func();" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  var baris_barang_counter = 0;

  function tambah_baris_barang() {
    var html = `
        <tr>
            <input type = 'hidden' name = 'check[]' value = '${baris_barang_counter}'>
            <td>
                <input type = 'text' class = 'form-control' list = 'datalist_barang' name = 'barang${baris_barang_counter}'>
            </td>
            <td>
                <input type = 'text' class = 'form-control nf-input' name = 'qty${baris_barang_counter}'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
    $("#btn_tambah_baris_barang_container").before(html);
    init_nf();
    baris_barang_counter++;
  }
</script>