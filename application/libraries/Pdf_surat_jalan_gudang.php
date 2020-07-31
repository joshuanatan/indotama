<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
class Pdf_surat_jalan_gudang extends TCPDF
{
    private $logo;
    function __construct()
    {
        parent::__construct();
    }
    function set_logo($logo){
        $this->logo = $logo;
    }

    // Page footer
    public function Footer() {
         
    $this->SetY(-18);
    $html="
    <p>
    <hr style='width:98%;'>
    <br><br><span style='font-size: medium;'>SURAT JALAN</span>
    </p>";
    $this->SetFontSize(8);
    $this->SetTextColor(105, 105, 105);
    //$this->writeHTML($html, false, true, false, 0);
    //$this->WriteHTML($html, true, 0, true, 0);     
    //$this->Cell(0, 27, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    $this->WriteHTML($html, false, true, false, true);                    
    }
}
?>