<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = (int)mysql_real_escape_string(trim($_GET["penawaran-view"]));
  $tanggal = mysql_real_escape_string(strip_tags(trim($_POST["tanggal"])));
  $customer = (int)mysql_real_escape_string(strip_tags(trim($_POST["customer"])));
  $note = mysql_real_escape_string($_POST["note"]);
  $perihal = mysql_real_escape_string($_POST["perihal"]);
  $paragraf = mysql_real_escape_string($_POST["paragraf"]);

  mysql_query("
    UPDATE penawaran
    SET tanggal = '$tanggal', cust_id = '$customer', perihal = '$perihal', note = '$note', paragraf = '$paragraf'
    WHERE id = '$id';
    ") or die(mysql_error());

  $x = 0;
  while ($x < 5) {
    $x++;

    $pricelist = mysql_real_escape_string(strip_tags(trim($_POST["pricelist_" . $x])));
    $harga = mysql_real_escape_string(strip_tags(trim($_POST["harga_" . $x])));
    $satuan = $_POST["satuan_" . $x];

    if ($pricelist == 0) {

      $nama = $_POST["nama_" . $x];
      $merk = $_POST["merk_" . $x];
      $tipe = $_POST["tipe_" . $x];
      $deskripsi = $_POST["deskripsi_" . $x];

      if ($nama == '' && $merk == '') {
        continue;
      } else {
        mysql_query("INSERT INTO penawaran_detail VALUES ('', '$id', '0', '$nama', '$merk', '$tipe', '$satuan', '$harga', '$deskripsi')") or die(mysql_error());
      }
    } else {

      mysql_query("INSERT INTO penawaran_detail VALUES ('', '$id', '$pricelist', '-', '-', '-', '$satuan', '$harga', '-')") or die(mysql_error());
    }
  }

  $notifikasi = 'success';
}

if (isset($_GET["penawaran_detail-delete"])) {
  $idpenawarandetail = (int)mysql_real_escape_string(trim($_GET["penawaran_detail-delete"]));

  $sql_idpenawaran = mysql_query("SELECT * FROM penawaran_detail WHERE id = '$idpenawarandetail'") or die(mysql_error());
  $row_idpenawaran = mysql_fetch_array($sql_idpenawaran);
  $idpenawaran = $row_idpenawaran['penawaran_id'];

  mysql_query("DELETE FROM penawaran_detail WHERE id = '$idpenawarandetail'") or die(mysql_error());

  header("location:index.php?penawaran-view=" . $idpenawaran);
}

