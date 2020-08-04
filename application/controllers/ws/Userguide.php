<?php
defined("BASEPATH") or exit("No direct script");
class Userguide extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    private function menu(){
        $array = array(
            array(
                "name" => "stok",
                "link" => "stok",
                "type" => "single"
            ),
            array(
                "name" => "pembelian",
                "link" => "pembelian",
                "type" => "single"
            ),
            array(
                "name" => "penerimaan",
                "link" => "penerimaan",
                "type" => "multiple",
                "child" => array(
                    array(
                        "name" => "penerimaan pembelian",
                        "link" => "penerimaan_pembelian",
                        "type" => "single"
                    ),
                    array(
                        "name" => "penerimaan retur",
                        "link" => "penerimaan_retur",
                        "type" => "single"
                    ),
                    array(
                        "name" => "penerimaan permintaan",
                        "link" => "penerimaan_permintaan",
                        "type" => "single"
                    )
                )
            ),
            array(
                "name" => "penjualan",
                "link" => "penjualan",
                "type" => "single"
            ),
            array(
                "name" => "pengiriman",
                "link" => "pengiriman",
                "type" => "multiple",
                "child" => array(
                    array(
                        "name" => "pengiriman penjualan",
                        "link" => "pengiriman_penjualan",
                        "type" => "single"
                    ),
                    array(
                        "name" => "pengiriman retur",
                        "link" => "pengiriman_retur",
                        "type" => "single"
                    ),
                    array(
                        "name" => "pengiriman permintaan",
                        "link" => "pengiriman_permintaan",
                        "type" => "single"
                    )
                )
            ),
            array(
                "name" => "retur",
                "link" => "retur",
                "type" => "single"
            ),
            array(
                "name" => "permintaan",
                "link" => "permintaan",
                "type" => "single"
            ),
            array(
                "name" => "pemberian",
                "link" => "pemberian",
                "type" => "single"
            ),
        );
        return $array;
    }
    private function guides(){
        $array = array(
            array(
                "id" => "stok",
                "title" => "stok",
                "desc" => "stok",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            base_url()."test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "pembelian",
                "title" => "pembelian",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "penerimaan_pembelian",
                "title" => "penerimaan pembelian",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "penerimaan_retur",
                "title" => "penerimaan retur",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "penerimaan_permintaan",
                "title" => "penerimaan permintaan",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "penjualan",
                "title" => "penjualan",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            
            array(
                "id" => "pengiriman_pembelian",
                "title" => "pengiriman pembelian",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "pengiriman_retur",
                "title" => "pengiriman retur",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "pengiriman_permintaan",
                "title" => "pengiriman permintaan",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "retur",
                "title" => "retur",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "permintaan",
                "title" => "permintaan",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
            array(
                "id" => "pemberian",
                "title" => "pemberian",
                "desc" => "single",
                "content" => array(
                    array(
                        "title" => "title",
                        "images" => array(
                            "test.jpg","test2.jpg"
                        ),
                        "explanation" => ""
                    )
                )
            ),
        );
        return $array;
    }
    public function content(){
        $response["status"] = "success";
        $response["menu"] = $this->menu();
        $response["content"] = $this->guides();
        echo json_encode($response);
    }
}