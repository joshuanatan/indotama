  <html>

  <head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
      .logo-invoice {
        max-height: 100px;
      }

      .kopsurat {
        margin-top: 20px;
      }

      .no-invoice {
        margin: 40px 0 30px 0;
      }

      .kopsurat p {
        margin-bottom: 0 !important;
        font-size: 22px;
      }

      .brg-penjualan,
      .brg-penjualan td,
      .brg-penjualan th {
        border: 1px solid #474747;
        padding: 5px;
        font-size: 20px;
      }

      .final-total,
      .final-total-value {
        font-weight: bold;
      }

      .brg-penjualan th {
        text-align: center;
      }

      .nomor,
      .qty {
        text-align: center;
      }

      .harga-satuan,
      .final-total {
        text-align: right;
      }

      .perhatian {
        margin-top: 20px;
        text-align: center;
      }

      .ttd {
        margin-top: 20px;
        font-size: 21px;
      }

      .ttd-img {
        max-height: 150px;
      }

      .no-invoice {
        font-size: 22px;
        margin-bottom: 30px;
      }

      .customerr {
        text-align: right;
      }
    </style>
  </head>
  <div id="area">
    <div class="row logo">
      <img class="logo-invoice" src="<?php echo base_url() ?>asset/uploads/toko/logo/<?php echo $toko_cabang[0]['toko_logo'] ?>" />
    </div>
    <div class="row d-flex flex-row justify-content-between kopsurat">
      <div class="col-6">
        <p><?php echo $toko_cabang[0]['cabang_alamat'] ?></p>
        <p>Phone: <?php echo $toko_cabang[0]['cabang_notelp'] ?></p>
        <p>Website: www.pusatsafety.com</p>
        <p>Email: order@pusatsafety.com</p>
      </div>
      <div class="col-6 customerr">
        <p>Jakarta, <?php echo date("d-m-Y", strtotime(explode(" ", $penawaran[0]['penawaran_tgl'])[0])) ?></p>

        <p><?php echo $customer[0]['cust_perusahaan'] ?></p>

        <p><?php echo $customer[0]['cust_alamat'] ?></p>
      </div>
    </div>



    <div class="no-invoice">
      <hr>
      <p>Penawaran No : &nbsp;&nbsp; <b><?php echo $penawaran[0]['penawaran_no'] ?></b></p>
      <hr>
    </div>

    <div class="details">
      <h4><?php echo $penawaran[0]['penawaran_subject'] ?></h4>
      <br/><br/>
      <p style = "font-size:20px"><?php echo nl2br($penawaran[0]['penawaran_content']) ?></b></p>
    </div>
    <br /><br />
    <table class="brg-penjualan">
      <tr>
        <th style="width:2%">No</th>
        <th style="width:20%">Produk</th>
        <th style="width:5%">Qty</th>
        <th style="width:8%">Harga Satuan</th>
        <th style="width:10%">Total</th>
      </tr>
      <?php
      $total = 0;
      for ($x = 0; $x < count($brg_penawaran); $x++) { ?>
        <tr>
          <td class="nomor"><?= $x + 1 ?></td>
          <td class="poduk"><?= $brg_penawaran[$x]['brg_nama'] ?></td>
          <td class="qty"><?= number_format($brg_penawaran[$x]['brg_penawaran_qty']) ?> <?= $brg_penawaran[$x]['brg_penawaran_satuan'] ?></td>
          <td class="harga-satuan">Rp <?= number_format($brg_penawaran[$x]['brg_penawaran_price']) ?></td>
          <td class="total">Rp <?= number_format($brg_penawaran[$x]['brg_penawaran_qty'] * $brg_penawaran[$x]['brg_penawaran_price']) ?></td>
        </tr>
      <?php

        $total = $total + $brg_penawaran[$x]['brg_penawaran_qty'] * $brg_penawaran[$x]['brg_penawaran_price'];
      } ?>
      <tr>
        <td class="final-total" colspan="4">TOTAL &nbsp;&nbsp;</td>
        <td class="final-total-value">Rp <?= number_format($total) ?></td>
      </tr>
    </table>
    <br/><br/>
    <div class="notes">
      <h5>Catatan:</h5>
      <p style = "font-size:20px"><?php echo nl2br($penawaran[0]['penawaran_notes']) ?></p>
    </div>
  </div>

  </html>
  <script>
    printDiv('area');

    function printDiv(divName) {
      var printContents = document.getElementById(divName).innerHTML;
      var originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
    }
  </script>