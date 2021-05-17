<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $id = (int)mysql_real_escape_string(trim($_GET["invoice-view"]));
  $sql_invoice = mysql_query("SELECT invoice.*, customer.nama FROM invoice LEFT JOIN customer ON customer.id = invoice.customer_id WHERE invoice.id = '$id'") or die(mysql_error());
  $row_invoice = mysql_fetch_array($sql_invoice);
  $total = $row_invoice['total'];
  if ($_POST["dp"] == $total) {
    $status = 1;
  } else {
    $status = (int)mysql_real_escape_string(trim($_POST["status"]));
  }
  $tanggal = mysql_real_escape_string(trim($_POST["tanggal"]));
  $pembayaran = mysql_real_escape_string(strip_tags(trim($_POST["dp"])));
  $note = mysql_real_escape_string($_POST["note"]);
  $customer = (int)mysql_real_escape_string(trim($_POST["customer"]));
  $jenis = (int)mysql_real_escape_string(trim($_POST["jenis"]));
  $jatuhtempo = mysql_real_escape_string(trim($_POST["jatuhtempo"]));
  $tanggaldp = mysql_real_escape_string(trim($_POST["tanggaldp"]));
  $online = (int)mysql_real_escape_string(trim($_POST["online"]));
  $capttd = (int)mysql_real_escape_string(trim($_POST["capttd"]));
  $nofp = mysql_real_escape_string(trim($_POST["nofp"]));

  //Ambil Data Produk ------------------------------------------------------------------

  $x = 0;
  while ($x < 5) {
    $x++;

    $invoice_id = (int)mysql_real_escape_string(trim($_GET["invoice-view"]));

    $produk = (int)mysql_real_escape_string(trim($_POST["produk_" . $x]));
    $produk_custom = $_POST["produk_custom_" . $x];

    if ($produk == 0 && $produk_custom == '') {
      continue;
    } else {

      $qty = mysql_real_escape_string(strip_tags(trim($_POST["qty_" . $x])));
      $qty_markup = mysql_real_escape_string(strip_tags(trim($_POST["qty_markup_" . $x])));

      if ($produk != 0) {
        $sql_produkname = mysql_query("SELECT stok.id, jenis_barang.nama FROM stok LEFT JOIN jenis_barang ON jenis_barang.id = stok.jenisbarang_id WHERE stok.id = '$produk'") or die(mysql_error());
        $row_produkname = mysql_fetch_array($sql_produkname);

        if ($produk_custom == '') {
          $produk_name = $row_produkname['nama'];
        } else {
          $produk_name = $row_produkname['nama'] . ' ' . $produk_custom;
        }

        $sql_stok = mysql_query("SELECT * FROM stok WHERE id = '$produk'") or die(mysql_error());
        $row_stok = mysql_fetch_array($sql_stok);
        $stokawal = $row_stok['stok'];
        $stokakhir = $stokawal - $qty;

        mysql_query("UPDATE stok SET stok = '$stokakhir' WHERE id = '$produk'") or die(mysql_error());
      } else {
        $produk_name = $produk_custom;
        $stokakhir = '';
      }

      $harga = mysql_real_escape_string(strip_tags(trim($_POST["harga_" . $x])));
      $harga_markup = mysql_real_escape_string(strip_tags(trim($_POST["harga_markup_" . $x])));

      $tambahtotal = $qty * $harga;

      $sql_invoicedetail = mysql_query("SELECT * FROM invoice WHERE id = '$invoice_id'") or die(mysql_error());
      $row_invoicedetail = mysql_fetch_array($sql_invoicedetail);
      $noinv = $row_invoicedetail['nomor'];
      $totalsekarang = $row_invoicedetail['total'] + $tambahtotal;

      mysql_query("UPDATE invoice SET total = '$totalsekarang' WHERE id = '$invoice_id'") or die(mysql_error());

      mysql_query("INSERT INTO invoice_product VALUES ('', '$invoice_id', '$produk', '$produk_name', '$qty', '$qty_markup', '$harga', '$harga_markup', '', '', '', '', '')") or die(mysql_error());

      $datenow = date('Y-m-d H:i:s', strtotime('+7 hours'));

      mysql_query("INSERT INTO log VALUES ('', '$administrator_id', '$datenow', 'menambahkan Produk: $produk_name, dengan Qty: $qty, Harga: $harga, Qty MarkUp: $qty_markup, Harga MarkUp: $harga_markup, pada Invoice $noinv. Stok sekarang menjadi: $stokakhir')") or die(mysql_error());
    }
  }
  $sql_checklastinv = mysql_query("SELECT * FROM invoice WHERE id = '$invoice_id'") or die(mysql_error());
  $row_checklastinv = mysql_fetch_array($sql_checklastinv);

  $sql_log = mysql_query("SELECT * FROM invoice WHERE id = '$id'") or die(mysql_error());
  $row_log = mysql_fetch_array($sql_log);

  $noinv = $row_log['nomor'];
  $tanggallama = $row_log['tanggal'];
  $custlama = $row_log['customer_id'];
  $pembayaranlama = $row_log['pembayaran'];
  $statuslama = $row_log['status'];
  $jenislama = $row_log['jenis'];
  $jatuhtempolama = $row_log['jatuhtempo'];
  $notelama = $row_log['note'];

  if ($tanggallama == $tanggal && $custlama == $customer && $pembayaranlama == $pembayaran && $statuslama == $status && $jenislama == $jenis && $jatuhtempolama == $jatuhtempo && $notelama == $note) {
    $peler = 'keren';
  } else {

    $datenow = date('Y-m-d H:i:s', strtotime('+7 hours'));

    mysql_query("INSERT INTO log VALUES ('', '$administrator_id', '$datenow', 'mengedit Invoice $noinv, dengan Tanggal: $tanggallama menjadi $tanggal, Customer ID: $custlama menjadi $customer, Jumlah Pembayaran: $pembayaranlama menjadi $pembayaran, Status: $statuslama menjadi $status, Jenis Pembayaran: $jenislama menjadi $jenis, Tanggal Jatuh Tempo: $jatuhtempolama menjadi $jatuhtempo, Catatan: $notelama menjadi $note di Database Penjualan.')") or die(mysql_error());
  }

  //Set Markup Sesuai Invoice Product
  $sql_setmarkup = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id' AND harga_markup != 0 ") or die(mysql_error());
  if (mysql_num_rows($sql_setmarkup) == 0) {
    $markup = 1;
  } else {
    $markup = 2;
  }

  mysql_query("
    UPDATE invoice SET
    customer_id = '$customer',
    tanggal = '$tanggal',
    status = '$status',
    pembayaran = '$pembayaran',
    note = '$note',
    markup = '$markup',
    jenis = '$jenis',
    jatuhtempo = '$jatuhtempo',
    tanggaldp = '$tanggaldp',
    online = '$online',
    capttd = '$capttd',
		nofp = '$nofp'
    WHERE id = $id
    ") or die(mysql_error());

  header("location:index.php?invoice-view=" . $row_checklastinv['id']);
}

