
<?php
ob_start();
    $pdf = new Pdf_oc('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetTitle('ORDER CONFIRMATION');
    $pdf->SetTopMargin(30);
    $pdf->setFooterMargin(20);
    $pdf->SetAutoPageBreak(true,22);
    $pdf->SetAuthor('Author');
    $pdf->SetDisplayMode('real', 'default');
    $pdf->setPrintHeader(true);
      $pdf->setPrintFooter(true);
    $pdf->AddPage('P','A4');
    
    $fontname = TCPDF_FONTS::addTTFfont('../../../libraries/tcpdf/fonts/tahoma.ttf', 'TrueTypeUnicode', '', 96);
    $pdf->SetFont('Tahoma','', 10.5); //untuk font, liat dokumentasui

    $content='
    <div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
        View Invoice
        </h3>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box blue-hoki">
                    <div class="portlet-title">
                        <div class="caption">
                            <p><i class="fa fa-search"></i><?php echo $row_invoice["nomor"] ?> (Customer : <a href="index.php?customer-view=<?php echo $row_invoice["customer_id"] ?>" style="color:#fff"><?php echo $row_invoice["nama"]; ?></a>)</p>
                        </div>
                        <div class="actions">
                        <?php if($row_invoice["markup"] == 2){ ?>
                        	<div class="btn-group">
                                <button class="btn green" onclick="printDiv('areamarkup')"><i class="fa fa-print"></i> Print Markup</button>
                            </div>
                        <?php } ?>
                            <div class="btn-group">
                                <button class="btn red" onclick="printDiv('area')"><i class="fa fa-print"></i> Print</button>
                            </div>
                            <div class="btn-group">
                                <a class="btn default" href="index.php?invoice">
                                Back <i class="fa fa-arrow-left"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="" id="form_sample_1" class="horizontal-form" method="post">
                            <div class="form-body">
                                <h3 class="form-section">Invoice Info</h3>
                                <div class="row">
                                	<?php if($priv == 1){ ?>
                                	<div class="col-md-3">
                                    	<div class="form-group">
                                        <?php $sql_customer = mysql_query("SELECT * FROM customer ORDER BY nama DESC")or die(mysql_error()); ?>
										<label class="control-label">Customer
										</label>
                                        <select class="form-control select2me" name="customer">
                                            <option value="">Select...</option>
                                            <?php while($row_customer = mysql_fetch_array($sql_customer)) { ?>
                                            <option <?php if ($row_invoice["customer_id"] == $row_customer["id"]) { ?>selected<?php } ?> value="<?php echo $row_customer["id"] ?>"> <?php echo $row_customer["nama"] ?> (<?php echo $row_customer["perusahaan"]; ?>) </option>
                                            <?php } ?>
                                        </select>
                                        </div>
                                    </div>
                                    <!--/span-->
                                    <?php } ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Status Pembayaran</label>
                                            <div class="radio-list" data-error-container="#form_2_membership_error">
												<label class="radio-inline">
												<input type="radio" name="status" value="1" <?php if ($row_invoice["status"] == 1) { ?>checked<?php } ?> <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?>/>
												Full Payment </label>
												<label class="radio-inline">
												<input type="radio" name="status" value="2" <?php if ($row_invoice["status"] == 2) { ?>checked<?php } ?> <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?> />
												DP </label>
                                                <label class="radio-inline">
												<input type="radio" name="status" value="3" <?php if ($row_invoice["status"] == 0) { ?>checked<?php } ?> disabled/>
												Cancel </label>
											</div>
											<div id="form_2_membership_error">
											</div>
                                        </div>
                                    </div>
                                    <!--/span-->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label">Jumlah DP</label>
                                            <input type="text" id="dp" name="dp" class="form-control" value="<?php echo $row_invoice["pembayaran"] ?>" <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?>>
                                        </div>
                                    </div>
                                    <!--/span-->
                                    <div class="col-md-3">
                                        <div class="form-group">
										<label class="control-label">Tanggal Nota</label>
                                        <div class="input-group date date-picker" data-date-format="yyyy-mm-dd">
                                            <input type="text" class="form-control" value="<?php echo $row_invoice["tanggal"] ?>" name="tanggal" required <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?>>
                                            <span class="input-group-btn">
                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                        <!-- /input-group -->
									</div>
                                    </div>
                                    <!--/span-->
                                </div>
                                <!--/row-->       
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Catatan</label>
                                            <textarea class="form-control" rows="4" name="note" <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?>><?php echo $row_invoice["note"] ?></textarea>
                                        </div>
                                    </div>
                                    <!--/span-->
                                </div>
                                <!--/row-->     
                                <h3 class="form-section">Product Info</h3>
                                <table class="table">
                                    <tr>
                                        <td width="5%">No</td>
                                        <td width="20%">Produk</td>
                                        <td width="20%">Produk Custom</td>
                                        <td width="5%">Qty</td>
                                        <td width="5%">Qty Markup</td>
                                        <td width="12.5%">Harga</td>
                                        <td width="12.5%">Harga Markup</td>
                                        <td width="12.5%">Total</td>
                                        <td width="12.5%">Total Markup</td>
                                        <td width="5%">Action</td>
                                    </tr>
                                    <tbody>
                                    <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'")or die(mysql_error()); ?>
                                    <?php $total_arr = array(); $totalmarkup_arr = array(); $x = 1; while($row_listproduk = mysql_fetch_array($sql_listproduk)){ ?>
                                    <tr>
                                        <td><?php echo $x; ?></td>
                                        <td>
                                            <?php
                                            if ($row_listproduk["product_id"] == 0){
												echo '-';	
											}else{
												echo $row_listproduk["product_name"];	
											}
											?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($row_listproduk["product_id"] == 0){
												echo $row_listproduk["product_name"];	
											}else{
												echo '-';	
											}
											?>
                                        </td>
                                        <td><?php echo $row_listproduk["quantity"]; ?></td>
                                        <td><?php echo $row_listproduk["quantity_markup"]; ?></td>
                                        <td><?php echo 'Rp. ' . number_format(($row_listproduk["harga"]),0,',','.');  ?></td>
                                        <td><?php 
											if($row_listproduk["harga_markup"] == 0){
												echo '-';
											}else{
												echo 'Rp. ' . number_format(($row_listproduk["harga_markup"]),0,',','.');
											}
										  	?>
                                        </td>
                                        <td>
											<?php
											$total = $row_listproduk["harga"] * $row_listproduk["quantity"];
											$total_arr[] = $total;
											echo 'Rp. ' . number_format(($total),0,',','.');  
											?>
                                        </td>
                                        <td>
											<?php 
											if($row_listproduk["harga_markup"] == 0){
												echo '-';
											}else{
												$totalmarkup = $row_listproduk["harga_markup"] * $row_listproduk["quantity_markup"];
												$totalmarkup_arr[] = $totalmarkup;
												echo 'Rp. ' . number_format(($totalmarkup),0,',','.');
											}
										  	?>
										</td>
                                        <td>
                                        <a class="btn default btn-xs red" data-toggle="confirmation" data-original-title="Are you sure ?" data-placement="left" data-href="index.php?item-delete=<?php echo $row_listproduk["id"] ?>">
                                     <i class="fa fa-trash-o"></i></a>
                                        </td>
                                    </tr>
                                    <?php $x++; } ?>
                                    </tbody>
                                    <tr>
                                        <td width="5%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="12.5%">&nbsp;</td>
                                        <td width="12.5%" style="text-align:right"><h4 style="font-weight:bold">TOTAL</h4></td>
                                        <td width="12.5%"><h4 style="font-weight:bold"><?php $total_all = array_sum($total_arr); echo Rp.  . number_format(($total_all),0,',','.');  ?></h4></td>
                                        <td width="12.5%"><h4 style="font-weight:bold">
										
                                        </h4></td>
                                        <td width="5%"></td>
                                    </tr>
                                    <?php if($row_invoice["pembayaran"] != 0){ ?>
                                    <tr>
                                        <td width="5%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="12.5%">&nbsp;</td>
                                        <td width="12.5%" style="text-align:right"><h4 style="font-weight:bold">DP</h4></td>
                                        <td width="12.5%"><h4 style="font-weight:bold">,0,',','.');  ?></h4></td>
                                        <td width="12.5%">&nbsp;</td>
                                        <td width="5%"></td>
                                    </tr>
                                    <tr>
                                        <td width="5%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="20%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="5%">&nbsp;</td>
                                        <td width="12.5%">&nbsp;</td>
                                        <td width="12.5%" style="text-align:right"><h4 style="font-weight:bold">SISA BAYAR</h4></td>
                                        <td width="12.5%"><h4 style="font-weight:bold"><?php $sisabayar = $row_invoice["total"] - $row_invoice["pembayaran"]; echo  . number_format(($sisabayar),0,',','.');  ?> <?php if($row_invoice["status"] == 1){ ?>(LUNAS)<?php } ?></h4></td>
                                        <td width="12.5%">&nbsp;</td>
                                        <td width="5%"></td>
                                    </tr>
                                    <?php } ?>
                                </table> 
                            	<h3 class="form-section">Tambah Produk</h3>
                                <table class="table">
                                    <tr>
                                        <td width="2%">No</td>
                                        <td width="15%">Produk</td>
                                        <td width="15%">Produk Custom</td>
                                        <td width="7%">Qty</td>
                                        <td width="12%">Harga</td>
                                        <td width="7%">Qty MU</td>
                                        <td width="12%">Harga Markup</td>
                                    </tr>
                                    <tbody>
                                    <?php $x = 1; while($x < 6){ ?>
                                    <tr>
                                        <td><?php echo $x; ?></td>
                                        <td>
                                            <?php $sql_produk = mysql_query("SELECT stok.id AS id, jenis_barang.nama, stok.stok FROM stok LEFT JOIN jenis_barang ON jenis_barang.id = stok.jenisbarang_id WHERE toko = GROUP BY stok.jenisbarang_id")or die(mysql_error()); ?>
                                            <select class="form-control select2me" name="produk_<?php echo $x; ?>">
                                                <option value="0">Select...</option>
                                                <?php while($row_produk = mysql_fetch_array($sql_produk)) { ?>
                                                <option value="<?php echo $row_produk["id"] ?>"> <?php echo $row_produk["nama"] ?> (Stok Tersedia: <?php echo $row_produk["stok"] ?>) </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td><input class="form-control" type="text" name="produk_custom_<?php echo $x; ?>" /></td>
                                        <td><input class="form-control" type="text" name="qty_<?php echo $x; ?>" id="id_qty_<?php echo $x; ?>" /></td>
                                        <td><input class="form-control" type="text" name="harga_<?php echo $x; ?>" id="id_harga_<?php echo $x; ?>" /></td>
                                        <td><input class="form-control" type="text" name="qty_markup_<?php echo $x; ?>" id="id_qty_markup_<?php echo $x; ?>" /></td>
                                        <td><input class="form-control" type="text" name="harga_markup_<?php echo $x; ?>" id="id_harga_markup_<?php echo $x; ?>" value="0" /></td>
                                    </tr>
                                    <?php $x++; } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-actions right">
                                <button type="button" class="btn default" onClick="parent.location='index.php?invoice'">Cancel</button>
                                <button type="submit" name="submit_save" class="btn blue" <?php if($row_invoice["status"] == 0){ ?> disabled <?php } ?>><i class="fa fa-check"></i> Save</button>
                            </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- PRINT NOTA DI BAWAH INI -->
		<?php 
        $sql_datatoko = mysql_query("SELECT * FROM administrator WHERE id = '$toko_id'")or die(mysql_error());
        $row_datatoko = mysql_fetch_array($sql_datatoko);
        ?>
        <div class="row">
            <div class="col-md-12" style="visibility:hidden">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet-body" id="area">
                        <table style="font-size:11px; <?php if($row_invoice["status"] == 2){ ?>color:#42b549;<?php } ?>" class="table" width="100%">
                            <tr>
                                <td colspan="3" style="border-top:none !important">
                                    <img style="margin-left:-5px" src="assets/admin/layout/img/logo.png" alt="" width="200px"/>
                                    <p><?php echo $row_datatoko["kop"] ?></p>
                                </td>
                                <td colspan="2" style="text-align:right; border-top:none;">
                                    <p style="margin-top:50px">Jakarta, <?php echo $row_invoice["tanggal"] ?>,<br /><?php echo $row_invoice["nama"] ?><br /><?php echo $row_invoice["perusahaan"] ?><br /><?php echo $row_invoice["alamat"] ?><br /><?php echo $row_invoice["telepon"] ?></p>
                                </td>        
                            </tr>
                            <tr>
                                <td colspan="5"><p">INVOICE NO : <?php echo $row_invoice["nomor"] ?></p></td>
                            </tr>
                            <tr>
                                <td width="15%">No</td>
                                <td width="35%">Produk</td>
                                <td width="5%">Qty</td>
                                <td width="20%">Harga</td>
                                <td width="25%">Total</td>
                            </tr>
                            <tbody>
                            <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'")or die(mysql_error()); ?>
                            <?php $total_arr = array(); $totalmarkup_arr = array(); $x = 1; while($row_listproduk = mysql_fetch_array($sql_listproduk)){ ?>
                            <tr>
                                <td><?php echo $x; ?></td>
                                <td>
                                    <?php
                                        echo $row_listproduk["product_name"];
                                    ?>
                                </td>
                                <td><?php echo $row_listproduk["quantity"]; ?></td>
                                <td><?php echo 'Rp. ' . number_format(($row_listproduk["harga"]),0,',','.');  ?></td>
                                <td>
                                    <?php
                                    $total = $row_listproduk["harga"] * $row_listproduk["quantity"];
                                    $total_arr[] = $total;
                                    echo 'Rp. ' . number_format(($total),0,',','.');  
                                    ?>
                                </td>
                            </tr>
                            <?php $x++; } ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">SUB TOTAL</td>
                                <td style="font-weight:bold"><?php $total_all = array_sum($total_arr); echo 'Rp. ' . number_format(($total_all),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">PPN 10%</td>
                                <td style="font-weight:bold"><?php $ppn = $total_all / 10; echo 'Rp. ' . number_format(($ppn),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">TOTAL</td>
                                <td style="font-weight:bold"><?php $totalakhir = $total_all + $ppn; echo 'Rp. ' . number_format(($totalakhir),0,',','.');  ?></td>
                            </tr>
                            <?php if($row_invoice["pembayaran"] != 0){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">DP</td>
                                <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice["pembayaran"]),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">SISA BAYAR</td>
                                <td style="font-weight:bold"><?php $sisabayar = $row_invoice["total"] - $row_invoice["pembayaran"]; echo 'Rp. ' . number_format(($sisabayar),0,',','.');  ?> <?php if($row_invoice["status"] == 1){ ?>(LUNAS)<?php } ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="1" width="200px" style="border-bottom:none"><p style="font-weight:bold;">Tanda Terima,</p></td>
                                <td colspan="3" style="border-bottom:none"><p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p></td>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold">Hormat Kami,</p></td>
                            </tr>
                        </table>
                        <table style="font-size:11px; color:#CC0000;" class="table pb" width="100%">
                            <tr">
                                <td colspan="3" style="border-top:none !important">
                                    <img style="margin-left:-5px" src="assets/admin/layout/img/logo.png" alt="" width="200px"/>
                                    <p><?php echo $row_datatoko["kop"] ?></p>
                                </td>
                                <td colspan="2" style="text-align:right; border-top:none;">
                                    <p style="margin-top:50px">Jakarta, <?php echo $row_invoice["tanggal"] ?>,<br /><?php echo $row_invoice["nama"] ?><br /><?php echo $row_invoice["perusahaan"] ?><br /><?php echo $row_invoice["alamat"] ?><br /><?php echo $row_invoice["telepon"] ?></p>
                                </td>        
                            </tr>
                            <tr>
                                <td colspan="5"><p">INVOICE NO : <?php echo $row_invoice["nomor"] ?></p></td>
                            </tr>
                            <tr>
                                <td width="15%">No</td>
                                <td width="35%">Produk</td>
                                <td width="5%">Qty</td>
                                <td width="20%">Harga</td>
                                <td width="25%">Total</td>
                            </tr>
                            <tbody>
                            <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'")or die(mysql_error()); ?>
                            <?php $total_arr = array(); $totalmarkup_arr = array(); $x = 1; while($row_listproduk = mysql_fetch_array($sql_listproduk)){ ?>
                            <tr>
                                <td><?php echo $x; ?></td>
                                <td>
                                    <?php
                                        echo $row_listproduk["product_name"];
                                    ?>
                                </td>
                                <td><?php echo $row_listproduk["quantity"]; ?></td>
                                <td><?php echo 'Rp. ' . number_format(($row_listproduk["harga"]),0,',','.');  ?></td>
                                <td>
                                    <?php
                                    $total = $row_listproduk["harga"] * $row_listproduk["quantity"];
                                    $total_arr[] = $total;
                                    echo 'Rp. ' . number_format(($total),0,',','.');  
                                    ?>
                                </td>
                            </tr>
                            <?php $x++; } ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">TOTAL</td>
                                <td style="font-weight:bold"><?php $total_all = array_sum($total_arr); echo 'Rp. ' . number_format(($total_all),0,',','.');  ?></td>
                            </tr>
                            <?php if($row_invoice["pembayaran"] != 0){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">DP</td>
                                <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice["pembayaran"]),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">SISA BAYAR</td>
                                <td style="font-weight:bold"><?php $sisabayar = $row_invoice["total"] - $row_invoice["pembayaran"]; echo 'Rp. ' . number_format(($sisabayar),0,',','.');  ?> <?php if($row_invoice["status"] == 1){ ?>(LUNAS)<?php } ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold;">Tanda Terima,</p></td>
                                <td colspan="3" style="border-bottom:none"><p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p></td>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold">Hormat Kami,</p></td>
                            </tr>
                        </table>
                        <table style="font-size:11px; color:#1875D7" class="table pb" width="100%">
                            <tr">
                                <td colspan="3" style="border-top:none !important">
                                    <img style="margin-left:-5px" src="assets/admin/layout/img/logo.png" alt="" width="200px"/>
                                    <p><?php echo $row_datatoko["kop"] ?></p>
                                </td>
                                <td colspan="2" style="text-align:right; border-top:none;">
                                    <p style="margin-top:50px">Jakarta, <?php echo $row_invoice["tanggal"] ?>,<br /><?php echo $row_invoice["nama"] ?><br /><?php echo $row_invoice["perusahaan"] ?><br /><?php echo $row_invoice["alamat"] ?><br /><?php echo $row_invoice["telepon"] ?></p>
                                </td>        
                            </tr>
                            <tr>
                                <td colspan="5"><p">INVOICE NO : <?php echo $row_invoice["nomor"] ?></p></td>
                            </tr>
                            <tr>
                                <td width="15%">No</td>
                                <td width="35%">Produk</td>
                                <td width="5%">Qty</td>
                                <td width="20%">Harga</td>
                                <td width="25%">Total</td>
                            </tr>
                            <tbody>
                            <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'")or die(mysql_error()); ?>
                            <?php $total_arr = array(); $totalmarkup_arr = array(); $x = 1; while($row_listproduk = mysql_fetch_array($sql_listproduk)){ ?>
                            <tr>
                                <td><?php echo $x; ?></td>
                                <td>
                                    <?php
                                        echo $row_listproduk["product_name"];
                                    ?>
                                </td>
                                <td><?php echo $row_listproduk["quantity"]; ?></td>
                                <td><?php echo 'Rp. ' . number_format(($row_listproduk["harga"]),0,',','.');  ?></td>
                                <td>
                                    <?php
                                    $total = $row_listproduk["harga"] * $row_listproduk["quantity"];
                                    $total_arr[] = $total;
                                    echo 'Rp. ' . number_format(($total),0,',','.');  
                                    ?>
                                </td>
                            </tr>
                            <?php $x++; } ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">TOTAL</td>
                                <td style="font-weight:bold"><?php $total_all = array_sum($total_arr); echo 'Rp. ' . number_format(($total_all),0,',','.');  ?></td>
                            </tr>
                            <?php if($row_invoice["pembayaran"] != 0){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">DP</td>
                                <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice["pembayaran"]),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">SISA BAYAR</td>
                                <td style="font-weight:bold"><?php $sisabayar = $row_invoice["total"] - $row_invoice["pembayaran"]; echo 'Rp. ' . number_format(($sisabayar),0,',','.');  ?> <?php if($row_invoice["status"] == 1){ ?>(LUNAS)<?php } ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold;">Tanda Terima,</p></td>
                                <td colspan="3" style="border-bottom:none"><p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p></td>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold">Hormat Kami,</p></td>
                            </tr>
                        </table>
                </div>
            </div>            
        </div>
        <?php if($row_invoice["markup"] == 2){ ?>
        <div class="row">
            <div class="col-md-12" style="visibility:hidden">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet-body" id="areamarkup">
                        <table style="font-size:11px;" class="table" width="100%">
                            <tr>
                                <td colspan="3" style="border-top:none !important">
                                    <img style="margin-left:-5px" src="assets/admin/layout/img/logo.png" alt="" width="200px"/>
                                    <p><?php echo $row_datatoko["kop"] ?></p>
                                </td>
                                <td colspan="2" style="text-align:right; border-top:none;">
                                    <p style="margin-top:50px">Jakarta, <?php echo $row_invoice["tanggal"] ?>,<br /><?php echo $row_invoice["nama"] ?><br /><?php echo $row_invoice["perusahaan"] ?><br /><?php echo $row_invoice["alamat"] ?><br /><?php echo $row_invoice["telepon"] ?></p>
                                </td>        
                            </tr>
                            <tr>
                                <td colspan="5"><p">INVOICE NO : <?php echo $row_invoice["nomor"] ?></p></td>
                            </tr>
                            <tr>
                                <td width="15%">No</td>
                                <td width="35%">Produk</td>
                                <td width="5%">Qty</td>
                                <td width="20%">Harga</td>
                                <td width="25%">Total</td>
                            </tr>
                            <tbody>
                            <?php $sql_listproduk = mysql_query("SELECT * FROM invoice_product WHERE invoice_id = '$id'")or die(mysql_error()); ?>
                            <?php $total_arr = array(); $totalmarkup_arr = array(); $x = 1; while($row_listproduk = mysql_fetch_array($sql_listproduk)){ ?>
                            <tr>
                                <td><?php echo $x; ?></td>
                                <td>
                                    <?php
                                        echo $row_listproduk["product_name"];
                                    ?>
                                </td>
                                <td><?php echo $row_listproduk["quantity_markup"]; ?></td>
                                <td><?php echo 'Rp. ' . number_format(($row_listproduk["harga_markup"]),0,',','.');  ?></td>
                                <td>
                                    <?php
                                    $total = $row_listproduk["harga_markup"] * $row_listproduk["quantity_markup"];
                                    $total_arr[] = $total;
                                    echo 'Rp. ' . number_format(($total),0,',','.');  
                                    ?>
                                </td>
                            </tr>
                            <?php $x++; } ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">TOTAL</td>
                                <td style="font-weight:bold"><?php $total_all = array_sum($total_arr); echo 'Rp. ' . number_format(($total_all),0,',','.');  ?></td>
                            </tr>
                            <?php if($row_invoice["pembayaran"] != 0){ ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">DP</td>
                                <td style="font-weight:bold"><?php echo 'Rp. ' . number_format(($row_invoice["pembayaran"]),0,',','.');  ?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="font-weight:bold">SISA BAYAR</td>
                                <td style="font-weight:bold"><?php $sisabayar = $total_all - $row_invoice["pembayaran"]; echo 'Rp. ' . number_format(($sisabayar),0,',','.');  ?> <?php if($row_invoice["status"] == 1){ ?>(LUNAS)<?php } ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold;">Tanda Terima,</p></td>
                                <td colspan="3" style="border-bottom:none"><p style="text-align:center; font-weight:bold">PERHATIAN !!!<br />Barang-barang yang sudah dibeli<br />tidak dapat ditukar/dikembalikan.</p></td>
                                <td colspan="1" style="border-bottom:none"><p style="font-weight:bold">Hormat Kami,</p></td>
                            </tr>
                        </table>
                </div>
            </div>            
        </div>
        <?php } ?>
        <!-- END PAGE CONTENT-->
    </div>
</div>
';
$pdf->writeHTML($content);
//echo $content;
$pdf->SetFont('MonotypeCorsivai','', 24);
$content = $this->session->id_user;
$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya

$pdf->SetFont('Tahoma','', 10.5);
$content ='PT LEITER INDONESIA';
$pdf->writeHTML($content);
    //$obj_pdf->SetFont(Courier','', 8); //untuk font, liat dokumentasui
    //$pdf->writeHTML($content); //yang keluarin html nya. Setfont nya harus diatas kontennya
    //$pdf->Write(5, 'Contoh Laporan PDF dengan CodeIgniter + tcpdf');
    $pdf->Output('contoh1.pdf', 'I');
?>