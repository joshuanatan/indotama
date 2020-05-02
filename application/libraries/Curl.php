<?php
class Curl{
    public function get($url,$header=array()){
        if($url == ""){
            return false;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST =>"GET",
            CURLOPT_HTTPHEADER => $header
        ));
        $response["response"] = curl_exec($curl);
        $response["err"] = curl_error($curl);
        return $response;
    }
    public function post($url,$header=array(),$body){
        if($url == ""){
            echo "Need url";
            return false;
        }
        if($body == ""){
            echo "Need Body Variable";
            return false;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => "-",
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => http_build_query($body)
        ));
        
        $response["response"] = curl_exec($curl);
        $response["err"] = curl_error($curl);
        return $response;
    }
}
?>