$id = (int)mysql_real_escape_string(trim($_GET["penawaran-view"]));
$sql_penawaran = mysql_query("SELECT penawaran.*, customer.nama AS namacust, customer.perusahaan FROM penawaran LEFT JOIN customer ON customer.id = penawaran.cust_id WHERE penawaran.id = '$id'") or die(mysql_error());
$row_penawaran = mysql_fetch_array($sql_penawaran);
?>
<div class="page-content-wrapper">
  <div class="page-content">
    <!-- BEGIN PAGE HEADER-->
    <h3 class="page-title">
      Penawaran
    </h3>
    <!-- END PAGE HEADER-->
    <!-- BEGIN PAGE CONTENT-->
    <div class="row">
      <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue-hoki">
          <div class="portlet-title">
            <div class="caption">
              <i class="fa fa-plus"></i>View Penawaran <?php echo $row_penawaran['nomor']; ?>
            </div>
            <div class="actions">
              <div class="btn-group">
                <button class="btn red" onclick="printDiv('area')"><i class="fa fa-print"></i> Print</button>
              </div>
              <div class="btn-group">
                <a class="btn default" href="index.php?penawaran">
                  Back <i class="fa fa-arrow-left"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="portlet-body form">
            <!-- BEGIN FORM-->
            <form action="" id="form_sample_1" class="horizontal-form" method="post">
              <div class="form-body">
                <?php if (isset($notifikasi)) { ?>
                  <?php if ($notifikasi == 'success') { ?>
                    <div class="alert alert-success display">
                      <button class="close" data-close="alert"></button>
                      <span>
                        Data Anda Telah Ter-update! </span>
                    </div>
                  <?php } ?>
                <?php } ?>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php $sql_customer = mysql_query("SELECT * FROM customer WHERE toko = '$toko_id' ORDER BY nama DESC") or die(mysql_error()); ?>
                      <label class="control-label">Customer (<a href="index.php?customer-add" target="_blank">Tambah Cepat</a>)
                      </label>
                      <select class="form-control select2me" name="customer">
                        <option value="">Select...</option>
                        <?php while ($row_customer = mysql_fetch_array($sql_customer)) { ?>
                          <option <?php if ($row_penawaran['cust_id'] == $row_customer['id']) { ?>selected<?php } ?> value="<?php echo $row_customer['id'] ?>"> <?php echo $row_customer['nama'] ?> (<?php echo $row_customer['perusahaan']; ?>) </option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Tanggal</label>
                      <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                        <input type="text" class="form-control" value="<?php echo $row_penawaran['tanggal']; ?>" name="tanggal" required>
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
                      <label class="control-label">Perihal</label>
                      <input type="text" name="perihal" value="<?php echo $row_penawaran['perihal']; ?>" class="form-control" required="required">
                    </div>
                  </div>
                  <!--/span-->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Catatan</label>
                      <textarea class="form-control" rows="4" name="note"><?php echo $row_penawaran['note']; ?></textarea>
                    </div>
                  </div>
                  <!--/span-->
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="control-label">Paragraf Custom (Isi dengan "-" untuk default)</label>
                      <textarea class="form-control wysihtml5" rows="5" name="paragraf"><?php echo $row_penawaran['paragraf'] ?></textarea>
                    </div>
                  </div>
                  <!--/span-->
                </div>
                <h3 class="form-section">Item Penawaran</h3>
                <table class="table">
                  <tr>
                    <td width="5%">No</td>
                    <td width="10%">Nama</td>
                    <td width="10%">Merk</td>
                    <td width="10%">Tipe</td>
                    <td width="10%">Satuan</td>
                    <td width="15%">Harga Satuan</td>
                    <td width="40%">Keterangan</td>
                    <td></td>
                  </tr>
                  <tbody>
                    <?php $sql_penawaran_detail = mysql_query("SELECT penawaran_detail.*, pricelist_item.nama AS namapl, pricelist_item.merk AS merkpl, pricelist_item.tipe AS tipepl, pricelist_item.satuan AS satuanpl, pricelist_item.harga1, pricelist_item.harga2, pricelist_item.deskripsi AS deskripsipl FROM penawaran_detail LEFT JOIN pricelist_item ON pricelist_item.id = penawaran_detail.pricelist_id WHERE penawaran_id = '$id'") or die(mysql_error()); ?>
                    <?php $x = 1;
                    while ($row_penawaran_detail = mysql_fetch_array($sql_penawaran_detail)) { ?>
                      <tr>
                        <td><?php echo $x; ?></td>
                        <?php if ($row_penawaran_detail['pricelist_id'] == 0) { ?>
                          <td>
                            <?php
                            echo $row_penawaran_detail['nama'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['merk'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['tipe'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['satuan'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo 'Rp. ' . number_format(($row_penawaran_detail['harga']), 0, ',', '.');
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['deskripsi'];
                            ?>
                          </td>
                        <?php } else { ?>
                          <td>
                            <?php
                            echo $row_penawaran_detail['namapl'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['merkpl'];
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['tipepl'];
                            ?>
                          </td>
                          <td>
                            <?php
                            if ($row_penawaran_detail['satuan'] != '') {
                              echo $row_penawaran_detail['satuan'];
                            } else {
                              echo $row_penawaran_detail['satuanpl'];
                            }
                            ?>
                          </td>
                          <td>
                            <?php
                            if ($row_penawaran_detail['harga'] != '0') {
                              echo 'Rp. ' . number_format(($row_penawaran_detail['harga']), 0, ',', '.');
                            } else {
                              if ($toko_id == 1) {
                                echo 'Rp. ' . number_format(($row_penawaran_detail['harga1']), 0, ',', '.');
                              } elseif ($toko_id == 2) {
                                echo 'Rp. ' . number_format(($row_penawaran_detail['harga2']), 0, ',', '.');
                              }
                            }
                            ?>
                          </td>
                          <td>
                            <?php
                            echo $row_penawaran_detail['deskripsipl'];
                            ?>
                          </td>
                        <?php } ?>
                        <td>
                          <a class="btn default btn-xs red" data-toggle="confirmation" data-original-title="Are you sure ?" data-placement="left" data-href="index.php?penawaran_detail-delete=<?php echo $row_penawaran_detail['id'] ?>">
                            <i class="fa fa-trash-o"></i></a>
                        </td>
                      </tr>
                    <?php $x++;
                    } ?>
                  </tbody>
                </table>
                <h3 class="form-section">Tambah Item Penawaran</h3>
                <table class="table">
                  <tr>
                    <td width="10%">No</td>
                    <td width="20%">Nama</td>
                    <td width="20%">Merk</td>
                    <td width="15%">Tipe</td>
                    <td width="15%">Satuan</td>
                    <td width="20%">Harga Satuan</td>
                  </tr>
                  <tbody>
                    <?php $x = 1;
                    while ($x < 6) { ?>
                      <tr>
                        <td><?php echo $x; ?></td>
                        <td colspan="2">
                          <?php $sql_pricelist = mysql_query("SELECT * FROM pricelist_item ORDER BY nama ASC") or die(mysql_error()); ?>
                          <select class="form-control select2me" name="pricelist_<?php echo $x; ?>">
                            <option value="0">Select...</option>
                            <?php while ($row_pricelist = mysql_fetch_array($sql_pricelist)) { ?>
                              <option value="<?php echo $row_pricelist['id'] ?>"> <?php echo $row_pricelist['nama'] ?> <?php echo $row_pricelist['merk'] ?> <?php echo $row_pricelist['tipe'] ?> Harga: <?php echo $row_pricelist['harga1'] ?></option>
                            <?php } ?>
                          </select>
                        </td>
                        <td colspan="2"></td>
                      </tr>
                      <tr>
                        <td style="border-top: 0px !important"></td>
                        <td style="border-top: 0px !important"><input class="form-control" type="text" name="nama_<?php echo $x; ?>" /></td>
                        <td style="border-top: 0px !important"><input class="form-control" type="text" name="merk_<?php echo $x; ?>" /></td>
                        <td style="border-top: 0px !important"><input class="form-control" type="text" name="tipe_<?php echo $x; ?>" /></td>
                        <td style="border-top: 0px !important"><input class="form-control" type="text" name="satuan_<?php echo $x; ?>" /></td>
                        <td style="border-top: 0px !important"><input class="form-control" type="number" name="harga_<?php echo $x; ?>" id="harga_<?php echo $x; ?>" /></td>
                      </tr>
                      <tr>
                        <td style="border-top: 0px !important"></td>
                        <td colspan="4" style="border-top: 0px !important"><textarea class="form-control wysihtml5" rows="4" name="deskripsi_<?php echo $x; ?>"></textarea></td>
                      </tr>
                    <?php $x++;
                    } ?>
                  </tbody>
                </table>
              </div>
              <div class="form-actions right">
                <button type="button" class="btn default" onClick="parent.location='index.php?penawaran'">Cancel</button>
                <button type="submit" class="btn blue"><i class="fa fa-check"></i> Save</button>
              </div>
            </form>
            <!-- END FORM-->
          </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
      </div>
    </div>
    <!-- END PAGE CONTENT-->
    <div class="row" id="area" style="visibility: hidden">
      <div><img style="position: absolute;" src="assets/images/mmbg.jpg" /></div>
      <div class="col-md-12">
        <p><br /><br /><br /><br /><br /></p>
        <table class="table">
          <tr>
            <td style="border-top: none" width="80%"></td>
            <td style="border-top: none" width="20%">Jakarta, <?php echo date('d-m-Y'); ?></td>
          </tr>
        </table>
        <p>Nomor&emsp;:&emsp;<?php echo $row_penawaran['nomor']; ?><br />Perihal&emsp;:&emsp;<?php echo $row_penawaran['perihal']; ?></p>
        <p>Kepada,<br />Yth. <?php echo $row_penawaran['namacust'] ?><?php if ($row_penawaran['perusahaan'] != '') {
                                                                        echo '<br />' . $row_penawaran['perusahaan'];
                                                                      } ?></p><br />
        <p>&emsp;Berikut kami lampirkan penawaran harga untuk produk yang Bapak / Ibu minta: </p><br />
        <table class="table">
          <tr>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="5%">No</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="15%">Nama</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Merk</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Tipe</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Satuan</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Harga Satuan</td>
            <td style="border-top: 1px solid #000; font-weight: bold;" width="40%">Keterangan</td>
          </tr>
          <?php $sql_penawaran_detail = mysql_query("SELECT penawaran_detail.*, pricelist_item.nama AS namapl, pricelist_item.merk AS merkpl, pricelist_item.tipe AS tipepl, pricelist_item.satuan AS satuanpl, pricelist_item.harga1, pricelist_item.harga2, pricelist_item.deskripsi AS deskripsipl FROM penawaran_detail LEFT JOIN pricelist_item ON pricelist_item.id = penawaran_detail.pricelist_id WHERE penawaran_id = '$id'") or die(mysql_error()); ?>
          <?php $x = 1;
          while ($row_penawaran_detail = mysql_fetch_array($sql_penawaran_detail)) { ?>
            <tr st>
              <td style="border-top: 1px solid #000;"><?php echo $x; ?></td>
              <?php if ($row_penawaran_detail['pricelist_id'] == 0) { ?>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['nama'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['merk'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['tipe'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['satuan'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo 'Rp. ' . number_format(($row_penawaran_detail['harga']), 0, ',', '.');
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php echo $row_penawaran_detail['deskripsi'] ?>
                </td>
              <?php } else { ?>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['namapl'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['merkpl'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  echo $row_penawaran_detail['tipepl'];
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  if ($row_penawaran_detail['satuan'] != '') {
                    echo $row_penawaran_detail['satuan'];
                  } else {
                    echo $row_penawaran_detail['satuanpl'];
                  }
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php
                  if ($row_penawaran_detail['harga'] != '0') {
                    echo 'Rp. ' . number_format(($row_penawaran_detail['harga']), 0, ',', '.');
                  } else {
                    if ($toko_id == 1) {
                      echo 'Rp. ' . number_format(($row_penawaran_detail['harga1']), 0, ',', '.');
                    } elseif ($toko_id == 2) {
                      echo 'Rp. ' . number_format(($row_penawaran_detail['harga2']), 0, ',', '.');
                    }
                  }
                  ?>
                </td>
                <td style="border-top: 1px solid #000;">
                  <?php echo $row_penawaran_detail['deskripsipl'] ?>
                </td>
              <?php } ?>
            </tr>
          <?php $x++;
          } ?>
          <tr>
            <td style="border-top: 1px solid #000;" colspan="7"></td>
          </tr>
        </table>
        <h3>Ketentuan</h3>
        <?php if ($row_penawaran['paragraf'] == '-') { ?>
          <ul>
            <li>Harga di atas tidak termasuk biaya pengiriman.</li>
            <li>DP minimal 50% untuk proses pengumpulan barang.</li>
            <li>Barang yang tidak ready dibutuhkan waktu pembuatan maksimal 14 hari kerja terhitung dari tanggal DP / pembayaran.</li>
            <li>Setelah barang selesai disiapkan / dibuat, akan kami informasikan agar dapat segera di ambil.</li>
          </ul>
        <?php } else { ?>
          <?php echo $row_penawaran['paragraf']; ?>
        <?php } ?>
        <p>&emsp;Demikian penawaran harga dari kami. Untuk pertanyaan lebih lanjut, dapat hubungi kami. Terima Kasih.<br /><br /></p>
        <table class="table">
          <tr style="text-align: left;">
            <td style="border-top: none" width="75%"></td>
            <td style="border-top: none" width="25%">&emsp;Hormat Kami,<br /><img src="assets/images/capttd.jpg" /></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>