<!DOCTYPE html>
<html lang="en">

    <head>
        <?php $this->load->view('req/mm_css.php');?>
    </head>

    <body>
        <!--Preloader-->
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <!--/Preloader-->
        <div class="wrapper theme-1-active pimary-color-pink">

            <?php $this->load->view('req/mm_menubar.php');?>

            <!-- Main Content -->
            <div class="page-wrapper">

                <div class="container-fluid pt-25">
                    <div class="col-lg-12 col-sm-12">
                        <div class="panel panel-default card-view">
                            <div class="panel-heading bg-gradient">
                                <div class="pull-left">
                                    <h6 class="panel-title txt-light">Penjualan</h6>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-wrapper collapse in">
                                <div class="panel-body" style="margin-top:-50px">
                                    <div class="pills-struct mt-40">
                                        <ul role="tablist" class="nav nav-pills" id="myTabs_6">
                                            <li class="active" role="presentation"><a aria-expanded="true"
                                                    data-toggle="tab" role="tab" id="home_tab_6" href="#home_6"><i
                                                        class="fa fa-folder"></i><span class="right-nav-text"
                                                        style="margin-left:20px">Order</span></a></li>
                                            <li role="presentation" class=""><a data-toggle="tab" id="profile_tab_6"
                                                    role="tab" href="#profile_6" aria-expanded="false"><i
                                                        class="fa fa-folder"></i><span class="right-nav-text"
                                                        style="margin-left:20px">Invoice</span></a></li>
                                            <li role="presentation" class=""><a data-toggle="tab" id="profile_tab_7"
                                                    role="tab" href="#profile_7" aria-expanded="false"><i
                                                        class="fa fa-folder"></i><span class="right-nav-text"
                                                        style="margin-left:20px">Tempo</span></a></li>
                                            <li role="presentation" class=""><a data-toggle="tab" id="profile_tab_8"
                                                    role="tab" href="#profile_8" aria-expanded="false"><i
                                                        class="fa fa-folder"></i><span class="right-nav-text"
                                                        style="margin-left:20px">Down Payment</span></a></li>
                                            <li role="presentation" class=""><a data-toggle="tab" id="profile_tab_9"
                                                    role="tab" href="#profile_9" aria-expanded="false"><i
                                                        class="fa fa-folder"></i><span class="right-nav-text"
                                                        style="margin-left:20px">Keep</span></a></li>
                                        </ul>
                                        <div class="tab-content" id="myTabContent_6">
                                            <div id="home_6" class="tab-pane fade active in" role="tabpanel">
                                                <!-- Row Order-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default card-view">
                                                            <div class="panel-wrapper collapse in">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-xs-12">
                                                                            <div class="form-wrap">
                                                                                <form action="#">
                                                                                    <div class="form-body">
                                                                                        <h2 class="txt-dark"
                                                                                            style="margin-top:-10px">
                                                                                            ORDER</h2>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Tanggal
                                                                                                        Nota</label>
                                                                                                    <input type="date"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy"
                                                                                                        name="date_nota">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Tanggal
                                                                                                        Jatuh
                                                                                                        Tempo</label>
                                                                                                    <input type="date"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy"
                                                                                                        name="date_jatuhTempo">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-12">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Customer</label>
                                                                                                    <select
                                                                                                        class="form-control select2">
                                                                                                        <option>Select
                                                                                                            Customer
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="AK">
                                                                                                            Toko Lestari
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="HI">
                                                                                                            Toko Hawai
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Jenis
                                                                                                        Pembayaran</label>
                                                                                                    <select
                                                                                                        class="form-control select2"
                                                                                                        id="coba"
                                                                                                        onchange="checkKredit(this)">
                                                                                                        <option
                                                                                                            value="Cash">
                                                                                                            Cash
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Debit">
                                                                                                            Debit
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Kredit">
                                                                                                            Kredit
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Transfer">
                                                                                                            Transfer
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>


                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Status
                                                                                                        Pembayaran</label>
                                                                                                    <select
                                                                                                        class="form-control select2"
                                                                                                        onchange="checkDP(this)">
                                                                                                        <option
                                                                                                            value="Full Payment">
                                                                                                            Full
                                                                                                            Payment
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="DP">
                                                                                                            Dp
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Tempo">
                                                                                                            Tempo
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Keep">
                                                                                                            Keep
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <!-- /Row -->
                                                                                        <div class="row" id="rowKredit">
                                                                                            <div class="col-md-12">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Keterangan
                                                                                                        Kredit</label>
                                                                                                    <textarea
                                                                                                        class="form-control"
                                                                                                        rows="5">
                                                                                                        </textarea>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <!-- /Row -->
                                                                                        <div class="row" id="rowDP">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Jumlah
                                                                                                        DP</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control"
                                                                                                        name="total_dp">
                                                                                                </div>
                                                                                            </div>


                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Tanggal
                                                                                                        DP</label>
                                                                                                    <input type="date"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy"
                                                                                                        name="date_dp">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <!-- /Row -->
                                                                                        <div class="row" id="rowToko">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Toko
                                                                                                        / Online</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-30">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="cabang"
                                                                                                                    id="radio_cabang"
                                                                                                                    value="cabang"
                                                                                                                    onclick="javascript:checkToko();">
                                                                                                                <label
                                                                                                                    for="
                                                                                                                    radio_5">Toko</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="online"
                                                                                                                    id="radio_ol"
                                                                                                                    value="option2"
                                                                                                                    onclick="javascript:checkOl();">
                                                                                                                <label
                                                                                                                    for="radio_6">Online
                                                                                                                </label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group"
                                                                                                    id="toko">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Toko</label>
                                                                                                    <select
                                                                                                        class="form-control select2">
                                                                                                        <option
                                                                                                            value="MM Safety">
                                                                                                            MM Safety
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Maju Abadi">
                                                                                                            Maju Abadi
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="form-group"
                                                                                                    id="market_place">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Market
                                                                                                        Place</label>
                                                                                                    <select
                                                                                                        class="form-control select2">
                                                                                                        <option
                                                                                                            value="Tokopedia">
                                                                                                            Tokopedia
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Lazada">
                                                                                                            Lazada
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-12">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Keterangan
                                                                                                        Order</label>
                                                                                                    <textarea
                                                                                                        class="form-control"
                                                                                                        rows="5"
                                                                                                        name="ket_order">
                                                                                                        </textarea>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <h3 class="txt-dark"
                                                                                            style="margin-top:-20px">
                                                                                            Barang
                                                                                        </h3>
                                                                                        <hr class="light-grey-hr" />

                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-12">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Barang
                                                                                                        Non Custom atau
                                                                                                        Custom</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-30">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="non_custom"
                                                                                                                    id="radio_nonCustom"
                                                                                                                    value="1"
                                                                                                                    onclick="javascript:checkNonCustom();">
                                                                                                                <label>Non
                                                                                                                    Custom</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="custom"
                                                                                                                    id="radio_custom"
                                                                                                                    value="2"
                                                                                                                    onclick="javascript:checkCustom();">
                                                                                                                <label>
                                                                                                                    Custom</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <!-- Row Order Barang Non Custom-->
                                                                                        <div class="row"
                                                                                            id="rowNonCustom">
                                                                                            <div class="col-sm-12">
                                                                                                <div
                                                                                                    class="panel panel-default card-view">
                                                                                                    <div
                                                                                                        class="panel-wrapper collapse in">
                                                                                                        <div
                                                                                                            class="panel-body">
                                                                                                            <div
                                                                                                                class="table-wrap">
                                                                                                                <div
                                                                                                                    class="table-responsive">
                                                                                                                    <table
                                                                                                                        class="table table-bordered display pb-30">
                                                                                                                        <thead>
                                                                                                                            <tr>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    No
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Produk
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Qty
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Harga
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Qty
                                                                                                                                    MU
                                                                                                                                </th
                                                                                                                                    class="text-center">
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Harga
                                                                                                                                    MU
                                                                                                                                </th
                                                                                                                                    class="text-center">
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Total
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Total
                                                                                                                                    MU
                                                                                                                                </th>
                                                                                                                            </tr>
                                                                                                                        </thead>

                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    1
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    2
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    3
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    4
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    5
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    6
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    7
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    8
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    9
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    10
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>


                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <!-- Row Order Barang Custom-->
                                                                                        <div class="row" id="rowCustom">
                                                                                            <div class="col-sm-12">
                                                                                                <div
                                                                                                    class="panel panel-default card-view">
                                                                                                    <div
                                                                                                        class="panel-wrapper collapse in">
                                                                                                        <div
                                                                                                            class="panel-body">
                                                                                                            <div
                                                                                                                class="table-wrap">
                                                                                                                <div
                                                                                                                    class="table-responsive">
                                                                                                                    <table
                                                                                                                        class="table table-bordered display pb-30">
                                                                                                                        <thead>
                                                                                                                            <tr>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    No
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Produk
                                                                                                                                    Asli
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Custom
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Qty
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Harga
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Qty
                                                                                                                                    MU
                                                                                                                                </th
                                                                                                                                    class="text-center">
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Harga
                                                                                                                                    MU
                                                                                                                                </th
                                                                                                                                    class="text-center">
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Total
                                                                                                                                </th>
                                                                                                                                <th
                                                                                                                                    class="text-center">
                                                                                                                                    Total
                                                                                                                                    MU
                                                                                                                                </th>
                                                                                                                            </tr>
                                                                                                                        </thead>

                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    1
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    2
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    3
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    4
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    5
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    6
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    7
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    8
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    9
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                            <tr>
                                                                                                                                <td
                                                                                                                                    class="text-center">
                                                                                                                                    10
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <select
                                                                                                                                        class="
                                                                                                                                    form-control
                                                                                                                                    select2"
                                                                                                                                        name="product">
                                                                                                                                        <option
                                                                                                                                            value="red parker T 181">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            181
                                                                                                                                        </option>
                                                                                                                                        <option
                                                                                                                                            value="red parker T 182">
                                                                                                                                            Red
                                                                                                                                            Parker
                                                                                                                                            T
                                                                                                                                            182
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="text"
                                                                                                                                        class="form-control"
                                                                                                                                        name="ket_product">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="qty_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="harga_mu">
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                                <td>
                                                                                                                                    <input
                                                                                                                                        type="number"
                                                                                                                                        class="form-control"
                                                                                                                                        name="total_mu"
                                                                                                                                        readonly>
                                                                                                                                </td>
                                                                                                                            </tr>


                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                    </div>
                                                                                    <div class="form-actions mt-10">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success  mr-10">
                                                                                            Save</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-default">Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Row Order-->
                                            </div>
                                            <div id="profile_6" class="tab-pane fade" role="tabpanel">
                                                <!-- Row Order-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default card-view">
                                                            <div class="panel-wrapper collapse in">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-xs-12">
                                                                            <div class="form-wrap">
                                                                                <form action="#">
                                                                                    <div class="form-body">
                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account mr-10"></i>Person's
                                                                                            Info</h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">First
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="firstName"
                                                                                                        class="form-control"
                                                                                                        placeholder="John doe">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This is inline
                                                                                                        help </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div
                                                                                                    class="form-group has-error">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Last
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="lastName"
                                                                                                        class="form-control"
                                                                                                        placeholder="12n">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This field has
                                                                                                        error. </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Gender</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Male
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Female
                                                                                                        </option>
                                                                                                    </select>
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        Select your
                                                                                                        gender </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Date
                                                                                                        of Birth</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Category</label>
                                                                                                    <select
                                                                                                        class="form-control"
                                                                                                        data-placeholder="Choose a Category"
                                                                                                        tabindex="1">
                                                                                                        <option
                                                                                                            value="Category 1">
                                                                                                            Category 1
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 2">
                                                                                                            Category 2
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 3">
                                                                                                            Category 5
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 4">
                                                                                                            Category 4
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Membership</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-0">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_5"
                                                                                                                    value="option1">
                                                                                                                <label
                                                                                                                    for="radio_5">Option
                                                                                                                    1</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_6"
                                                                                                                    value="option2">
                                                                                                                <label
                                                                                                                    for="radio_6">Option
                                                                                                                    2
                                                                                                                </label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <div class="seprator-block">
                                                                                        </div>

                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account-box mr-10"></i>address
                                                                                        </h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-12 ">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Street</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">City</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">State</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Post
                                                                                                        Code</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Country</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option>--Select
                                                                                                            your
                                                                                                            Country--
                                                                                                        </option>
                                                                                                        <option>India
                                                                                                        </option>
                                                                                                        <option>Sri
                                                                                                            Lanka
                                                                                                        </option>
                                                                                                        <option>USA
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-actions mt-10">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success  mr-10">
                                                                                            Save</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-default">Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Row Order-->
                                            </div>
                                            <div id="profile_7" class="tab-pane fade" role="tabpanel">
                                                <!-- Row Order-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default card-view">
                                                            <div class="panel-wrapper collapse in">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-xs-12">
                                                                            <div class="form-wrap">
                                                                                <form action="#">
                                                                                    <div class="form-body">
                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account mr-10"></i>Person's
                                                                                            Info</h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">First
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="firstName"
                                                                                                        class="form-control"
                                                                                                        placeholder="John doe">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This is inline
                                                                                                        help </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div
                                                                                                    class="form-group has-error">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Last
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="lastName"
                                                                                                        class="form-control"
                                                                                                        placeholder="12n">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This field has
                                                                                                        error. </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Gender</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Male
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Female
                                                                                                        </option>
                                                                                                    </select>
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        Select your
                                                                                                        gender </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Date
                                                                                                        of Birth</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Category</label>
                                                                                                    <select
                                                                                                        class="form-control"
                                                                                                        data-placeholder="Choose a Category"
                                                                                                        tabindex="1">
                                                                                                        <option
                                                                                                            value="Category 1">
                                                                                                            Category 1
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 2">
                                                                                                            Category 2
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 3">
                                                                                                            Category 5
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 4">
                                                                                                            Category 4
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Membership</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-0">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_5"
                                                                                                                    value="option1">
                                                                                                                <label
                                                                                                                    for="radio_5">Option
                                                                                                                    1</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_6"
                                                                                                                    value="option2">
                                                                                                                <label
                                                                                                                    for="radio_6">Option
                                                                                                                    2
                                                                                                                </label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <div class="seprator-block">
                                                                                        </div>

                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account-box mr-10"></i>address
                                                                                        </h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-12 ">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Street</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">City</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">State</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Post
                                                                                                        Code</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Country</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option>--Select
                                                                                                            your
                                                                                                            Country--
                                                                                                        </option>
                                                                                                        <option>India
                                                                                                        </option>
                                                                                                        <option>Sri
                                                                                                            Lanka
                                                                                                        </option>
                                                                                                        <option>USA
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-actions mt-10">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success  mr-10">
                                                                                            Save</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-default">Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Row Order-->
                                            </div>
                                            <div id="profile_8" class="tab-pane fade" role="tabpanel">
                                                <!-- Row Order-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default card-view">
                                                            <div class="panel-wrapper collapse in">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-xs-12">
                                                                            <div class="form-wrap">
                                                                                <form action="#">
                                                                                    <div class="form-body">
                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account mr-10"></i>Person's
                                                                                            Info</h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">First
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="firstName"
                                                                                                        class="form-control"
                                                                                                        placeholder="John doe">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This is inline
                                                                                                        help </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div
                                                                                                    class="form-group has-error">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Last
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="lastName"
                                                                                                        class="form-control"
                                                                                                        placeholder="12n">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This field has
                                                                                                        error. </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Gender</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Male
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Female
                                                                                                        </option>
                                                                                                    </select>
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        Select your
                                                                                                        gender </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Date
                                                                                                        of Birth</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Category</label>
                                                                                                    <select
                                                                                                        class="form-control"
                                                                                                        data-placeholder="Choose a Category"
                                                                                                        tabindex="1">
                                                                                                        <option
                                                                                                            value="Category 1">
                                                                                                            Category 1
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 2">
                                                                                                            Category 2
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 3">
                                                                                                            Category 5
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 4">
                                                                                                            Category 4
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Membership</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-0">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_5"
                                                                                                                    value="option1">
                                                                                                                <label
                                                                                                                    for="radio_5">Option
                                                                                                                    1</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_6"
                                                                                                                    value="option2">
                                                                                                                <label
                                                                                                                    for="radio_6">Option
                                                                                                                    2
                                                                                                                </label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <div class="seprator-block">
                                                                                        </div>

                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account-box mr-10"></i>address
                                                                                        </h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-12 ">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Street</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">City</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">State</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Post
                                                                                                        Code</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Country</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option>--Select
                                                                                                            your
                                                                                                            Country--
                                                                                                        </option>
                                                                                                        <option>India
                                                                                                        </option>
                                                                                                        <option>Sri
                                                                                                            Lanka
                                                                                                        </option>
                                                                                                        <option>USA
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-actions mt-10">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success  mr-10">
                                                                                            Save</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-default">Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Row Order-->
                                            </div>
                                            <div id="profile_9" class="tab-pane fade" role="tabpanel">
                                                <!-- Row Order-->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel panel-default card-view">
                                                            <div class="panel-wrapper collapse in">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-xs-12">
                                                                            <div class="form-wrap">
                                                                                <form action="#">
                                                                                    <div class="form-body">
                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account mr-10"></i>Person's
                                                                                            Info</h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">First
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="firstName"
                                                                                                        class="form-control"
                                                                                                        placeholder="John doe">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This is inline
                                                                                                        help </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div
                                                                                                    class="form-group has-error">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Last
                                                                                                        Name</label>
                                                                                                    <input type="text"
                                                                                                        id="lastName"
                                                                                                        class="form-control"
                                                                                                        placeholder="12n">
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        This field has
                                                                                                        error. </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Gender</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Male
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="">
                                                                                                            Female
                                                                                                        </option>
                                                                                                    </select>
                                                                                                    <span
                                                                                                        class="help-block">
                                                                                                        Select your
                                                                                                        gender </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Date
                                                                                                        of Birth</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control"
                                                                                                        placeholder="dd/mm/yyyy">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Category</label>
                                                                                                    <select
                                                                                                        class="form-control"
                                                                                                        data-placeholder="Choose a Category"
                                                                                                        tabindex="1">
                                                                                                        <option
                                                                                                            value="Category 1">
                                                                                                            Category 1
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 2">
                                                                                                            Category 2
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 3">
                                                                                                            Category 5
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="Category 4">
                                                                                                            Category 4
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Membership</label>
                                                                                                    <div
                                                                                                        class="radio-list">
                                                                                                        <div
                                                                                                            class="radio-inline pl-0">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_5"
                                                                                                                    value="option1">
                                                                                                                <label
                                                                                                                    for="radio_5">Option
                                                                                                                    1</label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="radio-inline">
                                                                                                            <span
                                                                                                                class="radio radio-info">
                                                                                                                <input
                                                                                                                    type="radio"
                                                                                                                    name="radio5"
                                                                                                                    id="radio_6"
                                                                                                                    value="option2">
                                                                                                                <label
                                                                                                                    for="radio_6">Option
                                                                                                                    2
                                                                                                                </label>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->

                                                                                        <div class="seprator-block">
                                                                                        </div>

                                                                                        <h6
                                                                                            class="txt-dark capitalize-font">
                                                                                            <i
                                                                                                class="zmdi zmdi-account-box mr-10"></i>address
                                                                                        </h6>
                                                                                        <hr class="light-grey-hr" />
                                                                                        <div class="row">
                                                                                            <div class="col-md-12 ">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Street</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">City</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">State</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                        <!-- /Row -->
                                                                                        <div class="row">
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Post
                                                                                                        Code</label>
                                                                                                    <input type="text"
                                                                                                        class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                            <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                    <label
                                                                                                        class="control-label mb-10">Country</label>
                                                                                                    <select
                                                                                                        class="form-control">
                                                                                                        <option>--Select
                                                                                                            your
                                                                                                            Country--
                                                                                                        </option>
                                                                                                        <option>India
                                                                                                        </option>
                                                                                                        <option>Sri
                                                                                                            Lanka
                                                                                                        </option>
                                                                                                        <option>USA
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!--/span-->
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-actions mt-10">
                                                                                        <button type="submit"
                                                                                            class="btn btn-success  mr-10">
                                                                                            Save</button>
                                                                                        <button type="button"
                                                                                            class="btn btn-default">Cancel</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Row Order-->
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <?php $this->load->view('req/mm_footer.php');?>
                <!-- /Footer -->

            </div>
            <!-- /Main Content -->

        </div>
        <!-- /#wrapper -->

        <!-- JavaScript -->
        <script type="text/javascript">
        window.onload = function() {
            document
                .getElementById(
                    'toko')
                .style.display =
                'none';
            document
                .getElementById(
                    'market_place'
                )
                .style.display =
                'none';
            document
                .getElementById(
                    'rowKredit'
                )
                .style.display =
                'none';
            document
                .getElementById(
                    'rowDP'
                )
                .style.display =
                'none';
            document
                .getElementById(
                    'rowNonCustom'
                )
                .style.display =
                'none';
            document
                .getElementById(
                    'rowCustom'
                )
                .style.display =
                'none';
        }

        function checkToko() {
            if (document
                .getElementById(
                    'radio_cabang')
                .checked) {
                document
                    .getElementById(
                        'toko')
                    .style.display =
                    'block';
            } else {
                document
                    .getElementById(
                        'toko')
                    .style.display =
                    'none';
            }

        }

        function checkOl() {
            if (document
                .getElementById(
                    'radio_ol')
                .checked) {
                document
                    .getElementById(
                        'market_place'
                    )
                    .style.display =
                    'block';
            } else {
                document
                    .getElementById(
                        'market_place'
                    )
                    .style.display =
                    'none';
            }

        }

        function checkKredit(
            select) {
            if (select.value ==
                "Kredit") {
                document
                    .getElementById(
                        'rowKredit'
                    ).style
                    .display =
                    "block";
            } else {
                document
                    .getElementById(
                        'rowKredit'
                    ).style
                    .display =
                    "none";
            }
        }

        function checkDP(
            select) {
            if (select.value ==
                "DP") {
                document
                    .getElementById(
                        'rowDP'
                    ).style
                    .display =
                    "block";
            } else {
                document
                    .getElementById(
                        'rowDP'
                    ).style
                    .display =
                    "none";
            }
        }

        function checkNonCustom() {
            if (document
                .getElementById(
                    'radio_nonCustom')
                .checked) {
                document
                    .getElementById(
                        'rowNonCustom'
                    )
                    .style.display =
                    'block';
            } else {
                document
                    .getElementById(
                        'rowNonCustom'
                    )
                    .style.display =
                    'none';
            }

        }

        function checkCustom() {
            if (document
                .getElementById(
                    'radio_custom')
                .checked) {
                document
                    .getElementById(
                        'rowCustom'
                    )
                    .style.display =
                    'block';
            } else {
                document
                    .getElementById(
                        'rowCustom'
                    )
                    .style.display =
                    'none';
            }

        }
        </script>




        <?php $this->load->view('req/mm_js.php');?>


    </body>

</html>