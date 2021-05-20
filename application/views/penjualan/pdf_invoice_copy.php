  <html>

  <head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
      .logo-invoice{
        max-height: 100px;
      }
      .kopsurat{
        margin-top:20px;
      }
      .no-invoice{
        margin: 40px 0 30px 0;
      }
      .kopsurat p{
        margin-bottom: 0 !important;
        font-size: 22px;
      }
      .brg-penjualan, .brg-penjualan td, .brg-penjualan th{
        border: 1px solid #474747;
        padding: 5px;
        font-size: 20px;
      }
      .final-total, .final-total-value{
        font-weight: bold;
      }
      .brg-penjualan th{
        text-align: center;
      }
      .nomor, .qty{
        text-align: center;
      }
      .harga-satuan, .final-total{
        text-align: right;
      }
      .perhatian{
        margin-top: 20px;
        text-align: center;
      }
      .ttd{
        margin-top: 20px;
        font-size: 21px;
      }
      .ttd-img{
        max-height: 150px;
      }
      .no-invoice{
        font-size: 22px;
        margin-bottom: 30px;
      }
      .customerr{
        text-align: right;
      }
      @media print {
          .pagebreak { page-break-before: always; } /* page-break-after works, as well */
      }


      .merah{
        color: #990f02 !important;
      }
      .merah .brg-penjualan, .merah .brg-penjualan td, .merah .brg-penjualan th{
        border: 1px solid #990f02 !important;
        color: #990f02;
      }

      .biru{
        color: #1338be !important;
      }
      .biru .brg-penjualan, .biru .brg-penjualan td, .biru .brg-penjualan th{
        border: 1px solid #1338be !important;
        color: #1338be;
      }
    </style>
  </head>
  <div id="area">
    <div class="merah">
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
        <p>Jakarta, <?php echo date("d-m-Y",strtotime($penjualan[0]['penj_tgl'])) ?></p>
        
        <p><?php echo $customer[0]['cust_suff'] ?> <?php echo $customer[0]['cust_name'] ?></p>
        
        <p><?php echo $customer[0]['cust_perusahaan'] ?></p>
        
        <p><?php echo $customer[0]['cust_alamat'] ?></p>
      </div>
    </div>

    

    <div class="no-invoice">
      <hr>
      <p>INVOICE NO : &nbsp;&nbsp; <b><?php echo $penjualan[0]['penj_nomor'] ?></b></p>
      <hr>
    </div>

    
      <table class="brg-penjualan">
        <tr>
          <th style="width:2%">No</th>
          <th style="width:20%">Produk</th>
          <th style="width:5%">Qty</th>
          <th style="width:8%">Harga Satuan</th>
          <th style="width:10%">Total</th>
        </tr>
        <?php 
        $total=0;
        for($x=0; $x<count($brg_penjualan); $x++){ ?>
        <tr>
          <td class="nomor"><?= $x+1 ?></td>
          <td class="poduk"><?= $brg_penjualan[$x]['brg_nama'] ?></td>
          <td class="qty"><?= number_format($brg_penjualan[$x]['brg_penjualan_qty']) ?> <?= $brg_penjualan[$x]['brg_penjualan_satuan'] ?></td>
          <td class="harga-satuan">Rp <?= number_format($brg_penjualan[$x]['brg_penjualan_harga']) ?></td>
          <td class="total">Rp <?= number_format($brg_penjualan[$x]['brg_penjualan_qty']*$brg_penjualan[$x]['brg_penjualan_harga']) ?></td>
        </tr>
        <?php 
      
          $total=$total+$brg_penjualan[$x]['brg_penjualan_qty']*$brg_penjualan[$x]['brg_penjualan_harga'];
          } ?>
        <tr>
          <td class="final-total" colspan="4">TOTAL &nbsp;&nbsp;</td>
          <td class="final-total-value">Rp <?= number_format($total) ?></td>
        </tr>
      </table>

      <div class="row perhatian">
        <div class="col-12">
          <h5>PERHATIAN !!!<br>Barang-barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</h5>
        </div>
      </div>

      <div class="row d-flex flex-row justify-content-between ttd">
          <div class="col-4">
            <p>Tanda Terima,</p>
            <br><br><br><br><br><br>
            <p>BCA : 7570706025
<br>MANDIRI : 115.000.2510255
<br>A/N : Andre Okto Wijaya</p>
          </div>
          <div class="col-3">
            <p>Hormat Kami,</p>
            <?php if($cap_status=="cap"){ ?>
              <img class="ttd-img" src="<?= base_url()?>asset/uploads/toko/ttd/<?= $toko_cabang[0]['toko_ttd'] ?>">
              <?php } ?>
          </div>
      </div>
    </div>
    



      <div class="pagebreak"></div>




      <div class="biru">
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
        <p>Jakarta, <?php echo date("d-m-Y",strtotime($penjualan[0]['penj_tgl'])) ?></p>
        
        <p><?php echo $customer[0]['cust_suff'] ?> <?php echo $customer[0]['cust_name'] ?></p>
        
        <p><?php echo $customer[0]['cust_perusahaan'] ?></p>
        
        <p><?php echo $customer[0]['cust_alamat'] ?></p>
      </div>
    </div>

    

    <div class="no-invoice">
      <hr>
      <p>INVOICE NO : &nbsp;&nbsp; <b><?php echo $penjualan[0]['penj_nomor'] ?></b></p>
      <hr>
    </div>

    
      <table class="brg-penjualan">
        <tr>
          <th style="width:2%">No</th>
          <th style="width:20%">Produk</th>
          <th style="width:5%">Qty</th>
          <th style="width:8%">Harga Satuan</th>
          <th style="width:10%">Total</th>
        </tr>
        <?php 
        $total=0;
        for($x=0; $x<count($brg_penjualan); $x++){ ?>
        <tr>
          <td class="nomor"><?= $x+1 ?></td>
          <td class="poduk"><?= $brg_penjualan[$x]['brg_nama'] ?></td>
          <td class="qty"><?= number_format($brg_penjualan[$x]['brg_penjualan_qty']) ?> <?= $brg_penjualan[$x]['brg_penjualan_satuan'] ?></td>
          <td class="harga-satuan">Rp <?= number_format($brg_penjualan[$x]['brg_penjualan_harga']) ?></td>
          <td class="total">Rp <?= number_format($brg_penjualan[$x]['brg_penjualan_qty']*$brg_penjualan[$x]['brg_penjualan_harga']) ?></td>
        </tr>
        <?php 
      
          $total=$total+$brg_penjualan[$x]['brg_penjualan_qty']*$brg_penjualan[$x]['brg_penjualan_harga'];
          } ?>
        <tr>
          <td class="final-total" colspan="4">TOTAL &nbsp;&nbsp;</td>
          <td class="final-total-value">Rp <?= number_format($total) ?></td>
        </tr>
      </table>

      <div class="row perhatian">
        <div class="col-12">
          <h5>PERHATIAN !!!<br>Barang-barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</h5>
        </div>
      </div>

      <div class="row d-flex flex-row justify-content-between ttd">
          <div class="col-4">
            <p>Tanda Terima,</p>
            <br><br><br><br><br><br>
            <p>BCA : 7570706025
<br>MANDIRI : 115.000.2510255
<br>A/N : Andre Okto Wijaya</p>
          </div>
          <div class="col-3">
            <p>Hormat Kami,</p>
            <?php if($cap_status=="cap"){ ?>
              <img class="ttd-img" src="<?= base_url()?>asset/uploads/toko/ttd/<?= $toko_cabang[0]['toko_ttd'] ?>">
              <?php } ?>
          </div>
      </div>
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