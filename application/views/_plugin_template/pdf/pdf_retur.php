
<?php
ob_start();
    $pdf = new Pdf_retur('P', 'mm', 'A4', true, 'UTF-8', false);
    $logo = base_url() . 'asset/uploads/toko/logo/' . $retur_main[0]['toko_logo'];

    $pdf->set_logo($logo);
    $pdf->SetTitle('RETUR');
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
            <td colspan="5" style="text-align:right;width:260px;font-size:15pt;font-weight:bold">NOTA RETUR</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><b>No. NOTA</b></td>
            <td style="width:330px">: '.$retur_main[0]['retur_no'].'</td>
            <td></td>
            <td style="width:200px; text-aign:right">Jakarta, '.date("j M Y",strtotime($retur_main[0]['retur_tgl'])).'</td>
        </tr>
        <hr>
        
        <tr>
            <td colspan="2" style="font-size:11pt;font-weight:bold"><br><br>Pembeli</td>
            <td></td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>: '.$retur_main[0]['cust_suff'].' '.$retur_main[0]['cust_name'].' ['.$retur_main[0]['cust_perusahaan'].']</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: '.$retur_main[0]['cust_alamat'].'</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size:11pt;font-weight:bold">Kepada Penjual</td>
            <td></td>
        </tr>
        <tr>
            <td>Nama</td>
            <td>: '.$retur_main[0]['cabang_nama'].'</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>: '.$retur_main[0]['cabang_alamat'].'</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <table class="tabel">
            <tr>
                <td class="judul" style="width:30px;">No</td>
                <td class="judul" style="width:400px;">Produk</td>
                <td class="judul" style="width:100px;text-align:center">Qty</td>
            </tr>
            ';
            $total=0;
            for($x=0; $x<count($retur_barang); $x++){
            $no = $x+1;
            $content=$content . '
            <tr>
                <td style="text-align:center">'.$no.'</td>
                <td>'.$retur_barang[$x]['brg_nama'].'</td>    
                <td style="text-align:center">'.$retur_barang[$x]['retur_brg_qty'].' '.$retur_barang[$x]['retur_brg_satuan'].'</td> 
            </tr>';
            }


            $content=$content . '
        </table>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="width:100px;font-weight:bold"></td>
                <td style="width:300px; text-align:center;line-height:15pt;font-weight:bold">
                </td>
                <td style="width:130px; text-align:center">Jakarta, '.date("j M Y",strtotime($retur_main[0]['retur_tgl'])).'</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="width:130px; text-align:center">Pembeli</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="width:130px; text-align:center">(_____________)</td>
            </tr>
    </table>
    
    ';

$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya

    //$obj_pdf->SetFont(Courier','', 8); //untuk font, liat dokumentasui
    //$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya
    //$pdf->Write(5, 'Contoh Laporan PDF dengan CodeIgniter + tcpdf');
    $pdf->Output('Invoice'.date("d-m-Y").'.pdf', 'I');
?>