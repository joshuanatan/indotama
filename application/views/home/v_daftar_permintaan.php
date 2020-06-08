<div class="row">
    <?php for($a = 0; $a<4; $a++):?>
    <div class="col-lg-3 col-sm-12">
        <div class="thumbnail">
            <div class="caption text-center">
                <div class="position-relative">
                    <img src="https://az818438.vo.msecnd.net/icons/slack.png" style="width:50%;height:50%;" />
                </div>
                <h4 id="thumbnail-label">Nama Barang</h4>
                <p>10/50</p>
                <h5>Item Given</h5>
                <div class = "table-responsive">
                    <table class = "table table-striped table-bordered">
                        <thead>
                            <th>Date Given</th>
                            <th>Amount (pcs)</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>31/12/2020</td>
                                <td>1456</td>
                                <td><i class = 'text-danger md-trash'></i></td>
                            </tr>
                            <tr>
                                <td>-</td>
                                <td>-</td>
                                <td><i class = 'text-danger md-trash'></td>
                            </tr>
                            <tr>
                                <td>-</td>
                                <td>-</td>
                                <td><i class = 'text-danger md-trash'></td>
                            </tr>
                            <tr>
                                <td colspan = 3>See More</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <h5 align = "center">Jumlah Beri:</h5>
            <div style = "margin:6px;padding:0" class = "form-group col-lg-5">
                <input type = "text" class = "form-control form-control-sm">
            </div>
            <div style = "margin:6px;padding:0" class = "form-group col-lg-3">
                <select class = "form-control form-control-sm"></select>
            </div>
            <div style = "margin:6px;padding:0" class = "form-group col-lg-1">
                <button type = "button" class = "btn btn-primary btn-sm md-check"></button>
            </div>
            <div class = "clearfix"></div>
            <div class="caption card-footer text-center">
                <p align = "center"><strong>Maju Mandiri - Taman Anggrek</strong></p>
                <p align = "center">Requested: 18/02/2020</p>
            </div>
        </div>
    </div>
    <?php endfor;?>
    <p align = "center" href = "<?php echo base_url();?>">See More</p>
</div>
<hr/>