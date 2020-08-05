<?php
defined("BASEPATH") or exit("No direct script");
class Userguide extends CI_Controller{
    private $separator;
    public function __construct(){
        parent::__construct();
        $this->separator = "›";
    }
    private function menu(){
        $array = array(
            array(
                "name" => "Toko",
                "link" => "toko",
                "type" => "single"
            ),
            array(
                "name" => "Cabang",
                "link" => "cabang",
                "type" => "single"
            ),
            array(
                "name" => "Stok",
                "link" => "stok",
                "type" => "single"
            ),
            array(
                "name" => "Pembelian",
                "link" => "pembelian",
                "type" => "single"
            ),
            array(
                "name" => "Penerimaan",
                "link" => "penerimaan_pembelian",
                "type" => "multiple",
                "child" => array(
                    array(
                        "name" => "Penerimaan Pembelian",
                        "link" => "penerimaan_pembelian",
                        "type" => "single"
                    ),
                    array(
                        "name" => "Penerimaan Retur",
                        "link" => "penerimaan_retur",
                        "type" => "single"
                    ),
                    array(
                        "name" => "Penerimaan Permintaan",
                        "link" => "penerimaan_permintaan",
                        "type" => "single"
                    )
                )
            ),
            array(
                "name" => "Penjualan",
                "link" => "penjualan",
                "type" => "single"
            ),
            array(
                "name" => "Pengiriman",
                "link" => "pengiriman_penjualan",
                "type" => "multiple",
                "child" => array(
                    array(
                        "name" => "Pengiriman Penjualan",
                        "link" => "pengiriman_penjualan",
                        "type" => "single"
                    ),
                    array(
                        "name" => "Pengiriman Retur",
                        "link" => "pengiriman_retur",
                        "type" => "single"
                    ),
                    array(
                        "name" => "Pengiriman Permintaan",
                        "link" => "pengiriman_permintaan",
                        "type" => "single"
                    )
                )
            ),
            array(
                "name" => "Retur",
                "link" => "retur",
                "type" => "single"
            ),
            array(
                "name" => "Permintaan",
                "link" => "permintaan",
                "type" => "single"
            ),
            array(
                "name" => "Pemberian",
                "link" => "pemberian",
                "type" => "single"
            ),
        );
        return $array;
    }
    private function guides(){
        $array = array(
            array(
                "id" => "toko",
                "title" => "Manajemen Toko",
                "desc" => "Bagian ini ditujukan untuk mengganti toko apabila user memiliki akses pada lebih dari satu toko. 
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Toko $this->separator Daftar Toko</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Stok Toko",
                        "images" => array(
                            base_url()."asset/userguide/uploads/manajemen-toko/home.png",
                            base_url()."asset/userguide/uploads/manajemen-toko/active.png"
                        ),
                        "explanation" => "User dapat mengaktifkan toko yang diinginkan dengan menekan button pada kolom <i>action</i>"
                    )
                )
            ),
            array(
                "id" => "cabang",
                "title" => "Manajemen Cabang",
                "desc" => "Bagian ini ditujukan untuk mengganti toko apabila user memiliki akses pada lebih dari satu cabang. 
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Daftar Cabang</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Stok Cabang",
                        "images" => array(
                            base_url()."asset/userguide/uploads/manajemen-cabang/home.png",
                            base_url()."asset/userguide/uploads/manajemen-cabang/active.png"
                        ),
                        "explanation" => "User dapat mengaktifkan cabang yang diinginkan dengan menekan button pada kolom <i>action</i>"
                    )
                )
            ),
            array(
                "id" => "stok",
                "title" => "Stok",
                "desc" => "Bagian ini ditujukan untuk membantu mendata barang-barang serta stok yang dimiliki oleh masing-masing cabang dan gudang. 
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Stok Cabang / Manajemen Gudang $this->separator Stok Gudang</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Stok Toko",
                        "images" => array(
                            base_url()."asset/userguide/uploads/stok/home.png",
                            base_url()."asset/userguide/uploads/stok/add.png"
                        ),
                        "explanation" => "User dapat mengelola data pada cabang/gudang tertentu di halaman ini. User dapat menambahkan, mengubah, dan menghapus stok cabang/gudang. Bagian ini yang akan menjadi refrensi segala aktivitas di cabang/gudang."
                    )
                )
            ),
            array(
                "id" => "pembelian",
                "title" => "Pembelian",
                "desc" => "Bagian ini ditujukan untuk membantu mendata pembelian barang kepada supplier. Bagian ini <strong>Belum mengubah jumlah stok</strong>. Setelah pembelian kepada supplier, pesanan dapat diterima di <a href = '#penerimaan_pembelian'> cabang / gudang</a>.  
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Pembelian</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Pembelian",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pembelian/home.png",
                        ),
                        "explanation" => "User dapat mengelola data pembelian (menambah, mengurangi, menghapus, cetak PDF, dll). Data ini akan menjadi refrensi untuk mendata penerimaan barang di <a href = '#penerimaan_pembelian'> cabang / gudang</a>"
                    ),
                    array(
                        "title" => "Penambahan Pembelian",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pembelian/add.png",
                        ),
                        "explanation" => "Dalam penambahan data pembelian, user dapat memasukan tanggal pembelian dan supplier. Kemudian user memasukan barang-barang yang sudah terdaftar di <a href = '#stok'>cabang</a>. Silahkan memasukan satuan sebagai refrensi dalam penerimaan nanti (xxx pcs) [gunakan spasi antara jumlah dan satuan]. default value dari satuan adalah 'Pcs'. Data tambahan juga silahkan ditambah sebagai refrensi tambahan saat penerimaan. Default satuan adalah 'Pcs', silahkan ditambahkan menggunakan format serupa apabila dibutuhkan"
                    )
                )
            ),
            array(
                "id" => "penerimaan_pembelian",
                "title" => "Penerimaan Pembelian",
                "desc" => "Bagian ini ditujukan untuk membantu mendata penerimaan barang dari <a href = '#pembelian'>pembelian</a>. Bagian ini akan <strong>melakukan perubahan pada <a href = '#stok'>stok penerima</a></strong>.
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Penerimaan / Manajemen Gudang $this->separator Penerimaan Gudang</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Penerimaan Pembelian",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penerimaan-pembelian/home.png",
                        ),
                        "explanation" => "User dapat mengelola data penerimaan barang pada cabang/gudang tertentu di halaman ini. User dapat menambahkan, mengubah, dan menghapus penerimaan data cabang/gudang. Bagian ini akan mempengaruhi stok barang pada cabang/gudang"
                    ),
                    array(
                        "title" => "Penambahan Penerimaan Pembelian",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penerimaan-pembelian/add.png",
                        ),
                        "explanation" => "Dalam penambahan penerimaan, user dapat memasukan nomor pembelian dan menekan tombol <i>Load Data Barang</i>. Kemudian user dapat memasukan tanggal penerimaan. tombol <i>Load Data Barang</i> akan mengeluarkan data detail dari pembelian, data barang pembelian, dan data tambahan pembelian. User dapat memasukan jumlah penerimaan dan satuan penerimaan (yang nanti menjadi acuan dalam mengurangi stok karena pengurangan stok menggunakan satuan terkecil yaitu pcs)."
                    )
                )
            ),
            array(
                "id" => "penerimaan_retur",
                "title" => "Penerimaan Retur",
                "desc" => "Bagian ini ditujukan untuk membantu mendata penerimaan barang dari <a href = '#retur'>retur</a>. Bagian ini akan <strong>melakukan perubahan pada <a href = '#stok'>stok cabang</a></strong> karena hanya cabang yang dapat menerima retur
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Penerimaan</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Penerimaan Retur",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penerimaan-retur/home.png"
                        ),
                        "explanation" => "User dapat mengubah jenis penerimaan terlebih dahulu dengan mengubah <i>drop down</i> di atas dan menekan tombol <i>Buka</i>. User dapat mengelola data penerimaan retur di halaman ini. User dapat menambahkan, mengubah, dan menghapus penerimaan retur. Bagian ini akan <strong>mempengaruhi stok barang pada cabang penerima</strong>."
                    ),
                    array(
                        "title" => "Penambahan Penerimaan Retur",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penerimaan-retur/add.png"
                        ),
                        "explanation" => "Dalam penambahan penerimaan retur, user dapat memasukan nomor retur dan tanggal penerimaan. Kemudian menekan tombol <i>Load Data Barang</i>. Setelah tombol tersebut ditekan, data detail mengenai retur serta barang yang dikembalikan. User dapat memasukan jumlah barang yang diterima berserta satuan penerimaannya."
                    )
                )
            ),
            array(
                "id" => "penerimaan_permintaan",
                "title" => "Penerimaan Permintaan",
                "desc" => "Bagian ini ditujukan untuk membantu mendata penerimaan barang dari <a href = '#permintaan'>permintaan</a>. Bagian ini akan <strong>melakukan perubahan pada <a href = '#stok'>stok cabang</a></strong> karena hanya cabang yang dapat melakukan permintaan dan menerima pemberian dari permintaan.",
                "content" => array(
                    array(
                        "title" => "Manajemen Penerimaan Permintaan",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penerimaan-permintaan/home.png",
                            base_url()."asset/userguide/uploads/penerimaan-permintaan/ui.png",
                            base_url()."asset/userguide/uploads/penerimaan-permintaan/add.png",
                        ),
                        "explanation" => "User dapat menerima pengiriman dan membatalkan penerimaan permintaan. Bagian ini akan <strong>mempengaruhi stok barang pada cabang penerima</strong>.
                        <br/>
                        <br/>
                        Penambahan penerimaan dapat dilakukan melalui 2 cara yaitu melalui grafik/skema yang dapat dibuka di <strong>Manajemen Cabang $this->separator Permintaan</strong> atau melalui table yang dapat dibuka di <strong>Manajemen Cabang $this->separator Penerimaan Permintaan</strong>. Data pada table lebih lengkap karena akan menampilkan data yang telah diterima sedangkan pada grafik/skema hanya menampilkan pengiriman yang sedang dalam perjalanan. Pada table, user dapat menekan tombol <i>centang</i> untuk melakukan penerimaan. Pada grafik, user dapat menekan tombol <i>Done</i> untuk melakukan penerimaan."
                    )
                )
            ),
            array(
                "id" => "penjualan",
                "title" => "Penjualan",
                "desc" => "Bagian ini ditujukan untuk membantu mendata penjualan cabang. Bagian ini akan menjadi refrensi dalam <a href = '#pengiriman_penjualan'>pengiriman</a> dan <a href = '#retur'>retur</a>.
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Penjualan</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Penjualan",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penjualan/home.png",
                            base_url()."asset/userguide/uploads/penjualan/metodebayar.png"
                        ),
                        "explanation" => "User dapat mengelola penjualan pada halaman ini. Selain itu user dapat melihat daftar penjualan berdasarkan metode pembayaran dengan mengubah <i>dropdown</i> di pojok kanan atas dan menekan tombol <i>Buka</i>."
                    ),
                    array(
                        "title" => "Penambahan Penjualan (1/4)",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penjualan/add-1.png"
                        ),
                        "explanation" => "Penambahan data penjualan dapat dilakukan dengan memilih customer, tanggal penjualan, dan dateline penjualan. Kemudian user dapat memilih jenis penjualannya apakah melalui platform online atau offline. Setelah itu user dapat memilih tipe pembayaran yang disetujui dengan customer (digunakan juga untuk filter tampilan di halaman <i>manajemen penjualan</i>."
                    ),
                    array(
                        "title" => "Penambahan Penjualan (2/4)",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penjualan/custom_produk.png"
                        ),
                        "explanation" => "Dalam menambahkan penjualan, user dapat melakukan <i>custom produk</i>. <i>Custom produk</i> akan membantu user dalam mengubah stok secara cepat untuk kepentingan penjualan. <strong style = 'color:red'>WARNING! SETIAP DATA YANG DISUBMIT AKAN LANGSUNG BERPENGARUH PADA STOK CABANG TERKAIT.</strong>" 
                    ),
                    array(
                        "title" => "Penambahan Penjualan (3/4)",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penjualan/add-2.png"
                        ),
                        "explanation" => "Kemudian user dapat menambah barang penjualan serta tambahan penjualan. <i>Total Price</i> adalah akumulasi dari perkalian jumlah dengan harga. Pastikan setiap harga yang dimasukan itu adalah harga satuan. Notes dapat digunakan untuk menyampaikan informasi sederhana terkait barang dan tambahan pembelian karena bagian notes akan tampil pada saat <a href = '#pengiriman_penjualan'>pengiriman barang</a>. Silahkan memasukan jumlah barang dengan pola <i>[jumlah barang] [satuan]</i>. Default dari satuan adalah 'Pcs'. Silahkan menekan elemen form <i>Total Data</i> untuk melakukan perhitungan penjualan."
                    ),
                    array(
                        "title" => "Penambahan Penjualan (4/4)",
                        "images" => array(
                            base_url()."asset/userguide/uploads/penjualan/add-3.png"
                        ),
                        "explanation" => "Bagian terakhir dalam proses penambahan penjualan adalah pembagian pembayaran. User dapat mencatat pembagian pembayaran untuk penjualan ini serta menandai fase yang sudah lunas. Sistem akan menghitung otomatis nominal pembayaran setelah persentase pembayaran dimasukan, apabila terdapat kebutuhan pembulatan / perubahan nominal, dapat dilakukan pada kolom jumlah."
                    )
                )
            ),
            
            array(
                "id" => "pengiriman_penjualan",
                "title" => "Pengiriman Penjualan",
                "desc" => "Bagian ini ditujukan untuk membantu mendata pengiriman <a href = '#penjualan'>penjualan</a>. <strong>Bagian ini akan mempengaruhi stok barang.</strong>
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Pengiriman</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Pengiriman",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pengiriman-penjualan/home.png",
                            base_url()."asset/userguide/uploads/pengiriman-penjualan/add.png"
                        ),
                        "explanation" => "Pada halaman manajemen pengiriman, user dapat melihat data pengiriman yang didaftarkan. Dalam penambahan pengiriman, user memilih nomor <a href = '#penjualan'>penjualan</a> terlebih dahulu dan menekan tombol <i>Load Data Barang</i>. Kemudian user dapat memasukan tanggal pengiriman. Serupa seperti <a href = '#penerimaan_pembelian'>penerimaan</a>, user dapat memasukan notes dan nominal pengiriman serta memilih satuan. Sistem akan mengonversi jumlah pengiriman berdasarkan satuan tersebut dan mengurangi ke stok."
                    ))
            ),
            array(
                "id" => "pengiriman_retur",
                "title" => "Pengiriman Retur",
                "desc" => "Bagian ini ditujukan untuk membantu mendata pengiriman <a href = '#retur'>retur</a>. <strong>Bagian ini akan mempengaruhi stok barang.</strong>
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Pengiriman</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Pengiriman Retur",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pengiriman-retur/home.png",
                            base_url()."asset/userguide/uploads/pengiriman-retur/add.png"
                        ),
                        "explanation" => "User dapat mengubah tipe pengiriman terlebih dahulu menjadi retur kemudian menekan tombol <i>buka</i>. Dalam penambahan pengiriman, user dapat memilih nomor <a href = '#retur'>retur</a> terlebih dahulu dan menekan tombol <i>Load Data Barang</i>. Kemudian user dapat memasukan tanggal pengiriman. Serupa seperti <a href = '#penerimaan_retur'>penerimaan retur</a>, user dapat memasukan notes dan nominal pengiriman serta memilih satuan. Sistem akan mengonversi jumlah pengiriman berdasarkan satuan tersebut dan mengurangi ke stok."
                    )
                )
            ),
            array(
                "id" => "pengiriman_permintaan",
                "title" => "Pengiriman Permintaan",
                "desc" => "Bagian ini ditujukan untuk membantu mendata pengiriman <a href = '#permintaan'>permintaan</a> <strong>Bagian ini akan mempengaruhi stok barang.</strong>
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Pengiriman Permintaan  / Manajemen Gudang $this->separator Pengiriman Permintaan</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Pengiriman Permintaan",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pengiriman-permintaan/home.png",
                            base_url()."asset/userguide/uploads/pengiriman-permintaan/add.png"
                        ),
                        "explanation" => "User dapat mengirim dan membatalkan pengiriman permintaan. Pengiriman permintaan ini akan tersedia setelah user melakukan <a href = '#pemberian'>pemberian</a> pada <a href = '#permintaan'>permintaan</a>. Untuk melakukan pengiriman dapat menekan tombol <i>truck</i>."
                    )
                )
            ),
            array(
                "id" => "retur",
                "title" => "Retur",
                "desc" => "Bagian ini ditujukan untuk membantu mendata retur dari <a href = '#penjualan'>penjualan</a>. Bagian ini akan mendata barang yang dikembalikan dan metode ganti rugi dalam bentuk uang atau barang. <strong>Bagian ini tidak berpengaruh pada stok</strong>
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Retur</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Retur",
                        "images" => array(
                            base_url()."asset/userguide/uploads/retur/home.png",
                            base_url()."asset/userguide/uploads/retur/add.png"
                        ),
                        "explanation" => "User dapat mengelola data retur pada halaman ini. Dalam penambahan retur, user dapat memilih nomor penjualan yang akan diretur. Kemudian user dapat memilih tanggal retur dan opsi pengembalian retur apakah dalam bentuk uang atau barang. Kemudian dari data barang yang dibeli customer, user dapat memasukan jumlah barang yang akan diretur beserta satuannya apabila ada. Bagian ini akan menjadi refrensi di <a href = '#penerimaan_retur'>penerimaan retur</a>
                        <br/>
                        <br/>
                        Apabila opsi pengembalian dalam bentuk barang, user dapat memasukan data <a href = '#stok'>barang</a> pengembalian beserta jumlah dan harga satuannya. Bagian ini akan menjadi refrensi dalam <a href = '#pengiriman_retur'>pengiriman retur</a>
                        <br/>
                        <br/>
                        Untuk menyertakan satuan, gunakan format [jumlah barang] [satuan]. " 
                    )
                )
            ),
            array(
                "id" => "permintaan",
                "title" => "Permintaan Barang",
                "desc" => "Bagian ini ditujukan untuk mendata permintaan barang kepada cabang lain atau gudang. Terdapat 2 cara untuk mengelola permintaan yaitu melalui grafik/skema atau melalui table. 
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Permintaan</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Permintaan",
                        "images" => array(
                            base_url()."asset/userguide/uploads/permintaan/home.png",
                            base_url()."asset/userguide/uploads/permintaan/ui.png"
                        ),
                        "explanation" => "User dapat menambah, mengubah, dan menyelesaikan permintaan. Permintaan yang melewati dateline, sudah diselesaikan, dan dihapus tidak keluar di cabang lain. Untuk menampilkan data dalam bentuk table dapat menekan tombol <i>Daftar Permintaan</i>"
                    ),
                    array(
                        "title" => "Penambahan Permintaan",
                        "images" => array(
                            base_url()."asset/userguide/uploads/permintaan/add.png",
                        ),
                        "explanation" => "Dalam penambahan permintaan, user dapat memasukan nama <a href = '#stok'>barang</a> yang diinginkan, jumlah barang dalam satuan pcs, notes, dan deadline. Data ini akan jadi refrensi dalam melakukan <a href = '#pemberian'>pemberian/pemenuhan</a>"
                    )
                )
            ),
            array(
                "id" => "pemberian",
                "title" => "Pemberian",
                "desc" => "Bagian ini ditujukan untuk mendata pemberian/pemenuhan <a href = '#permintaan'>permintaan</a> dari cabang lain. <strong>Bagian ini tidak berpengaruh pada <a href = '#stok'>stok barang</a></strong>
                <br/>
                <br/>
                User dapat membuka pada menu <strong>Manajemen Cabang $this->separator Permintaan Barang / Manajemen Gudang $this->separator Permintaan Barang</strong>",
                "content" => array(
                    array(
                        "title" => "Manajemen Pemberian",
                        "images" => array(
                            base_url()."asset/userguide/uploads/pemberian/home.png",
                            base_url()."asset/userguide/uploads/pemberian/add.png",
                        ),
                        "explanation" => "User dapat memberikan sejumlah barang pada <a href = '#permintaan'>permintaan</a> yang ada. Permintaan akan muncul apabila barang yang diminta merupakan <a href = '#stok'>barang cabang/warehouse</a>, belum selesai, tidak dihapus, dan masih dalam dateline. Semua pemberian dilakukan dengan satuan 'Pcs'. User dapat memberikan barang lebih dari 1x pada permintaan yang sama. Pendataan pemberian barang akan menjadi refrensi dalam <a href = '#pengiriman_permintaan'>pengiriman permintaan</a>."
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