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
                <div class="container-fluid">
                    <!-- Row -->
                    <div class="row mt-20">
                        <div class="col-lg-12 col-sm-12">
                            <div class="panel panel-default card-view">
                                <div class="panel-heading bg-gradient">
                                    <div class="pull-left">
                                        <h6 class="panel-title txt-light">Master</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-wrapper collapse in">
                                    <div class="panel-body">
                                        <div class="tab-struct custom-tab-1">
                                            <ul role="tablist" class="nav nav-tabs" id="myTabs_7">
                                                <li <?php if($this->input->get('tab', TRUE)=="tabCab"){echo "class='active'";}
                                                elseif($this->input->get('tab', TRUE)=="tabSup"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabCust"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabEmp"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabGud"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabNama"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabJenis"){echo "";}
                                                else{echo "class='active'";}?> role="presentation"><a
                                                        aria-expanded="true" data-toggle="tab" role="tab"
                                                        id="home_tab_7" href="#home_7"><i
                                                            class="fa fa-building"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Cabang</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabSup"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation"><a data-toggle="tab" id="profile_tab_9"
                                                        role="tab" href="#profile_9" aria-expanded="false"><i
                                                            class="fa fa-truck"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Supplier</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabCust"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation" class=""><a data-toggle="tab"
                                                        id="profile_tab_10" role="tab" href="#profile_10"
                                                        aria-expanded="false"><i class="fa fa-users"></i><span
                                                            class="right-nav-text"
                                                            style="margin-left:20px">Customer</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabEmp"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation"><a data-toggle="tab" id="profile_tab_11"
                                                        role="tab" href="#master_emp" aria-expanded="false"><i
                                                            class="fa fa-database"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Karyawan</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabGud"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation"><a data-toggle="tab" id="profile_tab_12"
                                                        role="tab" href="#profile_12" aria-expanded="false"><i
                                                            class="fa fa-fax"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Gudang</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabNama"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation"><a data-toggle="tab" id="profile_tab_13"
                                                        role="tab" href="#profile_13" aria-expanded="false"><i
                                                            class="fa fa-folder"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Nama
                                                            Akun</span></a></li>
                                                <li <?php if($this->input->get('tab', TRUE)=="tabJenis"){echo "class='active'";}else{echo "";}?>
                                                    role="presentation"><a data-toggle="tab" id="profile_tab_13"
                                                        role="tab" href="#profile_14" aria-expanded="false"><i
                                                            class="fa fa-folder"></i><span class="right-nav-text"
                                                            style="margin-left:20px">Jenis
                                                            Product</span></a></li>
                                            </ul>
                                            <div class="tab-content" id="myTabContent_7">
                                                <div id="home_7" class="tab-pane fade <?php if($this->input->get('tab', TRUE)=="tabCab"){echo "active in";}
                                                elseif($this->input->get('tab', TRUE)=="tabSup"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabSup"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabCust"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabEmp"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabGud"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabNama"){echo "";}
                                                elseif($this->input->get('tab', TRUE)=="tabJenis"){echo "";}
                                                else{echo "active in";}?>" role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah
                                                                                    Cabang</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display  pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Cabang</th>
                                                                                            <th>Alamat</th>
                                                                                            <th>Telepon</th>
                                                                                            <th>Email</th>
                                                                                            <th>Kepala Cabang</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>Jakarta Maju Mandiri
                                                                                            </td>
                                                                                            <td>LTC Jakarta</td>
                                                                                            <td>+6281266252486</td>
                                                                                            <td>jktmm@msm.co.id</td>
                                                                                            <td>Andre</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Jambi Maju Mandiri</td>
                                                                                            <td>Jambi</td>
                                                                                            <td>+6281266252486</td>
                                                                                            <td>jmm@msm.co.id</td>
                                                                                            <td>Santo</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Surabaya Maju Mandiri
                                                                                            </td>
                                                                                            <td>Surabaya</td>
                                                                                            <td>+6281266252486</td>
                                                                                            <td>smm@msm.co.id</td>
                                                                                            <td>Rudi</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                                <div id="profile_9" class="tab-pane fade " role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah
                                                                                    Supplier</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display  pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Nama</th>
                                                                                            <th>Perusahaan</th>
                                                                                            <th>Email</th>
                                                                                            <th>Telepon</th>
                                                                                            <th>Alamat</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>Priyani Ilyas</td>
                                                                                            <td>PT. Lgrande Global
                                                                                                Teknologindo</td>
                                                                                            <td>priyani.ilyas@lgt-indonesia.com
                                                                                            </td>
                                                                                            <td>+6281266252486</td>
                                                                                            <td>Jakarta</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                                <div id="profile_10" class="tab-pane fade" role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah
                                                                                    Customer</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display  pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Nama</th>
                                                                                            <th>Perusahaan</th>
                                                                                            <th>Email</th>
                                                                                            <th>Telepon</th>
                                                                                            <th>Alamat</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>Widi Wijayanto</td>
                                                                                            <td>PT.Sucofindo</td>
                                                                                            <td>widiw@sucofindo.co.id
                                                                                            </td>
                                                                                            <td>081255675434</td>
                                                                                            <td>Jakarta</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                                <div id="master_emp"
                                                    class="tab-pane fade <?php if($this->input->get('tab', TRUE)=="tabEmp"){echo "active in";}else{echo "";}?>"
                                                    role="tabpanel">
                                                    <?php $this->load->view('master_employee.php');?>
                                                </div>

                                                <div id="profile_12" class="tab-pane fade" role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah
                                                                                    Gudang</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Kode Gudang</th>
                                                                                            <th>Gudang</th>
                                                                                            <th>Alamat</th>
                                                                                            <th>Kota</th>
                                                                                            <th>Telepon</th>
                                                                                            <th>PIC</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>Pus01</td>
                                                                                            <td>LTC Lantai 2</td>
                                                                                            <td>Hayam Wuruk Sreet No.127
                                                                                            </td>
                                                                                            <td>6287889</td>
                                                                                            <td>Amin Sarimin</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Pus02</td>
                                                                                            <td>LTC Lantai 3</td>
                                                                                            <td>Hayam Wuruk Sreet No.127
                                                                                            </td>
                                                                                            <td>6287890</td>
                                                                                            <td>Sutoto</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Pus03</td>
                                                                                            <td>LTC Lantai 3</td>
                                                                                            <td>Hayam Wuruk Sreet No.127
                                                                                            </td>
                                                                                            <td>6287889</td>
                                                                                            <td>Sulaiman Adi</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Cab01</td>
                                                                                            <td>Benhil</td>
                                                                                            <td>Hayam Wuruk Sreet No.127
                                                                                            </td>
                                                                                            <td>6287889</td>
                                                                                            <td>Sulaiman Adi</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                                <div id="profile_13" class="tab-pane fade" role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah Kode
                                                                                    Akun</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Kode Akun</th>
                                                                                            <th>Kode Akun Induk</th>
                                                                                            <th>Nama Akun</th>
                                                                                            <th>Tipe Akun</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>

                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>1000.00</td>
                                                                                            <td>Kas & Bank</td>
                                                                                            <td>Aktiva Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>1000.01</td>
                                                                                            <td>1000.00</td>
                                                                                            <td>Kas Mandiri</td>
                                                                                            <td>Aktiva Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>1000.02</td>
                                                                                            <td>1000.00</td>
                                                                                            <td>Deposito Mandiri</td>
                                                                                            <td>Aktiva Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>1100.00</td>
                                                                                            <td>Piutang</td>
                                                                                            <td>Aktiva Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>1100.01</td>
                                                                                            <td>1100.00</td>
                                                                                            <td>Piutang Karyawan</td>
                                                                                            <td>Aktiva Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>2100.00</td>
                                                                                            <td>Hutang</td>
                                                                                            <td>Kewajiban Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>2100.01</td>
                                                                                            <td>2100.00</td>
                                                                                            <td>Hutang Pembelian
                                                                                                Supplier</td>
                                                                                            <td>Kewajiban Lancar</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>3000.00</td>
                                                                                            <td>Modal</td>
                                                                                            <td>Ekuitas</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>3000.01</td>
                                                                                            <td>3000.00</td>
                                                                                            <td>Deviden</td>
                                                                                            <td>Ekuitas</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>4000.00</td>
                                                                                            <td>Pendapatan</td>
                                                                                            <td>Pendapatan</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>4000.00</td>
                                                                                            <td>4000.01</td>
                                                                                            <td>Penjualan</td>
                                                                                            <td>Pendapatan</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>4000.00</td>
                                                                                            <td>4000.02</td>
                                                                                            <td>Retur Penjualan</td>
                                                                                            <td>Pendapatan</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>5000.00</td>
                                                                                            <td>COGS</td>
                                                                                            <td>Harga Pokok Penjualan
                                                                                            </td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>-</td>
                                                                                            <td>6000.00</td>
                                                                                            <td>Beban</td>
                                                                                            <td>Beban</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>6000.01</td>
                                                                                            <td>6000.00</td>
                                                                                            <td>Beban Gaji</td>
                                                                                            <td>Beban</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>6000.02</td>
                                                                                            <td>6000.00</td>
                                                                                            <td>Beban Lembur</td>
                                                                                            <td>Beban</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                                <div id="profile_14" class="tab-pane fade" role="tabpanel">
                                                    <!-- Row -->
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="panel panel-default card-view">
                                                                <div class="panel-heading">
                                                                    <div class="row mt-10 ">
                                                                        <a href="#"><button
                                                                                class="btn btn-warning btn-anim pull-right"
                                                                                style="margin-right:30px !important"><i
                                                                                    class="fa fa-pencil"></i><span
                                                                                    class="btn-text">Tambah Jenis
                                                                                    Produk</span></button></a>
                                                                    </div>
                                                                </div>
                                                                <div class="panel-wrapper collapse in">
                                                                    <div class="panel-body">
                                                                        <div class="table-wrap">
                                                                            <div class="table-responsive">
                                                                                <table id="example"
                                                                                    class="table table-hover display  pb-30">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Jenis Produk</th>
                                                                                            <th>Aksi</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td>Coverall</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Sepatu</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Rompi</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td>Bodyharness</td>
                                                                                            <td class="text-center">
                                                                                                <button
                                                                                                    class="btn btn-primary btn-icon-anim btn-square"><i
                                                                                                        class="fa fa-pencil"></i></button>
                                                                                                <button
                                                                                                    class="btn btn-danger btn-icon-anim btn-square"><i
                                                                                                        class="icon-trash"></i></button>
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
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Row -->

                    <!-- Footer -->
                    <?php $this->load->view('req/mm_footer.php');?>
                    <!-- /Footer -->
                </div>
            </div>
            <!-- /Main Content -->

        </div>
        <!-- /#wrapper -->

        <!-- JavaScript -->

        <?php $this->load->view('req/mm_js.php');?>


    </body>

</html>