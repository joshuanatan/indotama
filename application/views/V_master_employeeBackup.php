<!-- Row Employee-->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default card-view">
            <div class="panel-heading">
                <div class="row mt-10 ">
                    <a href="<?php echo base_url();?>/Master/FormAddEmp"><button
                            class="btn btn-warning btn-anim pull-right" style="margin-right:30px !important"><i
                                class="fa fa-pencil"></i><span class="btn-text">Tambah Karyawan</span></button></a>
                </div>
            </div>
            <div class="panel-wrapper collapse in">
                <div class="panel-body">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table id="example" class="table table-hover display ßpb-30">
                                <thead>
                                    <tr>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>Toko</th>
                                        <th>Jabatan</th>
                                        <th>Email</th>
                                        <th>HP</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td><a href="<?php echo base_url(); ?>asset/img/user1_3.png"
                                                target="_blank"><img
                                                    src="<?php echo base_url(); ?>asset/img/user1_3.png" /></a></td>
                                        <td>Amin Sarimin</td>
                                        <td>MM Safety</td>
                                        <td>Kepala Gudang</td>
                                        <td>Amin_Sarimin@gmail.com</td>
                                        <td>08129966547854</td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-icon-anim btn-square" data-toggle="modal"
                                                data-target="#seeKaryawan"><i class="fa fa-eye"></i></button>
                                            <button class="btn btn-primary btn-icon-anim btn-square"><i
                                                    class="fa fa-pencil"></i></button>
                                            <button class="btn btn-danger btn-icon-anim btn-square"><i
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
<!-- Isi Modal Eye EMPLOYEE Button-->
<div class="modal fade" id="seeKaryawan" tabindex="-1" role="dialog" aria-labelledby="Detail Karyawan">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default contact-card card-view">
                        <div class="panel-heading bg-gradient">
                            <div class="pull-left">
                                <div class="pull-left user-img-wrap mr-15">
                                    <img class="card-user-img img-circle pull-left"
                                        src="<?php echo base_url(); ?>asset/img/user1_3.png" alt="user" />
                                </div>
                                <div class="pull-left user-detail-wrap">
                                    <span class="block card-user-name">
                                        Amin Sarimin
                                    </span>
                                    <span class="block card-user-desn">
                                        MM Safety
                                    </span>
                                    <span class="block card-user-desn">
                                        Kepala Gudang
                                    </span>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="panel-wrapper collapse in">
                            <div class="panel-body row">
                                <div class="user-others-details pl-15 pr-15">
                                    <div class="mb-5">
                                        <i class="fa fa-credit-card-alt inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">NPWP 15.1546.6452.121</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-credit-card-alt inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">NPWP 00.00.665.45236.11</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-phone-square inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">&nbsp;MOBILE 08129966547854</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-comment inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">&nbsp;EMAIL Amin_Sarimin@gmail.com</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-money inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">&nbsp;JABATAN Kepala Gudang</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-money inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">&nbsp;GAJI 5.000.000/span>
                                    </div>
                                </div>
                                <hr class="light-grey-hr mt-20 mb-20" />
                                <div class="user-others-details pl-15 pr-15">
                                    <div class="mb-15">
                                        <span class="inline-block txt-dark">ALAMAT</span><br>
                                        <span class="inline-block txt-dark">Lorem Ipsum is simply dummy text of the
                                            printing and typesetting industry. Lorem Ipsum has been the industry's
                                            standard dummy text ever since the 1500s. Kode Pos 12830</span>
                                    </div>
                                </div>
                                <hr class="light-grey-hr mt-20 mb-20" />
                                <div class="emp-detail pl-15 pr-15">
                                    <div class="mb-5">
                                        <i class="fa fa-bank inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">BANK Bank Central Asia</span>
                                    </div>
                                    <div class="mb-5">
                                        <i class="fa fa-bank inline-block mr-10"></i>
                                        <span class="inline-block txt-dark">ACCOUNT 567.235.896.125</span>
                                    </div>
                                </div>
                                <hr class="light-grey-hr mt-20 mb-20" />
                                <div class="emp-detail pl-15 pr-15">
                                    <div class="pull-left user-img-wrap">
                                        <img class="card-user-img" src="<?php echo base_url(); ?>asset/img/user1_3.png"
                                            alt="user" />
                                    </div>
                                    <div class="pull-left user-img-wrap" style="padding-left:150px">
                                        <img class="card-user-img" src="<?php echo base_url(); ?>asset/img/user1_3.png"
                                            alt="user" />
                                    </div>
                                    <div class="pull-left user-img-wrap" style="padding-left:150px">
                                        <img class="card-user-img" src="<?php echo base_url(); ?>asset/img/user1_3.png"
                                            alt="user" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Isi Modal Eye Button -->