if (isset($_GET["item-delete"])) {
  $itemid = (int)mysql_real_escape_string(trim($_GET["item-delete"]));
  $sql_idinvoice = mysql_query("SELECT invoice_product.*, invoice.nomor as noinv FROM invoice_product LEFT JOIN invoice ON invoice.id = invoice_product.invoice_id WHERE invoice_product.id = '$itemid'") or die(mysql_error());
  $row_idinvoice = mysql_fetch_array($sql_idinvoice);

  $qtyitem = $row_idinvoice['quantity'];
  $iditem = $row_idinvoice['product_id'];

  $namaproduk = $row_idinvoice['product_name'];
  $qtylog = $row_idinvoice['quantity'];
  $hargalog = $row_idinvoice['harga'];
  $qtymarkuplog = $row_idinvoice['quantity_markup'];
  $hargamarkuplog = $row_idinvoice['harga_markup'];
  $noinv = $row_idinvoice['noinv'];

  $idinvoice = $row_idinvoice['invoice_id'];
  $kurangtotal = $row_idinvoice['quantity'] * $row_idinvoice['harga'];

  $sql_invoicedetail = mysql_query("SELECT * FROM invoice WHERE id = '$idinvoice'") or die(mysql_error());
  $row_invoicedetail = mysql_fetch_array($sql_invoicedetail);
  $totalsekarang = $row_invoicedetail['total'] - $kurangtotal;

  if ($iditem != 0) {
    // Ganti Stok
    $sql_stok = mysql_query("SELECT * FROM stok WHERE id = '$iditem'") or die(mysql_error());
    $row_stok = mysql_fetch_array($sql_stok);
    $stokawal = $row_stok['stok'];
    $stokakhir = $stokawal + $qtyitem;

    mysql_query("UPDATE stok SET stok = '$stokakhir' WHERE id = '$iditem'") or die(mysql_error());
  }

  mysql_query("UPDATE invoice SET total = '$totalsekarang' WHERE id = '$idinvoice'") or die(mysql_error());

  mysql_query("DELETE FROM invoice_product WHERE id = '$itemid';") or die(mysql_error());

  $datenow = date('Y-m-d H:i:s', strtotime('+7 hours'));

  mysql_query("INSERT INTO log VALUES ('', '$administrator_id', '$datenow', 'menghapus Produk: $namaproduk dengan Qty: $qtylog, dan Harga Jual: $hargalog pada Invoice: $noinv. Stok sekarang menjadi: $stokakhir')") or die(mysql_error());

  //Set Markup Sesuai Invoice Product
  $sql_setmarkup = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$idinvoice' AND harga_markup != 0 ") or die(mysql_error());
  if (mysql_num_rows($sql_setmarkup) == 0) {
    $markup = 1;
  } else {
    $markup = 2;
  }

  mysql_query("
    UPDATE invoice SET
    markup = '$markup'
    WHERE id = $idinvoice
    ") or die(mysql_error());

  header("location:index.php?invoice-view=" . $idinvoice);
}

$id = (int)mysql_real_escape_string(trim($_GET["invoice-view"]));
$sql_invoice = mysql_query("SELECT invoice.*, customer.nama, customer.perusahaan, customer.alamat, customer.telepon, customer.npwp FROM invoice LEFT JOIN customer ON customer.id = invoice.customer_id WHERE invoice.id = '$id'") or die(mysql_error());
$row_invoice = mysql_fetch_array($sql_invoice);
?>
<?php
if (isset($_GET["suratjalan-delete"])) {

  $id = (int)mysql_real_escape_string(trim($_GET["suratjalan-delete"]));

  $sql_invoiceid = mysql_query("SELECT * FROM suratjalan WHERE id = '$id'") or die(mysql_error());
  $row_invoiceid = mysql_fetch_array($sql_invoiceid);

  mysql_query("DELETE FROM suratjalan_product WHERE suratjalan_id = '$id'") or die(mysql_error());

  mysql_query("DELETE FROM suratjalan WHERE id = '$id'") or die(mysql_error());

  header("location:index.php?invoice-view=" . $row_invoiceid['invoice_id']);
}
?>
<?php
if (isset($_GET["suratjalaninvoice-add"])) {

  $idinvoice = (int)mysql_real_escape_string(trim($_GET["suratjalaninvoice-add"]));

  // Start Nomor SJ
  $sql_tambah = mysql_query("SELECT SUBSTRING(nomor,4,10) as tambah FROM suratjalan ORDER BY id DESC LIMIT 1;") or die(mysql_error());
  $row_tambah = mysql_fetch_array($sql_tambah);
  if (mysql_num_rows($sql_tambah) == 0) {
    $id_sekarang = 1;
  } else {
    $id_sekarang = $row_tambah["tambah"] + 1;
  }

  $nomorsj = 'SJ-' . $id_sekarang;
  // End Nomor SJ

  $tanggal = date('Y-m-d');
  $user_id = $administrator_id;

  mysql_query("INSERT INTO suratjalan VALUES ('', '$nomorsj', '$tanggal', '$idinvoice', 'Sesuai Invoice', '$user_id')") or die(mysql_error());

  $sql_idsuratjalan = mysql_query("SELECT * FROM suratjalan ORDER BY id DESC LIMIT 1") or die(mysql_error());
  $row_idsuratjalan = mysql_fetch_array($sql_idsuratjalan);
  $suratjalan_id = $row_idsuratjalan['id'];

  $sql_addproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$idinvoice'") or die(mysql_error());

  while ($row_addproduk = mysql_fetch_array($sql_addproduk)) {
    $namaproduk = $row_addproduk['product_name'];
    $qty = $row_addproduk['quantity'];

    mysql_query("INSERT INTO suratjalan_product VALUES ('', '$suratjalan_id', '$namaproduk', '$qty')") or die(mysql_error());
  }

  header("location:index.php?invoice-view=" . $idinvoice);
}
?>
<div class="page-content-wrapper">
  <div class="page-content">
    <!-- BEGIN PAGE HEADER-->
    <h3 class="page-title">
      View Invoice
    </h3>
    <!-- END PAGE HEADER-->
    <!-- BEGIN PAGE CONTENT-->
    <div class="row">
      <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue-hoki">
          <div class="portlet-title">
            <div class="caption">
              <p><i class="fa fa-search"></i><?php echo $row_invoice['nomor'] ?> (Customer : <a href="index.php?customer-view=<?php echo $row_invoice['customer_id'] ?>" style="color:#fff"><?php echo $row_invoice['nama']; ?></a>)</p>
            </div>
            <div class="actions">
              <div class="btn-group">
                <a class="btn default yellow" <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?> data-toggle="confirmation" data-original-title="Are you sure ?" data-placement="left" data-href="index.php?invoice-cancel=<?php echo $row_invoice['id'] ?>">
                  Cancel Nota <i class="fa fa-arrow-left"></i>
                </a>
              </div>
              <?php if ($row_invoice['markup'] == 2) { ?>
                <div class="btn-group">
                  <button class="btn green" onclick="printDiv('areamarkup')"><i class="fa fa-print"></i> Print Markup</button>
                </div>
              <?php } ?>
              <?php if ($toko_id == 2 || $toko_id == 5) { ?>
                <div class="btn-group">
                  <button class="btn red" onclick="printDiv('areaall')"><i class="fa fa-print"></i> Print</button>
                </div>
              <?php } else { ?>
                <div class="btn-group">
                  <button class="btn red" onclick="printDiv('areaasli')"><i class="fa fa-print"></i> Print Asli</button>
                </div>
                <div class="btn-group">
                  <button class="btn yellow" onclick="printDiv('areacopy')"><i class="fa fa-print"></i> Print Copy</button>
                </div>
              <?php } ?>
              <div class="btn-group">
                <a class="btn default" href="index.php?invoice">
                  Back <i class="fa fa-arrow-left"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="portlet-body form">
            <!-- BEGIN FORM-->
            <form action="" id="form_sample_1" class="horizontal-form" method="post">
              <div class="form-body">
                <h3 class="form-section">Invoice Info</h3>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php $sql_customer = mysql_query("SELECT * FROM customer WHERE toko = '$toko_id' ORDER BY nama DESC") or die(mysql_error()); ?>
                      <label class="control-label">Customer
                      </label>
                      <select class="form-control select2me" name="customer">
                        <option value="">Select...</option>
                        <?php while ($row_customer = mysql_fetch_array($sql_customer)) { ?>
                          <option <?php if ($row_invoice['customer_id'] == $row_customer['id']) { ?>selected<?php } ?> value="<?php echo $row_customer['id'] ?>"> <?php echo $row_customer['nama'] ?> (<?php echo $row_customer['perusahaan']; ?>) </option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Tanggal Nota</label>
                      <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                        <input type="text" class="form-control" value="<?php echo $row_invoice['tanggal'] ?>" name="tanggal" required <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>>
                        <span class="input-group-btn">
                          <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                      </div>
                      <!-- /input-group -->
                    </div>
                  </div>
                  <!--/span-->
                </div>
                <!--/row-->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Jenis Pembayaran</label>
                      <div class="radio-list" data-error-container="#form_2_membership_error">
                        <label class="radio-inline">
                          <input type="radio" name="jenis" value="1" <?php if ($row_invoice['jenis'] == 1) { ?>checked<?php } ?> />
                          Cash </label>
                        <label class="radio-inline">
                          <input type="radio" name="jenis" value="2" <?php if ($row_invoice['jenis'] == 2) { ?>checked<?php } ?> />
                          Debit </label>
                        <label class="radio-inline">
                          <input type="radio" name="jenis" value="3" <?php if ($row_invoice['jenis'] == 3) { ?>checked<?php } ?> />
                          Kredit </label>
                        <label class="radio-inline">
                          <input type="radio" name="jenis" value="4" <?php if ($row_invoice['jenis'] == 4) { ?>checked<?php } ?> />
                          Transfer </label>
                      </div>
                      <div id="form_2_membership_error">
                      </div>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Tanggal Jatuh Tempo</label>
                      <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                        <input type="text" class="form-control" value="<?php echo $row_invoice['jatuhtempo'] ?>" name="jatuhtempo" required <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>>
                        <span class="input-group-btn">
                          <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                      </div>
                      <!-- /input-group -->
                    </div>
                  </div>
                  <!--/span-->
                </div>
                <!--/row-->
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Status Pembayaran</label>
                      <div class="radio-list" data-error-container="#form_2_membership_error">
                        <label class="radio-inline">
                          <input type="radio" name="status" value="1" <?php if ($row_invoice['status'] == 1) { ?>checked<?php } ?> <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?> />
                          Full Payment </label>
                        <label class="radio-inline">
                          <input type="radio" name="status" value="2" <?php if ($row_invoice['status'] == 2) { ?>checked<?php } ?> <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?> />
                          DP </label>
                        <label class="radio-inline">
                          <input type="radio" name="status" value="3" <?php if ($row_invoice['status'] == 3) { ?>checked<?php } ?> <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?> />
                          Tempo </label>
                        <label class="radio-inline">
                          <input type="radio" name="status" value="4" <?php if ($row_invoice['status'] == 4) { ?>checked<?php } ?> <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?> />
                          Keep </label>
                        <?php if ($row_invoice['status'] == 0) { ?>
                          <label class="radio-inline">
                            <input type="radio" name="status" value="0" <?php if ($row_invoice['status'] == 0) { ?>checked<?php } ?> />
                            Cancel </label>
                        <?php } ?>
                      </div>
                      <div id="form_2_membership_error">
                      </div>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">Jumlah DP</label>
                      <input type="text" id="dp" name="dp" class="form-control" value="<?php echo $row_invoice['pembayaran'] ?>" <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">Tanggal DP</label>
                      <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                        <input type="text" class="form-control" value="<?php echo $row_invoice['tanggaldp'] ?>" name="tanggaldp" required <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>>
                        <span class="input-group-btn">
                          <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                      </div>
                      <!-- /input-group -->
                    </div>
                  </div>
                  <!--/span-->
                </div>
                <!--/row-->
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">Catatan</label>
                      <textarea class="form-control" rows="4" name="note" <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>><?php echo $row_invoice['note'] ?></textarea>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">Toko</label>
                      <div class="radio-list" data-error-container="#form_2_membership_error">
                        <label class="radio-inline">
                          <input <?php if ($toko_id != 1) { ?> disabled <?php } ?> type="radio" name="online" value="1" <?php if ($row_invoice['online'] == 1) { ?>checked<?php } ?> />
                          Bukan Online </label>
                        <?php if ($toko_id == 1) { ?>
                          <label class="radio-inline">
                            <input type="radio" name="online" value="2" <?php if ($row_invoice['online'] == 2) { ?>checked<?php } ?> />
                            Pusat Safety </label>
                        <?php } ?>
                      </div>
                      <div id="form_2_membership_error">
                      </div>
                    </div>
                  </div>
                  <!--/span-->
                  <?php if ($toko_id == 1 || $toko_id == 2 || $toko_id == 5) { ?>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="control-label">Cap TTD</label>
                        <div class="radio-list" data-error-container="#form_2_membership_error">
                          <label class="radio-inline">
                            <input <?php if ($toko_id != 1 && $toko_id != 2 && $toko_id != 5) { ?> disabled <?php } ?> type="radio" name="capttd" value="1" <?php if ($row_invoice['capttd'] == 1) { ?>checked<?php } ?> />
                            Off </label>
                          <?php if ($toko_id == 1 || $toko_id == 2 || $toko_id == 5) { ?>
                            <label class="radio-inline">
                              <input type="radio" name="capttd" value="2" <?php if ($row_invoice['capttd'] == 2) { ?>checked<?php } ?> />
                              On </label>
                          <?php } ?>
                        </div>
                        <div id="form_2_membership_error">
                        </div>
                      </div>
                    </div>
                    <!--/span-->
                  <?php } ?>
                  <?php if ($toko_id == 5) { ?>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="control-label">No Faktur Pajak</label>
                        <input type="text" name="nofp" value="<?php echo $row_invoice['nofp'] ?>" class="form-control">
                      </div>
                    </div>
                    <!--/span-->
                  <?php } ?>
                </div>
                <!--/row-->
                <h3 class="form-section">Product Info</h3>
                <table class="table">
                  <tr>
                    <td width="5%">No</td>
                    <td width="20%">Produk</td>
                    <td width="20%">Produk Custom</td>
                    <td width="5%">Qty</td>
                    <td width="5%">Qty Markup</td>
                    <td width="12.5%">Harga</td>
                    <td width="12.5%">Harga Markup</td>
                    <td width="12.5%">Total</td>
                    <td width="12.5%">Total Markup</td>
                    <td width="5%">Action</td>
                  </tr>
                  <tbody>
                    <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                    <?php $total_arr = array();
                    $totalmarkup_arr = array();
                    $x = 1;
                    while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                      <tr>
                        <td><?php echo $x; ?></td>
                        <td>
                          <?php
                          if ($row_listproduk['product_id'] == 0) {
                            echo '-';
                          } else {
                            echo $row_listproduk['product_name'];
                          }
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($row_listproduk['product_id'] == 0) {
                            echo $row_listproduk['product_name'];
                          } else {
                            echo '-';
                          }
                          ?>
                        </td>
                        <td><?php echo $row_listproduk['quantity']; ?></td>
                        <td><?php echo $row_listproduk['quantity_markup']; ?></td>
                        <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                        <td><?php
                            if ($row_listproduk['harga_markup'] == 0) {
                              echo '-';
                            } else {
                              echo 'Rp. ' . number_format(($row_listproduk['harga_markup']), 0, ',', '.');
                            }
                            ?>
                        </td>
                        <td>
                          <?php
                          $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                          $total_arr[] = $total;
                          echo 'Rp. ' . number_format(($total), 0, ',', '.');
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($row_listproduk['harga_markup'] == 0) {
                            echo '-';
                          } else {
                            $totalmarkup = $row_listproduk['harga_markup'] * $row_listproduk['quantity_markup'];
                            $totalmarkup_arr[] = $totalmarkup;
                            echo 'Rp. ' . number_format(($totalmarkup), 0, ',', '.');
                          }
                          ?>
                        </td>
                        <td>
                          <a class="btn default btn-xs red" data-toggle="confirmation" data-original-title="Are you sure ?" data-placement="left" data-href="index.php?item-delete=<?php echo $row_listproduk['id'] ?>">
                            <i class="fa fa-trash-o"></i></a>
                        </td>
                      </tr>
                    <?php $x++;
                    } ?>
                  </tbody>
                  <tr>
                    <td width="5%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="5%">&nbsp;</td>
                    <td width="5%">&nbsp;</td>
                    <td width="12.5%">&nbsp;</td>
                    <td width="12.5%" style="text-align:right">
                      <h4 style="font-weight:bold">TOTAL</h4>
                    </td>
                    <td width="12.5%">
                      <h4 style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                    echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></h4>
                    </td>
                    <td width="12.5%">
                      <h4 style="font-weight:bold">
                        <?php
                        $totalmarkup_all = array_sum($totalmarkup_arr);
                        if ($totalmarkup_all == 0) {
                          echo '-';
                        } else {
                          echo 'Rp. ' . number_format(($totalmarkup_all), 0, ',', '.');
                        }
                        ?>
                      </h4>
                    </td>
                    <td width="5%"></td>
                  </tr>
                  <?php if ($row_invoice['pembayaran'] != 0) { ?>
                    <tr>
                      <td width="5%">&nbsp;</td>
                      <td width="20%">&nbsp;</td>
                      <td width="20%">&nbsp;</td>
                      <td width="5%">&nbsp;</td>
                      <td width="5%">&nbsp;</td>
                      <td width="12.5%">&nbsp;</td>
                      <td width="12.5%" style="text-align:right">
                        <h4 style="font-weight:bold">DP</h4>
                      </td>
                      <td width="12.5%">
                        <h4 style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></h4>
                      </td>
                      <td width="12.5%">&nbsp;</td>
                      <td width="5%"></td>
                    </tr>
                    <tr>
                      <td width="5%">&nbsp;</td>
                      <td width="20%">&nbsp;</td>
                      <td width="20%">&nbsp;</td>
                      <td width="5%">&nbsp;</td>
                      <td width="5%">&nbsp;</td>
                      <td width="12.5%">&nbsp;</td>
                      <td width="12.5%" style="text-align:right">
                        <h4 style="font-weight:bold">SISA BAYAR</h4>
                      </td>
                      <td width="12.5%">
                        <h4 style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                      echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></h4>
                      </td>
                      <td width="12.5%">&nbsp;</td>
                      <td width="5%"></td>
                    </tr>
                  <?php } ?>
                </table>
                <h3 class="form-section">Tambah Produk</h3>
                <table class="table">
                  <tr>
                    <td width="2%">No</td>
                    <td width="15%">Produk</td>
                    <td width="15%">Produk Custom</td>
                    <td width="7%">Qty</td>
                    <td width="12%">Harga</td>
                    <td width="7%">Qty MU</td>
                    <td width="12%">Harga Markup</td>
                  </tr>
                  <tbody>
                    <?php $x = 1;
                    while ($x < 6) { ?>
                      <tr>
                        <td><?php echo $x; ?></td>
                        <td>
                          <?php
                          if ($toko_id != 5) {
                            $sql_produk = mysql_query("SELECT stok.id AS id, jenis_barang.nama, jenis_barang.kode, stok.stok FROM stok LEFT JOIN jenis_barang ON jenis_barang.id = stok.jenisbarang_id WHERE toko = '$toko_id' GROUP BY stok.jenisbarang_id") or die(mysql_error());
                          } else {
                            $sql_produk = mysql_query("SELECT stok.id AS id, jenis_barang.nama, jenis_barang.kode, stok.stok FROM stok LEFT JOIN jenis_barang ON jenis_barang.id = stok.jenisbarang_id WHERE toko = 2 GROUP BY stok.jenisbarang_id") or die(mysql_error());
                          }
                          ?>
                          <select class="form-control select2me" name="produk_<?php echo $x; ?>">
                            <option value="0">Select...</option>
                            <?php while ($row_produk = mysql_fetch_array($sql_produk)) { ?>
                              <option value="<?php echo $row_produk['id'] ?>"> <?php echo $row_produk['kode'] ?> (Stok Tersedia: <?php echo $row_produk['stok'] ?>) </option>
                            <?php } ?>
                          </select>
                        </td>
                        <td><input class="form-control" type="text" name="produk_custom_<?php echo $x; ?>" /></td>
                        <td><input class="form-control" type="text" name="qty_<?php echo $x; ?>" id="id_qty_<?php echo $x; ?>" /></td>
                        <td><input class="form-control" type="text" name="harga_<?php echo $x; ?>" id="id_harga_<?php echo $x; ?>" /></td>
                        <td><input class="form-control" type="text" name="qty_markup_<?php echo $x; ?>" id="id_qty_markup_<?php echo $x; ?>" /></td>
                        <td><input class="form-control" type="text" name="harga_markup_<?php echo $x; ?>" id="id_harga_markup_<?php echo $x; ?>" value="0" /></td>
                      </tr>
                    <?php $x++;
                    } ?>
                  </tbody>
                </table>
              </div>
              <div class="form-actions right">
                <button type="button" class="btn default" onClick="parent.location='index.php?invoice'">Cancel</button>
                <button type="submit" name="submit_save" class="btn blue" <?php if ($row_invoice['status'] == 0) { ?> disabled <?php } ?>><i class="fa fa-check"></i> Save</button>
              </div>
            </form>
            <!-- END FORM-->
          </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
      </div>
    </div>
    <?php
    $sql_suratjalan = mysql_query("SELECT suratjalan.*, administrator.nama AS nama_admin FROM suratjalan LEFT JOIN administrator ON administrator.id = suratjalan.user_id WHERE suratjalan.invoice_id = '$id'") or die(mysql_error());
    ?>
    <div class="row">
      <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box green">
          <div class="portlet-title">
            <div class="caption">
              <i class="fa fa-globe"></i>Surat Jalan List
            </div>
            <div class="actions">
              <div class="btn-group">
                <a class="btn default" href="index.php?suratjalaninvoice-add=<?php echo $id; ?>">
                  Tambah Sesuai Invoice <i class="fa fa-plus"></i>
                </a>
              </div>
              <div class="btn-group">
                <a class="btn default" href="index.php?suratjalan-add=<?php echo $id; ?>">
                  Tambah <i class="fa fa-plus"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="portlet-body">
            <table class="table table-striped table-bordered table-hover" id="sample_4">
              <thead>
                <tr>
                  <th>
                    Nomor
                  </th>
                  <th>
                    Tanggal
                  </th>
                  <th>
                    Note
                  </th>
                  <th>
                    By
                  </th>
                  <th>
                    Action
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysql_num_rows($sql_suratjalan) > 0) { ?>
                  <?php while ($row_suratjalan = mysql_fetch_array($sql_suratjalan)) { ?>
                    <tr>
                      <td>
                        <a href="index.php?suratjalan-view=<?php echo $row_suratjalan['id'] ?>" target="_blank"><?php echo $row_suratjalan['nomor']; ?></a>
                      </td>
                      <td>
                        <?php echo $row_suratjalan['tanggal']; ?>
                      </td>
                      <td>
                        <?php echo $row_suratjalan['note']; ?>
                      </td>
                      <td>
                        <?php echo $row_suratjalan['nama_admin'] ?>
                      </td>
                      <td>
                        <a href="index.php?suratjalan-view=<?php echo $row_suratjalan['id'] ?>" class="btn default btn-xs purple">
                          <i class="fa fa-search"></i></a>
                        <a class="btn default btn-xs red" data-toggle="confirmation" data-original-title="Are you sure ?" data-placement="left" data-href="index.php?suratjalan-delete=<?php echo $row_suratjalan['id'] ?>">
                          <i class="fa fa-trash-o"></i></a>
                      </td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
      </div>
    </div>
    <!-- PRINT NOTA DI BAWAH INI -->
    <?php
    if ($row_invoice['online'] == 2) {
      $sql_datatoko1 = mysql_query("SELECT * FROM administrator WHERE id = '16'") or die(mysql_error());
      $row_datatoko1 = mysql_fetch_array($sql_datatoko1);
    } else {
      $sql_datatoko1 = mysql_query("SELECT * FROM administrator WHERE id = '$administrator_id'") or die(mysql_error());
      $row_datatoko1 = mysql_fetch_array($sql_datatoko1);
    }
    $sql_datatoko = mysql_query("SELECT * FROM administrator WHERE id = '$administrator_id'") or die(mysql_error());
    $row_datatoko = mysql_fetch_array($sql_datatoko);
    ?>
    <?php if ($toko_id == 2 || $toko_id == 5) { ?>
      <div class="row">
        <div class="col-md-12" style="visibility:hidden">
          <!-- BEGIN EXAMPLE TABLE PORTLET-->
          <div class="portlet-body" id="areaall">
            <table style="font-size:11px; <?php if ($row_invoice['status'] == 2) { ?>color:#42b549;<?php } ?>" class="table" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko1['logokop']; ?>
                  <p><?php echo $row_datatoko1['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /><?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
                <?php if ($toko_id == 5) { ?>
                  <td colspan="3">
                    <p>NO FAKTUR PAJAK : <?php echo $row_invoice['nofp'] ?></p>
                  </td>
                <?php } ?>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($toko_id == 5) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">PPN 10%</td>
                    <td style="font-weight:bold"><?php $ppn = $total_all * 0.1;
                                                  echo 'Rp. ' . number_format(($ppn), 0, ',', '.');  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">TOTAL</td>
                    <td style="font-weight:bold"><?php $totaltotal = $total_all + $ppn;
                                                  echo 'Rp. ' . number_format(($totaltotal), 0, ',', '.');  ?></td>
                  </tr>
                <?php } ?>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" width="200px" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="4">
                    <p style="margin-top:30px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                  <?php if ($row_invoice['capttd'] == 2) { ?>
                    <td style="border-top: none;" colspan="4">
                      <p style="margin-top:-55px; margin-left: -10px;"><img src="assets/images/capttd2.jpg" alt="" /></p>
                    </td>
                  <?php } ?>
                </tr>
                <tr>
                  <td style="border-top: none" colspan="3">
                    <p style="margin-top:30px; font-size: 10px">BLACK: ORIGINAL</p>
                  </td>
                </tr>
            </table>
            <table style="font-size:11px; color:#CC0000;" class="table pb" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko['logokop']; ?>
                  <p><?php echo $row_datatoko['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
                <?php if ($toko_id == 5) { ?>
                  <td colspan="3">
                    <p>NO FAKTUR PAJAK : <?php echo $row_invoice['nofp'] ?></p>
                  </td>
                <?php } ?>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($toko_id == 5) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">PPN 10%</td>
                    <td style="font-weight:bold"><?php $ppn = $total_all * 0.1;
                                                  echo 'Rp. ' . number_format(($ppn), 0, ',', '.');  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">TOTAL</td>
                    <td style="font-weight:bold"><?php $totaltotal = $total_all + $ppn;
                                                  echo 'Rp. ' . number_format(($totaltotal), 0, ',', '.');  ?></td>
                  </tr>
                <?php } ?>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="5">
                    <p style="margin-top:30px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none" colspan="3">
                    <p style="margin-top:30px; font-size: 10px">RED: COPY 1</p>
                  </td>
                </tr>
            </table>
            <table style="font-size:11px; color:#1875D7" class="table pb" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko['logokop']; ?>
                  <p><?php echo $row_datatoko['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
                <?php if ($toko_id == 5) { ?>
                  <td colspan="3">
                    <p>NO FAKTUR PAJAK : <?php echo $row_invoice['nofp'] ?></p>
                  </td>
                <?php } ?>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($toko_id == 5) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">PPN 10%</td>
                    <td style="font-weight:bold"><?php $ppn = $total_all * 0.1;
                                                  echo 'Rp. ' . number_format(($ppn), 0, ',', '.');  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">TOTAL</td>
                    <td style="font-weight:bold"><?php $totaltotal = $total_all + $ppn;
                                                  echo 'Rp. ' . number_format(($totaltotal), 0, ',', '.');  ?></td>
                  </tr>
                <?php } ?>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="5">
                    <p style="margin-top:30px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none" colspan="3">
                    <p style="margin-top:30px; font-size: 10px">BLUE: COPY 2</p>
                  </td>
                </tr>
            </table>
          </div>
        </div>
      </div>
    <?php } else { ?>
      <div class="row">
        <div class="col-md-12" style="visibility:hidden">
          <!-- BEGIN EXAMPLE TABLE PORTLET-->
          <div class="portlet-body" id="areaasli">
            <div class="divFooter">REKAP NOTA ASLI</div>
            <table style="font-size:11px; <?php if ($row_invoice['status'] == 2) { ?>color:#42b549;<?php } ?>" class="table" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko1['logokop']; ?>
                  <p><?php echo $row_datatoko1['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="5">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <?php if ($toko_id == 5) { ?>
                    <td style="font-weight:bold">SUBTOTAL</td>
                  <?php } else { ?>
                    <td style="font-weight:bold">TOTAL</td>
                  <?php } ?>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($toko_id == 5) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">PPN 10%</td>
                    <td style="font-weight:bold"><?php $ppn = $total_all * 0.1;
                                                  echo 'Rp. ' . number_format(($ppn), 0, ',', '.');  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">TOTAL</td>
                    <td style="font-weight:bold"><?php $totaltotal = $total_all + $ppn;
                                                  echo 'Rp. ' . number_format(($totaltotal), 0, ',', '.');  ?></td>
                  </tr>
                <?php } ?>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" width="200px" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="4">
                    <p style="margin-top:50px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                  <?php if ($row_invoice['capttd'] == 2) { ?>
                    <td style="border-top: none;" colspan="4">
                      <p style="margin-top:-55px; margin-left: -10px;"><img src="assets/images/capttd.jpg" alt="" /></p>
                    </td>
                  <?php } ?>
                </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12" style="visibility:hidden">
          <!-- BEGIN EXAMPLE TABLE PORTLET-->
          <div class="portlet-body" id="areacopy">
            <div class="divFooter">REKAP NOTA COPY</div>
            <table style="font-size:11px; color:#CC0000;" class="table pb" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko['logokop']; ?>
                  <p><?php echo $row_datatoko['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="5">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="5">
                    <p style="margin-top:50px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                </tr>
            </table>
            <table style="font-size:11px; color:#1875D7" class="table pb" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko['logokop']; ?>
                  <p><?php echo $row_datatoko['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="5">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga'] * $row_listproduk['quantity'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $row_invoice['total'] - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="5">
                    <p style="margin-top:50px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                </tr>
            </table>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php if ($row_invoice['markup'] == 2) { ?>
      <div class="row">
        <div class="col-md-12" style="visibility:hidden">
          <!-- BEGIN EXAMPLE TABLE PORTLET-->
          <div class="portlet-body" id="areamarkup">
            <table style="font-size:11px;" class="table" width="100%">
              <tr>
                <td colspan="3" style="border-top:none !important">
                  <?php echo $row_datatoko1['logokop']; ?>
                  <p><?php echo $row_datatoko1['kop'] ?></p>
                </td>
                <td colspan="2" style="text-align:right; border-top:none;">
                  <p style="margin-top:50px">Jakarta, <?php echo $row_invoice['tanggal'] ?>,<br /> <?php echo $row_invoice['nama'] ?><br /><?php echo $row_invoice['perusahaan'] ?><br /><?php echo $row_invoice['alamat'] ?><br /><?php echo $row_invoice['telepon'] ?><br /><?php if ($toko_id == 5) { ?>NPWP: <?php echo $row_invoice['npwp']; ?><?php } ?></p>
                </td>
              </tr>
              <tr>
                <td colspan="5">
                  <p>INVOICE NO : <?php echo $row_invoice['nomor'] ?></p>
                </td>
              </tr>
              <tr>
                <td width="15%">No</td>
                <td width="35%">Produk</td>
                <td width="5%">Qty</td>
                <td width="20%">Harga</td>
                <td width="25%">Total</td>
              </tr>
              <tbody>
                <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'") or die(mysql_error()); ?>
                <?php $total_arr = array();
                $totalmarkup_arr = array();
                $x = 1;
                while ($row_listproduk = mysql_fetch_array($sql_listproduk)) { ?>
                  <tr>
                    <td><?php echo $x; ?></td>
                    <td>
                      <?php
                      echo $row_listproduk['product_name'];
                      ?>
                    </td>
                    <td><?php echo $row_listproduk['quantity_markup']; ?></td>
                    <td><?php echo 'Rp. ' . number_format(($row_listproduk['harga_markup']), 0, ',', '.');  ?></td>
                    <td>
                      <?php
                      $total = $row_listproduk['harga_markup'] * $row_listproduk['quantity_markup'];
                      $total_arr[] = $total;
                      echo 'Rp. ' . number_format(($total), 0, ',', '.');
                      ?>
                    </td>
                  </tr>
                <?php $x++;
                } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td style="font-weight:bold">TOTAL</td>
                  <td style="font-weight:bold"><?php $total_all = array_sum($total_arr);
                                                echo 'Rp. ' . number_format(($total_all), 0, ',', '.');  ?></td>
                </tr>
                <?php if ($row_invoice['pembayaran'] != 0) { ?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">DP</td>
                    <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice['pembayaran']), 0, ',', '.') . ' (' . date("d-m-Y", strtotime($row_invoice['tanggaldp'])) . ')';  ?></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="font-weight:bold">SISA BAYAR</td>
                    <td style="font-weight:bold"><?php $sisabayar = $total_all - $row_invoice['pembayaran'];
                                                  echo 'Rp. ' . number_format(($sisabayar), 0, ',', '.');  ?> <?php if ($row_invoice['status'] == 1) { ?>(LUNAS)<?php } ?><?php if ($row_invoice['status'] == 2) { ?>(BELUM LUNAS)<?php } ?></td>
                  </tr>
                <?php } ?>
                <tr>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold;">Tanda Terima,</p>
                  </td>
                  <td colspan="3" style="border-bottom:none">
                    <p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p>
                  </td>
                  <td colspan="1" style="border-bottom:none">
                    <p style="font-weight:bold">Hormat Kami,</p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top: none;" colspan="5">
                    <p style="margin-top:50px; font-weight: bold"><?php echo $row_datatoko1['rekening']; ?></p>
                  </td>
                </tr>
            </table>
          </div>
        </div>
      </div>
    <?php } ?>
    <!-- END PAGE CONTENT-->
  </div>
</div>