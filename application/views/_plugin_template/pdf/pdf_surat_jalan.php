
<?php
ob_start();
    $pdf = new Pdf_surat_jalan('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetTitle('SURAT JALAN');
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
                '.$pengiriman_main[0]['cabang_nama'].'<br>
                '.$pengiriman_main[0]['cabang_alamat'].'<br>
                Phone: '.$pengiriman_main[0]['cabang_notelp'].'<br>
            </td>
            <td style="text-align:right">
                Jakarta, '.date("d M Y", strtotime($pengiriman_main[0]['pengiriman_tgl'])).'<br>
                '.$pengiriman_main[0]['cust_name'].'<br>
                '.$pengiriman_main[0]['cust_alamat'].'
                <br><br>
                '.$pengiriman_main[0]['cust_telp'].'
            </td>
        </tr>
        <tr>
            <td colspan="2" style="line-height:25px"><b>SURAT JALAN No. '.$pengiriman_main[0]['pengiriman_no'].'</b></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <table class="tabel">
            <tr>
                <td class="judul" style="width:30px;">No</td>
                <td class="judul" style="width:430px;">Produk</td>
                <td class="judul" style="width:80px;text-align:center">Qty</td>
            </tr>
            ';
            $total=0;
            for($x=0; $x<count($pengiriman_brg); $x++){
            $no = $x+1;
            $content=$content . '
            <tr>
                <td style="text-align:center">'.$no.'</td>
                <td>'.$pengiriman_brg[$x]['brg_nama'].'</td>    
                <td style="text-align:center">'.$pengiriman_brg[$x]['brg_pengiriman_qty'].' '.$pengiriman_brg[$x]['satuan_nama'].'</td> 
            </tr>';
            }


            $content=$content . '
        </table>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td style="width:100px;font-weight:bold">Tanda Terima,</td>
                <td style="width:300px; text-align:center;line-height:15pt;font-weight:bold">
                </td>
                <td style="width:130px; text-align:right;font-weight:bold">Hormat Kami,</td>
            </tr>
    </table>
    
    ';
$pdf->writeHTML($content);
//echo $content;
$pdf->SetFont('MonotypeCorsivai','', 24);
$content = $this->session->id_user;
$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya

    //$obj_pdf->SetFont(Courier','', 8); //untuk font, liat dokumentasui
    //$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya
    //$pdf->Write(5, 'Contoh Laporan PDF dengan CodeIgniter + tcpdf');
    $pdf->Output('Invoice'.date("d-m-Y").'.pdf', 'I');
?>