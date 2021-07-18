<div class="modal fade" id="register_modal">
  <div class="modal-dialog modal-lg">
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
        <form id="register_form" method="POST">
          <input type="hidden" id="id_warehouse" name="id_warehouse" value="<?php echo $warehouse[0]["id_pk_warehouse"]; ?>">
          <div class="form-group">
            <h5>Item Pembelian</h5>
            <table class="table table-striped table-bordered">
              <thead>
                <th>Barang</th>
                <th>Stok Saat Ini (Satuan)</th>
                <th>Notes</th>
                <th>Action</th>
              </thead>
              <tbody id="daftar_brg_beli_add">
                <tr id="add_brg_beli_but_container">
                  <td colspan=6><button type="button" class="btn btn-primary btn-sm col-lg-12" onclick="add_brg_beli_row()">Tambah Barang</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cancel</button>
            <button type="button" onclick="register_func();empty_table_form()" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  var brg_count = 0;

  function add_brg_beli_row() {
    $("#id_warehouse").val(<?php echo $warehouse[0]["id_pk_warehouse"]; ?>);
    var html = `
        <tr class = 'add_brg_count'>
            <td id = 'row${brg_count}'>
                <input name = 'check[]' value = ${brg_count} type = 'hidden'>
                <input type = 'text' list = 'datalist_barang' name = 'brg${brg_count}' class = 'form-control'>
            </td>
            <td>
                <input type = 'text' name = 'brg_qty${brg_count}' class = 'form-control nf-input'>
            </td>
            <td>
                <input type = 'text' name = 'brg_notes${brg_count}' class = 'form-control'>
            </td>
            <td>
                <i style = 'cursor:pointer;font-size:large;margin-left:10px' class = 'text-danger md-delete' onclick = '$(this).parent().parent().remove()'></i>
            </td>
        </tr>`;
    $("#add_brg_beli_but_container").before(html);
    init_nf();
    brg_count++;
  }

  function empty_table_form() {
    var brg_count = 0;
    $(".add_brg_count").remove();
  }
</script>