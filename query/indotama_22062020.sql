-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2020-06-22 05:11:58
-- 服务器版本： 10.4.11-MariaDB
-- PHP 版本： 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `indotama`
--

DELIMITER $$
--
-- 存储过程
--
CREATE PROCEDURE `check_and_auto_insert_brg_cabang` (IN `id_fk_brg_in` INT, IN `id_fk_cabang_in` INT, IN `id_create_data_in` INT)  begin
            set @a = "";
            select count(id_pk_brg_cabang) into @a from tbl_brg_cabang where id_fk_brg = id_fk_brg_in and id_fk_cabang = id_fk_cabang_in;

            if !@a>0
            then
                insert into tbl_brg_cabang 
                values (null,0,'Auto insert from database before penerimaan barang','aktif',0,id_fk_brg_in,id_fk_cabang_in,now(),now(),id_create_data_in,id_create_data_in);
            end if;
        end$$

CREATE PROCEDURE `insert_log_all` (IN `id_user` INT, IN `log_date` DATETIME, IN `log_text` VARCHAR(100), OUT `id_log_all` INT)  begin
	insert into log_all(id_user,log_date,log) values(id_user,log_date,log_text);
    select last_insert_id() into id_log_all ;
end$$

CREATE PROCEDURE `ubah_satuan_barang` (IN `id_satuan_in` INT, INOUT `brg_qty` DOUBLE)  begin
            declare conversion_exp varchar(20);
            select satuan_rumus 
            into conversion_exp
            from mstr_satuan
            where id_pk_satuan = id_satuan_in;
            
            set brg_qty = conversion_exp * brg_qty;
            
        end$$

CREATE PROCEDURE `update_stok_barang_cabang` (IN `id_barang` INT, IN `id_cabang` INT, IN `barang_masuk` DOUBLE, IN `id_satuan_masuk` INT, IN `barang_keluar` DOUBLE, IN `id_satuan_keluar` INT)  begin
            /*
            the logic is
            barang_masuk = n, barang_keluar = 0 [insert new data]
            barang_masuk = n, barang_keluar = m [update data]
            barang_masuk = 0, barang_keluar = m [delete data]
            */
            if barang_masuk != 0 then
            call ubah_satuan_barang(id_satuan_masuk, barang_masuk);
            end if;
            if barang_keluar != 0 then
            call ubah_satuan_barang(id_satuan_keluar, barang_keluar);
            end if;
            update tbl_brg_cabang 
            set brg_cabang_qty = brg_cabang_qty+barang_masuk-barang_keluar
            where id_fk_brg = id_barang and id_fk_cabang = id_cabang;

            call update_stok_kombinasi_barang_cabang(id_barang,barang_masuk, barang_keluar, id_cabang);

        end$$

CREATE PROCEDURE `update_stok_barang_warehouse` (IN `id_barang` INT, IN `id_warehouse` INT, IN `barang_masuk` DOUBLE, IN `id_satuan_masuk` INT, IN `barang_keluar` DOUBLE, IN `id_satuan_keluar` INT)  begin
            /*
            the logic is
            barang_masuk = n, barang_keluar = 0 [insert new data]
            barang_masuk = n, barang_keluar = m [update data]
            barang_masuk = 0, barang_keluar = m [delete data]
            */
            if barang_masuk != 0 then
            call ubah_satuan_barang(id_satuan_masuk, barang_masuk);
            end if;
            if barang_keluar != 0 then
            call ubah_satuan_barang(id_satuan_keluar, barang_keluar);
            end if;
            update tbl_brg_warehouse 
            set brg_warehouse_qty = brg_warehouse_qty+barang_masuk-barang_keluar
            where id_fk_brg = id_barang and id_fk_warehouse = id_warehouse;
        end$$

CREATE PROCEDURE `update_stok_kombinasi_barang_cabang` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `qty_brg_keluar_in` DOUBLE, IN `id_cabang_in` INT)  begin
            update tbl_barang_kombinasi
            inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
            set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)-(barang_kombinasi_qty*qty_brg_keluar_in)
            where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in and barang_kombinasi_status = 'aktif';
        end$$

CREATE PROCEDURE `use_combinasi_barang` (IN `id_barang_utama_in` INT, IN `qty_brg_masuk_in` DOUBLE, IN `id_cabang_in` INT, IN `jenis_transaksi` VARCHAR(15))  begin
	if jenis_transaksi = "penerimaan"
    then 
		update tbl_barang_kombinasi
		inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
		set brg_cabang_qty = brg_cabang_qty+(barang_kombinasi_qty*qty_brg_masuk_in)
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in;
    elseif jenis_transaksi = "pengiriman"
    then
		update tbl_barang_kombinasi
		inner join tbl_brg_cabang on tbl_brg_cabang.id_fk_brg = tbl_barang_kombinasi.id_barang_kombinasi
		set brg_cabang_qty = brg_cabang_qty-(barang_kombinasi_qty*qty_brg_masuk_in)
		where id_barang_utama = id_barang_utama_in and id_fk_cabang = id_cabang_in;
    end if;
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `log_all`
--

