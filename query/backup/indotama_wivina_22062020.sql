-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2020 at 09:44 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `indotama`
--

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang`
--

DROP TABLE IF EXISTS `mstr_barang`;
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
-- Dumping data for table `mstr_barang`
--

INSERT INTO `mstr_barang` (`id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`) VALUES
(1, 'BARANG 1', 'BARANG 1', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 1, 1),
(2, 'BARANG 2', 'BARANG 2', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 2, 2),
(3, 'BARANG 3', 'BARANG 3', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 3, 3),
(4, 'BARANG4', 'Barang 4', 'keterangan keterangan', 30, 'pcs', 'barang_BARANG4.png', 60987, 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 10:37:14', 1, 1, 3, 4),
(5, 'warepack-5', 'warepack5', 'keterangan keterangan ket', 60, 'pcs', 'barang_ware.png', 3000000, 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 2, 6);

--
-- Triggers `mstr_barang`
--
DROP TRIGGER IF EXISTS `trg_after_insert_barang`;
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
DROP TRIGGER IF EXISTS `trg_after_update_barang`;
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
-- Table structure for table `mstr_barang_jenis`
--

DROP TABLE IF EXISTS `mstr_barang_jenis`;
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
-- Dumping data for table `mstr_barang_jenis`
--

INSERT INTO `mstr_barang_jenis` (`id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1),
(4, 'Jenis 4', 'AKTIF', '2020-06-22 10:55:47', '2020-06-22 10:55:47', 1, 1);

--
-- Triggers `mstr_barang_jenis`
--
DROP TRIGGER IF EXISTS `trg_after_insert_barang_jenis`;
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
DROP TRIGGER IF EXISTS `trg_after_update_barang_jenis`;
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
-- Table structure for table `mstr_barang_jenis_log`
--

DROP TABLE IF EXISTS `mstr_barang_jenis_log`;
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
-- Dumping data for table `mstr_barang_jenis_log`
--

