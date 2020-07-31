
<?php
ob_start();
    $pdf = new Pdf_pembelian('P', 'mm', 'A4', true, 'UTF-8', false);
    $logo = base_url() . 'asset/uploads/toko/logo/' . $pembelian_main[0]['toko_logo'];

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
            <td></td>
            <td style="text-align:right;font-weight:bold"> No Faktur Pembelian: '.$pembelian_main[0]['pem_pk_nomor'].'</td>
        </tr>
        <tr>
            <td>
                '.$pembelian_main[0]['cabang_nama'].'<br>
                '.$pembelian_main[0]['cabang_alamat'].'<br>
                Phone: '.$pembelian_main[0]['cabang_notelp'].'<br>
            </td>
            <td style="text-align:right">
                '.$pembelian_main[0]['sup_nama'].' ['.$pembelian_main[0]['sup_perusahaan'].']<br>
                '.$pembelian_main[0]['sup_alamat'].'
                <br><br>
                Telp: '.$pembelian_main[0]['sup_telp'].'
            </td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <table class="tabel">
            <tr>
                <td class="judul" style="width:80px;">Kode Barang</td>
                <td class="judul" style="width:190px;">Nama Barang</td>
                <td class="judul" style="width:40px;text-align:center">Qty</td>
                <td class="judul" style="width:50px;text-align:center">Satuan</td>
                <td class="judul">Harga Satuan</td>
                <td class="judul">Sub Total</td>
            </tr>
            ';
            $total=0;
            for($x=0; $x<count($pembelian_barang); $x++){
            $no = $x+1;
            $content=$content . '
            <tr>
                <td  style="text-align:center" >'.$pembelian_barang[$x]['brg_kode'].'</td>
                <td>'.$pembelian_barang[$x]['brg_nama'].'</td>    
                <td style="text-align:center">'.$pembelian_barang[$x]['brg_pem_qty'].' </td> 
                <td style="text-align:center">'.$pembelian_barang[$x]['brg_pem_satuan'].'</td>
                <td style="text-align:right">Rp. '.number_format($pembelian_barang[$x]['brg_pem_harga']).'</td>
                <td style="text-align:right">Rp. '.number_format($pembelian_barang[$x]['brg_pem_harga']*$pembelian_barang[$x]['brg_pem_qty']).'</td>
            </tr>';
                $total = $total + ($pembelian_barang[$x]['brg_pem_harga']*$pembelian_barang[$x]['brg_pem_qty']);
            }

            if(count($pembelian_brg_tambahan)>0){
                $total2=0;
                for($y=0; $y<count($pembelian_brg_tambahan); $y++){
                    $no++;
                    $content=$content . '
                    <tr>
                        <td style="text-align:center">-</td>
                        <td>'.$pembelian_brg_tambahan[$y]['tmbhn'].'</td>    
                        <td style="text-align:center">'.$pembelian_brg_tambahan[$y]['tmbhn_jumlah'].'</td> 
                        <td style="text-align:center">'.$pembelian_brg_tambahan[$y]['tmbhn_satuan'].'</td> 
                        <td style="text-align:right">Rp. '.number_format($pembelian_brg_tambahan[$y]['tmbhn_harga']).'</td>
                        <td style="text-align:right">Rp. '.number_format($pembelian_brg_tambahan[$y]['tmbhn_harga']*$pembelian_brg_tambahan[$y]['tmbhn_jumlah']).'</td>
                    </tr>';

                    $total2 = $total2 + ($pembelian_brg_tambahan[$y]['tmbhn_harga']*$pembelian_brg_tambahan[$y]['tmbhn_jumlah']);
                }
            }

            $content=$content . '<tr>
                <td colspan="5" style="font-weight:bold; text-align:right;line-height:15px">TOTAL</td>
                <td style="font-weight:bold; text-align:right;line-height:15px">Rp. '.number_format($total+$total2).'</td>
            </tr>
        </table>
            
    </table>
    
    ';

$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya

    //$obj_pdf->SetFont(Courier','', 8); //untuk font, liat dokumentasui
    //$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya
    //$pdf->Write(5, 'Contoh Laporan PDF dengan CodeIgniter + tcpdf');
    $pdf->Output('Invoice'.date("d-m-Y").'.pdf', 'I');
?>