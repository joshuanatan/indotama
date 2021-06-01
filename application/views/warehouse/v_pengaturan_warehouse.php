<?php
$page_title = "Warehouse";
$breadcrumb = array(
  "Warehouse", "Pengaturan Warehouse"
);
$notif_data = array(
  "page_title" => $page_title
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php $this->load->view('req/mm_css.php'); ?>
</head>

<body>
  <div class="preloader-it">
    <div class="la-anim-1"></div>
  </div>
  <div class="wrapper theme-1-active pimary-color-pink">
    <?php $this->load->view('req/mm_menubar.php'); ?>
    <div class="page-wrapper">
      <?php $this->load->view('_notification/update_success', $notif_data); ?>
      <?php $this->load->view('_notification/update_error', $notif_data); ?>
      <div class="container-fluid">
        <div class="row mt-20">
          <div class="col-lg-12 col-sm-12">
            <div class="panel panel-default card-view">
              <div class="panel-heading bg-gradient">
                <div class="pull-left">
                  <h6 class="panel-title txt-light"><?php echo ucwords($page_title); ?></h6>
                </div>
                <div class="clearfix"></div>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">Home</a></li>
                  <?php for ($a = 0; $a < count($breadcrumb); $a++) : ?>
                    <?php if ($a + 1 != count($breadcrumb)) : ?>
                      <li class="breadcrumb-item"><?php echo ucwords($breadcrumb[$a]); ?></a></li>
                    <?php else : ?>
                      <li class="breadcrumb-item active"><?php echo ucwords($breadcrumb[$a]); ?></li>
                    <?php endif; ?>
                  <?php endfor; ?>
                </ol>
              </div>
              <div class="panel-wrapper collapse in">
                <div class="panel-body" style="background-color:white">
                  <div class="col-lg-12">
                    <form id="update_form" method="POST">
                      <input type="hidden" name="id" id="id_edit">
                      <div class="form-group">
                        <h5>Nama Warehouse</h5>
                        <input type="text" class="form-control" name="warehouse_nama" id="warehouse_nama_edit" required>
                      </div>

                      <div class="form-group">
                        <h5>Alamat</h5>
                        <input type="text" class="form-control" name="warehouse_alamat" id="warehouse_alamat_edit" required>
                      </div>

                      <div class="form-group">
                        <h5>No Telp</h5>
                        <input type="text" class="form-control" name="warehouse_notelp" id="warehouse_notelp_edit" required>
                      </div>

                      <div class="form-group">
                        <h5>Deskripsi</h5>
                        <input type="text" class="form-control" name="warehouse_desc" id="warehouse_desc_edit" required>
                      </div>
                      <div class="form-group">
                        <button type="button" onclick="update_func();update_id_gudang();location.reload()" class="btn btn-sm btn-primary">Submit</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php $this->load->view('req/mm_js.php'); ?>
</body>

</html>

<script>
  var ctrl = "warehouse";
</script>
<?php $this->load->view("_core_script/menubar_func"); ?>
<script>
  $.ajax({
    url: "<?php echo base_url(); ?>ws/warehouse/pengaturan",
    type: "GET",
    dataType: "JSON",
    success: function(respond) {
      if (respond["status"].toLowerCase() == "success") {
        $("#id_edit").val(respond["content"][0]["id"]);
        $("#warehouse_nama_edit").val(respond["content"][0]["nama"]);
        $("#warehouse_alamat_edit").val(respond["content"][0]["alamat"]);
        $("#warehouse_notelp_edit").val(respond["content"][0]["notelp"]);
        $("#warehouse_desc_edit").val(respond["content"][0]["desc"]);
      }
    }
  });

  function update_id_gudang() {
    $.ajax({
      url: "<?php echo base_url(); ?>ws/warehouse/refresh_id_warehouse",
      type: "GET",
      async: false,
      dataType: "JSON"
    });
  }
</script>


<?php $this->load->view('_notification/notif_general'); ?>
<?php $this->load->view("req/core_script"); ?>