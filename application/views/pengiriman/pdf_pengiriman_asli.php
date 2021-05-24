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
        <p>Email: order@pusatsafety.com</p>
      </div>
      <div class="col-6 customerr">
        <p>Jakarta, <?php echo date("d-m-Y",strtotime($pengiriman[0]['pengiriman_tgl'])) ?></p>
        
        <?php if($jenis_pengiriman != "pengiriman_pemenuhan"){ ?>
        <p><?php echo $customer[0]['cust_suff'] ?> <?php echo $customer[0]['cust_name'] ?></p>
        
        <p><?php echo $customer[0]['cust_perusahaan'] ?></p>
        
        <p><?php echo $customer[0]['cust_alamat'] ?></p>
        <?php }else{ ?>
          <p>CABANG <?php echo $customer_cabang[0]['cabang_nama'] ?></p>
        
          <p><?php echo $customer_cabang[0]['cabang_notelp'] ?></p>
          
          <p><?php echo $customer_cabang[0]['cabang_alamat'] ?></p>
        <?php } ?>
      </div>
    </div>

    

    <div class="no-invoice">
      <hr>
      <p>SURAT JALAN NO : &nbsp;&nbsp; <b><?php echo $pengiriman[0]['pengiriman_no'] ?></b></p>
      <hr>
    </div>

    
      <table class="brg-penjualan">
        <tr>
          <th style="width:2%">No</th>
          <th style="width:20%">Produk</th>
          <th style="width:5%">Qty</th>
        </tr>
        <?php 
        $total=0;
        for($x=0; $x<count($brg_pengiriman); $x++){ ?>
        <tr>
          <td class="nomor"><?= $x+1 ?></td>
          <td class="poduk"><?= $brg_pengiriman[$x]['brg_nama'] ?></td>
          <td class="qty"><?php 
          
          if($jenis_pengiriman=="pengiriman_retur"){
            echo number_format($brg_pengiriman[$x]['retur_brg_qty']) . ' ' . $brg_pengiriman[$x]['retur_brg_satuan'];
          }else if($jenis_pengiriman=="pengiriman_penjualan"){
            echo number_format($brg_pengiriman[$x]['brg_penjualan_qty']) . ' ' . $brg_pengiriman[$x]['brg_penjualan_satuan'];
          }else{
            echo number_format($brg_pengiriman[$x]['brg_pemenuhan_qty']);
          }
          
           ?>
          
          
          </td>
        </tr>
        <?php } ?>
      </table>

      <div class="row d-flex flex-row justify-content-between ttd">
          <div class="col-4">
            <p>Tanda Terima,</p>
            <br><br><br><br><br><br>
          </div>
          <div class="col-3">
            <p>Hormat Kami,</p>
            <br><br><br><br><br><br>
          </div>
      </div>
  </div>

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
</html>