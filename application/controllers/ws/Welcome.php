<?php
defined("BASEPATH") or exit("No direct script");
class Welcome extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }
    public function dashboard(){
        $this->load->model("m_dashboard_general");
        $response["status"] = "SUCCESS";
        $response["content"] = array(
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_penjualan_bulan_ini()),
                "title" => "Jumlah Penjualan Bulan Ini"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_penjualan_bulan_lalu()),
                "title" => "Jumlah Penjualan Bulan Lalu"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_penjualan_tahun_ini()),
                "title" => "Jumlah Penjualan Tahun Ini"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_penjualan_tahun_lalu()),
                "title" => "Jumlah Penjualan Tahun Lalu"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_barang()),
                "title" => "Jumlah Barang"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_jenis_barang()),
                "title" => "Jumlah Jenis Barang"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_nominal_penjualan(date("Y"))),
                "title" => "Nominal Penjualan Tahun Ini"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_nominal_penjualan(date("Y"),date("m"))),
                "title" => "Nominal Penjualan Bulan Ini"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_nominal_pembayaran(date("Y"))),
                "title" => "Nominal Pembayaran Tahun Ini"
            ),
            array(
                "type" => "widget",
                "data" => number_format($this->m_dashboard_general->jumlah_nominal_pembayaran(date("Y"),date("m"))),
                "title" => "Nominal Pembayaran Bulan Ini"
            ),
        );
        $result = $this->m_dashboard_general->list_penjualan_3_tahun_terakhir();
        $array = array(
            "type" => "doughnut",
            "title" => "Penjualan 3 Tahun Terakhir",
            "data" => array(
                "label" => $result["label"],
                "data" => $result["data"]
            )
        );
        array_push($response["content"],$array);

        $jumlah = 10;
        $result = $this->m_dashboard_general->urutan_barang(date("Y"),"",$jumlah,"desc");
        $array = array(
            "type" => "doughnut",
            "title" => $jumlah." Barang Paling Laku Tahun Ini",
            "data" => array(
                "label" => $result["label"],
                "data" => $result["data"]
            )
        );
        array_push($response["content"],$array);

        $online = $this->m_dashboard_general->jumlah_penjualan_tipe(date("Y"),"","online");
        $offline = $this->m_dashboard_general->jumlah_penjualan_tipe(date("Y"),"","offline");
        $array = array(
            "type" => "doughnut",
            "title" => "Perbandingan penjualan online/offline Tahun Ini",
            "data" => array(
                "label" => ["online","offline"],
                "data" => [$online,$offline]
            )
        );
        array_push($response["content"],$array);

        $online = $this->m_dashboard_general->jumlah_penjualan_tipe(date("Y"),date("m"),"online");
        $offline = $this->m_dashboard_general->jumlah_penjualan_tipe(date("Y"),date("m"),"offline");
        $array = array(
            "type" => "doughnut",
            "title" => "Perbandingan penjualan online/offline Bulan Ini",
            "data" => array(
                "label" => ["online","offline"],
                "data" => [$online,$offline]
            )
        );
        array_push($response["content"],$array);

        $online = $this->m_dashboard_general->nominal_penjualan_tipe(date("Y"),"","online");
        $offline = $this->m_dashboard_general->nominal_penjualan_tipe(date("Y"),"","offline");
        $array = array(
            "type" => "doughnut",
            "title" => "Perbandingan nominal online/offline Tahun Ini",
            "data" => array(
                "label" => ["online","offline"],
                "data" => [$online,$offline]
            )
        );
        array_push($response["content"],$array);

        $online = $this->m_dashboard_general->nominal_penjualan_tipe(date("Y"),date("m"),"online");
        $offline = $this->m_dashboard_general->nominal_penjualan_tipe(date("Y"),date("m"),"offline");
        $array = array(
            "type" => "doughnut",
            "title" => "Perbandingan nominal online/offline Bulan Ini",
            "data" => array(
                "label" => ["online","offline"],
                "data" => [$online,$offline]
            )
        );
        array_push($response["content"],$array);

       

        $result = $this->m_dashboard_general->urutan_barang(date("Y")-1,"",10,"desc");
        $array = array(
            "type" => "doughnut",
            "title" => $jumlah." Barang Paling Laku Tahun Lalu",
            "data" => array(
                "label" => $result["label"],
                "data" => $result["data"]
            )
        );
        array_push($response["content"],$array);

        
        $result = $this->m_dashboard_general->list_penjualan_tahun_ini_perbulan();
        $array = array(
            "type" => "chart",
            "title" => "Penjualan Tahun Ini Setiap Bulan",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan",
                    "data" => $result["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);

        $result = $this->m_dashboard_general->jumlah_penjualan_cabang(date("Y"));
        $array = array(
            "type" => "chart",
            "title" => "Penjualan Setiap Cabang Tahun Ini",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan",
                    "data" => $result["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);

        $result = $this->m_dashboard_general->jumlah_penjualan_cabang(date("Y"),date("m"));
        $array = array(
            "type" => "chart",
            "title" => "Penjualan Setiap Cabang Bulan Ini",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan",
                    "data" => $result["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);

        $result = $this->m_dashboard_general->penjualan_dekat_dateline();

        $array = array(
            "type" => "table",
            "title" => "Penjualan Mendekati / Melewati Dateline",
            "header" => array(
                "No Penjualan","Nominal","Dateline","Toko","Jenis"
            ),
            "data" =>$this->m_dashboard_general->penjualan_dekat_dateline(),
        );
        array_push($response["content"],$array);
        

        $result = $this->m_dashboard_general->list_penjualan_tahun_ini_perbulan();
        $result2 = $this->m_dashboard_general->list_penjualan_tahun_lalu_perbulan(1);
        $result3 = $this->m_dashboard_general->list_penjualan_tahun_lalu_perbulan(2);
        $array = array(
            "type" => "chart",
            "title" => "Penjualan 3 Tahun Setiap Bulan",
            "data" => array(
                array(
                    "label" => "Jumlah Penjualan Tahun ".date("Y"),
                    "data" => $result["data"]
                ),
                array(
                    "label" => "Jumlah Penjualan Tahun ".((int)date("Y")-1),
                    "data" => $result2["data"]
                ),
                array(
                    "label" => "Jumlah Penjualan Tahun ".((int)date("Y")-2),
                    "data" => $result3["data"]
                )
            ),
            "xlabel" => $result["label"]
        );
        array_push($response["content"],$array);
        echo json_encode($response);
    }
}