  <html>

  <head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <div class="row" id="area">
    <div><img style="position: absolute;" src="assets/images/mmbg.jpg" /></div>
    <div class="col-md-12">
      <p><br /><br /><br /><br /><br /></p>
      <table class="table">
        <tr>
          <td style="border-top: none" width="80%"></td>
          <td style="border-top: none" width="20%">Jakarta, <?php echo date('d-m-Y'); ?></td>
        </tr>
      </table>
      <p>Nomor&emsp;:&emsp;1234<br />Perihal&emsp;:&emsp;12345</p>
      <p>Kepada,<br />Yth. 12345<br />1234</p><br />
      <p>&emsp;Berikut kami lampirkan penawaran harga untuk produk yang Bapak / Ibu minta: </p><br />
      <table class="table table-bordered table-striped">
        <tr>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="5%">No</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="15%">Nama</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Merk</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Tipe</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Satuan</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="10%">Harga Satuan</td>
          <td style="border-top: 1px solid #000; font-weight: bold;" width="40%">Keterangan</td>
        </tr>
        <?php for($a = 0; $a<100; $a++):?>
        <tr>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
          <td style="border-top: 1px solid #000;"></td>
        </tr>
        <?php endfor;?>
        <tr>
          <td style="border-top: 1px solid #000;" colspan="7"></td>
        </tr>
      </table>
      <h3>Ketentuan</h3>
      <ul>
        <li>Harga di atas tidak termasuk biaya pengiriman.</li>
        <li>DP minimal 50% untuk proses pengumpulan barang.</li>
        <li>Barang yang tidak ready dibutuhkan waktu pembuatan maksimal 14 hari kerja terhitung dari tanggal DP / pembayaran.</li>
        <li>Setelah barang selesai disiapkan / dibuat, akan kami informasikan agar dapat segera di ambil.</li>
      </ul>
      <p>&emsp;Demikian penawaran harga dari kami. Untuk pertanyaan lebih lanjut, dapat hubungi kami. Terima Kasih.<br /><br /></p>
      <table class="table">
        <tr style="text-align: left;">
          <td style="border-top: none" width="75%"></td>
          <td style="border-top: none" width="25%">&emsp;Hormat Kami,<br /><img src="assets/images/capttd.jpg" /></td>
        </tr>
      </table>
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