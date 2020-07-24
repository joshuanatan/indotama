
<?php
ob_start();
    $pdf = new Pdf_invoice_asli('P', 'mm', 'A4', true, 'UTF-8', false);
    $logo = base_url() . 'asset/uploads/toko/logo/' . $penjualan_main[0]['toko_logo'];

    $pdf->set_logo($logo);
    $pdf->SetTitle('INVOICE');
    $pdf->SetTopMargin(30);
    $pdf->setFooterMargin(20);
    $pdf->SetAutoPageBreak(true,22);
    $pdf->SetAuthor('Author');
    $pdf->SetDisplayMode('real', 'default');
    $pdf->setPrintHeader(true);
      $pdf->setPrintFooter(true);
    $pdf->AddPage('P','A4');
    
    $fontname = TCPDF_FONTS::addTTFfont('../../../libraries/tcpdf/fonts/tahoma.ttf', 'TrueTypeUnicode', '', 96);
    $pdf->SetFont('Tahoma','', 10.5); //untuk font, liat dokumentasui

    $content='
    <head>
        <style>
            .judul{
                text-align:center;
                font-weight: bold;
                line-height:20px;
                font-size: 11pt;
            }
            .tabel{
                border:1px solid black;
            }
            .tabel td{
                border:1px solid black
            }
            
        </style>
    </head>
    <table>
        <tr>
            <td>
                '.$penjualan_main[0]['cabang_nama'].'<br>
                '.$penjualan_main[0]['cabang_alamat'].'<br>
                Phone: '.$penjualan_main[0]['cabang_notelp'].'<br>
            </td>
            <td style="text-align:right">
                Jakarta, '.date("d M Y", strtotime($penjualan_main[0]['penj_tgl'])).'<br>
                '.$penjualan_main[0]['cust_name'].'<br>
                '.$penjualan_main[0]['cust_alamat'].'
                <br><br>
                '.$penjualan_main[0]['cust_telp'].'
            </td>
        </tr>
        <tr>
            <td style="line-height:25px; font-size:13pt"><b>INVOICE NO:  '.$penjualan_main[0]['penj_nomor'].'</b></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <table class="tabel">
            <tr>
                <td class="judul" style="width:30px;">No</td>
                <td class="judul" style="width:250px;">Produk</td>
                <td class="judul" style="width:40px;text-align:center">Qty</td>
                <td class="judul">Harga</td>
                <td class="judul">Total</td>
            </tr>
            ';
            $total=0;
            for($x=0; $x<count($penjualan_brg); $x++){
            $no = $x+1;
            $content=$content . '
            <tr>
                <td style="text-align:center">'.$no.'</td>
                <td>'.$penjualan_brg[$x]['brg_nama'].'</td>    
                <td style="text-align:center">'.$penjualan_brg[$x]['brg_penjualan_qty'].' '.$penjualan_brg[$x]['brg_penjualan_satuan'].'</td> 
                <td style="text-align:right">Rp. '.number_format($penjualan_brg[$x]['brg_penjualan_harga']).'</td>
                <td style="text-align:right">Rp. '.number_format($penjualan_brg[$x]['brg_penjualan_harga']*$penjualan_brg[$x]['brg_penjualan_qty']).'</td>
            </tr>';
                $total = $total + ($penjualan_brg[$x]['brg_penjualan_harga']*$penjualan_brg[$x]['brg_penjualan_qty']);
            }


            $content=$content . '<tr>
                <td colspan="4" style="font-weight:bold; text-align:right;line-height:15px">TOTAL</td>
                <td style="font-weight:bold; text-align:right;line-height:15px">Rp. '.number_format($total).'</td>
            </tr>
        </table>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td style="width:100px;font-weight:bold">Tanda Terima,</td>
                <td style="width:300px; text-align:center;line-height:15pt;font-weight:bold">PERHATIAN!!!<br>
                Barang-barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
                </td>
                <td style="width:130px; text-align:right;font-weight:bold">Hormat Kami,</td>
            </tr>
    </table>
    
    ';
$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya

    //$obj_pdf->SetFont(Courier','', 8); //untuk font, liat dokumentasui
    //$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya
    //$pdf->Write(5, 'Contoh Laporan PDF dengan CodeIgniter + tcpdf');
    $pdf->Output('Invoice'.date("d-m-Y").'.pdf', 'I');
?>