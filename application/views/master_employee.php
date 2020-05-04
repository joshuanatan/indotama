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
                                            <button class="btn btn-info btn-icon-anim btn-square"
                                                onclick="window.location.href='<?php echo base_url();?>Master/formViewEmp';"><i
                                                    class="fa fa-eye"></i></button>
                                            <button class="btn btn-primary btn-icon-anim btn-square"
                                                onclick="window.location.href='<?php echo base_url();?>Master/formEdtEmp';"><i
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