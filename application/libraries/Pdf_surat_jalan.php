<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';
class Pdf_surat_jalan extends TCPDF
{
    function __construct()
    {
        parent::__construct();
    }

    //Page header
    public function Header() {
        // Logo
        $image_file = base_url().'asset/img/logo.png';
        $this->Image($image_file, 10, 8, '', 18, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        //$this->Image('@' . $image_file, 10, 8, 45, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Set font
        //$this->SetFont('helvetica', 'B', 20);
        // Title
        //$this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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