CREATE TABLE `log_all` (
  `id_log_all` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `log_date` datetime DEFAULT NULL,
  `log` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `log_all`
--

INSERT INTO `log_all` (`id_log_all`, `id_user`, `log_date`, `log`) VALUES
(1, 1, '2020-06-21 11:16:34', '1 INSERT DATA AT2020-06-21 11:16:34'),
(2, 0, '2020-06-21 23:26:35', '0 insert data at 2020-06-21 23:26:35'),
(3, 1, '2020-06-21 11:27:06', '1 insert data at2020-06-21 11:27:06'),
(4, 1, '2020-06-21 11:28:42', '1 insert data at2020-06-21 11:28:42'),
(5, 1, '2020-06-21 11:28:57', '1 insert data at 2020-06-21 11:28:57'),
(6, 1, '2020-06-21 11:28:57', '1 insert data at2020-06-21 11:28:57'),
(7, 1, '2020-06-21 11:28:57', '1 insert data at2020-06-21 11:28:57'),
(8, 1, '2020-06-21 11:29:04', '1 update data at 2020-06-21 11:29:04'),
(9, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(10, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(11, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(12, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(13, 1, '2020-06-21 11:38:11', '1 insert data at2020-06-21 11:38:11'),
(14, 1, '2020-06-21 11:38:11', '1 insert data at2020-06-21 11:38:11'),
(15, 1, '2020-06-21 11:38:23', '1 insert data at2020-06-21 11:38:23'),
(16, 1, '2020-06-21 11:38:23', '1 insert data at2020-06-21 11:38:23'),
(17, 1, '2020-06-21 11:38:35', '1 insert data at2020-06-21 11:38:35'),
(18, 1, '2020-06-21 11:38:35', '1 insert data at2020-06-21 11:38:35'),
(19, 1, '2020-06-21 11:38:44', '1 insert data at2020-06-21 11:38:44'),
(20, 1, '2020-06-21 11:38:44', '1 insert data at2020-06-21 11:38:44'),
(21, 1, '2020-06-21 11:38:54', '1 insert data at2020-06-21 11:38:54'),
(22, 1, '2020-06-21 11:38:54', '1 insert data at2020-06-21 11:38:54'),
(23, 1, '2020-06-21 11:39:36', '1 insert data at2020-06-21 11:39:36'),
(24, 1, '2020-06-21 11:39:36', '1 insert data at2020-06-21 11:39:36'),
(25, 1, '2020-06-21 11:40:07', '1 insert data at2020-06-21 11:40:07'),
(26, 1, '2020-06-21 11:40:07', '1 insert data at2020-06-21 11:40:07'),
(27, 1, '2020-06-21 11:40:15', '1 update data at2020-06-21 11:40:15'),
(28, 1, '2020-06-21 11:40:52', '1 insert data at2020-06-21 11:40:52'),
(29, 1, '2020-06-21 11:40:52', '1 insert data at2020-06-21 11:40:52'),
(30, 1, '2020-06-21 11:41:04', '1 insert data at2020-06-21 11:41:04'),
(31, 1, '2020-06-21 11:41:04', '1 insert data at2020-06-21 11:41:04'),
(32, 1, '2020-06-21 11:41:23', '1 insert data at2020-06-21 11:41:23'),
(33, 1, '2020-06-21 11:41:23', '1 insert data at2020-06-21 11:41:23'),
(34, 1, '2020-06-21 11:41:33', '1 insert data at2020-06-21 11:41:33'),
(35, 1, '2020-06-21 11:41:33', '1 insert data at2020-06-21 11:41:33'),
(36, 1, '2020-06-21 11:41:42', '1 insert data at2020-06-21 11:41:42'),
(37, 1, '2020-06-21 11:41:42', '1 insert data at2020-06-21 11:41:42'),
(38, 1, '2020-06-21 11:41:58', '1 insert data at2020-06-21 11:41:58'),
(39, 1, '2020-06-21 11:41:58', '1 insert data at2020-06-21 11:41:58'),
(40, 1, '2020-06-21 11:42:07', '1 insert data at2020-06-21 11:42:07'),
(41, 1, '2020-06-21 11:42:07', '1 insert data at2020-06-21 11:42:07'),
(42, 1, '2020-06-21 11:42:16', '1 insert data at2020-06-21 11:42:16'),
(43, 1, '2020-06-21 11:42:16', '1 insert data at2020-06-21 11:42:16'),
(44, 1, '2020-06-21 11:42:28', '1 insert data at2020-06-21 11:42:28'),
(45, 1, '2020-06-21 11:42:28', '1 insert data at2020-06-21 11:42:28'),
(46, 1, '2020-06-21 11:42:37', '1 insert data at2020-06-21 11:42:37'),
(47, 1, '2020-06-21 11:42:37', '1 insert data at2020-06-21 11:42:37'),
(48, 1, '2020-06-21 11:42:53', '1 update data at 2020-06-21 11:42:53'),
(49, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(50, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(51, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(52, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(53, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(54, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(55, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(56, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(57, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(58, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(59, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(60, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(61, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(62, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(63, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(64, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(65, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(66, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(67, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(68, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(69, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(70, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(71, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(72, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(73, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(74, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(75, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(76, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(77, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(78, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(79, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(80, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(81, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(82, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(83, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(84, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(85, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(86, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(87, 1, '2020-06-21 11:43:08', '1 update data at2020-06-21 11:43:08'),
(88, 1, '2020-06-21 11:44:14', '1 insert data at 2020-06-21 11:44:14'),
(89, 1, '2020-06-21 11:44:24', '1 insert data at2020-06-21 11:44:24'),
(90, 1, '2020-06-21 11:44:49', '1 insert data at2020-06-21 11:44:49'),
(91, 1, '2020-06-21 11:45:03', '1 insert data at2020-06-21 11:45:03'),
(92, 1, '2020-06-21 11:45:42', '1 insert data at2020-06-21 11:45:42'),
(93, 1, '2020-06-21 11:45:53', '1 insert data at2020-06-21 11:45:53'),
(94, 1, '2020-06-22 12:12:04', '1 insert data at2020-06-22 12:12:04'),
(95, 1, '2020-06-22 12:12:04', '1 insert data at2020-06-22 12:12:04'),
(96, 1, '2020-06-22 07:50:23', '1 insert data at2020-06-22 07:50:23'),
(97, 1, '2020-06-22 07:50:23', '1 insert data at2020-06-22 07:50:23'),
(98, 1, '2020-06-22 07:50:39', '1 update data at 2020-06-22 07:50:39'),
(99, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(100, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(101, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(102, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(103, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(104, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(105, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(106, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(107, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(108, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(109, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(110, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(111, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(112, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(113, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(114, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(115, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(116, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(117, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(118, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(119, 1, '2020-06-22 07:50:23', '1 update data at2020-06-22 07:50:23'),
(120, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(121, 1, '2020-06-21 11:28:57', '1 update data at2020-06-21 11:28:57'),
(122, 1, '2020-06-21 11:38:11', '1 update data at2020-06-21 11:38:11'),
(123, 1, '2020-06-21 11:38:23', '1 update data at2020-06-21 11:38:23'),
(124, 1, '2020-06-21 11:38:35', '1 update data at2020-06-21 11:38:35'),
(125, 1, '2020-06-21 11:38:44', '1 update data at2020-06-21 11:38:44'),
(126, 1, '2020-06-21 11:38:54', '1 update data at2020-06-21 11:38:54'),
(127, 1, '2020-06-21 11:39:36', '1 update data at2020-06-21 11:39:36'),
(128, 1, '2020-06-21 11:40:07', '1 update data at2020-06-21 11:40:07'),
(129, 1, '2020-06-21 11:40:52', '1 update data at2020-06-21 11:40:52'),
(130, 1, '2020-06-21 11:41:04', '1 update data at2020-06-21 11:41:04'),
(131, 1, '2020-06-21 11:41:23', '1 update data at2020-06-21 11:41:23'),
(132, 1, '2020-06-21 11:41:33', '1 update data at2020-06-21 11:41:33'),
(133, 1, '2020-06-21 11:41:42', '1 update data at2020-06-21 11:41:42'),
(134, 1, '2020-06-21 11:41:58', '1 update data at2020-06-21 11:41:58'),
(135, 1, '2020-06-21 11:42:07', '1 update data at2020-06-21 11:42:07'),
(136, 1, '2020-06-21 11:42:16', '1 update data at2020-06-21 11:42:16'),
(137, 1, '2020-06-21 11:42:28', '1 update data at2020-06-21 11:42:28'),
(138, 1, '2020-06-21 11:42:37', '1 update data at2020-06-21 11:42:37'),
(139, 1, '2020-06-22 12:12:04', '1 update data at2020-06-22 12:12:04'),
(140, 1, '2020-06-22 07:51:13', '1 insert data at 2020-06-22 07:51:13'),
(141, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(142, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(143, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(144, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(145, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(146, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(147, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(148, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(149, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(150, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(151, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(152, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(153, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(154, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(155, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(156, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(157, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(158, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(159, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(160, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(161, 1, '2020-06-22 07:51:13', '1 insert data at2020-06-22 07:51:13'),
(162, 1, '2020-06-22 07:51:42', '1 update data at 2020-06-22 07:51:42'),
(163, 1, '2020-06-22 07:53:15', '1 insert data at 2020-06-22 07:53:15'),
(164, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(165, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(166, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(167, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(168, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(169, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(170, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(171, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(172, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(173, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(174, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(175, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(176, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(177, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(178, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(179, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(180, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(181, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(182, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(183, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(184, 1, '2020-06-22 07:53:15', '1 insert data at2020-06-22 07:53:15'),
(185, 1, '2020-06-22 08:02:21', '1 insert data at 2020-06-22 08:02:21'),
(186, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(187, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(188, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(189, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(190, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(191, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(192, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(193, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(194, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(195, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(196, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(197, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(198, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(199, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(200, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(201, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(202, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(203, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(204, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(205, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(206, 1, '2020-06-22 08:02:21', '1 insert data at2020-06-22 08:02:21'),
(207, 1, '2020-06-22 08:02:21', '1 update data at2020-06-22 08:02:21'),
(208, 1, '2020-06-22 08:02:21', '1 update data at2020-06-22 08:02:21'),
(209, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(210, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(211, 1, '2020-06-22 08:03:15', '1 insert data at2020-06-22 08:03:15'),
(212, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(213, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(214, 1, '2020-06-22 08:03:23', '1 insert data at2020-06-22 08:03:23'),
(215, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(216, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(217, 1, '2020-06-22 08:03:32', '1 insert data at2020-06-22 08:03:32'),
(218, 1, '2020-06-22 08:03:53', '1 insert data at 2020-06-22 08:03:53'),
(219, 1, '2020-06-22 08:04:32', '1 insert data at 2020-06-22 08:04:32'),
(220, 1, '2020-06-22 08:04:32', '1 insert data at 2020-06-22 08:04:32'),
(221, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(222, 1, '2020-06-22 08:07:25', '1 update data at 2020-06-22 08:07:25'),
(223, 1, '2020-06-22 08:07:27', '1 update data at 2020-06-22 08:07:27'),
(224, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(225, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(226, 1, '2020-06-22 08:07:40', '1 insert data at 2020-06-22 08:07:40'),
(227, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(228, 1, '2020-06-22 08:08:58', '1 update data at 2020-06-22 08:08:58'),
(229, 1, '2020-06-22 08:09:00', '1 update data at 2020-06-22 08:09:00'),
(230, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(231, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(232, 1, '2020-06-22 08:09:14', '1 insert data at 2020-06-22 08:09:14'),
(233, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(234, 1, '2020-06-22 08:10:06', '1 update data at 2020-06-22 08:10:06'),
(235, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(236, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(237, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(238, 1, '2020-06-22 08:16:43', '1 insert data at2020-06-22 08:16:43'),
(239, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(240, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(241, 1, '2020-06-22 08:18:34', '1 update data at2020-06-22 08:18:34'),
(242, 1, '0000-00-00 00:00:00', '1 insert data at0000-00-00 00:00:00'),
(243, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(244, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(245, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(246, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(247, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(248, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(249, 1, '2020-06-22 08:26:28', '1 insert data at2020-06-22 08:26:28'),
(250, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(251, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(252, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(253, 1, '0000-00-00 00:00:00', '1 insert data at0000-00-00 00:00:00'),
(254, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(255, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(256, 1, '2020-06-22 08:26:54', '1 update data at2020-06-22 08:26:54'),
(257, 1, '0000-00-00 00:00:00', '1 update data at0000-00-00 00:00:00'),
(258, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(259, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(260, 1, '2020-06-22 08:27:08', '1 update data at2020-06-22 08:27:08'),
(261, 1, '2020-06-22 08:27:08', '1 insert data at2020-06-22 08:27:08'),
(262, 1, '0000-00-00 00:00:00', '1 update data at0000-00-00 00:00:00'),
(263, 1, '2020-06-22 08:27:18', '1 update data at2020-06-22 08:27:18'),
(264, 1, '2020-06-22 08:36:13', '1 insert data at2020-06-22 08:36:13'),
(265, 1, '2020-06-22 08:36:19', '1 insert data at2020-06-22 08:36:19'),
(266, 1, '2020-06-22 08:36:23', '1 insert data at2020-06-22 08:36:23'),
(267, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(268, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(269, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(270, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(271, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(272, 1, '2020-06-22 08:52:15', '1 insert data at2020-06-22 08:52:15'),
(273, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(274, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(275, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(276, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(277, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(278, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(279, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(280, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(281, 1, '2020-06-22 08:52:58', '1 update data at2020-06-22 08:52:58'),
(282, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(283, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(284, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(285, 1, '2020-06-22 08:54:59', '1 update data at2020-06-22 08:54:59'),
(286, 1, '2020-06-22 08:54:59', '1 update data at2020-06-22 08:54:59'),
(287, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(288, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(289, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(290, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(291, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(292, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(293, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(294, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(295, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(296, 1, '2020-06-22 09:00:23', '1 update data at2020-06-22 09:00:23'),
(297, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(298, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(299, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(300, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(301, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(302, 1, '2020-06-22 09:00:41', '1 update data at2020-06-22 09:00:41'),
(303, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(304, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(305, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(306, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(307, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(308, 1, '2020-06-22 08:07:23', '1 update data at 2020-06-22 08:07:23'),
(309, 1, '2020-06-22 08:08:55', '1 update data at 2020-06-22 08:08:55'),
(310, 1, '2020-06-22 08:10:02', '1 update data at 2020-06-22 08:10:02'),
(311, 1, '2020-06-22 09:00:47', '1 update data at2020-06-22 09:00:47'),
(312, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(313, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(314, 1, '2020-06-22 08:26:28', '1 update data at 2020-06-22 08:26:28'),
(315, 1, '2020-06-22 09:08:48', '1 insert data at2020-06-22 09:08:48'),
(316, 1, '2020-06-22 09:38:00', '1 insert data at2020-06-22 09:38:00'),
(317, 1, '2020-06-22 09:39:50', '1 insert data at 2020-06-22 09:39:50'),
(318, 1, '2020-06-22 09:39:50', '1 insert data at2020-06-22 09:39:50'),
(319, 1, '2020-06-22 09:39:50', '1 insert data at2020-06-22 09:39:50'),
(320, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(321, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(322, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(323, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(324, 1, '2020-06-22 09:39:51', '1 insert data at2020-06-22 09:39:51'),
(325, 1, '2020-06-22 09:38:00', '1 update data at2020-06-22 09:38:00'),
(326, 1, '2020-06-22 09:53:30', '1 update data at2020-06-22 09:53:30'),
(327, 1, '2020-06-22 09:53:30', '1 update data at2020-06-22 09:53:30'),
(328, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(329, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(330, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(331, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(332, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(333, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(334, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(335, 1, '2020-06-22 09:55:58', '1 insert data at2020-06-22 09:55:58'),
(336, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(337, 1, '2020-06-22 09:55:58', '1 update data at2020-06-22 09:55:58'),
(338, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(339, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(340, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(341, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(342, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(343, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(344, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(345, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(346, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28'),
(347, 1, '2020-06-22 10:05:28', '1 update data at2020-06-22 10:05:28');

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang`
--

CREATE TABLE `mstr_barang` (
  `id_pk_brg` int(11) NOT NULL,
  `brg_kode` varchar(50) DEFAULT NULL,
  `brg_nama` varchar(100) DEFAULT NULL,
  `brg_ket` varchar(200) DEFAULT NULL,
  `brg_minimal` double DEFAULT NULL,
  `brg_satuan` varchar(30) DEFAULT NULL,
  `brg_image` varchar(100) DEFAULT NULL,
  `brg_harga` int(11) DEFAULT NULL,
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang`
--

INSERT INTO `mstr_barang` (`id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`) VALUES
(1, 'BARANG 1', 'BARANG 1', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 1, 1),
(2, 'BARANG 2', 'BARANG 2', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 2, 2),
(3, 'BARANG 3', 'BARANG 3', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 3, 3);

--
-- 触发器 `mstr_barang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang` AFTER INSERT ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after insert',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang` AFTER UPDATE ON `mstr_barang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_log(executed_function,
            id_pk_brg,brg_kode,brg_nama,brg_ket,brg_minimal,brg_satuan,brg_image,brg_harga,brg_status,brg_create_date,brg_last_modified,id_create_data,id_last_modified,id_fk_brg_jenis,id_fk_brg_merk,id_log_all) values ('after update',new.id_pk_brg,new.brg_kode,new.brg_nama,new.brg_ket,new.brg_minimal,new.brg_satuan,new.brg_image,new.brg_harga,new.brg_status,new.brg_create_date,new.brg_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_brg_jenis,new.id_fk_brg_merk,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang_jenis`
--

CREATE TABLE `mstr_barang_jenis` (
  `id_pk_brg_jenis` int(11) NOT NULL,
  `brg_jenis_nama` varchar(100) DEFAULT NULL,
  `brg_jenis_status` varchar(15) DEFAULT NULL,
  `brg_jenis_create_date` datetime DEFAULT NULL,
  `brg_jenis_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang_jenis`
--

INSERT INTO `mstr_barang_jenis` (`id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1);

--
-- 触发器 `mstr_barang_jenis`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_jenis` AFTER INSERT ON `mstr_barang_jenis` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_jenis` AFTER UPDATE ON `mstr_barang_jenis` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_jenis_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_jenis_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_jenis_log(executed_function,id_pk_brg_jenis,brg_jenis_nama,brg_jenis_status,brg_jenis_create_date,brg_jenis_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_jenis,new.brg_jenis_nama,new.brg_jenis_status,new.brg_jenis_create_date,new.brg_jenis_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang_jenis_log`
--

CREATE TABLE `mstr_barang_jenis_log` (
  `id_pk_brg_jenis_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_brg_jenis` int(11) DEFAULT NULL,
  `brg_jenis_nama` varchar(100) DEFAULT NULL,
  `brg_jenis_status` varchar(15) DEFAULT NULL,
  `brg_jenis_create_date` datetime DEFAULT NULL,
  `brg_jenis_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang_jenis_log`
--

INSERT INTO `mstr_barang_jenis_log` (`id_pk_brg_jenis_log`, `executed_function`, `id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 209),
(2, 'after insert', 2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 212),
(3, 'after insert', 3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 215);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang_log`
--

CREATE TABLE `mstr_barang_log` (
  `id_pk_brg_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_brg` int(11) DEFAULT NULL,
  `brg_kode` varchar(50) DEFAULT NULL,
  `brg_nama` varchar(100) DEFAULT NULL,
  `brg_ket` varchar(200) DEFAULT NULL,
  `brg_minimal` double DEFAULT NULL,
  `brg_satuan` varchar(30) DEFAULT NULL,
  `brg_image` varchar(100) DEFAULT NULL,
  `brg_harga` int(11) DEFAULT NULL,
  `brg_status` varchar(15) DEFAULT NULL,
  `brg_create_date` datetime DEFAULT NULL,
  `brg_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_brg_jenis` int(11) DEFAULT NULL,
  `id_fk_brg_merk` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang_log`
--

INSERT INTO `mstr_barang_log` (`id_pk_brg_log`, `executed_function`, `id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`, `id_log_all`) VALUES
(1, 'after insert', 1, 'BARANG 1', 'BARANG 1', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 1, 1, 211),
(2, 'after insert', 2, 'BARANG 2', 'BARANG 2', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 2, 2, 214),
(3, 'after insert', 3, 'BARANG 3', 'BARANG 3', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 3, 3, 217);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang_merk`
--

CREATE TABLE `mstr_barang_merk` (
  `id_pk_brg_merk` int(11) NOT NULL,
  `brg_merk_nama` varchar(100) DEFAULT NULL,
  `brg_merk_status` varchar(15) DEFAULT NULL,
  `brg_merk_create_date` datetime DEFAULT NULL,
  `brg_merk_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang_merk`
--

INSERT INTO `mstr_barang_merk` (`id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1);

--
-- 触发器 `mstr_barang_merk`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_merk` AFTER INSERT ON `mstr_barang_merk` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_merk` AFTER UPDATE ON `mstr_barang_merk` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_merk_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_merk_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_barang_merk_log(executed_function,id_pk_brg_merk,brg_merk_nama,brg_merk_status,brg_merk_create_date,brg_merk_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_merk,new.brg_merk_nama,new.brg_merk_status,new.brg_merk_create_date,new.brg_merk_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_barang_merk_log`
--

CREATE TABLE `mstr_barang_merk_log` (
  `id_pk_brg_merk_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_merk` int(11) DEFAULT NULL,
  `brg_merk_nama` varchar(100) DEFAULT NULL,
  `brg_merk_status` varchar(15) DEFAULT NULL,
  `brg_merk_create_date` datetime DEFAULT NULL,
  `brg_merk_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_barang_merk_log`
--

INSERT INTO `mstr_barang_merk_log` (`id_pk_brg_merk_log`, `executed_function`, `id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 210),
(2, 'after insert', 2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 213),
(3, 'after insert', 3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 216);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_cabang`
--

CREATE TABLE `mstr_cabang` (
  `id_pk_cabang` int(11) NOT NULL,
  `cabang_daerah` varchar(50) DEFAULT NULL,
  `cabang_notelp` varchar(30) DEFAULT NULL,
  `cabang_alamat` varchar(100) DEFAULT NULL,
  `cabang_status` varchar(15) DEFAULT NULL,
  `cabang_create_date` datetime DEFAULT NULL,
  `cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_cabang`
--

INSERT INTO `mstr_cabang` (`id_pk_cabang`, `cabang_daerah`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`) VALUES
(1, 'TAMAN ANGGREK', '12345678', 'Taman Anggrek Tanjung Duren', 'AKTIF', '2020-06-21 11:44:49', '2020-06-21 11:44:49', 1, 1, 1);

--
-- 触发器 `mstr_cabang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cabang` AFTER INSERT ON `mstr_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after insert',new.id_pk_cabang,new.cabang_daerah,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_cabang` AFTER UPDATE ON `mstr_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_cabang_log(executed_function,id_pk_cabang,cabang_daerah,cabang_notelp,cabang_alamat,cabang_status,cabang_create_date,cabang_last_modified,id_create_data,id_last_modified,id_fk_toko,id_log_all) values ('after update',new.id_pk_cabang,new.cabang_daerah,new.cabang_notelp,new.cabang_alamat,new.cabang_status,new.cabang_create_date,new.cabang_last_modified,new.id_create_data,new.id_last_modified,new.id_fk_toko,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_cabang_log`
--

CREATE TABLE `mstr_cabang_log` (
  `id_pk_cabang_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cabang` int(11) DEFAULT NULL,
  `cabang_daerah` varchar(50) DEFAULT NULL,
  `cabang_notelp` varchar(30) DEFAULT NULL,
  `cabang_alamat` varchar(100) DEFAULT NULL,
  `cabang_status` varchar(15) DEFAULT NULL,
  `cabang_create_date` datetime DEFAULT NULL,
  `cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_cabang_log`
--

INSERT INTO `mstr_cabang_log` (`id_pk_cabang_log`, `executed_function`, `id_pk_cabang`, `cabang_daerah`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`, `id_log_all`) VALUES
(1, 'after insert', 1, 'TAMAN ANGGREK', '12345678', 'Taman Anggrek Tanjung Duren', 'AKTIF', '2020-06-21 11:44:49', '2020-06-21 11:44:49', 1, 1, 1, 90);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_customer`
--

CREATE TABLE `mstr_customer` (
  `id_pk_cust` int(11) NOT NULL,
  `cust_name` varchar(100) DEFAULT NULL,
  `cust_suff` varchar(10) DEFAULT NULL,
  `cust_perusahaan` varchar(100) DEFAULT NULL,
  `cust_email` varchar(100) DEFAULT NULL,
  `cust_telp` varchar(30) DEFAULT NULL,
  `cust_hp` varchar(30) DEFAULT NULL,
  `cust_alamat` varchar(150) DEFAULT NULL,
  `cust_keterangan` varchar(150) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `cust_status` varchar(15) DEFAULT NULL,
  `cust_create_date` datetime DEFAULT NULL,
  `cust_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_customer`
--

INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, NULL, NULL, 'TOTAL Construction', NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1);

--
-- 触发器 `mstr_customer`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_customer` AFTER INSERT ON `mstr_customer` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cust,new.cust_name,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_customer` AFTER UPDATE ON `mstr_customer` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cust_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.cust_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_customer_log(executed_function,id_pk_cust,cust_name,cust_suff,cust_perusahaan,cust_email,cust_telp,cust_hp,cust_alamat,cust_keterangan,id_fk_toko,cust_status,cust_create_date,cust_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cust,new.cust_name,new.cust_suff,new.cust_perusahaan,new.cust_email,new.cust_telp,new.cust_hp,new.cust_alamat,new.cust_keterangan,new.id_fk_toko,new.cust_status,new.cust_create_date,new.cust_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_customer_log`
--

CREATE TABLE `mstr_customer_log` (
  `id_pk_cust_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cust` int(11) DEFAULT NULL,
  `cust_name` varchar(100) DEFAULT NULL,
  `cust_suff` varchar(10) DEFAULT NULL,
  `cust_perusahaan` varchar(100) DEFAULT NULL,
  `cust_email` varchar(100) DEFAULT NULL,
  `cust_telp` varchar(30) DEFAULT NULL,
  `cust_hp` varchar(30) DEFAULT NULL,
  `cust_alamat` varchar(150) DEFAULT NULL,
  `cust_keterangan` varchar(150) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `cust_status` varchar(15) DEFAULT NULL,
  `cust_create_date` datetime DEFAULT NULL,
  `cust_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_customer_log`
--

INSERT INTO `mstr_customer_log` (`id_pk_cust_log`, `executed_function`, `id_pk_cust`, `cust_name`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, NULL, NULL, 'TOTAL Construction', NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 317);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_employee`
--

CREATE TABLE `mstr_employee` (
  `id_pk_employee` int(11) NOT NULL,
  `emp_nama` varchar(400) DEFAULT NULL,
  `emp_npwp` varchar(25) DEFAULT NULL,
  `emp_ktp` varchar(20) DEFAULT NULL,
  `emp_hp` varchar(15) DEFAULT NULL,
  `emp_alamat` varchar(300) DEFAULT NULL,
  `emp_kode_pos` varchar(10) DEFAULT NULL,
  `emp_foto_npwp` varchar(50) DEFAULT NULL,
  `emp_foto_ktp` varchar(50) DEFAULT NULL,
  `emp_foto_lain` varchar(50) DEFAULT NULL,
  `emp_foto` varchar(50) DEFAULT NULL,
  `emp_gaji` int(11) DEFAULT NULL,
  `emp_startdate` datetime DEFAULT NULL,
  `emp_enddate` datetime DEFAULT NULL,
  `emp_rek` varchar(30) DEFAULT NULL,
  `emp_gender` varchar(6) DEFAULT NULL,
  `emp_suff` varchar(10) DEFAULT NULL,
  `emp_status` varchar(15) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `mstr_employee`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_employee` AFTER INSERT ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,id_fk_toko,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.id_fk_toko,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_employee` AFTER UPDATE ON `mstr_employee` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.emp_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.emp_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_employee_log(executed_function,id_pk_employee,emp_nama,emp_npwp,emp_ktp,emp_hp,emp_alamat,emp_kode_pos,emp_foto_npwp,emp_foto_ktp,emp_foto_lain,emp_foto,emp_gaji,emp_startdate,emp_enddate,emp_rek,emp_gender,emp_suff,emp_status,id_fk_toko,emp_create_date,emp_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_employee,new.emp_nama,new.emp_npwp,new.emp_ktp,new.emp_hp,new.emp_alamat,new.emp_kode_pos,new.emp_foto_npwp,new.emp_foto_ktp,new.emp_foto_lain,new.emp_foto,new.emp_gaji,new.emp_startdate,new.emp_enddate,new.emp_rek,new.emp_gender,new.emp_suff,new.emp_status,new.id_fk_toko,new.emp_create_date,new.emp_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_employee_log`
--

CREATE TABLE `mstr_employee_log` (
  `id_pk_employee_log` int(11) NOT NULL,
  `executed_function` varchar(40) DEFAULT NULL,
  `id_pk_employee` int(11) DEFAULT NULL,
  `emp_nama` varchar(400) DEFAULT NULL,
  `emp_npwp` varchar(25) DEFAULT NULL,
  `emp_ktp` varchar(20) DEFAULT NULL,
  `emp_hp` varchar(15) DEFAULT NULL,
  `emp_alamat` varchar(300) DEFAULT NULL,
  `emp_kode_pos` varchar(10) DEFAULT NULL,
  `emp_foto_npwp` varchar(50) DEFAULT NULL,
  `emp_foto_ktp` varchar(50) DEFAULT NULL,
  `emp_foto_lain` varchar(50) DEFAULT NULL,
  `emp_foto` varchar(50) DEFAULT NULL,
  `emp_gaji` int(11) DEFAULT NULL,
  `emp_startdate` datetime DEFAULT NULL,
  `emp_enddate` datetime DEFAULT NULL,
  `emp_rek` varchar(30) DEFAULT NULL,
  `emp_gender` varchar(6) DEFAULT NULL,
  `emp_suff` varchar(10) DEFAULT NULL,
  `emp_status` varchar(15) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `emp_create_date` datetime DEFAULT NULL,
  `emp_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_jabatan`
--

CREATE TABLE `mstr_jabatan` (
  `id_pk_jabatan` int(11) NOT NULL,
  `jabatan_nama` varchar(100) DEFAULT NULL,
  `jabatan_status` varchar(15) DEFAULT NULL,
  `jabatan_create_date` datetime DEFAULT NULL,
  `jabatan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_jabatan`
--

INSERT INTO `mstr_jabatan` (`id_pk_jabatan`, `jabatan_nama`, `jabatan_status`, `jabatan_create_date`, `jabatan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 07:50:39', 1, 1),
(2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 07:51:42', 1, 1),
(3, 'admin3', 'AKTIF', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(4, 'admin4', 'AKTIF', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1);

--
-- 触发器 `mstr_jabatan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_jabatan` AFTER INSERT ON `mstr_jabatan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

            /* insert new jabatan to all hak akses*/
            set @id_jabatan = new.id_pk_jabatan;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select @id_jabatan,id_pk_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_menu;
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_jabatan` AFTER UPDATE ON `mstr_jabatan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.jabatan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.jabatan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_jabatan_log(executed_function,id_pk_jabatan,jabatan_nama,jabatan_status,jabatan_create_date,jabatan_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_jabatan,new.jabatan_nama,new.jabatan_status,new.jabatan_create_date,new.jabatan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_jabatan_log`
--

CREATE TABLE `mstr_jabatan_log` (
  `id_pk_jabatan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_jabatan` int(11) DEFAULT NULL,
  `jabatan_nama` varchar(100) DEFAULT NULL,
  `jabatan_status` varchar(15) DEFAULT NULL,
  `jabatan_create_date` datetime DEFAULT NULL,
  `jabatan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_jabatan_log`
--

INSERT INTO `mstr_jabatan_log` (`id_pk_jabatan_log`, `executed_function`, `id_pk_jabatan`, `jabatan_nama`, `jabatan_status`, `jabatan_create_date`, `jabatan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 5),
(2, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:29:04', 1, 1, 8),
(3, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-21 11:42:53', 1, 1, 48),
(4, 'after update', 1, 'admin', 'AKTIF', '2020-06-21 11:28:57', '2020-06-22 07:50:39', 1, 1, 98),
(5, 'after insert', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 140),
(6, 'after update', 2, 'admin2', 'AKTIF', '2020-06-22 07:51:13', '2020-06-22 07:51:42', 1, 1, 162),
(7, 'after insert', 3, 'admin3', 'AKTIF', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 163),
(8, 'after insert', 4, 'admin4', 'AKTIF', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 185);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_menu`
--

CREATE TABLE `mstr_menu` (
  `id_pk_menu` int(11) NOT NULL,
  `menu_name` varchar(100) DEFAULT NULL,
  `menu_display` varchar(100) DEFAULT NULL,
  `menu_icon` varchar(100) DEFAULT NULL,
  `menu_category` varchar(100) DEFAULT NULL,
  `menu_status` varchar(15) DEFAULT NULL,
  `menu_create_date` datetime DEFAULT NULL,
  `menu_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_menu`
--

INSERT INTO `mstr_menu` (`id_pk_menu`, `menu_name`, `menu_display`, `menu_icon`, `menu_category`, `menu_status`, `menu_create_date`, `menu_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'menu', 'MENU', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-06-21 11:27:06', 1, 1),
(2, 'roles', 'ROLE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-06-21 11:43:08', 1, 1),
(3, 'barang', 'BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1),
(4, 'barang_jenis', 'JENIS BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1),
(5, 'barang_merk', 'MERK BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1),
(6, 'customer', 'CUSTOMER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1),
(7, 'employee', 'EMPLOYEE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1),
(8, 'pembelian', 'PEMBELIAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-06-21 11:40:15', 1, 1),
(9, 'penerimaan/cabang', 'PENERIMAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1),
(10, 'pengiriman/cabang', 'PENGIRIMAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1),
(11, 'pengiriman/warehouse', 'PENGIRIMAN GUDANG', 'edit', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1),
(12, 'penjualan', 'PENJUALAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1),
(13, 'permintaan', 'PERMINTAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1),
(14, 'retur', 'RETUR CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1),
(15, 'satuan', 'SATUAN', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1),
(16, 'supplier', 'SUPPLIER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1),
(17, 'toko', 'TOKO', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1),
(18, 'user', 'USER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1),
(19, 'warehouse', 'WAREHOUSE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1),
(20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1),
(21, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1);

--
-- 触发器 `mstr_menu`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_menu` AFTER INSERT ON `mstr_menu` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            /* insert new menu to all hak akses*/
            set @id_menu = new.id_pk_menu;
            insert into tbl_hak_akses(id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified)
            select id_pk_jabatan,@id_menu,'nonaktif',@tgl_action,@tgl_action,@id_user,@id_user from mstr_jabatan;
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_menu` AFTER UPDATE ON `mstr_menu` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.menu_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.menu_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_menu_log(executed_function,id_pk_menu,menu_name,menu_display,menu_icon,menu_category,menu_status,menu_create_date,menu_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_menu,new.menu_name,new.menu_display,new.menu_icon,new.menu_category,new.menu_status,new.menu_create_date,new.menu_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_menu_log`
--

CREATE TABLE `mstr_menu_log` (
  `id_pk_menu_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_menu` int(11) DEFAULT NULL,
  `menu_name` varchar(100) DEFAULT NULL,
  `menu_display` varchar(100) DEFAULT NULL,
  `menu_icon` varchar(100) DEFAULT NULL,
  `menu_category` varchar(100) DEFAULT NULL,
  `menu_status` varchar(15) DEFAULT NULL,
  `menu_create_date` datetime DEFAULT NULL,
  `menu_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_menu_log`
--

INSERT INTO `mstr_menu_log` (`id_pk_menu_log`, `executed_function`, `id_pk_menu`, `menu_name`, `menu_display`, `menu_icon`, `menu_category`, `menu_status`, `menu_create_date`, `menu_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'menu', 'MENU', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:27:06', '2020-06-21 11:27:06', 1, 1, 3),
(2, 'after insert', 2, 'role', 'ROLE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-06-21 11:28:42', 1, 1, 4),
(3, 'after insert', 3, 'barang', 'BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 13),
(4, 'after insert', 4, 'barang_jenis', 'JENIS BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 15),
(5, 'after insert', 5, 'barang_merk', 'MERK BARANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 17),
(6, 'after insert', 6, 'customer', 'CUSTOMER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 19),
(7, 'after insert', 7, 'employee', 'EMPLOYEE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 21),
(8, 'after insert', 8, 'pembelian', 'PEMBELIAN CABANG', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 23),
(9, 'after insert', 9, 'penerimaan/cabang', 'PENERIMAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 25),
(10, 'after update', 8, 'pembelian', 'PEMBELIAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:39:36', '2020-06-21 11:40:15', 1, 1, 27),
(11, 'after insert', 10, 'pengiriman/cabang', 'PENGIRIMAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 28),
(12, 'after insert', 11, 'pengiriman/warehouse', 'PENGIRIMAN GUDANG', 'edit', 'GUDANG', 'AKTIF', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 30),
(13, 'after insert', 12, 'penjualan', 'PENJUALAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 32),
(14, 'after insert', 13, 'permintaan', 'PERMINTAAN CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 34),
(15, 'after insert', 14, 'retur', 'RETUR CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 36),
(16, 'after insert', 15, 'satuan', 'SATUAN', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 38),
(17, 'after insert', 16, 'supplier', 'SUPPLIER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 40),
(18, 'after insert', 17, 'toko', 'TOKO', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 42),
(19, 'after insert', 18, 'user', 'USER', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 44),
(20, 'after insert', 19, 'warehouse', 'WAREHOUSE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 46),
(21, 'after update', 2, 'roles', 'ROLE', 'edit', 'GENERAL', 'AKTIF', '2020-06-21 11:28:42', '2020-06-21 11:43:08', 1, 1, 87),
(22, 'after insert', 20, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 94),
(23, 'after insert', 21, 'toko/brg_cabang', 'STOK CABANG', 'edit', 'CABANG', 'AKTIF', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 96);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_pembelian`
--

CREATE TABLE `mstr_pembelian` (
  `id_pk_pembelian` int(11) NOT NULL,
  `pem_pk_nomor` varchar(30) DEFAULT NULL,
  `pem_tgl` date DEFAULT NULL,
  `pem_status` varchar(15) DEFAULT NULL,
  `id_fk_supp` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pem_create_date` datetime DEFAULT NULL,
  `pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_pembelian`
--

INSERT INTO `mstr_pembelian` (`id_pk_pembelian`, `pem_pk_nomor`, `pem_tgl`, `pem_status`, `id_fk_supp`, `id_fk_cabang`, `pem_create_date`, `pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1);

--
-- 触发器 `mstr_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pembelian` AFTER INSERT ON `mstr_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pembelian` AFTER UPDATE ON `mstr_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pembelian_log(executed_function,id_pk_pembelian,pem_pk_nomor,pem_tgl,pem_status,id_fk_supp,id_fk_cabang,pem_create_date,pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_pembelian,new.pem_pk_nomor,new.pem_tgl,new.pem_status,new.id_fk_supp,new.id_fk_cabang,new.pem_create_date,new.pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_pembelian_log`
--

CREATE TABLE `mstr_pembelian_log` (
  `id_pk_pembelian_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_pembelian` int(11) DEFAULT NULL,
  `pem_pk_nomor` varchar(30) DEFAULT NULL,
  `pem_tgl` date DEFAULT NULL,
  `pem_status` varchar(15) DEFAULT NULL,
  `id_fk_supp` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pem_create_date` datetime DEFAULT NULL,
  `pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_pembelian_log`
--

INSERT INTO `mstr_pembelian_log` (`id_pk_pembelian_log`, `executed_function`, `id_pk_pembelian`, `pem_pk_nomor`, `pem_tgl`, `pem_status`, `id_fk_supp`, `id_fk_cabang`, `pem_create_date`, `pem_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 236),
(2, 'after update', 1, 'nomorpembelian1', '1111-11-11', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 239),
(3, 'after insert', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 244),
(4, 'after update', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 254),
(5, 'after update', 2, 'nomorpembelian2', '2000-12-22', 'AKTIF', 2, 1, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 258);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_penerimaan`
--

CREATE TABLE `mstr_penerimaan` (
  `id_pk_penerimaan` int(11) NOT NULL,
  `penerimaan_tgl` datetime DEFAULT NULL,
  `penerimaan_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `penerimaan_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penerimaan_create_date` datetime DEFAULT NULL,
  `penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_penerimaan`
--

INSERT INTO `mstr_penerimaan` (`id_pk_penerimaan`, `penerimaan_tgl`, `penerimaan_status`, `id_fk_pembelian`, `penerimaan_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `penerimaan_create_date`, `penerimaan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, '1111-11-11 00:00:00', 'nonaktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1);

--
-- 触发器 `mstr_penerimaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penerimaan` AFTER INSERT ON `mstr_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penerimaan` AFTER UPDATE ON `mstr_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penerimaan_log(executed_function,id_pk_penerimaan,penerimaan_tgl,penerimaan_status,id_fk_pembelian,penerimaan_tempat,id_fk_warehouse,id_fk_cabang,penerimaan_create_date,penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penerimaan,new.penerimaan_tgl,new.penerimaan_status,new.id_fk_pembelian,new.penerimaan_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.penerimaan_create_date,new.penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_penerimaan_log`
--

CREATE TABLE `mstr_penerimaan_log` (
  `id_pk_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penerimaan` int(11) DEFAULT NULL,
  `penerimaan_tgl` datetime DEFAULT NULL,
  `penerimaan_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `penerimaan_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penerimaan_create_date` datetime DEFAULT NULL,
  `penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_penerimaan_log`
--

INSERT INTO `mstr_penerimaan_log` (`id_pk_penerimaan_log`, `executed_function`, `id_pk_penerimaan`, `penerimaan_tgl`, `penerimaan_status`, `id_fk_pembelian`, `penerimaan_tempat`, `id_fk_warehouse`, `id_fk_cabang`, `penerimaan_create_date`, `penerimaan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, '1111-11-11 00:00:00', 'AKTIF', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:15', 1, 1, 267),
(2, 'after update', 1, '1111-11-11 00:00:00', 'AKTIF', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:58', 1, 1, 276),
(3, 'after update', 1, '1111-11-11 00:00:00', 'nonaktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 08:54:59', 1, 1, 285),
(4, 'after update', 1, '1111-11-11 00:00:00', 'aktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 08:54:59', 1, 1, 286),
(5, 'after update', 1, '1111-11-11 00:00:00', 'nonaktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:23', 1, 1, 287),
(6, 'after update', 1, '1111-11-11 00:00:00', 'aktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:23', 1, 1, 296),
(7, 'after update', 1, '1111-11-11 00:00:00', 'aktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:41', 1, 1, 297),
(8, 'after update', 1, '1111-11-11 00:00:00', 'nonaktif', 1, 'CABANG', NULL, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1, 306);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_pengiriman`
--

CREATE TABLE `mstr_pengiriman` (
  `id_pk_pengiriman` int(11) NOT NULL,
  `pengiriman_tgl` datetime DEFAULT NULL,
  `pengiriman_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `pengiriman_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pengiriman_create_date` datetime DEFAULT NULL,
  `pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `mstr_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_pengiriman` AFTER INSERT ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,id_fk_penjualan,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.id_fk_penjualan,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_pengiriman` AFTER UPDATE ON `mstr_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_pengiriman_log(executed_function,id_pk_pengiriman,pengiriman_tgl,pengiriman_status,id_fk_penjualan,pengiriman_tempat,id_fk_warehouse,id_fk_cabang,pengiriman_create_date,pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_pengiriman,new.pengiriman_tgl,new.pengiriman_status,new.id_fk_penjualan,new.pengiriman_tempat,new.id_fk_warehouse,new.id_fk_cabang,new.pengiriman_create_date,new.pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_pengiriman_log`
--

CREATE TABLE `mstr_pengiriman_log` (
  `id_pk_pengiriman_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_pengiriman` int(11) DEFAULT NULL,
  `pengiriman_tgl` datetime DEFAULT NULL,
  `pengiriman_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `pengiriman_tempat` varchar(30) DEFAULT NULL COMMENT 'warehouse/cabang',
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `pengiriman_create_date` datetime DEFAULT NULL,
  `pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_penjualan`
--

CREATE TABLE `mstr_penjualan` (
  `id_pk_penjualan` int(11) NOT NULL,
  `penj_nomor` varchar(30) DEFAULT NULL,
  `penj_tgl` datetime DEFAULT NULL,
  `penj_dateline_tgl` datetime DEFAULT NULL,
  `penj_jenis` varchar(50) DEFAULT NULL,
  `penj_tipe_pembayaran` varchar(50) DEFAULT NULL,
  `penj_status` varchar(15) DEFAULT NULL,
  `id_fk_customer` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penj_create_date` datetime DEFAULT NULL,
  `penj_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_penjualan`
--

INSERT INTO `mstr_penjualan` (`id_pk_penjualan`, `penj_nomor`, `penj_tgl`, `penj_dateline_tgl`, `penj_jenis`, `penj_tipe_pembayaran`, `penj_status`, `id_fk_customer`, `id_fk_cabang`, `penj_create_date`, `penj_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1);

--
-- 触发器 `mstr_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan` AFTER INSERT ON `mstr_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan` AFTER UPDATE ON `mstr_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_penjualan_log(executed_function,id_pk_penjualan,penj_nomor,penj_tgl,penj_dateline_tgl,penj_jenis,penj_tipe_pembayaran,penj_status,id_fk_customer,id_fk_cabang,penj_create_date,penj_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan,new.penj_nomor,new.penj_tgl,new.penj_dateline_tgl,new.penj_jenis,new.penj_tipe_pembayaran,new.penj_status,new.id_fk_customer,new.id_fk_cabang,new.penj_create_date,new.penj_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_penjualan_log`
--

CREATE TABLE `mstr_penjualan_log` (
  `id_pk_penjualan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan` int(11) DEFAULT NULL,
  `penj_nomor` varchar(30) DEFAULT NULL,
  `penj_tgl` datetime DEFAULT NULL,
  `penj_dateline_tgl` datetime DEFAULT NULL,
  `penj_jenis` varchar(50) DEFAULT NULL,
  `penj_tipe_pembayaran` varchar(50) DEFAULT NULL,
  `penj_status` varchar(15) DEFAULT NULL,
  `id_fk_customer` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `penj_create_date` datetime DEFAULT NULL,
  `penj_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_penjualan_log`
--

INSERT INTO `mstr_penjualan_log` (`id_pk_penjualan_log`, `executed_function`, `id_pk_penjualan`, `penj_nomor`, `penj_tgl`, `penj_dateline_tgl`, `penj_jenis`, `penj_tipe_pembayaran`, `penj_status`, `id_fk_customer`, `id_fk_cabang`, `penj_create_date`, `penj_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 318),
(2, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:53:30', 1, 1, 326),
(3, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 09:55:58', 1, 1, 328),
(4, 'after update', 1, 'nomorpenjualan1', '1111-11-11 00:00:00', '2222-02-22 00:00:00', 'ONLINE', 'DP', 'AKTIF', 1, 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1, 338);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_retur`
--

CREATE TABLE `mstr_retur` (
  `id_pk_retur` int(11) NOT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `retur_no` varchar(100) DEFAULT NULL,
  `retur_tgl` datetime DEFAULT NULL,
  `retur_tipe` varchar(15) DEFAULT NULL,
  `retur_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_retur_log`
--

CREATE TABLE `mstr_retur_log` (
  `id_pk_retur_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur` int(11) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `retur_no` varchar(100) DEFAULT NULL,
  `retur_tgl` datetime DEFAULT NULL,
  `retur_tipe` varchar(15) DEFAULT NULL,
  `retur_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_satuan`
--

CREATE TABLE `mstr_satuan` (
  `id_pk_satuan` int(11) NOT NULL,
  `satuan_nama` varchar(100) DEFAULT NULL,
  `satuan_rumus` varchar(100) DEFAULT NULL,
  `satuan_status` varchar(15) DEFAULT NULL,
  `satuan_create_date` datetime DEFAULT NULL,
  `satuan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_satuan`
--

INSERT INTO `mstr_satuan` (`id_pk_satuan`, `satuan_nama`, `satuan_rumus`, `satuan_status`, `satuan_create_date`, `satuan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'PCS', '1', 'AKTIF', '2020-06-22 08:36:13', '2020-06-22 08:36:13', 1, 1),
(2, 'BOX', '40', 'AKTIF', '2020-06-22 08:36:19', '2020-06-22 08:36:19', 1, 1),
(3, 'LUSIN', '12', 'AKTIF', '2020-06-22 08:36:23', '2020-06-22 08:36:23', 1, 1);

--
-- 触发器 `mstr_satuan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_satuan` AFTER INSERT ON `mstr_satuan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_satuan` AFTER UPDATE ON `mstr_satuan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.satuan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.satuan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_satuan_log(executed_function,id_pk_satuan,satuan_nama,satuan_rumus,satuan_status,satuan_create_date,satuan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_satuan,new.satuan_nama,new.satuan_status,new.satuan_rumus,new.satuan_create_date,new.satuan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_satuan_log`
--

CREATE TABLE `mstr_satuan_log` (
  `id_pk_satuan_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_satuan` int(11) DEFAULT NULL,
  `satuan_nama` varchar(100) DEFAULT NULL,
  `satuan_rumus` varchar(100) DEFAULT NULL,
  `satuan_status` varchar(15) DEFAULT NULL,
  `satuan_create_date` datetime DEFAULT NULL,
  `satuan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_satuan_log`
--

INSERT INTO `mstr_satuan_log` (`id_pk_satuan_log`, `executed_function`, `id_pk_satuan`, `satuan_nama`, `satuan_rumus`, `satuan_status`, `satuan_create_date`, `satuan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'PCS', 'AKTIF', '1', '2020-06-22 08:36:13', '2020-06-22 08:36:13', 1, 1, 264),
(2, 'after insert', 2, 'BOX', 'AKTIF', '40', '2020-06-22 08:36:19', '2020-06-22 08:36:19', 1, 1, 265),
(3, 'after insert', 3, 'LUSIN', 'AKTIF', '12', '2020-06-22 08:36:23', '2020-06-22 08:36:23', 1, 1, 266);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_stock_opname`
--

CREATE TABLE `mstr_stock_opname` (
  `ID_PK_STOCK_OPNAME` int(11) NOT NULL,
  `SO_TGL` datetime DEFAULT NULL,
  `SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_TOKO` int(11) DEFAULT NULL,
  `ID_EMP_DET` int(11) DEFAULT NULL,
  `SO_CREATE_DATE` datetime DEFAULT NULL,
  `SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 触发器 `mstr_stock_opname`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_STOCK_OPNAME` AFTER INSERT ON `mstr_stock_opname` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_STOCK_OPNAME_LOG(EXECUTED_FUNCTION,ID_PK_STOCK_OPNAME,SO_TGL,SO_NOTES,ID_FK_TOKO,ID_EMP_DET,SO_CREATE_DATE,SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_STOCK_OPNAME,NEW.SO_TGL,NEW.SO_NOTES,NEW.ID_FK_TOKO,NEW.ID_EMP_DET,NEW.SO_CREATE_DATE,NEW.SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_STOCK_OPNAME` AFTER UPDATE ON `mstr_stock_opname` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_STOCK_OPNAME_LOG(EXECUTED_FUNCTION,ID_PK_STOCK_OPNAME,SO_TGL,SO_NOTES,ID_FK_TOKO,ID_EMP_DET,SO_CREATE_DATE,SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_STOCK_OPNAME,NEW.SO_TGL,NEW.SO_NOTES,NEW.ID_FK_TOKO,NEW.ID_EMP_DET,NEW.SO_CREATE_DATE,NEW.SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_stock_opname_log`
--

CREATE TABLE `mstr_stock_opname_log` (
  `ID_PK_STOCK_OPNAME_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `SO_TGL` datetime DEFAULT NULL,
  `SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_TOKO` int(11) DEFAULT NULL,
  `ID_EMP_DET` int(11) DEFAULT NULL,
  `SO_CREATE_DATE` datetime DEFAULT NULL,
  `SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_supplier`
--

CREATE TABLE `mstr_supplier` (
  `id_pk_sup` int(11) NOT NULL,
  `sup_nama` varchar(100) DEFAULT NULL,
  `sup_suff` varchar(10) DEFAULT NULL,
  `sup_perusahaan` varchar(100) DEFAULT NULL,
  `sup_email` varchar(100) DEFAULT NULL,
  `sup_telp` varchar(30) DEFAULT NULL,
  `sup_hp` varchar(30) DEFAULT NULL,
  `sup_alamat` varchar(150) DEFAULT NULL,
  `sup_keterangan` varchar(150) DEFAULT NULL,
  `sup_status` varchar(15) DEFAULT NULL,
  `sup_create_date` datetime DEFAULT NULL,
  `sup_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_supplier`
--

INSERT INTO `mstr_supplier` (`id_pk_sup`, `sup_nama`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, NULL, NULL, 'Microsoft', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1),
(2, NULL, NULL, 'IBM', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1);

--
-- 触发器 `mstr_supplier`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_supplier` AFTER INSERT ON `mstr_supplier` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sup,new.sup_nama,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_supplier` AFTER UPDATE ON `mstr_supplier` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sup_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sup_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_supplier_log(executed_function,id_pk_sup,sup_nama,sup_suff,sup_perusahaan,sup_email,sup_telp,sup_hp,sup_alamat,sup_keterangan,sup_status,sup_create_date,sup_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sup,new.sup_nama,new.sup_suff,new.sup_perusahaan,new.sup_email,new.sup_telp,new.sup_hp,new.sup_alamat,new.sup_keterangan,new.sup_status,new.sup_create_date,new.sup_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_supplier_log`
--

CREATE TABLE `mstr_supplier_log` (
  `id_pk_sup_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_sup` int(11) DEFAULT NULL,
  `sup_nama` varchar(100) DEFAULT NULL,
  `sup_suff` varchar(10) DEFAULT NULL,
  `sup_perusahaan` varchar(100) DEFAULT NULL,
  `sup_email` varchar(100) DEFAULT NULL,
  `sup_telp` varchar(30) DEFAULT NULL,
  `sup_hp` varchar(30) DEFAULT NULL,
  `sup_alamat` varchar(150) DEFAULT NULL,
  `sup_keterangan` varchar(150) DEFAULT NULL,
  `sup_status` varchar(15) DEFAULT NULL,
  `sup_create_date` datetime DEFAULT NULL,
  `sup_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_supplier_log`
--

INSERT INTO `mstr_supplier_log` (`id_pk_sup_log`, `executed_function`, `id_pk_sup`, `sup_nama`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, NULL, NULL, 'Microsoft', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 235),
(2, 'after insert', 2, NULL, NULL, 'IBM', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 243);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_surat_jalan`
--

CREATE TABLE `mstr_surat_jalan` (
  `ID_PK_SURAT_JALAN` int(11) NOT NULL,
  `SJ_NOMOR` varchar(30) DEFAULT NULL,
  `SJ_TGL` datetime DEFAULT NULL,
  `SJ_PENERIMA` varchar(100) DEFAULT NULL,
  `SJ_PENGIRIM` varchar(100) DEFAULT NULL,
  `SJ_ACC` varchar(50) DEFAULT NULL,
  `SJ_NOTE` varchar(150) DEFAULT NULL,
  `SJ_NO_PENJUALAN` varchar(100) DEFAULT NULL,
  `SJ_JMLH_ITEM` double DEFAULT NULL,
  `SJ_TUJUAN` varchar(150) DEFAULT NULL,
  `SJ_ALAMAT` varchar(150) DEFAULT NULL,
  `SJ_STATUS` varchar(15) DEFAULT NULL,
  `ID_FK_PENJUALAN` int(11) DEFAULT NULL,
  `SJ_CREATE_DATE` datetime DEFAULT NULL,
  `SJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 触发器 `mstr_surat_jalan`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_SURAT_JALAN` AFTER INSERT ON `mstr_surat_jalan` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_SURAT_JALAN` AFTER UPDATE ON `mstr_surat_jalan` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO MSTR_SURAT_JALAN_LOG(EXECUTED_FUNCTION,ID_PK_SURAT_JALAN,SJ_NOMOR,SJ_TGL,SJ_PENERIMA,SJ_PENGIRIM,SJ_ACC,SJ_NOTE,SJ_NO_PENJUALAN,SJ_JMLH_ITEM,SJ_TUJUAN,SJ_ALAMAT,SJ_STATUS,ID_FK_PENJUALAN,SJ_CREATE_DATE,SJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SURAT_JALAN,NEW.SJ_NOMOR,NEW.SJ_TGL,NEW.SJ_PENERIMA,NEW.SJ_PENGIRIM,NEW.SJ_ACC,NEW.SJ_NOTE,NEW.SJ_NO_PENJUALAN,NEW.SJ_JMLH_ITEM,NEW.SJ_TUJUAN,NEW.SJ_ALAMAT,NEW.SJ_STATUS,NEW.ID_FK_PENJUALAN,NEW.SJ_CREATE_DATE,NEW.SJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_surat_jalan_log`
--

CREATE TABLE `mstr_surat_jalan_log` (
  `ID_PK_SURAT_JALAN_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SURAT_JALAN` int(11) DEFAULT NULL,
  `SJ_NOMOR` varchar(30) DEFAULT NULL,
  `SJ_TGL` datetime DEFAULT NULL,
  `SJ_PENERIMA` varchar(100) DEFAULT NULL,
  `SJ_PENGIRIM` varchar(100) DEFAULT NULL,
  `SJ_ACC` varchar(50) DEFAULT NULL,
  `SJ_NOTE` varchar(150) DEFAULT NULL,
  `SJ_NO_PENJUALAN` varchar(100) DEFAULT NULL,
  `SJ_JMLH_ITEM` double DEFAULT NULL,
  `SJ_TUJUAN` varchar(150) DEFAULT NULL,
  `SJ_ALAMAT` varchar(150) DEFAULT NULL,
  `SJ_STATUS` varchar(15) DEFAULT NULL,
  `ID_FK_PENJUALAN` int(11) DEFAULT NULL,
  `SJ_CREATE_DATE` datetime DEFAULT NULL,
  `SJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_toko`
--

CREATE TABLE `mstr_toko` (
  `id_pk_toko` int(11) NOT NULL,
  `toko_logo` varchar(100) DEFAULT NULL,
  `toko_nama` varchar(100) DEFAULT NULL,
  `toko_kode` varchar(20) DEFAULT NULL,
  `toko_status` varchar(15) DEFAULT NULL,
  `toko_create_date` datetime DEFAULT NULL,
  `toko_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_toko`
--

INSERT INTO `mstr_toko` (`id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Pendaftaran_SYNC_STUDY.png', 'TOKO MAJU MANDIRI', 'MM', 'AKTIF', '2020-06-21 11:44:14', '2020-06-21 11:44:14', 1, 1);

--
-- 触发器 `mstr_toko`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_toko` AFTER INSERT ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko,new.toko_logo,new.toko_nama,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_toko` AFTER UPDATE ON `mstr_toko` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.toko_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_toko_log(executed_function,id_pk_toko,toko_logo,toko_nama,toko_kode,toko_status,toko_create_date,toko_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko,new.toko_logo,new.toko_nama,new.toko_kode,new.toko_status,new.toko_create_date,new.toko_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_toko_log`
--

CREATE TABLE `mstr_toko_log` (
  `id_pk_toko_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_toko` int(11) DEFAULT NULL,
  `toko_logo` varchar(100) DEFAULT NULL,
  `toko_nama` varchar(100) DEFAULT NULL,
  `toko_kode` varchar(20) DEFAULT NULL,
  `toko_status` varchar(15) DEFAULT NULL,
  `toko_create_date` datetime DEFAULT NULL,
  `toko_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_toko_log`
--

INSERT INTO `mstr_toko_log` (`id_pk_toko_log`, `executed_function`, `id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Pendaftaran_SYNC_STUDY.png', 'TOKO MAJU MANDIRI', 'MM', 'AKTIF', '2020-06-21 11:44:14', '2020-06-21 11:44:14', 1, 1, 88);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_user`
--

CREATE TABLE `mstr_user` (
  `id_pk_user` int(11) NOT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_pass` varchar(200) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_status` varchar(15) DEFAULT NULL,
  `id_fk_role` int(11) DEFAULT NULL,
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_user`
--

INSERT INTO `mstr_user` (`id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0);

--
-- 触发器 `mstr_user`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_user` AFTER INSERT ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at',' ', new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after insert',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_user` AFTER UPDATE ON `mstr_user` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.user_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.user_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_user_log(executed_function,id_pk_user,user_name,user_pass,user_email,user_status,id_fk_role,user_last_modified,user_create_date,id_create_date,id_last_modified,id_log_all) values('after update',new.id_pk_user,new.user_name,new.user_pass,new.user_email,new.user_status,new.id_fk_role,new.user_last_modified,new.user_create_date,new.id_create_date,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_user_log`
--

CREATE TABLE `mstr_user_log` (
  `id_pk_user_log` int(11) NOT NULL,
  `executed_function` varchar(40) DEFAULT NULL,
  `id_pk_user` int(11) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_pass` varchar(200) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_status` varchar(15) DEFAULT NULL,
  `id_fk_role` int(11) DEFAULT NULL,
  `user_last_modified` datetime DEFAULT NULL,
  `user_create_date` datetime DEFAULT NULL,
  `id_create_date` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_user_log`
--

INSERT INTO `mstr_user_log` (`id_pk_user_log`, `executed_function`, `id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0, 2);

-- --------------------------------------------------------

--
-- 表的结构 `mstr_warehouse`
--

CREATE TABLE `mstr_warehouse` (
  `id_pk_warehouse` int(11) NOT NULL,
  `warehouse_nama` varchar(100) DEFAULT NULL,
  `warehouse_alamat` varchar(200) DEFAULT NULL,
  `warehouse_notelp` varchar(30) DEFAULT NULL,
  `warehouse_desc` varchar(150) DEFAULT NULL,
  `warehouse_status` varchar(15) DEFAULT NULL,
  `warehouse_create_date` datetime DEFAULT NULL,
  `warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_warehouse`
--

INSERT INTO `mstr_warehouse` (`id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1);

--
-- 触发器 `mstr_warehouse`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_warehouse` AFTER INSERT ON `mstr_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_warehouse` AFTER UPDATE ON `mstr_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into mstr_warehouse_log(executed_function,id_pk_warehouse,warehouse_nama,warehouse_alamat,warehouse_notelp,warehouse_desc,warehouse_status,warehouse_create_date,warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_warehouse,new.warehouse_nama,new.warehouse_alamat,new.warehouse_notelp,new.warehouse_desc,new.warehouse_status,new.warehouse_create_date,new.warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `mstr_warehouse_log`
--

CREATE TABLE `mstr_warehouse_log` (
  `id_pk_warehouse_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_warehouse` int(11) DEFAULT NULL,
  `warehouse_nama` varchar(100) DEFAULT NULL,
  `warehouse_alamat` varchar(200) DEFAULT NULL,
  `warehouse_notelp` varchar(30) DEFAULT NULL,
  `warehouse_desc` varchar(150) DEFAULT NULL,
  `warehouse_status` varchar(15) DEFAULT NULL,
  `warehouse_create_date` datetime DEFAULT NULL,
  `warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `mstr_warehouse_log`
--

INSERT INTO `mstr_warehouse_log` (`id_pk_warehouse_log`, `executed_function`, `id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1, 92);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_barang_kombinasi`
--

CREATE TABLE `tbl_barang_kombinasi` (
  `id_pk_barang_kombinasi` int(11) NOT NULL,
  `id_barang_utama` int(11) DEFAULT NULL,
  `id_barang_kombinasi` int(11) DEFAULT NULL,
  `barang_kombinasi_qty` double DEFAULT NULL,
  `barang_kombinasi_status` varchar(15) DEFAULT NULL,
  `barang_kombinasi_create_date` datetime DEFAULT NULL,
  `barang_kombinasi_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_barang_kombinasi`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_barang_kombinasi` AFTER INSERT ON `tbl_barang_kombinasi` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_barang_kombinasi` AFTER UPDATE ON `tbl_barang_kombinasi` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.barang_kombinasi_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.barang_kombinasi_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_barang_kombinasi_log(executed_function,id_pk_barang_kombinasi,id_barang_utama,id_barang_kombinasi,barang_kombinasi_qty,barang_kombinasi_status,barang_kombinasi_create_date,barang_kombinasi_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_barang_kombinasi,new.id_barang_utama,new.id_barang_kombinasi,new.barang_kombinasi_qty,new.barang_kombinasi_status,new.barang_kombinasi_create_date,new.barang_kombinasi_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_barang_kombinasi_log`
--

CREATE TABLE `tbl_barang_kombinasi_log` (
  `id_pk_barang_kombinasi_log` int(11) NOT NULL,
  `executed_function` varchar(20) DEFAULT NULL,
  `id_pk_barang_kombinasi` int(11) DEFAULT NULL,
  `id_barang_utama` int(11) DEFAULT NULL,
  `id_barang_kombinasi` int(11) DEFAULT NULL,
  `barang_kombinasi_qty` double DEFAULT NULL,
  `barang_kombinasi_status` varchar(15) DEFAULT NULL,
  `barang_kombinasi_create_date` datetime DEFAULT NULL,
  `barang_kombinasi_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_barang_ukuran`
--

CREATE TABLE `tbl_barang_ukuran` (
  `ID_PK_BARANG_UKURAN` int(11) NOT NULL,
  `ID_FK_BARANG` int(11) DEFAULT NULL,
  `UKURAN` varchar(10) DEFAULT NULL,
  `BRG_UKURAN_STATUS` varchar(15) DEFAULT NULL,
  `BRG_UKURAN_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_UKURAN_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATE` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `tbl_barang_ukuran`
--

INSERT INTO `tbl_barang_ukuran` (`ID_PK_BARANG_UKURAN`, `ID_FK_BARANG`, `UKURAN`, `BRG_UKURAN_STATUS`, `BRG_UKURAN_CREATE_DATE`, `BRG_UKURAN_LAST_MODIFIED`, `ID_CREATE_DATE`, `ID_LAST_MODIFIED`) VALUES
(4, 11, ',', 'AKTIF', '2020-05-08 15:54:57', '2020-05-08 15:54:57', 1, 1),
(8, 12, ',', 'AKTIF', '2020-05-08 15:57:11', '2020-05-08 15:57:11', 1, 1),
(9, 13, '5', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(10, 13, '6', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(11, 13, '7', 'AKTIF', '2020-05-08 15:58:36', '2020-05-08 15:58:36', 1, 1),
(12, 14, '5', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(13, 14, '6', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(14, 14, '7', 'AKTIF', '2020-05-08 15:58:45', '2020-05-08 15:58:45', 1, 1),
(15, 15, '33', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(16, 15, '44', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(17, 15, '55', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(18, 15, '66', 'AKTIF', '2020-05-08 15:59:06', '2020-05-08 15:59:06', 1, 1),
(23, 44, '11', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(24, 44, '12', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(25, 44, '13', 'AKTIF', '2020-05-21 23:41:10', '2020-05-21 23:41:10', 1, 1),
(59, 45, '11', 'AKTIF', '2020-05-26 08:11:44', '2020-05-26 08:11:44', 1, 1),
(60, 3, '7', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(61, 3, '5', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(62, 3, '6', 'AKTIF', '2020-05-29 03:38:40', '2020-05-29 03:38:40', 1, 1),
(63, 43, '11', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(64, 43, '22', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(65, 43, '33', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(66, 43, '44', 'AKTIF', '2020-05-29 03:39:26', '2020-05-29 03:39:26', 1, 1),
(70, 2, '11', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(71, 2, '12', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(72, 2, '13', 'AKTIF', '2020-05-29 03:40:17', '2020-05-29 03:40:17', 1, 1),
(73, 4, '1', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1),
(74, 4, '2', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1),
(75, 4, '3', 'AKTIF', '2020-05-29 03:41:03', '2020-05-29 03:41:03', 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_cabang`
--

CREATE TABLE `tbl_brg_cabang` (
  `id_pk_brg_cabang` int(11) NOT NULL,
  `brg_cabang_qty` int(11) DEFAULT NULL,
  `brg_cabang_notes` varchar(200) DEFAULT NULL,
  `brg_cabang_status` varchar(15) DEFAULT NULL,
  `brg_cabang_last_price` int(11) DEFAULT 0,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_cabang_create_date` datetime DEFAULT NULL,
  `brg_cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_cabang`
--

INSERT INTO `tbl_brg_cabang` (`id_pk_brg_cabang`, `brg_cabang_qty`, `brg_cabang_notes`, `brg_cabang_status`, `brg_cabang_last_price`, `id_fk_brg`, `id_fk_cabang`, `brg_cabang_create_date`, `brg_cabang_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 10, '-', 'nonaktif', 0, 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1),
(2, 10, '-', 'nonaktif', 30000, 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1),
(3, 10, '-', 'nonaktif', 40000, 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1),
(4, 10, '-', 'nonaktif', 0, 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1),
(5, 10, '-', 'nonaktif', 30000, 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1),
(6, 10, '-', 'nonaktif', 40000, 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1),
(7, 15, '-', 'AKTIF', 0, 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1),
(8, 15, '-', 'AKTIF', 30000, 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1),
(9, 10, '-', 'AKTIF', 40000, 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1);

--
-- 触发器 `tbl_brg_cabang`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_cabang` AFTER INSERT ON `tbl_brg_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_cabang` AFTER UPDATE ON `tbl_brg_cabang` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_cabang_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_cabang_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_cabang_log(executed_function,id_pk_brg_cabang,brg_cabang_qty,brg_cabang_last_price,brg_cabang_notes,brg_cabang_status,id_fk_brg,id_fk_cabang,brg_cabang_create_date,brg_cabang_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_cabang,new.brg_cabang_last_price,new.brg_cabang_qty,new.brg_cabang_notes,new.brg_cabang_status,new.id_fk_brg,new.id_fk_cabang,new.brg_cabang_create_date,new.brg_cabang_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_cabang_log`
--

CREATE TABLE `tbl_brg_cabang_log` (
  `id_pk_brg_cabang_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_cabang` int(11) DEFAULT NULL,
  `brg_cabang_qty` int(11) DEFAULT NULL,
  `brg_cabang_last_price` int(11) DEFAULT 0,
  `brg_cabang_notes` varchar(200) DEFAULT NULL,
  `brg_cabang_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_cabang_create_date` datetime DEFAULT NULL,
  `brg_cabang_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_cabang_log`
--

INSERT INTO `tbl_brg_cabang_log` (`id_pk_brg_cabang_log`, `executed_function`, `id_pk_brg_cabang`, `brg_cabang_qty`, `brg_cabang_last_price`, `brg_cabang_notes`, `brg_cabang_status`, `id_fk_brg`, `id_fk_cabang`, `brg_cabang_create_date`, `brg_cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:03:53', 1, 1, 218),
(2, 'after insert', 2, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:04:32', 1, 1, 219),
(3, 'after insert', 3, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:04:32', 1, 1, 220),
(4, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 221),
(5, 'after update', 2, 0, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:07:25', 1, 1, 222),
(6, 'after update', 3, 0, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:07:27', 1, 1, 223),
(7, 'after insert', 4, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 224),
(8, 'after insert', 5, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 225),
(9, 'after insert', 6, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:07:40', 1, 1, 226),
(10, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 227),
(11, 'after update', 5, 0, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:58', 1, 1, 228),
(12, 'after update', 6, 0, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:09:00', 1, 1, 229),
(13, 'after insert', 7, 0, 10, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 230),
(14, 'after insert', 8, 0, 10, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 231),
(15, 'after insert', 9, 0, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:09:14', 1, 1, 232),
(16, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 233),
(17, 'after update', 8, 0, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:06', 1, 1, 234),
(18, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 246),
(19, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 247),
(20, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 248),
(21, 'after update', 3, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 250),
(22, 'after update', 6, 40000, 10, '-', 'nonaktif', 3, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 251),
(23, 'after update', 9, 40000, 10, '-', 'AKTIF', 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 252),
(24, 'after update', 1, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 269),
(25, 'after update', 4, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 270),
(26, 'after update', 7, 0, 16, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 271),
(27, 'after update', 2, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 273),
(28, 'after update', 5, 30000, 11, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 274),
(29, 'after update', 8, 30000, 16, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 275),
(30, 'after update', 1, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 278),
(31, 'after update', 4, 0, 11, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 279),
(32, 'after update', 7, 0, 16, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 280),
(33, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 282),
(34, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 283),
(35, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 284),
(36, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 289),
(37, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 290),
(38, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 291),
(39, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 293),
(40, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 294),
(41, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 295),
(42, 'after update', 1, 0, 12, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 299),
(43, 'after update', 4, 0, 12, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 300),
(44, 'after update', 7, 0, 17, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 301),
(45, 'after update', 2, 30000, 12, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 303),
(46, 'after update', 5, 30000, 12, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 304),
(47, 'after update', 8, 30000, 17, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 305),
(48, 'after update', 1, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:03:53', '2020-06-22 08:07:23', 1, 1, 308),
(49, 'after update', 4, 0, 10, '-', 'nonaktif', 1, 1, '2020-06-22 08:07:40', '2020-06-22 08:08:55', 1, 1, 309),
(50, 'after update', 7, 0, 15, '-', 'AKTIF', 1, 1, '2020-06-22 08:09:14', '2020-06-22 08:10:02', 1, 1, 310),
(51, 'after update', 2, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:04:32', '2020-06-22 08:26:28', 1, 1, 312),
(52, 'after update', 5, 30000, 10, '-', 'nonaktif', 2, 1, '2020-06-22 08:07:40', '2020-06-22 08:26:28', 1, 1, 313),
(53, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 314);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pembelian`
--

CREATE TABLE `tbl_brg_pembelian` (
  `id_pk_brg_pembelian` int(11) NOT NULL,
  `brg_pem_qty` double DEFAULT NULL,
  `brg_pem_satuan` varchar(20) DEFAULT NULL,
  `brg_pem_harga` int(11) DEFAULT NULL,
  `brg_pem_note` varchar(150) DEFAULT NULL,
  `brg_pem_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_pem_create_date` datetime DEFAULT NULL,
  `brg_pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_pembelian`
--

INSERT INTO `tbl_brg_pembelian` (`id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(5, 10, 'Pcs', 20000, '-', 'nonaktif', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:18', 1, 1);

--
-- 触发器 `tbl_brg_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pembelian` AFTER INSERT ON `tbl_brg_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pembelian` AFTER UPDATE ON `tbl_brg_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pem_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pem_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pembelian_log(executed_function,id_pk_brg_pembelian,brg_pem_qty,brg_pem_satuan,brg_pem_harga,brg_pem_note,brg_pem_status,id_fk_pembelian,id_fk_barang,brg_pem_create_date,brg_pem_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pembelian,new.brg_pem_qty,new.brg_pem_satuan,new.brg_pem_harga,new.brg_pem_note,new.brg_pem_status,new.id_fk_pembelian,new.id_fk_barang,new.brg_pem_create_date,new.brg_pem_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pembelian_log`
--

CREATE TABLE `tbl_brg_pembelian_log` (
  `id_pk_brg_pembelian_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pembelian` int(11) DEFAULT NULL,
  `brg_pem_qty` double DEFAULT NULL,
  `brg_pem_satuan` varchar(20) DEFAULT NULL,
  `brg_pem_harga` int(11) DEFAULT NULL,
  `brg_pem_note` varchar(150) DEFAULT NULL,
  `brg_pem_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_pem_create_date` datetime DEFAULT NULL,
  `brg_pem_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_pembelian_log`
--

INSERT INTO `tbl_brg_pembelian_log` (`id_pk_brg_pembelian_log`, `executed_function`, `id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 237),
(2, 'after insert', 2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 238),
(3, 'after update', 1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 240),
(4, 'after update', 2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1, 241),
(5, 'after insert', 3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 245),
(6, 'after insert', 4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 249),
(7, 'after update', 3, 4, 'Pcs', 300000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 255),
(8, 'after update', 4, 5, 'Pcs', 400000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:26:54', 1, 1, 256),
(9, 'after update', 3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 259),
(10, 'after update', 4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1, 260),
(11, 'after insert', 5, 10, 'Pcs', 20000, '-', 'AKTIF', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:08', 1, 1, 261),
(12, 'after update', 5, 10, 'Pcs', 20000, '-', 'nonaktif', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:18', 1, 1, 263);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pemenuhan`
--

CREATE TABLE `tbl_brg_pemenuhan` (
  `id_pk_brg_pemenuhan` int(11) NOT NULL,
  `brg_pemenuhan_qty` int(11) DEFAULT NULL,
  `brg_pemenuhan_tipe` varchar(9) DEFAULT NULL COMMENT 'warehouse/cabang',
  `brg_pemenuhan_status` varchar(8) DEFAULT NULL COMMENT 'aktif/nonaktif',
  `id_fk_brg_permintaan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_pemenuhan_create_date` datetime DEFAULT NULL,
  `brg_pemenuhan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_brg_pemenuhan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pemenuhan` AFTER INSERT ON `tbl_brg_pemenuhan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pemenuhan` AFTER UPDATE ON `tbl_brg_pemenuhan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pemenuhan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_pemenuhan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pemenuhan_log(executed_function,
            id_pk_brg_pemenuhan,
            brg_pemenuhan_qty,
            brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            id_fk_brg_permintaan,
            id_fk_cabang,
            id_fk_warehouse,
            brg_pemenuhan_create_date,
            brg_pemenuhan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_pemenuhan,
            new.brg_pemenuhan_qty,
            new.brg_pemenuhan_tipe,
            brg_pemenuhan_status,
            new.id_fk_brg_permintaan,
            new.id_fk_cabang,
            new.id_fk_warehouse,
            new.brg_pemenuhan_create_date,
            new.brg_pemenuhan_last_modified,
            new.id_create_data,
            new.id_last_modified
            ,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pemenuhan_log`
--

CREATE TABLE `tbl_brg_pemenuhan_log` (
  `id_pk_brg_pemenuhan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pemenuhan` int(11) DEFAULT NULL,
  `brg_pemenuhan_qty` int(11) DEFAULT NULL,
  `brg_pemenuhan_tipe` varchar(9) DEFAULT NULL COMMENT 'warehouse/cabang',
  `brg_pemenuhan_status` varchar(8) DEFAULT NULL COMMENT 'aktif/nonaktif',
  `id_fk_brg_permintaan` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_pemenuhan_create_date` datetime DEFAULT NULL,
  `brg_pemenuhan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_penerimaan`
--

CREATE TABLE `tbl_brg_penerimaan` (
  `id_pk_brg_penerimaan` int(11) NOT NULL,
  `brg_penerimaan_qty` double DEFAULT NULL,
  `brg_penerimaan_note` varchar(200) DEFAULT NULL,
  `id_fk_penerimaan` int(11) DEFAULT NULL,
  `id_fk_brg_pembelian` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_penerimaan_create_date` datetime DEFAULT NULL,
  `brg_penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_penerimaan`
--

INSERT INTO `tbl_brg_penerimaan` (`id_pk_brg_penerimaan`, `brg_penerimaan_qty`, `brg_penerimaan_note`, `id_fk_penerimaan`, `id_fk_brg_pembelian`, `id_fk_satuan`, `brg_penerimaan_create_date`, `brg_penerimaan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 0, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1),
(2, 0, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1);

--
-- 触发器 `tbl_brg_penerimaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_penerimaan` AFTER INSERT ON `tbl_brg_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_penerimaan_qty = new.brg_penerimaan_qty;
            set @id_satuan_terima = new.id_fk_satuan;
            select id_fk_cabang, id_fk_barang, id_fk_warehouse into @id_cabang,@id_barang,@id_warehouse from tbl_brg_penerimaan
            inner join tbl_brg_pembelian on tbl_brg_pembelian.id_pk_brg_pembelian = tbl_brg_penerimaan.ID_FK_BRG_PEMBELIAN
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;

            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,@brg_penerimaan_qty,@id_satuan_terima,0,0);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,0,0);
            end if;

        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_penerimaan` AFTER UPDATE ON `tbl_brg_penerimaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penerimaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penerimaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penerimaan_log(executed_function,id_pk_brg_penerimaan,brg_penerimaan_qty,brg_penerimaan_note,id_fk_penerimaan,id_fk_brg_pembelian,id_fk_satuan,brg_penerimaan_create_date,brg_penerimaan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_penerimaan,new.brg_penerimaan_qty,new.brg_penerimaan_note,new.id_fk_penerimaan,new.id_fk_brg_pembelian,new.id_fk_satuan,new.brg_penerimaan_create_date,new.brg_penerimaan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

            set @id_cabang = 0;
            set @id_barang = 0;
            set @id_warehouse = 0;
            set @brg_penerimaan_qty = new.brg_penerimaan_qty;
            set @id_satuan_terima = new.id_fk_satuan;
            set @brg_keluar_qty = old.brg_penerimaan_qty;
            set @id_satuan_keluar = old.id_fk_satuan;

            select id_fk_cabang, id_fk_barang,id_fk_warehouse into @id_cabang, @id_barang,@id_warehouse from tbl_brg_penerimaan
            inner join tbl_brg_pembelian on tbl_brg_pembelian.id_pk_brg_pembelian = tbl_brg_penerimaan.ID_FK_BRG_PEMBELIAN
            inner join mstr_penerimaan on mstr_penerimaan.id_pk_penerimaan = tbl_brg_penerimaan.id_fk_penerimaan
            where id_pk_brg_penerimaan = new.id_pk_brg_penerimaan;
            
            if @id_warehouse is not null then
            call update_stok_barang_warehouse(@id_barang,@id_warehouse,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
            elseif @id_cabang is not null then 
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
            end if;

        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_penerimaan_log`
--

CREATE TABLE `tbl_brg_penerimaan_log` (
  `id_pk_brg_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_penerimaan` int(11) DEFAULT NULL,
  `brg_penerimaan_qty` double DEFAULT NULL,
  `brg_penerimaan_note` varchar(200) DEFAULT NULL,
  `id_fk_penerimaan` int(11) DEFAULT NULL,
  `id_fk_brg_pembelian` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_penerimaan_create_date` datetime DEFAULT NULL,
  `brg_penerimaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_penerimaan_log`
--

INSERT INTO `tbl_brg_penerimaan_log` (`id_pk_brg_penerimaan_log`, `executed_function`, `id_pk_brg_penerimaan`, `brg_penerimaan_qty`, `brg_penerimaan_note`, `id_fk_penerimaan`, `id_fk_brg_pembelian`, `id_fk_satuan`, `brg_penerimaan_create_date`, `brg_penerimaan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:15', 1, 1, 268),
(2, 'after insert', 2, 1, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:15', 1, 1, 272),
(3, 'after update', 1, 1, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:58', 1, 1, 277),
(4, 'after update', 2, 0, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 08:52:58', 1, 1, 281),
(5, 'after update', 1, 0, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:23', 1, 1, 288),
(6, 'after update', 2, 0, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:23', 1, 1, 292),
(7, 'after update', 1, 2, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:41', 1, 1, 298),
(8, 'after update', 2, 2, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:41', 1, 1, 302),
(9, 'after update', 1, 0, '-', 1, 1, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1, 307),
(10, 'after update', 2, 0, '-', 1, 2, 1, '2020-06-22 08:52:15', '2020-06-22 09:00:47', 1, 1, 311);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pengiriman`
--

CREATE TABLE `tbl_brg_pengiriman` (
  `id_pk_brg_pengiriman` int(11) NOT NULL,
  `brg_pengiriman_qty` double DEFAULT NULL,
  `brg_pengiriman_note` varchar(200) DEFAULT NULL,
  `id_fk_pengiriman` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_pengiriman_create_date` datetime DEFAULT NULL,
  `brg_pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_brg_pengiriman`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pengiriman` AFTER INSERT ON `tbl_brg_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @brg_pengiriman_qty = new.brg_pengiriman_qty;
            set @id_satuan_kirim = new.id_fk_satuan;
            
            select id_fk_cabang, id_fk_barang into @id_cabang, @id_barang 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            call update_stok_barang_cabang(@id_barang,@id_cabang,0,0,@brg_pengiriman_qty,@id_satuan_kirim);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pengiriman` AFTER UPDATE ON `tbl_brg_pengiriman` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pengiriman_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pengiriman_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pengiriman_log(executed_function,id_pk_brg_pengiriman,brg_pengiriman_qty,brg_pengiriman_note,id_fk_pengiriman,id_fk_brg_penjualan,id_fk_satuan,brg_pengiriman_create_date,brg_pengiriman_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pengiriman,new.brg_pengiriman_qty,new.brg_pengiriman_note,new.id_fk_pengiriman,new.id_fk_brg_penjualan,new.id_fk_satuan,new.brg_pengiriman_create_date,new.brg_pengiriman_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
            
            set @id_cabang = 0;
            set @id_barang = 0;
            set @brg_keluar_qty = new.brg_pengiriman_qty;
            set @id_satuan_keluar = new.id_fk_satuan;
            set @brg_penerimaan_qty = old.brg_pengiriman_qty;
            set @id_satuan_terima = old.id_fk_satuan;

            select id_fk_cabang, id_fk_barang into @id_cabang, @id_barang 
            from tbl_brg_pengiriman
            inner join tbl_brg_penjualan on tbl_brg_penjualan.id_pk_brg_penjualan = tbl_brg_pengiriman.id_fk_brg_penjualan
            inner join mstr_penjualan on mstr_penjualan.id_pk_penjualan = tbl_brg_penjualan.id_fk_penjualan
            where id_pk_brg_pengiriman = new.id_pk_brg_pengiriman;
            call update_stok_barang_cabang(@id_barang,@id_cabang,@brg_penerimaan_qty,@id_satuan_terima,@brg_keluar_qty,@id_satuan_keluar);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pengiriman_log`
--

CREATE TABLE `tbl_brg_pengiriman_log` (
  `id_pk_brg_pengiriman_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pengiriman` int(11) DEFAULT NULL,
  `brg_pengiriman_qty` double DEFAULT NULL,
  `brg_pengiriman_note` varchar(200) DEFAULT NULL,
  `id_fk_pengiriman` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `brg_pengiriman_create_date` datetime DEFAULT NULL,
  `brg_pengiriman_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_penjualan`
--

CREATE TABLE `tbl_brg_penjualan` (
  `id_pk_brg_penjualan` int(11) NOT NULL,
  `brg_penjualan_qty_real` double DEFAULT NULL,
  `brg_penjualan_satuan_real` varchar(20) DEFAULT NULL,
  `brg_penjualan_qty` double DEFAULT NULL,
  `brg_penjualan_satuan` varchar(20) DEFAULT NULL,
  `brg_penjualan_harga` int(11) DEFAULT NULL,
  `brg_penjualan_note` varchar(150) DEFAULT NULL,
  `brg_penjualan_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_penjualan_create_date` datetime DEFAULT NULL,
  `brg_penjualan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_penjualan`
--

INSERT INTO `tbl_brg_penjualan` (`id_pk_brg_penjualan`, `brg_penjualan_qty_real`, `brg_penjualan_satuan_real`, `brg_penjualan_qty`, `brg_penjualan_satuan`, `brg_penjualan_harga`, `brg_penjualan_note`, `brg_penjualan_status`, `id_fk_penjualan`, `id_fk_barang`, `brg_penjualan_create_date`, `brg_penjualan_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1),
(2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1),
(3, 3, 'Pcs', 4, 'Pcs', 4000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1);

--
-- 触发器 `tbl_brg_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_penjualan` AFTER INSERT ON `tbl_brg_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_penjualan` AFTER UPDATE ON `tbl_brg_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_penjualan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_penjualan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_penjualan_log(executed_function,id_pk_brg_penjualan,brg_penjualan_qty_real,brg_penjualan_satuan_real,brg_penjualan_qty,brg_penjualan_satuan,brg_penjualan_harga,brg_penjualan_note,brg_penjualan_status,id_fk_penjualan,id_fk_barang,brg_penjualan_create_date,brg_penjualan_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_penjualan,new.brg_penjualan_qty_real,new.brg_penjualan_satuan_real,new.brg_penjualan_qty,new.brg_penjualan_satuan,new.brg_penjualan_harga,new.brg_penjualan_note,new.brg_penjualan_status,new.id_fk_penjualan,new.id_fk_barang,new.brg_penjualan_create_date,new.brg_penjualan_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_penjualan_log`
--

CREATE TABLE `tbl_brg_penjualan_log` (
  `id_pk_brg_penjualan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_penjualan` int(11) DEFAULT NULL,
  `brg_penjualan_qty_real` double DEFAULT NULL,
  `brg_penjualan_satuan_real` varchar(20) DEFAULT NULL,
  `brg_penjualan_qty` double DEFAULT NULL,
  `brg_penjualan_satuan` varchar(20) DEFAULT NULL,
  `brg_penjualan_harga` int(11) DEFAULT NULL,
  `brg_penjualan_note` varchar(150) DEFAULT NULL,
  `brg_penjualan_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `id_fk_barang` int(11) DEFAULT NULL,
  `brg_penjualan_create_date` datetime DEFAULT NULL,
  `brg_penjualan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_penjualan_log`
--

INSERT INTO `tbl_brg_penjualan_log` (`id_pk_brg_penjualan_log`, `executed_function`, `id_pk_brg_penjualan`, `brg_penjualan_qty_real`, `brg_penjualan_satuan_real`, `brg_penjualan_qty`, `brg_penjualan_satuan`, `brg_penjualan_harga`, `brg_penjualan_note`, `brg_penjualan_status`, `id_fk_penjualan`, `id_fk_barang`, `brg_penjualan_create_date`, `brg_penjualan_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 320),
(2, 'after insert', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 321),
(3, 'after insert', 3, 3, 'Pcs', 4, 'Pcs', 40000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 330),
(4, 'after update', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 331),
(5, 'after update', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 332),
(6, 'after update', 1, 1, 'Pcs', 2, 'Pcs', 20000, '-', 'AKTIF', 1, 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 340),
(7, 'after update', 2, 2, 'Pcs', 2, 'Pcs', 30000, '-', 'AKTIF', 1, 2, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 341),
(8, 'after update', 3, 3, 'Pcs', 4, 'Pcs', 4000, '-', 'AKTIF', 1, 3, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 342);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_permintaan`
--

CREATE TABLE `tbl_brg_permintaan` (
  `id_pk_brg_permintaan` int(11) NOT NULL,
  `brg_permintaan_qty` int(11) DEFAULT NULL,
  `brg_permintaan_notes` text DEFAULT NULL,
  `brg_permintaan_deadline` date DEFAULT NULL,
  `brg_permintaan_status` varchar(7) DEFAULT NULL COMMENT 'BELUM/SEDANG/SUDAH/BATAL',
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_permintaan_create_date` datetime DEFAULT NULL,
  `brg_permintaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_brg_permintaan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_permintaan` AFTER INSERT ON `tbl_brg_permintaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_permintaan` AFTER UPDATE ON `tbl_brg_permintaan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_permintaan_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_permintaan_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_permintaan_log(executed_function,
            id_pk_brg_permintaan,
            brg_permintaan_qty,
            brg_permintaan_notes,
            brg_permintaan_deadline,
            brg_permintaan_status,
            id_fk_brg,
            id_fk_cabang,
            brg_permintaan_create_date,
            brg_permintaan_last_modified,
            id_create_data,
            id_last_modified,
            id_log_all) values ('after insert',
            new.id_pk_brg_permintaan,
            new.brg_permintaan_qty,
            new.brg_permintaan_notes,
            new.brg_permintaan_deadline,
            new.brg_permintaan_status,
            new.id_fk_brg,
            new.id_fk_cabang,
            new.brg_permintaan_create_date,
            new.brg_permintaan_last_modified,
            new.id_create_data,
            new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_permintaan_log`
--

CREATE TABLE `tbl_brg_permintaan_log` (
  `id_pk_penerimaan_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_permintaan` int(11) DEFAULT NULL,
  `brg_permintaan_qty` int(11) DEFAULT NULL,
  `brg_permintaan_notes` text DEFAULT NULL,
  `brg_permintaan_deadline` date DEFAULT NULL,
  `brg_permintaan_status` varchar(7) DEFAULT NULL COMMENT 'BELUM/SEDANG/SUDAH/BATAL',
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `brg_permintaan_create_date` datetime DEFAULT NULL,
  `brg_permintaan_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pindah`
--

CREATE TABLE `tbl_brg_pindah` (
  `id_pk_brg_pindah` int(11) NOT NULL,
  `brg_pindah_sumber` varchar(50) DEFAULT NULL COMMENT 'warehouse/penjualan/...',
  `id_fk_refrensi_sumber` int(11) DEFAULT NULL COMMENT 'id_warehouse/id_penjualan/...',
  `id_brg_awal` int(11) DEFAULT NULL,
  `id_brg_tujuan` int(11) DEFAULT NULL,
  `brg_pindah_qty` double DEFAULT NULL,
  `brg_pindah_status` varchar(15) DEFAULT NULL,
  `brg_pindah_create_date` datetime DEFAULT NULL,
  `brg_pindah_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_pindah`
--

INSERT INTO `tbl_brg_pindah` (`id_pk_brg_pindah`, `brg_pindah_sumber`, `id_fk_refrensi_sumber`, `id_brg_awal`, `id_brg_tujuan`, `brg_pindah_qty`, `brg_pindah_status`, `brg_pindah_create_date`, `brg_pindah_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'penjualan', 0, 1, 2, 5, 'AKTIF', '2020-06-22 09:08:48', '2020-06-22 09:08:48', 1, 1),
(2, 'penjualan', 1, 1, 2, 5, 'AKTIF', '2020-06-22 09:38:00', '2020-06-22 09:38:00', 1, 1);

--
-- 触发器 `tbl_brg_pindah`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_pindah` AFTER INSERT ON `tbl_brg_pindah` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pindah_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.brg_pindah_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_pindah` AFTER UPDATE ON `tbl_brg_pindah` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_pindah_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.brg_pindah_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_pindah_log(executed_function,id_pk_brg_pindah,brg_pindah_sumber,id_fk_refrensi_sumber,id_brg_awal,id_brg_tujuan,brg_pindah_qty,brg_pindah_status,brg_pindah_create_date,brg_pindah_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_pindah,new.brg_pindah_sumber,new.id_fk_refrensi_sumber,new.id_brg_awal,new.id_brg_tujuan,new.brg_pindah_qty,new.brg_pindah_status,new.brg_pindah_create_date,new.brg_pindah_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_pindah_log`
--

CREATE TABLE `tbl_brg_pindah_log` (
  `id_pk_brg_pindah_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_pindah` int(11) DEFAULT NULL,
  `brg_pindah_sumber` varchar(50) DEFAULT NULL COMMENT 'warehouse/penjualan/...',
  `id_fk_refrensi_sumber` int(11) DEFAULT NULL COMMENT 'id_warehouse/id_penjualan/...',
  `id_brg_awal` int(11) DEFAULT NULL,
  `id_brg_tujuan` int(11) DEFAULT NULL,
  `brg_pindah_qty` double DEFAULT NULL,
  `brg_pindah_status` varchar(15) DEFAULT NULL,
  `brg_pindah_create_date` datetime DEFAULT NULL,
  `brg_pindah_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_brg_pindah_log`
--

INSERT INTO `tbl_brg_pindah_log` (`id_pk_brg_pindah_log`, `executed_function`, `id_pk_brg_pindah`, `brg_pindah_sumber`, `id_fk_refrensi_sumber`, `id_brg_awal`, `id_brg_tujuan`, `brg_pindah_qty`, `brg_pindah_status`, `brg_pindah_create_date`, `brg_pindah_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'penjualan', 0, 1, 2, 5, 'AKTIF', '2020-06-22 09:08:48', '2020-06-22 09:08:48', 1, 1, 315),
(2, 'after insert', 2, 'penjualan', 0, 1, 2, 5, 'AKTIF', '2020-06-22 09:38:00', '2020-06-22 09:38:00', 1, 1, 316),
(3, 'after update', 2, 'penjualan', 1, 1, 2, 5, 'AKTIF', '2020-06-22 09:38:00', '2020-06-22 09:38:00', 1, 1, 325);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_so`
--

CREATE TABLE `tbl_brg_so` (
  `ID_PK_SO_BRG` int(11) NOT NULL,
  `BRG_SO_RESULT` double DEFAULT NULL,
  `BRG_SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_BRG` int(11) DEFAULT NULL,
  `BRG_SO_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 触发器 `tbl_brg_so`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_BRG_SO` AFTER INSERT ON `tbl_brg_so` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_BRG_SO` AFTER UPDATE ON `tbl_brg_so` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.BRG_SO_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.BRG_SO_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_BRG_SO_LOG(EXECUTED_FUNCTION,ID_PK_SO_BRG,BRG_SO_RESULT,BRG_SO_NOTES,ID_FK_STOCK_OPNAME,ID_FK_BRG,BRG_SO_CREATE_DATE,BRG_SO_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_BRG,NEW.BRG_SO_RESULT,NEW.BRG_SO_NOTES,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_BRG,NEW.BRG_SO_CREATE_DATE,NEW.BRG_SO_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_so_log`
--

CREATE TABLE `tbl_brg_so_log` (
  `ID_PK_SO_BRG_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SO_BRG` int(11) DEFAULT NULL,
  `BRG_SO_RESULT` double DEFAULT NULL,
  `BRG_SO_NOTES` varchar(200) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_BRG` int(11) DEFAULT NULL,
  `BRG_SO_CREATE_DATE` datetime DEFAULT NULL,
  `BRG_SO_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_warehouse`
--

CREATE TABLE `tbl_brg_warehouse` (
  `id_pk_brg_warehouse` int(11) NOT NULL,
  `brg_warehouse_qty` int(11) DEFAULT NULL,
  `brg_warehouse_notes` varchar(200) DEFAULT NULL,
  `brg_warehouse_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_create_date` datetime DEFAULT NULL,
  `brg_warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_brg_warehouse`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_brg_warehouse` AFTER INSERT ON `tbl_brg_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_brg_warehouse` AFTER UPDATE ON `tbl_brg_warehouse` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.brg_warehouse_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.brg_warehouse_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_brg_warehouse_log(executed_function,id_pk_brg_warehouse,brg_warehouse_qty,brg_warehouse_notes,brg_warehouse_status,id_fk_brg,id_fk_warehouse,brg_warehouse_create_date,brg_warehouse_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_brg_warehouse,new.brg_warehouse_qty,new.brg_warehouse_notes,new.brg_warehouse_status,new.id_fk_brg,new.id_fk_warehouse,new.brg_warehouse_create_date,new.brg_warehouse_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_brg_warehouse_log`
--

CREATE TABLE `tbl_brg_warehouse_log` (
  `id_pk_brg_warehouse_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_brg_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_qty` int(11) DEFAULT NULL,
  `brg_warehouse_notes` varchar(200) DEFAULT NULL,
  `brg_warehouse_status` varchar(15) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `brg_warehouse_create_date` datetime DEFAULT NULL,
  `brg_warehouse_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_cabang_admin`
--

CREATE TABLE `tbl_cabang_admin` (
  `id_pk_cabang_admin` int(11) NOT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `cabang_admin_status` varchar(15) DEFAULT NULL,
  `cabang_admin_create_date` datetime DEFAULT NULL,
  `cabang_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_cabang_admin`
--

INSERT INTO `tbl_cabang_admin` (`id_pk_cabang_admin`, `id_fk_cabang`, `id_fk_user`, `cabang_admin_status`, `cabang_admin_create_date`, `cabang_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'AKTIF', '2020-06-21 11:45:03', '2020-06-21 11:45:03', 1, 1);

--
-- 触发器 `tbl_cabang_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_cabang_admin` AFTER INSERT ON `tbl_cabang_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_cabang_admin` AFTER UPDATE ON `tbl_cabang_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.cabang_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.cabang_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_cabang_admin_log(executed_function,id_pk_cabang_admin,id_fk_cabang,id_fk_user,cabang_admin_status,cabang_admin_create_date,cabang_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_cabang_admin,new.id_fk_cabang,new.id_fk_user,new.cabang_admin_status,new.cabang_admin_create_date,new.cabang_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_cabang_admin_log`
--

CREATE TABLE `tbl_cabang_admin_log` (
  `id_pk_cabang_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_cabang_admin` int(11) DEFAULT NULL,
  `id_fk_cabang` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `cabang_admin_status` varchar(15) DEFAULT NULL,
  `cabang_admin_create_date` datetime DEFAULT NULL,
  `cabang_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_cabang_admin_log`
--

INSERT INTO `tbl_cabang_admin_log` (`id_pk_cabang_admin_log`, `executed_function`, `id_pk_cabang_admin`, `id_fk_cabang`, `id_fk_user`, `cabang_admin_status`, `cabang_admin_create_date`, `cabang_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:45:03', '2020-06-21 11:45:03', 1, 1, 91);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_hak_akses`
--

CREATE TABLE `tbl_hak_akses` (
  `id_pk_hak_akses` int(11) NOT NULL,
  `id_fk_jabatan` int(11) DEFAULT NULL,
  `id_fk_menu` int(11) DEFAULT NULL,
  `hak_akses_status` varchar(15) DEFAULT NULL,
  `hak_akses_create_date` datetime DEFAULT NULL,
  `hak_akses_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_hak_akses`
--

INSERT INTO `tbl_hak_akses` (`id_pk_hak_akses`, `id_fk_jabatan`, `id_fk_menu`, `hak_akses_status`, `hak_akses_create_date`, `hak_akses_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1),
(2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1),
(3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1),
(4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1),
(5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1),
(6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1),
(7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1),
(8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1),
(9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1),
(10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1),
(11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1),
(12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1),
(13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1),
(14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1),
(15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1),
(16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1),
(17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1),
(18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1),
(19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1),
(20, 1, 20, 'aktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1),
(21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1),
(22, 2, 1, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(24, 2, 3, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(42, 2, 21, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1),
(43, 3, 1, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(44, 3, 2, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(45, 3, 3, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(46, 3, 4, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(47, 3, 5, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(48, 3, 6, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(49, 3, 7, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(50, 3, 8, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(51, 3, 9, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(52, 3, 10, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(53, 3, 11, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(54, 3, 12, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(55, 3, 13, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(56, 3, 14, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(57, 3, 15, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(58, 3, 16, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(59, 3, 17, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(60, 3, 18, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(61, 3, 19, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(62, 3, 20, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(63, 3, 21, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1),
(64, 4, 1, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(65, 4, 2, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(66, 4, 3, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(67, 4, 4, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(68, 4, 5, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(69, 4, 6, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(70, 4, 7, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(71, 4, 8, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(72, 4, 9, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(73, 4, 10, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(74, 4, 11, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(75, 4, 12, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(76, 4, 13, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(77, 4, 14, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(78, 4, 15, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(79, 4, 16, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(80, 4, 17, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(81, 4, 18, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(82, 4, 19, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(83, 4, 20, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1),
(84, 4, 21, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1);

--
-- 触发器 `tbl_hak_akses`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_hak_akses` AFTER INSERT ON `tbl_hak_akses` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_hak_akses` AFTER UPDATE ON `tbl_hak_akses` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.hak_akses_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.hak_akses_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_hak_akses_log(executed_function,id_pk_hak_akses,id_fk_jabatan,id_fk_menu,hak_akses_status,hak_akses_create_date,hak_akses_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_hak_akses,new.id_fk_jabatan,new.id_fk_menu,new.hak_akses_status,new.hak_akses_create_date,new.hak_akses_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_hak_akses_log`
--

CREATE TABLE `tbl_hak_akses_log` (
  `id_pk_hak_akses_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_hak_akses` int(11) DEFAULT NULL,
  `id_fk_jabatan` int(11) DEFAULT NULL,
  `id_fk_menu` int(11) DEFAULT NULL,
  `hak_akses_status` varchar(15) DEFAULT NULL,
  `hak_akses_create_date` datetime DEFAULT NULL,
  `hak_akses_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_hak_akses_log`
--

INSERT INTO `tbl_hak_akses_log` (`id_pk_hak_akses_log`, `executed_function`, `id_pk_hak_akses`, `id_fk_jabatan`, `id_fk_menu`, `hak_akses_status`, `hak_akses_create_date`, `hak_akses_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 6),
(2, 'after insert', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 7),
(3, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 9),
(4, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 10),
(5, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 11),
(6, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 12),
(7, 'after insert', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 14),
(8, 'after insert', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 16),
(9, 'after insert', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 18),
(10, 'after insert', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 20),
(11, 'after insert', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 22),
(12, 'after insert', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 24),
(13, 'after insert', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 26),
(14, 'after insert', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 29),
(15, 'after insert', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 31),
(16, 'after insert', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 33),
(17, 'after insert', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 35),
(18, 'after insert', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 37),
(19, 'after insert', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 39),
(20, 'after insert', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 41),
(21, 'after insert', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 43),
(22, 'after insert', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 45),
(23, 'after insert', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 47),
(24, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 49),
(25, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 50),
(26, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 51),
(27, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 52),
(28, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 53),
(29, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 54),
(30, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 55),
(31, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 56),
(32, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 57),
(33, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 58),
(34, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 59),
(35, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 60),
(36, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 61),
(37, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 62),
(38, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 63),
(39, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 64),
(40, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 65),
(41, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 66),
(42, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 67),
(43, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 68),
(44, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 69),
(45, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 70),
(46, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 71),
(47, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 72),
(48, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 73),
(49, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 74),
(50, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 75),
(51, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 76),
(52, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 77),
(53, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 78),
(54, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 79),
(55, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 80),
(56, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 81),
(57, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 82),
(58, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 83),
(59, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 84),
(60, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 85),
(61, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 86),
(62, 'after insert', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 95),
(63, 'after insert', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 97),
(64, 'after update', 1, 1, 1, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 99),
(65, 'after update', 2, 1, 2, 'nonaktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 100),
(66, 'after update', 3, 1, 3, 'nonaktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 101),
(67, 'after update', 4, 1, 4, 'nonaktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 102),
(68, 'after update', 5, 1, 5, 'nonaktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 103),
(69, 'after update', 6, 1, 6, 'nonaktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 104),
(70, 'after update', 7, 1, 7, 'nonaktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 105),
(71, 'after update', 8, 1, 8, 'nonaktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 106),
(72, 'after update', 9, 1, 9, 'nonaktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 107),
(73, 'after update', 10, 1, 10, 'nonaktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 108),
(74, 'after update', 11, 1, 11, 'nonaktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 109),
(75, 'after update', 12, 1, 12, 'nonaktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 110),
(76, 'after update', 13, 1, 13, 'nonaktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 111),
(77, 'after update', 14, 1, 14, 'nonaktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 112),
(78, 'after update', 15, 1, 15, 'nonaktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 113),
(79, 'after update', 16, 1, 16, 'nonaktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 114),
(80, 'after update', 17, 1, 17, 'nonaktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 115),
(81, 'after update', 18, 1, 18, 'nonaktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 116),
(82, 'after update', 19, 1, 19, 'nonaktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 117),
(83, 'after update', 20, 1, 20, 'nonaktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 118),
(84, 'after update', 21, 1, 21, 'nonaktif', '2020-06-22 07:50:23', '2020-06-22 07:50:23', 1, 1, 119),
(85, 'after update', 1, 1, 1, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 120),
(86, 'after update', 2, 1, 2, 'aktif', '2020-06-21 11:28:57', '2020-06-21 11:28:57', 1, 1, 121),
(87, 'after update', 3, 1, 3, 'aktif', '2020-06-21 11:38:11', '2020-06-21 11:38:11', 1, 1, 122),
(88, 'after update', 4, 1, 4, 'aktif', '2020-06-21 11:38:23', '2020-06-21 11:38:23', 1, 1, 123),
(89, 'after update', 5, 1, 5, 'aktif', '2020-06-21 11:38:35', '2020-06-21 11:38:35', 1, 1, 124),
(90, 'after update', 6, 1, 6, 'aktif', '2020-06-21 11:38:44', '2020-06-21 11:38:44', 1, 1, 125),
(91, 'after update', 7, 1, 7, 'aktif', '2020-06-21 11:38:54', '2020-06-21 11:38:54', 1, 1, 126),
(92, 'after update', 8, 1, 8, 'aktif', '2020-06-21 11:39:36', '2020-06-21 11:39:36', 1, 1, 127),
(93, 'after update', 9, 1, 9, 'aktif', '2020-06-21 11:40:07', '2020-06-21 11:40:07', 1, 1, 128),
(94, 'after update', 10, 1, 10, 'aktif', '2020-06-21 11:40:52', '2020-06-21 11:40:52', 1, 1, 129),
(95, 'after update', 11, 1, 11, 'aktif', '2020-06-21 11:41:04', '2020-06-21 11:41:04', 1, 1, 130),
(96, 'after update', 12, 1, 12, 'aktif', '2020-06-21 11:41:23', '2020-06-21 11:41:23', 1, 1, 131),
(97, 'after update', 13, 1, 13, 'aktif', '2020-06-21 11:41:33', '2020-06-21 11:41:33', 1, 1, 132),
(98, 'after update', 14, 1, 14, 'aktif', '2020-06-21 11:41:42', '2020-06-21 11:41:42', 1, 1, 133),
(99, 'after update', 15, 1, 15, 'aktif', '2020-06-21 11:41:58', '2020-06-21 11:41:58', 1, 1, 134),
(100, 'after update', 16, 1, 16, 'aktif', '2020-06-21 11:42:07', '2020-06-21 11:42:07', 1, 1, 135),
(101, 'after update', 17, 1, 17, 'aktif', '2020-06-21 11:42:16', '2020-06-21 11:42:16', 1, 1, 136),
(102, 'after update', 18, 1, 18, 'aktif', '2020-06-21 11:42:28', '2020-06-21 11:42:28', 1, 1, 137),
(103, 'after update', 19, 1, 19, 'aktif', '2020-06-21 11:42:37', '2020-06-21 11:42:37', 1, 1, 138),
(104, 'after update', 20, 1, 20, 'aktif', '2020-06-22 12:12:04', '2020-06-22 12:12:04', 1, 1, 139),
(105, 'after insert', 22, 2, 1, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 141),
(106, 'after insert', 23, 2, 2, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 142),
(107, 'after insert', 24, 2, 3, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 143),
(108, 'after insert', 25, 2, 4, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 144),
(109, 'after insert', 26, 2, 5, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 145),
(110, 'after insert', 27, 2, 6, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 146),
(111, 'after insert', 28, 2, 7, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 147),
(112, 'after insert', 29, 2, 8, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 148),
(113, 'after insert', 30, 2, 9, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 149),
(114, 'after insert', 31, 2, 10, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 150),
(115, 'after insert', 32, 2, 11, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 151),
(116, 'after insert', 33, 2, 12, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 152),
(117, 'after insert', 34, 2, 13, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 153),
(118, 'after insert', 35, 2, 14, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 154),
(119, 'after insert', 36, 2, 15, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 155),
(120, 'after insert', 37, 2, 16, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 156),
(121, 'after insert', 38, 2, 17, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 157),
(122, 'after insert', 39, 2, 18, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 158),
(123, 'after insert', 40, 2, 19, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 159),
(124, 'after insert', 41, 2, 20, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 160),
(125, 'after insert', 42, 2, 21, 'nonaktif', '2020-06-22 07:51:13', '2020-06-22 07:51:13', 1, 1, 161),
(126, 'after insert', 43, 3, 1, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 164),
(127, 'after insert', 44, 3, 2, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 165),
(128, 'after insert', 45, 3, 3, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 166),
(129, 'after insert', 46, 3, 4, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 167),
(130, 'after insert', 47, 3, 5, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 168),
(131, 'after insert', 48, 3, 6, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 169),
(132, 'after insert', 49, 3, 7, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 170),
(133, 'after insert', 50, 3, 8, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 171),
(134, 'after insert', 51, 3, 9, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 172),
(135, 'after insert', 52, 3, 10, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 173),
(136, 'after insert', 53, 3, 11, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 174),
(137, 'after insert', 54, 3, 12, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 175),
(138, 'after insert', 55, 3, 13, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 176),
(139, 'after insert', 56, 3, 14, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 177),
(140, 'after insert', 57, 3, 15, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 178),
(141, 'after insert', 58, 3, 16, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 179),
(142, 'after insert', 59, 3, 17, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 180),
(143, 'after insert', 60, 3, 18, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 181),
(144, 'after insert', 61, 3, 19, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 182),
(145, 'after insert', 62, 3, 20, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 183),
(146, 'after insert', 63, 3, 21, 'nonaktif', '2020-06-22 07:53:15', '2020-06-22 07:53:15', 1, 1, 184),
(147, 'after insert', 64, 4, 1, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 186),
(148, 'after insert', 65, 4, 2, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 187),
(149, 'after insert', 66, 4, 3, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 188),
(150, 'after insert', 67, 4, 4, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 189),
(151, 'after insert', 68, 4, 5, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 190),
(152, 'after insert', 69, 4, 6, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 191),
(153, 'after insert', 70, 4, 7, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 192),
(154, 'after insert', 71, 4, 8, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 193),
(155, 'after insert', 72, 4, 9, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 194),
(156, 'after insert', 73, 4, 10, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 195),
(157, 'after insert', 74, 4, 11, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 196),
(158, 'after insert', 75, 4, 12, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 197),
(159, 'after insert', 76, 4, 13, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 198),
(160, 'after insert', 77, 4, 14, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 199),
(161, 'after insert', 78, 4, 15, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 200),
(162, 'after insert', 79, 4, 16, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 201),
(163, 'after insert', 80, 4, 17, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 202),
(164, 'after insert', 81, 4, 18, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 203),
(165, 'after insert', 82, 4, 19, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 204),
(166, 'after insert', 83, 4, 20, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 205),
(167, 'after insert', 84, 4, 21, 'nonaktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 206),
(168, 'after update', 64, 4, 1, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 207),
(169, 'after update', 65, 4, 2, 'aktif', '2020-06-22 08:02:21', '2020-06-22 08:02:21', 1, 1, 208);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_penjualan_online`
--

CREATE TABLE `tbl_penjualan_online` (
  `id_pk_penjualan_online` int(11) NOT NULL,
  `penj_on_marketplace` varchar(40) DEFAULT NULL,
  `penj_on_no_resi` varchar(40) DEFAULT NULL,
  `penj_on_kurir` varchar(40) DEFAULT NULL,
  `penj_on_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penj_on_create_date` datetime DEFAULT NULL,
  `penj_on_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_penjualan_online`
--

INSERT INTO `tbl_penjualan_online` (`id_pk_penjualan_online`, `penj_on_marketplace`, `penj_on_no_resi`, `penj_on_kurir`, `penj_on_status`, `id_fk_penjualan`, `penj_on_create_date`, `penj_on_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1);

--
-- 触发器 `tbl_penjualan_online`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan_online` AFTER INSERT ON `tbl_penjualan_online` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan_online` AFTER UPDATE ON `tbl_penjualan_online` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penj_on_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penj_on_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_online_log(executed_function,id_pk_penjualan_online,penj_on_marketplace,penj_on_no_resi,penj_on_kurir,penj_on_status,id_fk_penjualan,penj_on_create_date,penj_on_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_online,new.penj_on_marketplace,new.penj_on_no_resi,new.penj_on_kurir,new.penj_on_status,new.id_fk_penjualan,new.penj_on_create_date,new.penj_on_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_penjualan_online_log`
--

CREATE TABLE `tbl_penjualan_online_log` (
  `id_pk_penjualan_online_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan_online` int(11) DEFAULT NULL,
  `penj_on_marketplace` varchar(40) DEFAULT NULL,
  `penj_on_no_resi` varchar(40) DEFAULT NULL,
  `penj_on_kurir` varchar(40) DEFAULT NULL,
  `penj_on_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penj_on_create_date` datetime DEFAULT NULL,
  `penj_on_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_penjualan_online_log`
--

INSERT INTO `tbl_penjualan_online_log` (`id_pk_penjualan_online_log`, `executed_function`, `id_pk_penjualan_online`, `penj_on_marketplace`, `penj_on_no_resi`, `penj_on_kurir`, `penj_on_status`, `id_fk_penjualan`, `penj_on_create_date`, `penj_on_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 319),
(2, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:53:30', 1, 1, 327),
(3, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 09:55:58', 1, 1, 329),
(4, 'after update', 1, 'Tokopedia', '1234567', 'JNE', 'AKTIF', 1, '2020-06-22 09:39:50', '2020-06-22 10:05:28', 1, 1, 339);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_penjualan_pembayaran`
--

CREATE TABLE `tbl_penjualan_pembayaran` (
  `id_pk_penjualan_pembayaran` int(11) NOT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_nama` varchar(100) DEFAULT NULL,
  `penjualan_pmbyrn_persen` double DEFAULT NULL,
  `penjualan_pmbyrn_nominal` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_notes` varchar(200) DEFAULT NULL,
  `penjualan_pmbyrn_dateline` datetime DEFAULT NULL,
  `penjualan_pmbyrn_status` varchar(15) DEFAULT NULL,
  `penjualan_pmbyrn_create_date` datetime DEFAULT NULL,
  `penjualan_pmbyrn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_penjualan_pembayaran`
--

INSERT INTO `tbl_penjualan_pembayaran` (`id_pk_penjualan_pembayaran`, `id_fk_penjualan`, `penjualan_pmbyrn_nama`, `penjualan_pmbyrn_persen`, `penjualan_pmbyrn_nominal`, `penjualan_pmbyrn_notes`, `penjualan_pmbyrn_dateline`, `penjualan_pmbyrn_status`, `penjualan_pmbyrn_create_date`, `penjualan_pmbyrn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'Pembayaran DP', 50, 62000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1),
(2, 1, 'Pelunasan 1', 25, 31000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1),
(3, 1, 'Pelunasan 2', 25, 31000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1);

--
-- 触发器 `tbl_penjualan_pembayaran`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_penjualan_pembayaran` AFTER INSERT ON `tbl_penjualan_pembayaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_penjualan_pembayaran` AFTER UPDATE ON `tbl_penjualan_pembayaran` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.penjualan_pmbyrn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.penjualan_pmbyrn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_penjualan_pembayaran_log(executed_function,id_pk_penjualan_pembayaran,id_fk_penjualan,penjualan_pmbyrn_nama,penjualan_pmbyrn_persen,penjualan_pmbyrn_nominal,penjualan_pmbyrn_notes,penjualan_pmbyrn_dateline,penjualan_pmbyrn_status,penjualan_pmbyrn_create_date,penjualan_pmbyrn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_penjualan_pembayaran,new.id_fk_penjualan,new.penjualan_pmbyrn_nama,new.penjualan_pmbyrn_persen,new.penjualan_pmbyrn_nominal,new.penjualan_pmbyrn_notes,new.penjualan_pmbyrn_dateline,new.penjualan_pmbyrn_status,new.penjualan_pmbyrn_create_date,new.penjualan_pmbyrn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_penjualan_pembayaran_log`
--

CREATE TABLE `tbl_penjualan_pembayaran_log` (
  `id_pk_penjualan_pembayaran_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_penjualan_pembayaran` int(11) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_nama` varchar(100) DEFAULT NULL,
  `penjualan_pmbyrn_persen` double DEFAULT NULL,
  `penjualan_pmbyrn_nominal` int(11) DEFAULT NULL,
  `penjualan_pmbyrn_notes` varchar(200) DEFAULT NULL,
  `penjualan_pmbyrn_dateline` datetime DEFAULT NULL,
  `penjualan_pmbyrn_status` varchar(15) DEFAULT NULL,
  `penjualan_pmbyrn_create_date` datetime DEFAULT NULL,
  `penjualan_pmbyrn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_penjualan_pembayaran_log`
--

INSERT INTO `tbl_penjualan_pembayaran_log` (`id_pk_penjualan_pembayaran_log`, `executed_function`, `id_pk_penjualan_pembayaran`, `id_fk_penjualan`, `penjualan_pmbyrn_nama`, `penjualan_pmbyrn_persen`, `penjualan_pmbyrn_nominal`, `penjualan_pmbyrn_notes`, `penjualan_pmbyrn_dateline`, `penjualan_pmbyrn_status`, `penjualan_pmbyrn_create_date`, `penjualan_pmbyrn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 'Pembayaran DP', 50, 52000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 323),
(2, 'after insert', 2, 1, 'Pelunasan', 50, 52000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 324),
(3, 'after insert', 3, 1, 'Pelunasan 2', 25, 26000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 335),
(4, 'after update', 1, 1, 'Pembayaran DP', 50, 52000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 336),
(5, 'after update', 2, 1, 'Pelunasan 1', 25, 26000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 337),
(6, 'after update', 1, 1, 'Pembayaran DP', 50, 62000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 345),
(7, 'after update', 2, 1, 'Pelunasan 1', 25, 31000, '-', '2222-02-22 00:00:00', 'AKTIF', '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 346),
(8, 'after update', 3, 1, 'Pelunasan 2', 25, 31000, '-', '1111-11-11 00:00:00', 'AKTIF', '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 347);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_retur_brg`
--

CREATE TABLE `tbl_retur_brg` (
  `id_pk_retur_brg` int(11) NOT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_brg_qty` double DEFAULT NULL,
  `retur_brg_satuan` varchar(30) DEFAULT NULL,
  `retur_brg_notes` varchar(100) DEFAULT NULL,
  `retur_brg_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_retur_brg`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur` AFTER INSERT ON `tbl_retur_brg` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after insert',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);

        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur` AFTER UPDATE ON `tbl_retur_brg` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at ' , new.retur_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_brg_log(executed_function,id_pk_retur_brg,id_fk_retur,id_fk_brg,retur_brg_qty,retur_brg_satuan,retur_brg_notes,retur_brg_status,retur_create_date,retur_last_modified,id_create_data,id_last_modified,id_log_all) values('after update',new.id_pk_retur_brg,new.id_fk_retur,new.id_fk_brg,new.retur_brg_qty,new.retur_brg_satuan,new.retur_brg_notes,new.retur_brg_status,new.retur_create_date,new.retur_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_retur_brg_log`
--

CREATE TABLE `tbl_retur_brg_log` (
  `id_pk_retur_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur_brg` int(11) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_brg_qty` double DEFAULT NULL,
  `retur_brg_satuan` varchar(30) DEFAULT NULL,
  `retur_brg_notes` varchar(100) DEFAULT NULL,
  `retur_brg_status` varchar(15) DEFAULT NULL,
  `retur_create_date` datetime DEFAULT NULL,
  `retur_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_retur_kembali`
--

CREATE TABLE `tbl_retur_kembali` (
  `id_pk_retur_kembali` int(11) NOT NULL,
  `retur_kembali_qty_real` double DEFAULT NULL,
  `retur_kembali_satuan_real` varchar(20) DEFAULT NULL,
  `retur_kembali_qty` double DEFAULT NULL,
  `retur_kembali_satuan` varchar(20) DEFAULT NULL,
  `retur_kembali_harga` int(11) DEFAULT NULL,
  `retur_kembali_note` varchar(150) DEFAULT NULL,
  `retur_kembali_status` varchar(15) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_kembali_create_date` datetime DEFAULT NULL,
  `retur_kembali_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_retur_kembali`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_retur_kembali` AFTER INSERT ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_retur_kembali` AFTER UPDATE ON `tbl_retur_kembali` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.retur_kembali_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.retur_kembali_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_retur_kembali_log(executed_function,id_pk_retur_kembali,retur_kembali_qty_real,retur_kembali_satuan_real,retur_kembali_qty,retur_kembali_satuan,retur_kembali_harga,retur_kembali_note,retur_kembali_status,id_fk_retur,id_fk_brg,retur_kembali_create_date,retur_kembali_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_retur_kembali,new.retur_kembali_qty_real,new.retur_kembali_satuan_real,new.retur_kembali_qty,new.retur_kembali_satuan,new.retur_kembali_harga,new.retur_kembali_note,new.retur_kembali_status,new.id_fk_retur,new.id_fk_brg,new.retur_kembali_create_date,new.retur_kembali_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_retur_kembali_log`
--

CREATE TABLE `tbl_retur_kembali_log` (
  `id_pk_retur_kembali_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_retur_kembali` int(11) DEFAULT NULL,
  `retur_kembali_qty_real` double DEFAULT NULL,
  `retur_kembali_satuan_real` varchar(20) DEFAULT NULL,
  `retur_kembali_qty` double DEFAULT NULL,
  `retur_kembali_satuan` varchar(20) DEFAULT NULL,
  `retur_kembali_harga` int(11) DEFAULT NULL,
  `retur_kembali_note` varchar(150) DEFAULT NULL,
  `retur_kembali_status` varchar(15) DEFAULT NULL,
  `id_fk_retur` int(11) DEFAULT NULL,
  `id_fk_brg` int(11) DEFAULT NULL,
  `retur_kembali_create_date` datetime DEFAULT NULL,
  `retur_kembali_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_sj_item`
--

CREATE TABLE `tbl_sj_item` (
  `id_pk_sj_item` int(11) NOT NULL,
  `sj_item_qty` double DEFAULT NULL,
  `sj_item_note` varchar(150) DEFAULT NULL,
  `sj_item_status` varchar(15) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `id_fk_surat_jalan` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `sj_item_create_date` datetime DEFAULT NULL,
  `sj_item_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 触发器 `tbl_sj_item`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_sj_item` AFTER INSERT ON `tbl_sj_item` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_sj_item` AFTER UPDATE ON `tbl_sj_item` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.sj_item_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.sj_item_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_sj_item_log(executed_function,id_pk_sj_item,sj_item_qty,sj_item_note,sj_item_status,id_fk_satuan,id_fk_surat_jalan,id_fk_brg_penjualan,sj_item_create_date,sj_item_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_sj_item,new.sj_item_qty,new.sj_item_note,new.sj_item_status,new.id_fk_satuan,new.id_fk_surat_jalan,new.id_fk_brg_penjualan,new.sj_item_create_date,new.sj_item_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_sj_item_log`
--

CREATE TABLE `tbl_sj_item_log` (
  `id_pk_sj_item_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_sj_item` int(11) DEFAULT NULL,
  `sj_item_qty` double DEFAULT NULL,
  `sj_item_note` varchar(150) DEFAULT NULL,
  `sj_item_status` varchar(15) DEFAULT NULL,
  `id_fk_satuan` int(11) DEFAULT NULL,
  `id_fk_surat_jalan` int(11) DEFAULT NULL,
  `id_fk_brg_penjualan` int(11) DEFAULT NULL,
  `sj_item_create_date` datetime DEFAULT NULL,
  `sj_item_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_so_pj`
--

CREATE TABLE `tbl_so_pj` (
  `ID_PK_SO_PJ` int(11) NOT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_EMP` int(11) DEFAULT NULL,
  `SO_PJ_CREATE_DATE` datetime DEFAULT NULL,
  `SO_PJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 触发器 `tbl_so_pj`
--
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_INSERT_SO_PJ` AFTER INSERT ON `tbl_so_pj` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','INSERT DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER INSERT',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_AFTER_UPDATE_SO_PJ` AFTER UPDATE ON `tbl_so_pj` FOR EACH ROW BEGIN
    SET @ID_USER = NEW.ID_LAST_MODIFIED;
    SET @TGL_ACTION = NEW.SO_PJ_LAST_MODIFIED;
    SET @LOG_TEXT = CONCAT(NEW.ID_LAST_MODIFIED,' ','UPDATE DATA AT' , NEW.SO_PJ_LAST_MODIFIED);
    CALL INSERT_LOG_ALL(@ID_USER,@TGL_ACTION,@LOG_TEXT,@ID_LOG_ALL);
    
    INSERT INTO TBL_SO_PJ_LOG(EXECUTED_FUNCTION,ID_PK_SO_PJ,ID_FK_STOCK_OPNAME,ID_FK_EMP,SO_PJ_CREATE_DATE,SO_PJ_LAST_MODIFIED,ID_CREATE_DATA,ID_LAST_MODIFIED,ID_LOG_ALL) VALUES ('AFTER UPDATE',NEW.ID_PK_SO_PJ,NEW.ID_FK_STOCK_OPNAME,NEW.ID_FK_EMP,NEW.SO_PJ_CREATE_DATE,NEW.SO_PJ_LAST_MODIFIED,NEW.ID_CREATE_DATA,NEW.ID_LAST_MODIFIED,@ID_LOG_ALL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_so_pj_log`
--

CREATE TABLE `tbl_so_pj_log` (
  `ID_PK_SO_PJ_LOG` int(11) NOT NULL,
  `EXECUTED_FUNCTION` varchar(30) DEFAULT NULL,
  `ID_PK_SO_PJ` int(11) DEFAULT NULL,
  `ID_FK_STOCK_OPNAME` int(11) DEFAULT NULL,
  `ID_FK_EMP` int(11) DEFAULT NULL,
  `SO_PJ_CREATE_DATE` datetime DEFAULT NULL,
  `SO_PJ_LAST_MODIFIED` datetime DEFAULT NULL,
  `ID_CREATE_DATA` int(11) DEFAULT NULL,
  `ID_LAST_MODIFIED` int(11) DEFAULT NULL,
  `ID_LOG_ALL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_tambahan_pembelian`
--

CREATE TABLE `tbl_tambahan_pembelian` (
  `id_pk_tmbhn` int(11) NOT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_tambahan_pembelian`
--

INSERT INTO `tbl_tambahan_pembelian` (`id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_pembelian`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'TAMBAHAN 1', 1, 'Pcs', 12000, '-', 'AKTIF', 1, '2020-06-22 08:18:34', '0000-00-00 00:00:00', 20, 1),
(2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1);

--
-- 触发器 `tbl_tambahan_pembelian`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_tambahan_pembelian` AFTER INSERT ON `tbl_tambahan_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_tambahan_pembelian` AFTER UPDATE ON `tbl_tambahan_pembelian` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_pembelian_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_pembelian,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_pembelian,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_tambahan_pembelian_log`
--

CREATE TABLE `tbl_tambahan_pembelian_log` (
  `id_pk_tmbhn_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_tmbhn` int(11) DEFAULT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_pembelian` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_tambahan_pembelian_log`
--

INSERT INTO `tbl_tambahan_pembelian_log` (`id_pk_tmbhn_log`, `executed_function`, `id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_pembelian`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'TAMBAHAN 1', 1, 'Pcs', 12000, '-', 'AKTIF', 1, '2020-06-22 08:18:34', '0000-00-00 00:00:00', 20, 1, 242),
(2, 'after insert', 2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 253),
(3, 'after update', 2, 'PARKIR', 1, 'Jam', 40000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 257),
(4, 'after update', 2, 'PARKIR', 1, 'Jam', 4000, '-', 'AKTIF', 2, '2020-06-22 08:26:28', '0000-00-00 00:00:00', 20, 1, 262);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_tambahan_penjualan`
--

CREATE TABLE `tbl_tambahan_penjualan` (
  `id_pk_tmbhn` int(11) NOT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_tambahan_penjualan`
--

INSERT INTO `tbl_tambahan_penjualan` (`id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_penjualan`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1),
(2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1);

--
-- 触发器 `tbl_tambahan_penjualan`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_tambahan_penjualan` AFTER INSERT ON `tbl_tambahan_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_tambahan_penjualan` AFTER UPDATE ON `tbl_tambahan_penjualan` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.tmbhn_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.tmbhn_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_tambahan_penjualan_log(executed_function,id_pk_tmbhn,tmbhn,tmbhn_jumlah,tmbhn_satuan,tmbhn_harga,tmbhn_notes,tmbhn_status,id_fk_penjualan,tmbhn_create_date,tmbhn_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_tmbhn,new.tmbhn,new.tmbhn_jumlah,new.tmbhn_satuan,new.tmbhn_harga,new.tmbhn_notes,new.tmbhn_status,new.id_fk_penjualan,new.tmbhn_create_date,new.tmbhn_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_tambahan_penjualan_log`
--

CREATE TABLE `tbl_tambahan_penjualan_log` (
  `id_pk_tmbhn_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_tmbhn` int(11) DEFAULT NULL,
  `tmbhn` varchar(100) DEFAULT NULL,
  `tmbhn_jumlah` double DEFAULT NULL,
  `tmbhn_satuan` varchar(20) DEFAULT NULL,
  `tmbhn_harga` int(11) DEFAULT NULL,
  `tmbhn_notes` varchar(200) DEFAULT NULL,
  `tmbhn_status` varchar(15) DEFAULT NULL,
  `id_fk_penjualan` int(11) DEFAULT NULL,
  `tmbhn_create_date` datetime DEFAULT NULL,
  `tmbhn_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_tambahan_penjualan_log`
--

INSERT INTO `tbl_tambahan_penjualan_log` (`id_pk_tmbhn_log`, `executed_function`, `id_pk_tmbhn`, `tmbhn`, `tmbhn_jumlah`, `tmbhn_satuan`, `tmbhn_harga`, `tmbhn_notes`, `tmbhn_status`, `id_fk_penjualan`, `tmbhn_create_date`, `tmbhn_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 09:39:51', 1, 1, 322),
(2, 'after insert', 2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 09:55:58', 1, 1, 333),
(3, 'after update', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 09:55:58', 1, 1, 334),
(4, 'after update', 1, 'Uang Parkir', 1, 'Jam', 4000, '-', 'AKTIF', 1, '2020-06-22 09:39:51', '2020-06-22 10:05:28', 1, 1, 343),
(5, 'after update', 2, 'Kurir', 1, 'Trip', 4000, '-', 'AKTIF', 1, '2020-06-22 09:55:58', '2020-06-22 10:05:28', 1, 1, 344);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_toko_admin`
--

CREATE TABLE `tbl_toko_admin` (
  `id_pk_toko_admin` int(11) NOT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `toko_admin_status` varchar(15) DEFAULT NULL,
  `toko_admin_create_date` datetime DEFAULT NULL,
  `toko_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_toko_admin`
--

INSERT INTO `tbl_toko_admin` (`id_pk_toko_admin`, `id_fk_toko`, `id_fk_user`, `toko_admin_status`, `toko_admin_create_date`, `toko_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'AKTIF', '2020-06-21 11:44:24', '2020-06-21 11:44:24', 1, 1);

--
-- 触发器 `tbl_toko_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_toko_admin` AFTER INSERT ON `tbl_toko_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_toko_admin` AFTER UPDATE ON `tbl_toko_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.toko_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.toko_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_toko_admin_log(executed_function,id_pk_toko_admin,id_fk_toko,id_fk_user,toko_admin_status,toko_admin_create_date,toko_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_toko_admin,new.id_fk_toko,new.id_fk_user,new.toko_admin_status,new.toko_admin_create_date,new.toko_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_toko_admin_log`
--

CREATE TABLE `tbl_toko_admin_log` (
  `id_pk_toko_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_toko_admin` int(11) DEFAULT NULL,
  `id_fk_toko` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `toko_admin_status` varchar(15) DEFAULT NULL,
  `toko_admin_create_date` datetime DEFAULT NULL,
  `toko_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_toko_admin_log`
--

INSERT INTO `tbl_toko_admin_log` (`id_pk_toko_admin_log`, `executed_function`, `id_pk_toko_admin`, `id_fk_toko`, `id_fk_user`, `toko_admin_status`, `toko_admin_create_date`, `toko_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:44:24', '2020-06-21 11:44:24', 1, 1, 89);

-- --------------------------------------------------------

--
-- 表的结构 `tbl_warehouse_admin`
--

CREATE TABLE `tbl_warehouse_admin` (
  `id_pk_warehouse_admin` int(11) NOT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `warehouse_admin_status` varchar(15) DEFAULT NULL,
  `warehouse_admin_create_date` datetime DEFAULT NULL,
  `warehouse_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_warehouse_admin`
--

INSERT INTO `tbl_warehouse_admin` (`id_pk_warehouse_admin`, `id_fk_warehouse`, `id_fk_user`, `warehouse_admin_status`, `warehouse_admin_create_date`, `warehouse_admin_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 1, 'AKTIF', '2020-06-21 11:45:53', '2020-06-21 11:45:53', 1, 1);

--
-- 触发器 `tbl_warehouse_admin`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_warehouse_admin` AFTER INSERT ON `tbl_warehouse_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','insert data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after insert',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_update_warehouse_admin` AFTER UPDATE ON `tbl_warehouse_admin` FOR EACH ROW begin
            set @id_user = new.id_last_modified;
            set @tgl_action = new.warehouse_admin_last_modified;
            set @log_text = concat(new.id_last_modified,' ','update data at' , new.warehouse_admin_last_modified);
            call insert_log_all(@id_user,@tgl_action,@log_text,@id_log_all);
            
            insert into tbl_warehouse_admin_log(executed_function,id_pk_warehouse_admin,id_fk_warehouse,id_fk_user,warehouse_admin_status,warehouse_admin_create_date,warehouse_admin_last_modified,id_create_data,id_last_modified,id_log_all) values ('after update',new.id_pk_warehouse_admin,new.id_fk_warehouse,new.id_fk_user,new.warehouse_admin_status,new.warehouse_admin_create_date,new.warehouse_admin_last_modified,new.id_create_data,new.id_last_modified,@id_log_all);
        end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `tbl_warehouse_admin_log`
--

CREATE TABLE `tbl_warehouse_admin_log` (
  `id_pk_warehouse_admin_log` int(11) NOT NULL,
  `executed_function` varchar(30) DEFAULT NULL,
  `id_pk_warehouse_admin` int(11) DEFAULT NULL,
  `id_fk_warehouse` int(11) DEFAULT NULL,
  `id_fk_user` int(11) DEFAULT NULL,
  `warehouse_admin_status` varchar(15) DEFAULT NULL,
  `warehouse_admin_create_date` datetime DEFAULT NULL,
  `warehouse_admin_last_modified` datetime DEFAULT NULL,
  `id_create_data` int(11) DEFAULT NULL,
  `id_last_modified` int(11) DEFAULT NULL,
  `id_log_all` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `tbl_warehouse_admin_log`
--

INSERT INTO `tbl_warehouse_admin_log` (`id_pk_warehouse_admin_log`, `executed_function`, `id_pk_warehouse_admin`, `id_fk_warehouse`, `id_fk_user`, `warehouse_admin_status`, `warehouse_admin_create_date`, `warehouse_admin_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 1, 1, 'AKTIF', '2020-06-21 11:45:53', '2020-06-21 11:45:53', 1, 1, 93);

--
-- 转储表的索引
--

--
-- 表的索引 `log_all`
--
ALTER TABLE `log_all`
  ADD PRIMARY KEY (`id_log_all`);

--
-- 表的索引 `mstr_barang`
--
ALTER TABLE `mstr_barang`
  ADD PRIMARY KEY (`id_pk_brg`);

--
-- 表的索引 `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  ADD PRIMARY KEY (`id_pk_brg_jenis`);

--
-- 表的索引 `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  ADD PRIMARY KEY (`id_pk_brg_jenis_log`);

--
-- 表的索引 `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  ADD PRIMARY KEY (`id_pk_brg_log`);

--
-- 表的索引 `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  ADD PRIMARY KEY (`id_pk_brg_merk`);

--
-- 表的索引 `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  ADD PRIMARY KEY (`id_pk_brg_merk_log`);

--
-- 表的索引 `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  ADD PRIMARY KEY (`id_pk_cabang`);

--
-- 表的索引 `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  ADD PRIMARY KEY (`id_pk_cabang_log`);

--
-- 表的索引 `mstr_customer`
--
ALTER TABLE `mstr_customer`
  ADD PRIMARY KEY (`id_pk_cust`);

--
-- 表的索引 `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  ADD PRIMARY KEY (`id_pk_cust_log`);

--
-- 表的索引 `mstr_employee`
--
ALTER TABLE `mstr_employee`
  ADD PRIMARY KEY (`id_pk_employee`);

--
-- 表的索引 `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  ADD PRIMARY KEY (`id_pk_employee_log`);

--
-- 表的索引 `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  ADD PRIMARY KEY (`id_pk_jabatan`);

--
-- 表的索引 `mstr_jabatan_log`
--
ALTER TABLE `mstr_jabatan_log`
  ADD PRIMARY KEY (`id_pk_jabatan_log`);

--
-- 表的索引 `mstr_menu`
--
ALTER TABLE `mstr_menu`
  ADD PRIMARY KEY (`id_pk_menu`);

--
-- 表的索引 `mstr_menu_log`
--
ALTER TABLE `mstr_menu_log`
  ADD PRIMARY KEY (`id_pk_menu_log`);

--
-- 表的索引 `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  ADD PRIMARY KEY (`id_pk_pembelian`);

--
-- 表的索引 `mstr_pembelian_log`
--
ALTER TABLE `mstr_pembelian_log`
  ADD PRIMARY KEY (`id_pk_pembelian_log`);

--
-- 表的索引 `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  ADD PRIMARY KEY (`id_pk_penerimaan`);

--
-- 表的索引 `mstr_penerimaan_log`
--
ALTER TABLE `mstr_penerimaan_log`
  ADD PRIMARY KEY (`id_pk_penerimaan_log`);

--
-- 表的索引 `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  ADD PRIMARY KEY (`id_pk_pengiriman`);

--
-- 表的索引 `mstr_pengiriman_log`
--
ALTER TABLE `mstr_pengiriman_log`
  ADD PRIMARY KEY (`id_pk_pengiriman_log`);

--
-- 表的索引 `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  ADD PRIMARY KEY (`id_pk_penjualan`);

--
-- 表的索引 `mstr_penjualan_log`
--
ALTER TABLE `mstr_penjualan_log`
  ADD PRIMARY KEY (`id_pk_penjualan_log`);

--
-- 表的索引 `mstr_retur`
--
ALTER TABLE `mstr_retur`
  ADD PRIMARY KEY (`id_pk_retur`);

--
-- 表的索引 `mstr_retur_log`
--
ALTER TABLE `mstr_retur_log`
  ADD PRIMARY KEY (`id_pk_retur_log`);

--
-- 表的索引 `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  ADD PRIMARY KEY (`id_pk_satuan`);

--
-- 表的索引 `mstr_satuan_log`
--
ALTER TABLE `mstr_satuan_log`
  ADD PRIMARY KEY (`id_pk_satuan_log`);

--
-- 表的索引 `mstr_stock_opname`
--
ALTER TABLE `mstr_stock_opname`
  ADD PRIMARY KEY (`ID_PK_STOCK_OPNAME`);

--
-- 表的索引 `mstr_stock_opname_log`
--
ALTER TABLE `mstr_stock_opname_log`
  ADD PRIMARY KEY (`ID_PK_STOCK_OPNAME_LOG`);

--
-- 表的索引 `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  ADD PRIMARY KEY (`id_pk_sup`);

--
-- 表的索引 `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  ADD PRIMARY KEY (`id_pk_sup_log`);

--
-- 表的索引 `mstr_surat_jalan`
--
ALTER TABLE `mstr_surat_jalan`
  ADD PRIMARY KEY (`ID_PK_SURAT_JALAN`);

--
-- 表的索引 `mstr_surat_jalan_log`
--
ALTER TABLE `mstr_surat_jalan_log`
  ADD PRIMARY KEY (`ID_PK_SURAT_JALAN_LOG`);

--
-- 表的索引 `mstr_toko`
--
ALTER TABLE `mstr_toko`
  ADD PRIMARY KEY (`id_pk_toko`);

--
-- 表的索引 `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  ADD PRIMARY KEY (`id_pk_toko_log`);

--
-- 表的索引 `mstr_user`
--
ALTER TABLE `mstr_user`
  ADD PRIMARY KEY (`id_pk_user`);

--
-- 表的索引 `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  ADD PRIMARY KEY (`id_pk_user_log`);

--
-- 表的索引 `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  ADD PRIMARY KEY (`id_pk_warehouse`);

--
-- 表的索引 `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  ADD PRIMARY KEY (`id_pk_warehouse_log`);

--
-- 表的索引 `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi`);

--
-- 表的索引 `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi_log`);

--
-- 表的索引 `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  ADD PRIMARY KEY (`ID_PK_BARANG_UKURAN`);

--
-- 表的索引 `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  ADD PRIMARY KEY (`id_pk_brg_cabang`);

--
-- 表的索引 `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  ADD PRIMARY KEY (`id_pk_brg_cabang_log`);

--
-- 表的索引 `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  ADD PRIMARY KEY (`id_pk_brg_pembelian`);

--
-- 表的索引 `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  ADD PRIMARY KEY (`id_pk_brg_pembelian_log`);

--
-- 表的索引 `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  ADD PRIMARY KEY (`id_pk_brg_pemenuhan`);

--
-- 表的索引 `tbl_brg_pemenuhan_log`
--
ALTER TABLE `tbl_brg_pemenuhan_log`
  ADD PRIMARY KEY (`id_pk_brg_pemenuhan_log`);

--
-- 表的索引 `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  ADD PRIMARY KEY (`id_pk_brg_penerimaan`);

--
-- 表的索引 `tbl_brg_penerimaan_log`
--
ALTER TABLE `tbl_brg_penerimaan_log`
  ADD PRIMARY KEY (`id_pk_brg_penerimaan_log`);

--
-- 表的索引 `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  ADD PRIMARY KEY (`id_pk_brg_pengiriman`);

--
-- 表的索引 `tbl_brg_pengiriman_log`
--
ALTER TABLE `tbl_brg_pengiriman_log`
  ADD PRIMARY KEY (`id_pk_brg_pengiriman_log`);

--
-- 表的索引 `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  ADD PRIMARY KEY (`id_pk_brg_penjualan`);

--
-- 表的索引 `tbl_brg_penjualan_log`
--
ALTER TABLE `tbl_brg_penjualan_log`
  ADD PRIMARY KEY (`id_pk_brg_penjualan_log`);

--
-- 表的索引 `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  ADD PRIMARY KEY (`id_pk_brg_permintaan`);

--
-- 表的索引 `tbl_brg_permintaan_log`
--
ALTER TABLE `tbl_brg_permintaan_log`
  ADD PRIMARY KEY (`id_pk_penerimaan_log`);

--
-- 表的索引 `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  ADD PRIMARY KEY (`id_pk_brg_pindah`);

--
-- 表的索引 `tbl_brg_pindah_log`
--
ALTER TABLE `tbl_brg_pindah_log`
  ADD PRIMARY KEY (`id_pk_brg_pindah_log`);

--
-- 表的索引 `tbl_brg_so`
--
ALTER TABLE `tbl_brg_so`
  ADD PRIMARY KEY (`ID_PK_SO_BRG`);

--
-- 表的索引 `tbl_brg_so_log`
--
ALTER TABLE `tbl_brg_so_log`
  ADD PRIMARY KEY (`ID_PK_SO_BRG_LOG`);

--
-- 表的索引 `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  ADD PRIMARY KEY (`id_pk_brg_warehouse`);

--
-- 表的索引 `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  ADD PRIMARY KEY (`id_pk_brg_warehouse_log`);

--
-- 表的索引 `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  ADD PRIMARY KEY (`id_pk_cabang_admin`);

--
-- 表的索引 `tbl_cabang_admin_log`
--
ALTER TABLE `tbl_cabang_admin_log`
  ADD PRIMARY KEY (`id_pk_cabang_admin_log`);

--
-- 表的索引 `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  ADD PRIMARY KEY (`id_pk_hak_akses`);

--
-- 表的索引 `tbl_hak_akses_log`
--
ALTER TABLE `tbl_hak_akses_log`
  ADD PRIMARY KEY (`id_pk_hak_akses_log`);

--
-- 表的索引 `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  ADD PRIMARY KEY (`id_pk_penjualan_online`);

--
-- 表的索引 `tbl_penjualan_online_log`
--
ALTER TABLE `tbl_penjualan_online_log`
  ADD PRIMARY KEY (`id_pk_penjualan_online_log`);

--
-- 表的索引 `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  ADD PRIMARY KEY (`id_pk_penjualan_pembayaran`);

--
-- 表的索引 `tbl_penjualan_pembayaran_log`
--
ALTER TABLE `tbl_penjualan_pembayaran_log`
  ADD PRIMARY KEY (`id_pk_penjualan_pembayaran_log`);

--
-- 表的索引 `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  ADD PRIMARY KEY (`id_pk_retur_brg`);

--
-- 表的索引 `tbl_retur_brg_log`
--
ALTER TABLE `tbl_retur_brg_log`
  ADD PRIMARY KEY (`id_pk_retur_log`);

--
-- 表的索引 `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  ADD PRIMARY KEY (`id_pk_retur_kembali`);

--
-- 表的索引 `tbl_retur_kembali_log`
--
ALTER TABLE `tbl_retur_kembali_log`
  ADD PRIMARY KEY (`id_pk_retur_kembali_log`);

--
-- 表的索引 `tbl_sj_item`
--
ALTER TABLE `tbl_sj_item`
  ADD PRIMARY KEY (`id_pk_sj_item`);

--
-- 表的索引 `tbl_sj_item_log`
--
ALTER TABLE `tbl_sj_item_log`
  ADD PRIMARY KEY (`id_pk_sj_item_log`);

--
-- 表的索引 `tbl_so_pj`
--
ALTER TABLE `tbl_so_pj`
  ADD PRIMARY KEY (`ID_PK_SO_PJ`);

--
-- 表的索引 `tbl_so_pj_log`
--
ALTER TABLE `tbl_so_pj_log`
  ADD PRIMARY KEY (`ID_PK_SO_PJ_LOG`);

--
-- 表的索引 `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- 表的索引 `tbl_tambahan_pembelian_log`
--
ALTER TABLE `tbl_tambahan_pembelian_log`
  ADD PRIMARY KEY (`id_pk_tmbhn_log`);

--
-- 表的索引 `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  ADD PRIMARY KEY (`id_pk_tmbhn`);

--
-- 表的索引 `tbl_tambahan_penjualan_log`
--
ALTER TABLE `tbl_tambahan_penjualan_log`
  ADD PRIMARY KEY (`id_pk_tmbhn_log`);

--
-- 表的索引 `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  ADD PRIMARY KEY (`id_pk_toko_admin`);

--
-- 表的索引 `tbl_toko_admin_log`
--
ALTER TABLE `tbl_toko_admin_log`
  ADD PRIMARY KEY (`id_pk_toko_admin_log`);

--
-- 表的索引 `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  ADD PRIMARY KEY (`id_pk_warehouse_admin`);

--
-- 表的索引 `tbl_warehouse_admin_log`
--
ALTER TABLE `tbl_warehouse_admin_log`
  ADD PRIMARY KEY (`id_pk_warehouse_admin_log`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `log_all`
--
ALTER TABLE `log_all`
  MODIFY `id_log_all` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

--
-- 使用表AUTO_INCREMENT `mstr_barang`
--
ALTER TABLE `mstr_barang`
  MODIFY `id_pk_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  MODIFY `id_pk_brg_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  MODIFY `id_pk_brg_jenis_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  MODIFY `id_pk_brg_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  MODIFY `id_pk_brg_merk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  MODIFY `id_pk_brg_merk_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  MODIFY `id_pk_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  MODIFY `id_pk_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_customer`
--
ALTER TABLE `mstr_customer`
  MODIFY `id_pk_cust` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  MODIFY `id_pk_cust_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_employee`
--
ALTER TABLE `mstr_employee`
  MODIFY `id_pk_employee` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  MODIFY `id_pk_employee_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_jabatan`
--
ALTER TABLE `mstr_jabatan`
  MODIFY `id_pk_jabatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `mstr_jabatan_log`
--
ALTER TABLE `mstr_jabatan_log`
  MODIFY `id_pk_jabatan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `mstr_menu`
--
ALTER TABLE `mstr_menu`
  MODIFY `id_pk_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用表AUTO_INCREMENT `mstr_menu_log`
--
ALTER TABLE `mstr_menu_log`
  MODIFY `id_pk_menu_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用表AUTO_INCREMENT `mstr_pembelian`
--
ALTER TABLE `mstr_pembelian`
  MODIFY `id_pk_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `mstr_pembelian_log`
--
ALTER TABLE `mstr_pembelian_log`
  MODIFY `id_pk_pembelian_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `mstr_penerimaan`
--
ALTER TABLE `mstr_penerimaan`
  MODIFY `id_pk_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_penerimaan_log`
--
ALTER TABLE `mstr_penerimaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `mstr_pengiriman`
--
ALTER TABLE `mstr_pengiriman`
  MODIFY `id_pk_pengiriman` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_pengiriman_log`
--
ALTER TABLE `mstr_pengiriman_log`
  MODIFY `id_pk_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_penjualan`
--
ALTER TABLE `mstr_penjualan`
  MODIFY `id_pk_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_penjualan_log`
--
ALTER TABLE `mstr_penjualan_log`
  MODIFY `id_pk_penjualan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `mstr_retur`
--
ALTER TABLE `mstr_retur`
  MODIFY `id_pk_retur` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_retur_log`
--
ALTER TABLE `mstr_retur_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_satuan`
--
ALTER TABLE `mstr_satuan`
  MODIFY `id_pk_satuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_satuan_log`
--
ALTER TABLE `mstr_satuan_log`
  MODIFY `id_pk_satuan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mstr_stock_opname`
--
ALTER TABLE `mstr_stock_opname`
  MODIFY `ID_PK_STOCK_OPNAME` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_stock_opname_log`
--
ALTER TABLE `mstr_stock_opname_log`
  MODIFY `ID_PK_STOCK_OPNAME_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  MODIFY `id_pk_sup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  MODIFY `id_pk_sup_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `mstr_surat_jalan`
--
ALTER TABLE `mstr_surat_jalan`
  MODIFY `ID_PK_SURAT_JALAN` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_surat_jalan_log`
--
ALTER TABLE `mstr_surat_jalan_log`
  MODIFY `ID_PK_SURAT_JALAN_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `mstr_toko`
--
ALTER TABLE `mstr_toko`
  MODIFY `id_pk_toko` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  MODIFY `id_pk_toko_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_user`
--
ALTER TABLE `mstr_user`
  MODIFY `id_pk_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  MODIFY `id_pk_user_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  MODIFY `id_pk_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  MODIFY `id_pk_warehouse_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  MODIFY `id_pk_barang_kombinasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  MODIFY `id_pk_barang_kombinasi_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  MODIFY `ID_PK_BARANG_UKURAN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- 使用表AUTO_INCREMENT `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  MODIFY `id_pk_brg_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  MODIFY `id_pk_brg_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  MODIFY `id_pk_brg_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  MODIFY `id_pk_brg_pembelian_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pemenuhan`
--
ALTER TABLE `tbl_brg_pemenuhan`
  MODIFY `id_pk_brg_pemenuhan` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pemenuhan_log`
--
ALTER TABLE `tbl_brg_pemenuhan_log`
  MODIFY `id_pk_brg_pemenuhan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_penerimaan`
--
ALTER TABLE `tbl_brg_penerimaan`
  MODIFY `id_pk_brg_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `tbl_brg_penerimaan_log`
--
ALTER TABLE `tbl_brg_penerimaan_log`
  MODIFY `id_pk_brg_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pengiriman`
--
ALTER TABLE `tbl_brg_pengiriman`
  MODIFY `id_pk_brg_pengiriman` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pengiriman_log`
--
ALTER TABLE `tbl_brg_pengiriman_log`
  MODIFY `id_pk_brg_pengiriman_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_penjualan`
--
ALTER TABLE `tbl_brg_penjualan`
  MODIFY `id_pk_brg_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `tbl_brg_penjualan_log`
--
ALTER TABLE `tbl_brg_penjualan_log`
  MODIFY `id_pk_brg_penjualan_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `tbl_brg_permintaan`
--
ALTER TABLE `tbl_brg_permintaan`
  MODIFY `id_pk_brg_permintaan` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_permintaan_log`
--
ALTER TABLE `tbl_brg_permintaan_log`
  MODIFY `id_pk_penerimaan_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pindah`
--
ALTER TABLE `tbl_brg_pindah`
  MODIFY `id_pk_brg_pindah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `tbl_brg_pindah_log`
--
ALTER TABLE `tbl_brg_pindah_log`
  MODIFY `id_pk_brg_pindah_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `tbl_brg_so`
--
ALTER TABLE `tbl_brg_so`
  MODIFY `ID_PK_SO_BRG` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_so_log`
--
ALTER TABLE `tbl_brg_so_log`
  MODIFY `ID_PK_SO_BRG_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  MODIFY `id_pk_brg_warehouse` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  MODIFY `id_pk_brg_warehouse_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_cabang_admin`
--
ALTER TABLE `tbl_cabang_admin`
  MODIFY `id_pk_cabang_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_cabang_admin_log`
--
ALTER TABLE `tbl_cabang_admin_log`
  MODIFY `id_pk_cabang_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_hak_akses`
--
ALTER TABLE `tbl_hak_akses`
  MODIFY `id_pk_hak_akses` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- 使用表AUTO_INCREMENT `tbl_hak_akses_log`
--
ALTER TABLE `tbl_hak_akses_log`
  MODIFY `id_pk_hak_akses_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- 使用表AUTO_INCREMENT `tbl_penjualan_online`
--
ALTER TABLE `tbl_penjualan_online`
  MODIFY `id_pk_penjualan_online` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_penjualan_online_log`
--
ALTER TABLE `tbl_penjualan_online_log`
  MODIFY `id_pk_penjualan_online_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `tbl_penjualan_pembayaran`
--
ALTER TABLE `tbl_penjualan_pembayaran`
  MODIFY `id_pk_penjualan_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `tbl_penjualan_pembayaran_log`
--
ALTER TABLE `tbl_penjualan_pembayaran_log`
  MODIFY `id_pk_penjualan_pembayaran_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `tbl_retur_brg`
--
ALTER TABLE `tbl_retur_brg`
  MODIFY `id_pk_retur_brg` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_retur_brg_log`
--
ALTER TABLE `tbl_retur_brg_log`
  MODIFY `id_pk_retur_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_retur_kembali`
--
ALTER TABLE `tbl_retur_kembali`
  MODIFY `id_pk_retur_kembali` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_retur_kembali_log`
--
ALTER TABLE `tbl_retur_kembali_log`
  MODIFY `id_pk_retur_kembali_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_sj_item`
--
ALTER TABLE `tbl_sj_item`
  MODIFY `id_pk_sj_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_sj_item_log`
--
ALTER TABLE `tbl_sj_item_log`
  MODIFY `id_pk_sj_item_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_so_pj`
--
ALTER TABLE `tbl_so_pj`
  MODIFY `ID_PK_SO_PJ` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_so_pj_log`
--
ALTER TABLE `tbl_so_pj_log`
  MODIFY `ID_PK_SO_PJ_LOG` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tbl_tambahan_pembelian`
--
ALTER TABLE `tbl_tambahan_pembelian`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `tbl_tambahan_pembelian_log`
--
ALTER TABLE `tbl_tambahan_pembelian_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `tbl_tambahan_penjualan`
--
ALTER TABLE `tbl_tambahan_penjualan`
  MODIFY `id_pk_tmbhn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `tbl_tambahan_penjualan_log`
--
ALTER TABLE `tbl_tambahan_penjualan_log`
  MODIFY `id_pk_tmbhn_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `tbl_toko_admin`
--
ALTER TABLE `tbl_toko_admin`
  MODIFY `id_pk_toko_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_toko_admin_log`
--
ALTER TABLE `tbl_toko_admin_log`
  MODIFY `id_pk_toko_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_warehouse_admin`
--
ALTER TABLE `tbl_warehouse_admin`
  MODIFY `id_pk_warehouse_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `tbl_warehouse_admin_log`
--
ALTER TABLE `tbl_warehouse_admin_log`
  MODIFY `id_pk_warehouse_admin_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
