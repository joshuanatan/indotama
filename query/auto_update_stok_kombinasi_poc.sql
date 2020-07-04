drop view v_brg_kombinasi_final;
create view v_brg_kombinasi_final as 
select id_pk_barang_kombinasi, id_barang_utama, id_barang_kombinasi,sum(barang_kombinasi_qty) as barang_kombinasi_qty,barang_kombinasi_status
from tbl_barang_kombinasi 
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_barang_kombinasi.id_barang_kombinasi
where 
barang_kombinasi_status = 'aktif'
and brg_status = 'aktif'
group by id_barang_utama,id_barang_kombinasi;

create view v_brg_cabang_aktif as 
select id_pk_brg_cabang,brg_cabang_qty,brg_cabang_status,brg_cabang_last_price,id_fk_brg,id_fk_cabang,brg_nama 
from tbl_brg_cabang 
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_brg_cabang.id_fk_brg
where brg_cabang_status = 'aktif'
and brg_status = 'aktif'
order by id_fk_brg,id_fk_cabang;

/*core script*/


delimiter $$
create procedure update_latest_stok_mstr_brg_kombinasi(
in id_barang_utama_in int,
in id_fk_cabang_in int,
out new_stok_in double
)
begin
	select floor(min(rasio_stok)) into new_stok_in
	from (
		select id_fk_cabang,id_barang_utama,id_fk_brg,
		brg_cabang_qty,
		barang_kombinasi_qty,
		brg_cabang_qty/barang_kombinasi_qty as rasio_stok 
		from v_brg_kombinasi_final
		left join v_brg_cabang_aktif on v_brg_cabang_aktif.id_fk_brg = v_brg_kombinasi_final.id_barang_kombinasi
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_fk_cabang_in
		order by id_fk_cabang,id_barang_utama,id_barang_kombinasi
		) as a
		group by id_barang_utama,id_fk_cabang
		order by id_fk_cabang,id_barang_utama;
end$$
delimiter ;

drop procedure if exists list_barang_kombinasi_cabang;
delimiter $$
create procedure list_barang_kombinasi_cabang()
begin

declare finished int default 0;
declare id_barang_utama_var int default 0;
declare id_cabang_var int default 0;
    
declare brg_kombinasi_cur cursor for 
select id_barang_utama,id_fk_cabang
from tbl_barang_kombinasi
inner join mstr_barang on mstr_barang.id_pk_brg = tbl_barang_kombinasi.id_barang_utama
inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = mstr_barang.id_pk_brg
where mstr_barang.brg_status = 'aktif' 
and tbl_barang_kombinasi.barang_kombinasi_status = 'aktif' 
and tbl_brg_cabang.brg_cabang_status = 'aktif'
group by id_barang_utama,id_fk_cabang
/*supaya urutan dari yang paling awal dibuat, hingga yang akhir dibuat sehingga apabila terdapat kombinasi yang merupakan gabungan dari kombinasi lainnya jadi bisa terupdate dahulu sehingga dapat berjalan 1x. kalau tidak diurutkan berdasarkan id_pk_brg, maka dapat saja kombinasi yang terakhir terupdate terlebih dahulu daripada anggotanya menjadi tidak akurat. prinsipnya, update anggota dahulu sampe beres, baru update kombinasi lain yang menggunakan kombinasi sebelumnya*/
order by id_pk_brg,id_fk_cabang;

declare continue handler 
for not found set finished = 1;

open brg_kombinasi_cur;
mstr_kombinasi_loop: LOOP
	fetch brg_kombinasi_cur into id_barang_utama_var,id_cabang_var;
    select id_barang_utama_var,id_cabang_var;
    
    call update_latest_stok_mstr_brg_kombinasi(id_barang_utama_var,id_cabang_var,@new_stok);
    if finished = 1 then
		leave mstr_kombinasi_loop;
	end if;
    
    update tbl_brg_cabang set brg_cabang_qty = @new_stok 
    where id_fk_brg = id_barang_utama_var
    and id_fk_cabang = id_cabang_var;
    
END LOOP mstr_kombinasi_loop;
end$$
delimiter ;

call list_barang_kombinasi_cabang();
