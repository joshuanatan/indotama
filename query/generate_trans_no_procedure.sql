drop procedure if exists generate_trans_no;
delimiter $$
create procedure generate_trans_no(
	in id_cabang_in int,
    in jenis_trans varchar(15), /*pembelian/penjualan/retur*/
    in custom_tgl varchar(20),
    out trans_no varchar(100),
    out latest_no int
)
begin
	set @nomor = 0;
    if custom_tgl = "-"
    then
		set @bulan = convert(month(current_date),unsigned);
		set @tahun = convert(year(current_date),unsigned);
		set @tgl = convert(day(current_date),unsigned);
	else
		set @tahun = convert(substring_index(custom_tgl,"-",1),unsigned);
        set @bulan = convert(substring_index(substring_index(custom_tgl,"-",2),"-",-1),unsigned);
        set @tgl = convert(substring_index(custom_tgl,"-",-1),unsigned);
    end if;
    
    set @kode_cabang = "";
    if jenis_trans = "pembelian"
    then 
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_pembelian
		where bln_control = @bulan
		and thn_control = @tahun
		and pem_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "penjualan"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_penjualan
		where bln_control = @bulan
		and thn_control = @tahun
		and penj_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "retur"
	then
		select ifnull(max(mstr_retur.no_control),0)+1 into @nomor
        from mstr_retur
        inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
		where mstr_retur.bln_control = @bulan
		and mstr_retur.thn_control = @tahun
		and retur_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "pengiriman"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_pengiriman
		where bln_control = @bulan
		and thn_control = @tahun
		and pengiriman_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	elseif jenis_trans = "penerimaan"
    then
		select ifnull(max(no_control),0)+1 into @nomor
        from mstr_penerimaan
		where bln_control = @bulan
		and thn_control = @tahun
		and penerimaan_status != 'nonaktif'
		and id_fk_cabang = id_cabang_in;
	end if;
    
    select cabang_kode into @kode_cabang
    from mstr_cabang
    where id_pk_cabang = id_cabang_in;
    
    /*select id_cabang_in;*/
    
    set latest_no := @nomor;
    set trans_no = concat(upper(@kode_cabang),"-",upper(jenis_trans),"-",@tahun,"-",lpad(@bulan,2,0),"-",lpad(@tgl,2,0),"-",lpad(@nomor,6,0));
end$$
delimiter ;
call generate_trans_no(1,'retur','2020-07-11',@transno,@latest_no);select @transno,@latest_no;
