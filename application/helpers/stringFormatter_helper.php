<?php
if ( ! function_exists('splitterMoney')){
    function splitterMoney($string,$separator){
        $split = explode($separator,$string);
        $result = "";
        for($a = 0; $a<count($split); $a++){
            $result .= $split[$a];
        }
        return $result;
    }   
}
if(!function_exists("bulanRomawi")){
    function bulanRomawi($bulan){
        switch((int)$bulan){
            case 1: return "I"; break;
            case 2: return "II"; break;
            case 3: return "II"; break;
            case 4: return "IV"; break;
            case 5: return "V"; break;
            case 6: return "VI"; break;
            case 7: return "VII"; break;
            case 8: return "VIII"; break;
            case 9: return "IX"; break;
            case 10: return "X"; break;
            case 11: return "XI"; break;
            case 12: return "XII"; break;
        }
    }
}

if(!function_exists("tanggalCantik")){
    function tanggalCantik($tanggal){
        $cantik = explode("/",$tanggal);
        switch((int)$cantik[1]){
            case 1: return $cantik[2] . " Januari " .$cantik[0]; break;
            case 2: return $cantik[2] . " Februari " .$cantik[0]; break;
            case 3: return $cantik[2] . " Maret " .$cantik[0]; break;
            case 4: return $cantik[2] . " April " .$cantik[0]; break;
            case 5: return $cantik[2] . " Mei " .$cantik[0]; break;
            case 6: return $cantik[2] . " Juni " .$cantik[0]; break;
            case 7: return $cantik[2] . " Juli " .$cantik[0]; break;
            case 8: return $cantik[2] . " Agustus " .$cantik[0]; break;
            case 9: return $cantik[2] . " September " .$cantik[0]; break;
            case 10: return $cantik[2] . " Oktober " .$cantik[0]; break;
            case 11: return $cantik[2] . " November " .$cantik[0]; break;
            case 12: return $cantik[2] . " Desember " .$cantik[0]; break;
        }
    }
}

if(!function_exists("hariCantik")){
    function hariCantik($harii){
        $cantikk = explode(",",$harii);
        switch((int)$cantikk[0]){
            case 1: return "Senin"; break;
            case 2: return "Selasa"; break;
            case 3: return "Rabu"; break;
            case 4: return "Kamis"; break;
            case 5: return "Jumat"; break;
            case 6: return "Sabtu"; break;
            case 7: return "Minggu"; break;
        }
    }
}
if(!function_exists("splitGiveSpace")){
    function splitGiveSpace($string,$separator){
        $split = explode($separator,$string);
        $result = "";
        for($a = 0; $a<count($split); $a++){
            $result .= $split[$a]." ";
        }
        return $result;
    }
}

?>