INSERT INTO `mstr_barang_jenis_log` (`id_pk_brg_jenis_log`, `executed_function`, `id_pk_brg_jenis`, `brg_jenis_nama`, `brg_jenis_status`, `brg_jenis_create_date`, `brg_jenis_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'JENIS 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 209),
(2, 'after insert', 2, 'JENIS 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 212),
(3, 'after insert', 3, 'JENIS 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 215),
(4, 'after insert', 4, 'Jenis 4', 'AKTIF', '2020-06-22 10:55:47', '2020-06-22 10:55:47', 1, 1, 363);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_log`
--

DROP TABLE IF EXISTS `mstr_barang_log`;
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
-- Dumping data for table `mstr_barang_log`
--

INSERT INTO `mstr_barang_log` (`id_pk_brg_log`, `executed_function`, `id_pk_brg`, `brg_kode`, `brg_nama`, `brg_ket`, `brg_minimal`, `brg_satuan`, `brg_image`, `brg_harga`, `brg_status`, `brg_create_date`, `brg_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_brg_jenis`, `id_fk_brg_merk`, `id_log_all`) VALUES
(1, 'after insert', 1, 'BARANG 1', 'BARANG 1', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 1, 1, 211),
(2, 'after insert', 2, 'BARANG 2', 'BARANG 2', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 2, 2, 214),
(3, 'after insert', 3, 'BARANG 3', 'BARANG 3', '-', 10, 'PCS', '-', 20000, 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 3, 3, 217),
(4, 'after insert', 4, 'BARANG4', 'Barang 4', 'keterangan keterangan', 30, 'pcs', 'barang_BARANG4.png', 60987, 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 10:37:14', 1, 1, 3, 4, 349),
(5, 'after insert', 5, 'ware', 'warepack5', 'keterangan keterangan ket', 60, 'pcs', 'barang_ware.png', 3000000, 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 2, 5, 351),
(6, 'after update', 5, 'warepack-5', 'warepack5', 'keterangan keterangan ket', 60, 'pcs', 'barang_ware.png', 3000000, 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 2, 6, 355);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_barang_merk`
--

DROP TABLE IF EXISTS `mstr_barang_merk`;
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
-- Dumping data for table `mstr_barang_merk`
--

INSERT INTO `mstr_barang_merk` (`id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1),
(2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1),
(3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1),
(4, 'MERK 31', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 11:09:15', 1, 1),
(5, 'MERK 12', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 11:09:04', 1, 1),
(6, 'MERK 13', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 11:09:08', 1, 1);

--
-- Triggers `mstr_barang_merk`
--
DROP TRIGGER IF EXISTS `trg_after_insert_barang_merk`;
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
DROP TRIGGER IF EXISTS `trg_after_update_barang_merk`;
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
-- Table structure for table `mstr_barang_merk_log`
--

DROP TABLE IF EXISTS `mstr_barang_merk_log`;
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
-- Dumping data for table `mstr_barang_merk_log`
--

INSERT INTO `mstr_barang_merk_log` (`id_pk_brg_merk_log`, `executed_function`, `id_pk_brg_merk`, `brg_merk_nama`, `brg_merk_status`, `brg_merk_create_date`, `brg_merk_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'MERK 1', 'AKTIF', '2020-06-22 08:03:15', '2020-06-22 08:03:15', 1, 1, 210),
(2, 'after insert', 2, 'MERK 2', 'AKTIF', '2020-06-22 08:03:23', '2020-06-22 08:03:23', 1, 1, 213),
(3, 'after insert', 3, 'MERK 3', 'AKTIF', '2020-06-22 08:03:32', '2020-06-22 08:03:32', 1, 1, 216),
(4, 'after insert', 4, 'MERK 3', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 10:37:14', 1, 1, 348),
(5, 'after insert', 5, 'MERK 1', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 350),
(6, 'after insert', 6, 'MERK 1', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 10:39:37', 1, 1, 354),
(7, 'after update', 5, 'MERK 12', 'AKTIF', '2020-06-22 10:39:16', '2020-06-22 11:09:04', 1, 1, 392),
(8, 'after update', 6, 'MERK 13', 'AKTIF', '2020-06-22 10:39:37', '2020-06-22 11:09:08', 1, 1, 393),
(9, 'after update', 4, 'MERK 31', 'AKTIF', '2020-06-22 10:37:14', '2020-06-22 11:09:15', 1, 1, 394);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_cabang`
--

DROP TABLE IF EXISTS `mstr_cabang`;
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
-- Dumping data for table `mstr_cabang`
--

INSERT INTO `mstr_cabang` (`id_pk_cabang`, `cabang_daerah`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`) VALUES
(1, 'TAMAN ANGGREK', '12345678', 'Taman Anggrek Tanjung Duren', 'AKTIF', '2020-06-21 11:44:49', '2020-06-21 11:44:49', 1, 1, 1);

--
-- Triggers `mstr_cabang`
--
DROP TRIGGER IF EXISTS `trg_after_insert_cabang`;
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
DROP TRIGGER IF EXISTS `trg_after_update_cabang`;
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
-- Table structure for table `mstr_cabang_log`
--

DROP TABLE IF EXISTS `mstr_cabang_log`;
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
-- Dumping data for table `mstr_cabang_log`
--

INSERT INTO `mstr_cabang_log` (`id_pk_cabang_log`, `executed_function`, `id_pk_cabang`, `cabang_daerah`, `cabang_notelp`, `cabang_alamat`, `cabang_status`, `cabang_create_date`, `cabang_last_modified`, `id_create_data`, `id_last_modified`, `id_fk_toko`, `id_log_all`) VALUES
(1, 'after insert', 1, 'TAMAN ANGGREK', '12345678', 'Taman Anggrek Tanjung Duren', 'AKTIF', '2020-06-21 11:44:49', '2020-06-21 11:44:49', 1, 1, 1, 90);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_customer`
--

DROP TABLE IF EXISTS `mstr_customer`;
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
-- Dumping data for table `mstr_customer`
--

INSERT INTO `mstr_customer` (`id_pk_cust`, `cust_name`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Nama Lengkapsss', 'MR', 'TOTAL Construction', 'total@gmaill.com', '02124324234', '08216327422', 'jl mawar no 23 jakarta barat', 'ket ket ket', NULL, 'aktif', '2020-06-22 09:39:50', '2020-06-22 10:41:09', 1, 1),
(2, 'Rena Yaya', 'MR', 'Abdi Baca Canita', 'abdi@gmail.com', '021742453', '08657667865', 'jalan melati no 2 jakarta pusat', 'rerty', NULL, 'AKTIF', '2020-06-22 10:42:07', '2020-06-22 10:42:07', 1, 1);

--
-- Triggers `mstr_customer`
--
DROP TRIGGER IF EXISTS `trg_after_insert_customer`;
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
DROP TRIGGER IF EXISTS `trg_after_update_customer`;
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
-- Table structure for table `mstr_customer_log`
--

DROP TABLE IF EXISTS `mstr_customer_log`;
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
-- Dumping data for table `mstr_customer_log`
--

INSERT INTO `mstr_customer_log` (`id_pk_cust_log`, `executed_function`, `id_pk_cust`, `cust_name`, `cust_suff`, `cust_perusahaan`, `cust_email`, `cust_telp`, `cust_hp`, `cust_alamat`, `cust_keterangan`, `id_fk_toko`, `cust_status`, `cust_create_date`, `cust_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, NULL, NULL, 'TOTAL Construction', NULL, NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 09:39:50', '2020-06-22 09:39:50', 1, 1, 317),
(2, 'after update', 1, 'Nama Lengkapsss', 'MR', 'TOTAL Construction', 'total@gmaill.com', '02124324234', '08216327422', 'jl mawar no 23 jakarta barat', 'ket ket ket', NULL, 'aktif', '2020-06-22 09:39:50', '2020-06-22 10:41:09', 1, 1, 358),
(3, 'after insert', 2, 'Rena Yaya', 'MR', 'Abdi Baca Canita', 'abdi@gmail.com', '021742453', '08657667865', 'jalan melati no 2 jakarta pusat', 'rerty', NULL, 'AKTIF', '2020-06-22 10:42:07', '2020-06-22 10:42:07', 1, 1, 359);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_employee`
--

DROP TABLE IF EXISTS `mstr_employee`;
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
-- Dumping data for table `mstr_employee`
--

INSERT INTO `mstr_employee` (`id_pk_employee`, `emp_nama`, `emp_npwp`, `emp_ktp`, `emp_hp`, `emp_alamat`, `emp_kode_pos`, `emp_foto_npwp`, `emp_foto_ktp`, `emp_foto_lain`, `emp_foto`, `emp_gaji`, `emp_startdate`, `emp_enddate`, `emp_rek`, `emp_gender`, `emp_suff`, `emp_status`, `id_fk_toko`, `emp_create_date`, `emp_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 1, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1),
(2, 'Yane Pegawai', '567890', '876545678', '08765438888', 'jalan haha no 23 tangerang selatan', '87657', 'Screenshot_22.png', 'Screenshot_15.png', 'Screenshot_244.png', 'Screenshot_47.png', 7000000, '2020-01-01 00:00:00', '2020-06-15 00:00:00', '345678', 'WANITA', 'MRS', 'AKTIF', 1, '2020-06-22 10:55:19', '2020-06-22 10:55:19', 1, 1);

--
-- Triggers `mstr_employee`
--
DROP TRIGGER IF EXISTS `trg_after_insert_employee`;
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
DROP TRIGGER IF EXISTS `trg_after_update_employee`;
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
-- Table structure for table `mstr_employee_log`
--

DROP TABLE IF EXISTS `mstr_employee_log`;
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

--
-- Dumping data for table `mstr_employee_log`
--

INSERT INTO `mstr_employee_log` (`id_pk_employee_log`, `executed_function`, `id_pk_employee`, `emp_nama`, `emp_npwp`, `emp_ktp`, `emp_hp`, `emp_alamat`, `emp_kode_pos`, `emp_foto_npwp`, `emp_foto_ktp`, `emp_foto_lain`, `emp_foto`, `emp_gaji`, `emp_startdate`, `emp_enddate`, `emp_rek`, `emp_gender`, `emp_suff`, `emp_status`, `id_fk_toko`, `emp_create_date`, `emp_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 0, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1, 360),
(2, 'after update', 1, 'Budi Pegawai', '98765432345678', '45678987654', '0865456754', 'jalan kamboja no 3 jakarta selatan', '34567', 'Screenshot_261.png', 'Screenshot_631.png', 'Screenshot_661.png', 'Screenshot_281.png', 6000000, '2019-08-01 00:00:00', '0000-00-00 00:00:00', '3456788', 'PRIA', 'MR', 'AKTIF', 1, '2020-06-22 10:45:26', '2020-06-22 10:45:26', 1, 1, 361),
(3, 'after insert', 2, 'Yane Pegawai', '567890', '876545678', '08765438888', 'jalan haha no 23 tangerang selatan', '87657', 'Screenshot_22.png', 'Screenshot_15.png', 'Screenshot_244.png', 'Screenshot_47.png', 7000000, '2020-01-01 00:00:00', '2020-06-15 00:00:00', '345678', 'WANITA', 'MRS', 'AKTIF', 1, '2020-06-22 10:55:19', '2020-06-22 10:55:19', 1, 1, 362);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_supplier`
--

DROP TABLE IF EXISTS `mstr_supplier`;
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
-- Dumping data for table `mstr_supplier`
--

INSERT INTO `mstr_supplier` (`id_pk_sup`, `sup_nama`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Dani Asdf', 'MR', 'Microsoft', 'dani@gmail.com', '021456789', '082345678', 'jalan abcd no 5 jakarta barat', 'keterangankuuuuu', 'aktif', '2020-06-22 08:16:43', '2020-06-22 11:01:52', 1, 1),
(2, 'Pina Supp', 'MRS', 'IBM', 'pina@gmail.com', '02134567890', '089876543', 'jalan absd no 3 denpasar bali', 'keterangan ket ket', 'aktif', '2020-06-22 08:26:28', '2020-06-22 11:02:49', 1, 1),
(3, 'Intel', 'BAPAK', 'Budi Supplier', 'budih@gmail.com', '021456789', '08765432666', 'jalan asdafd no 4 jakarta utara', 'keterangan ket ket keterangan', 'AKTIF', '2020-06-22 11:04:04', '2020-06-22 11:04:04', 1, 1);

--
-- Triggers `mstr_supplier`
--
DROP TRIGGER IF EXISTS `trg_after_insert_supplier`;
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
DROP TRIGGER IF EXISTS `trg_after_update_supplier`;
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
-- Table structure for table `mstr_supplier_log`
--

DROP TABLE IF EXISTS `mstr_supplier_log`;
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
-- Dumping data for table `mstr_supplier_log`
--

INSERT INTO `mstr_supplier_log` (`id_pk_sup_log`, `executed_function`, `id_pk_sup`, `sup_nama`, `sup_suff`, `sup_perusahaan`, `sup_email`, `sup_telp`, `sup_hp`, `sup_alamat`, `sup_keterangan`, `sup_status`, `sup_create_date`, `sup_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, NULL, NULL, 'Microsoft', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:16:43', '2020-06-22 08:16:43', 1, 1, 235),
(2, 'after insert', 2, NULL, NULL, 'IBM', NULL, NULL, NULL, NULL, NULL, 'aktif', '2020-06-22 08:26:28', '2020-06-22 08:26:28', 1, 1, 243),
(3, 'after update', 1, 'Dani Asdf', 'MR', 'Microsoft', 'dani@gmail.com', '021456789', '082345678', 'jalan abcd no 5 jakarta barat', 'keterangankuuuuu', 'aktif', '2020-06-22 08:16:43', '2020-06-22 11:01:52', 1, 1, 387),
(4, 'after update', 2, 'Pina Supp', 'MRS', 'IBM', 'pina@gmail.com', '02134567890', '089876543', 'jalan absd no 3 denpasar bali', 'keterangan ket ket', 'aktif', '2020-06-22 08:26:28', '2020-06-22 11:02:49', 1, 1, 388),
(5, 'after insert', 3, 'Intel', 'BAPAK', 'Budi Supplier', 'budih@gmail.com', '021456789', '08765432666', 'jalan asdafd no 4 jakarta utara', 'keterangan ket ket keterangan', 'AKTIF', '2020-06-22 11:04:04', '2020-06-22 11:04:04', 1, 1, 389);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_toko`
--

DROP TABLE IF EXISTS `mstr_toko`;
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
-- Dumping data for table `mstr_toko`
--

INSERT INTO `mstr_toko` (`id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'Pendaftaran_SYNC_STUDY.png', 'TOKO MAJU MANDIRI', 'MM', 'AKTIF', '2020-06-21 11:44:14', '2020-06-21 11:44:14', 1, 1),
(2, 'Screenshot_79.png', 'TOKO PUSAT SAFETY', 'PS2323', 'AKTIF', '2020-06-22 11:05:00', '2020-06-22 11:05:00', 1, 1);

--
-- Triggers `mstr_toko`
--
DROP TRIGGER IF EXISTS `trg_after_insert_toko`;
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
DROP TRIGGER IF EXISTS `trg_after_update_toko`;
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
-- Table structure for table `mstr_toko_log`
--

DROP TABLE IF EXISTS `mstr_toko_log`;
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
-- Dumping data for table `mstr_toko_log`
--

INSERT INTO `mstr_toko_log` (`id_pk_toko_log`, `executed_function`, `id_pk_toko`, `toko_logo`, `toko_nama`, `toko_kode`, `toko_status`, `toko_create_date`, `toko_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'Pendaftaran_SYNC_STUDY.png', 'TOKO MAJU MANDIRI', 'MM', 'AKTIF', '2020-06-21 11:44:14', '2020-06-21 11:44:14', 1, 1, 88),
(2, 'after insert', 2, 'Screenshot_79.png', 'TOKO PUSAT SAFETY', 'PS2323', 'AKTIF', '2020-06-22 11:05:00', '2020-06-22 11:05:00', 1, 1, 390);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_user`
--

DROP TABLE IF EXISTS `mstr_user`;
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
-- Dumping data for table `mstr_user`
--

INSERT INTO `mstr_user` (`id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0),
(2, 'adminku2', 'e807f1fcf82d132f9bb018ca6738a19f', 'elfkyushfly@gmail.com', 'AKTIF', 2, '2020-06-22 11:06:08', '2020-06-22 11:06:08', 1, 1);

--
-- Triggers `mstr_user`
--
DROP TRIGGER IF EXISTS `trg_after_insert_user`;
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
DROP TRIGGER IF EXISTS `trg_after_update_user`;
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
-- Table structure for table `mstr_user_log`
--

DROP TABLE IF EXISTS `mstr_user_log`;
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
-- Dumping data for table `mstr_user_log`
--

INSERT INTO `mstr_user_log` (`id_pk_user_log`, `executed_function`, `id_pk_user`, `user_name`, `user_pass`, `user_email`, `user_status`, `id_fk_role`, `user_last_modified`, `user_create_date`, `id_create_date`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@example.com', 'AKTIF', 1, '2020-06-21 23:26:35', '2020-06-21 23:26:35', 0, 0, 2),
(2, 'after insert', 2, 'adminku2', 'e807f1fcf82d132f9bb018ca6738a19f', 'elfkyushfly@gmail.com', 'AKTIF', 2, '2020-06-22 11:06:08', '2020-06-22 11:06:08', 1, 1, 391);

-- --------------------------------------------------------

--
-- Table structure for table `mstr_warehouse`
--

DROP TABLE IF EXISTS `mstr_warehouse`;
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
-- Dumping data for table `mstr_warehouse`
--

INSERT INTO `mstr_warehouse` (`id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1),
(2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si sis', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:25:01', 1, 1);

--
-- Triggers `mstr_warehouse`
--
DROP TRIGGER IF EXISTS `trg_after_insert_warehouse`;
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
DROP TRIGGER IF EXISTS `trg_after_update_warehouse`;
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
-- Table structure for table `mstr_warehouse_log`
--

DROP TABLE IF EXISTS `mstr_warehouse_log`;
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
-- Dumping data for table `mstr_warehouse_log`
--

INSERT INTO `mstr_warehouse_log` (`id_pk_warehouse_log`, `executed_function`, `id_pk_warehouse`, `warehouse_nama`, `warehouse_alamat`, `warehouse_notelp`, `warehouse_desc`, `warehouse_status`, `warehouse_create_date`, `warehouse_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 'GUDANG 1', 'Puri Indah', '12345', '-', 'AKTIF', '2020-06-21 11:45:42', '2020-06-21 11:45:42', 1, 1, 92),
(2, 'after insert', 2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si si', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:24:49', 1, 1, 395),
(3, 'after update', 2, 'GUDANG 2', 'Jalan asdasd Green Lake City', '0216194567', 'deskripsi si si sis', 'AKTIF', '2020-06-22 01:24:49', '2020-06-22 01:25:01', 1, 1, 396);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barang_kombinasi`
--

DROP TABLE IF EXISTS `tbl_barang_kombinasi`;
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
-- Dumping data for table `tbl_barang_kombinasi`
--

INSERT INTO `tbl_barang_kombinasi` (`id_pk_barang_kombinasi`, `id_barang_utama`, `id_barang_kombinasi`, `barang_kombinasi_qty`, `barang_kombinasi_status`, `barang_kombinasi_create_date`, `barang_kombinasi_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1),
(2, 5, 4, 6, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1);

--
-- Triggers `tbl_barang_kombinasi`
--
DROP TRIGGER IF EXISTS `trg_after_insert_barang_kombinasi`;
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
DROP TRIGGER IF EXISTS `trg_after_update_barang_kombinasi`;
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
-- Table structure for table `tbl_barang_kombinasi_log`
--

DROP TABLE IF EXISTS `tbl_barang_kombinasi_log`;
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

--
-- Dumping data for table `tbl_barang_kombinasi_log`
--

INSERT INTO `tbl_barang_kombinasi_log` (`id_pk_barang_kombinasi_log`, `executed_function`, `id_pk_barang_kombinasi`, `id_barang_utama`, `id_barang_kombinasi`, `barang_kombinasi_qty`, `barang_kombinasi_status`, `barang_kombinasi_create_date`, `barang_kombinasi_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 352),
(2, 'after insert', 2, 5, 4, 6, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:16', 1, 1, 353),
(3, 'after update', 1, 5, 2, 5, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 356),
(4, 'after update', 2, 5, 4, 6, 'aktif', '2020-06-22 10:39:16', '2020-06-22 10:39:37', 1, 1, 357);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barang_ukuran`
--

DROP TABLE IF EXISTS `tbl_barang_ukuran`;
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
-- Dumping data for table `tbl_barang_ukuran`
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
-- Table structure for table `tbl_brg_cabang`
--

DROP TABLE IF EXISTS `tbl_brg_cabang`;
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
-- Dumping data for table `tbl_brg_cabang`
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
(9, 10, '-', 'AKTIF', 40000, 3, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1),
(10, 88, 'poiuytrewq', 'AKTIF', 0, 5, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1),
(11, 0, 'Auto insert from item existance check', 'aktif', 0, 4, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1);

--
-- Triggers `tbl_brg_cabang`
--
DROP TRIGGER IF EXISTS `trg_after_insert_brg_cabang`;
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
DROP TRIGGER IF EXISTS `trg_after_update_brg_cabang`;
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
-- Table structure for table `tbl_brg_cabang_log`
--

DROP TABLE IF EXISTS `tbl_brg_cabang_log`;
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
-- Dumping data for table `tbl_brg_cabang_log`
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
(53, 'after update', 8, 30000, 15, '-', 'AKTIF', 2, 1, '2020-06-22 08:09:14', '2020-06-22 08:26:28', 1, 1, 314),
(54, 'after insert', 10, 0, 88, 'poiuytrewq', 'AKTIF', 5, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 399),
(55, 'after insert', 11, 0, 0, 'Auto insert from item existance check', 'aktif', 4, 1, '2020-06-22 01:46:33', '2020-06-22 01:46:33', 1, 1, 400);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brg_pembelian`
--

DROP TABLE IF EXISTS `tbl_brg_pembelian`;
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
-- Dumping data for table `tbl_brg_pembelian`
--

INSERT INTO `tbl_brg_pembelian` (`id_pk_brg_pembelian`, `brg_pem_qty`, `brg_pem_satuan`, `brg_pem_harga`, `brg_pem_note`, `brg_pem_status`, `id_fk_pembelian`, `id_fk_barang`, `brg_pem_create_date`, `brg_pem_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 1, 'pcs', 10000, '-', 'AKTIF', 1, 1, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(2, 2, 'pcs', 11000, '-', 'AKTIF', 1, 2, '2020-06-22 08:16:43', '2020-06-22 08:18:34', 1, 1),
(3, 4, 'Pcs', 30000, '-', 'AKTIF', 2, 2, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(4, 5, 'Pcs', 40000, '-', 'AKTIF', 2, 3, '2020-06-22 08:26:28', '2020-06-22 08:27:08', 1, 1),
(5, 10, 'Pcs', 20000, '-', 'nonaktif', 2, 1, '2020-06-22 08:27:08', '2020-06-22 08:27:18', 1, 1);

--
-- Triggers `tbl_brg_pembelian`
--
DROP TRIGGER IF EXISTS `trg_after_insert_brg_pembelian`;
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
DROP TRIGGER IF EXISTS `trg_after_update_brg_pembelian`;
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
-- Table structure for table `tbl_brg_pembelian_log`
--

DROP TABLE IF EXISTS `tbl_brg_pembelian_log`;
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
-- Dumping data for table `tbl_brg_pembelian_log`
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
-- Table structure for table `tbl_brg_warehouse`
--

DROP TABLE IF EXISTS `tbl_brg_warehouse`;
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
-- Dumping data for table `tbl_brg_warehouse`
--

INSERT INTO `tbl_brg_warehouse` (`id_pk_brg_warehouse`, `brg_warehouse_qty`, `brg_warehouse_notes`, `brg_warehouse_status`, `id_fk_brg`, `id_fk_warehouse`, `brg_warehouse_create_date`, `brg_warehouse_last_modified`, `id_create_data`, `id_last_modified`) VALUES
(1, 8, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:44:56', 1, 1);

--
-- Triggers `tbl_brg_warehouse`
--
DROP TRIGGER IF EXISTS `trg_after_insert_brg_warehouse`;
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
DROP TRIGGER IF EXISTS `trg_after_update_brg_warehouse`;
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
-- Table structure for table `tbl_brg_warehouse_log`
--

DROP TABLE IF EXISTS `tbl_brg_warehouse_log`;
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

--
-- Dumping data for table `tbl_brg_warehouse_log`
--

INSERT INTO `tbl_brg_warehouse_log` (`id_pk_brg_warehouse_log`, `executed_function`, `id_pk_brg_warehouse`, `brg_warehouse_qty`, `brg_warehouse_notes`, `brg_warehouse_status`, `id_fk_brg`, `id_fk_warehouse`, `brg_warehouse_create_date`, `brg_warehouse_last_modified`, `id_create_data`, `id_last_modified`, `id_log_all`) VALUES
(1, 'after insert', 1, 88, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:31:25', 1, 1, 397),
(2, 'after update', 1, 8, 'aaa', 'AKTIF', 1, 1, '2020-06-22 01:31:25', '2020-06-22 01:44:56', 1, 1, 398);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  ADD PRIMARY KEY (`id_pk_brg`);

--
-- Indexes for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  ADD PRIMARY KEY (`id_pk_brg_jenis`);

--
-- Indexes for table `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  ADD PRIMARY KEY (`id_pk_brg_jenis_log`);

--
-- Indexes for table `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  ADD PRIMARY KEY (`id_pk_brg_log`);

--
-- Indexes for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  ADD PRIMARY KEY (`id_pk_brg_merk`);

--
-- Indexes for table `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  ADD PRIMARY KEY (`id_pk_brg_merk_log`);

--
-- Indexes for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  ADD PRIMARY KEY (`id_pk_cabang`);

--
-- Indexes for table `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  ADD PRIMARY KEY (`id_pk_cabang_log`);

--
-- Indexes for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  ADD PRIMARY KEY (`id_pk_cust`);

--
-- Indexes for table `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  ADD PRIMARY KEY (`id_pk_cust_log`);

--
-- Indexes for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  ADD PRIMARY KEY (`id_pk_employee`);

--
-- Indexes for table `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  ADD PRIMARY KEY (`id_pk_employee_log`);

--
-- Indexes for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  ADD PRIMARY KEY (`id_pk_sup`);

--
-- Indexes for table `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  ADD PRIMARY KEY (`id_pk_sup_log`);

--
-- Indexes for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  ADD PRIMARY KEY (`id_pk_toko`);

--
-- Indexes for table `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  ADD PRIMARY KEY (`id_pk_toko_log`);

--
-- Indexes for table `mstr_user`
--
ALTER TABLE `mstr_user`
  ADD PRIMARY KEY (`id_pk_user`);

--
-- Indexes for table `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  ADD PRIMARY KEY (`id_pk_user_log`);

--
-- Indexes for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  ADD PRIMARY KEY (`id_pk_warehouse`);

--
-- Indexes for table `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  ADD PRIMARY KEY (`id_pk_warehouse_log`);

--
-- Indexes for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi`);

--
-- Indexes for table `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  ADD PRIMARY KEY (`id_pk_barang_kombinasi_log`);

--
-- Indexes for table `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  ADD PRIMARY KEY (`ID_PK_BARANG_UKURAN`);

--
-- Indexes for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  ADD PRIMARY KEY (`id_pk_brg_cabang`);

--
-- Indexes for table `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  ADD PRIMARY KEY (`id_pk_brg_cabang_log`);

--
-- Indexes for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  ADD PRIMARY KEY (`id_pk_brg_pembelian`);

--
-- Indexes for table `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  ADD PRIMARY KEY (`id_pk_brg_pembelian_log`);

--
-- Indexes for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  ADD PRIMARY KEY (`id_pk_brg_warehouse`);

--
-- Indexes for table `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  ADD PRIMARY KEY (`id_pk_brg_warehouse_log`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mstr_barang`
--
ALTER TABLE `mstr_barang`
  MODIFY `id_pk_brg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis`
--
ALTER TABLE `mstr_barang_jenis`
  MODIFY `id_pk_brg_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_barang_jenis_log`
--
ALTER TABLE `mstr_barang_jenis_log`
  MODIFY `id_pk_brg_jenis_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mstr_barang_log`
--
ALTER TABLE `mstr_barang_log`
  MODIFY `id_pk_brg_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_barang_merk`
--
ALTER TABLE `mstr_barang_merk`
  MODIFY `id_pk_brg_merk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mstr_barang_merk_log`
--
ALTER TABLE `mstr_barang_merk_log`
  MODIFY `id_pk_brg_merk_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mstr_cabang`
--
ALTER TABLE `mstr_cabang`
  MODIFY `id_pk_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mstr_cabang_log`
--
ALTER TABLE `mstr_cabang_log`
  MODIFY `id_pk_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mstr_customer`
--
ALTER TABLE `mstr_customer`
  MODIFY `id_pk_cust` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_customer_log`
--
ALTER TABLE `mstr_customer_log`
  MODIFY `id_pk_cust_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_employee`
--
ALTER TABLE `mstr_employee`
  MODIFY `id_pk_employee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_employee_log`
--
ALTER TABLE `mstr_employee_log`
  MODIFY `id_pk_employee_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_supplier`
--
ALTER TABLE `mstr_supplier`
  MODIFY `id_pk_sup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mstr_supplier_log`
--
ALTER TABLE `mstr_supplier_log`
  MODIFY `id_pk_sup_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mstr_toko`
--
ALTER TABLE `mstr_toko`
  MODIFY `id_pk_toko` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_toko_log`
--
ALTER TABLE `mstr_toko_log`
  MODIFY `id_pk_toko_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_user`
--
ALTER TABLE `mstr_user`
  MODIFY `id_pk_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_user_log`
--
ALTER TABLE `mstr_user_log`
  MODIFY `id_pk_user_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_warehouse`
--
ALTER TABLE `mstr_warehouse`
  MODIFY `id_pk_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mstr_warehouse_log`
--
ALTER TABLE `mstr_warehouse_log`
  MODIFY `id_pk_warehouse_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi`
--
ALTER TABLE `tbl_barang_kombinasi`
  MODIFY `id_pk_barang_kombinasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_barang_kombinasi_log`
--
ALTER TABLE `tbl_barang_kombinasi_log`
  MODIFY `id_pk_barang_kombinasi_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_barang_ukuran`
--
ALTER TABLE `tbl_barang_ukuran`
  MODIFY `ID_PK_BARANG_UKURAN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang`
--
ALTER TABLE `tbl_brg_cabang`
  MODIFY `id_pk_brg_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_brg_cabang_log`
--
ALTER TABLE `tbl_brg_cabang_log`
  MODIFY `id_pk_brg_cabang_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian`
--
ALTER TABLE `tbl_brg_pembelian`
  MODIFY `id_pk_brg_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_brg_pembelian_log`
--
ALTER TABLE `tbl_brg_pembelian_log`
  MODIFY `id_pk_brg_pembelian_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse`
--
ALTER TABLE `tbl_brg_warehouse`
  MODIFY `id_pk_brg_warehouse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_brg_warehouse_log`
--
ALTER TABLE `tbl_brg_warehouse_log`
  MODIFY `id_pk_brg_warehouse_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
