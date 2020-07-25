#jumlah barang penjualan per id retur
select sum(tbl_brg_penjualan.brg_penjualan_qty),id_pk_retur_brg from tbl_retur_brg
inner join mstr_retur on mstr_retur.id_pk_retur = tbl_retur_brg.id_fk_retur
inner join tbl_brg_penjualan on tbl_brg_penjualan.id_fk_penjualan = mstr_retur.id_fk_penjualan and tbl_brg_penjualan.id_fk_barang = tbl_retur_brg.id_fk_brg
where id_fk_retur = 15 
group by id_fk_brg;

#jumlah barang pengantaran per id retur
select ifnull(sum(tbl_brg_pengiriman.brg_pengiriman_qty),0) as brg_terkirim,id_pk_retur_brg from tbl_retur_brg
inner join mstr_retur on mstr_retur.id_pk_retur = tbl_retur_brg.id_fk_retur
inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
left join mstr_pengiriman on mstr_pengiriman.id_fk_penjualan = mstr_penjualan.id_pk_penjualan
left join tbl_brg_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
where id_pk_retur = 15 
group by id_fk_brg;

#final script
select ifnull(sum(tbl_brg_pengiriman.brg_pengiriman_qty),0) as brg_terkirim,sum(tbl_brg_penjualan.brg_penjualan_qty) as brg_beli,id_pk_retur_brg from tbl_retur_brg
inner join mstr_retur on mstr_retur.id_pk_retur = tbl_retur_brg.id_fk_retur
inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = mstr_retur.id_fk_penjualan
inner join tbl_brg_penjualan on tbl_brg_penjualan.id_fk_penjualan = mstr_retur.id_fk_penjualan and tbl_brg_penjualan.id_fk_barang = tbl_retur_brg.id_fk_brg
left join mstr_pengiriman on mstr_pengiriman.id_fk_penjualan = mstr_penjualan.id_pk_penjualan
left join tbl_brg_pengiriman on mstr_pengiriman.id_pk_pengiriman = tbl_brg_pengiriman.id_fk_pengiriman
where id_pk_retur = 15 
group by id_fk_brg
