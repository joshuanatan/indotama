<?php
class Database_handler extends CI_Controller{
        /*idenya kita drop dan reassign trigger insert dan update*/
        /*polanya namauser - menambah/mengubah/menghapus data [barang/customer/dll]. Perubahan data [new_data_tbl1 => new_data_tbl2]*/

    public function install_triggers($db_name){

        $sql = "select distinct table_name from information_schema.tables where table_schema = 'indotama' and (table_name like 'tbl%' or table_name like 'mstr%') and table_name not like '%log%' and table_type = 'BASE TABLE'";
        $main = executeQuery($sql);
        $main = $main->result_array();

        for($a = 0; $a<count($main); $a++){
            $this->install_log_tables($main[$a]["table_name"],$main[$a]["table_name"]."_log");
            $this->install_std_triggers($main[$a]["table_name"],$main[$a]["table_name"]."_log");
        }

        $tables_main = array(
            "mstr_barang",
            "mstr_barang_jenis",
            "mstr_barang_merk",
            "mstr_cabang",
            "mstr_customer",
            "mstr_employee",
            "mstr_jabatan",
            "mstr_marketplace",
            "mstr_menu",
            "mstr_pembelian",
            "mstr_penawaran",
            "mstr_penerimaan",
            "mstr_pengiriman",
            "mstr_penjualan",
            "mstr_retur",
            "mstr_satuan",
            "mstr_supplier",
            "mstr_toko",
            "mstr_user",
            "mstr_warehouse",
            "tbl_brg_cabang",
            "tbl_brg_permintaan",
            "tbl_brg_pindah",
            "tbl_brg_warehouse",
            "tbl_cabang_admin",
            "tbl_toko_admin",
            "tbl_warehouse_admin"
        );
        for($a = 0; $a<count($tables_main); $a++){
            $this->install_adv_triggers($tables_main[$a],$tables_main[$a]."_log");
        }
    }
    public function install_std_triggers($table_main,$table_log){
        $trig_table_ref = explode("_",$table_main);
        unset($trig_table_ref[0]);
        $trig_table_ref = implode("_",$trig_table_ref);


        $sql = "show columns from ".$table_main;
        $result = executeQuery($sql);
        $tbl_name = $result->result_array();

        
        $target_cols = "executed_function";
        $update_log = "'after update'";
        $insert_log = "'after insert'";
        $log_all_msg = "";
        for($a = 0; $a<count($tbl_name); $a++){ //yang 0 pasti pk, jadi diskipp soalnya AI. yang terakhir id_log_all, gamau diisi
            $target_cols .= ",".$tbl_name[$a]["Field"];
            $insert_log .= ",new.".$tbl_name[$a]["Field"];
            $update_log .= ",new.".$tbl_name[$a]["Field"];
            $log_all_msg .= "[new.".$tbl_name[$a]["Field"]." => old.".$tbl_name[$a]["Field"]."]";
        }

        $sql = "drop trigger if exists trg_after_insert_$trig_table_ref;";
        executeQuery($sql);

        $sql = "
        create trigger trg_after_insert_$trig_table_ref
        after insert on $table_main\r\n
        for each row\r\n
        begin
            insert into $table_log($target_cols) values ($insert_log);\r\n
        end\r\n";
        executeQuery($sql);

        $sql = "drop trigger if exists trg_after_update_".$trig_table_ref.";";
        executeQuery($sql);

        $sql = "
        create trigger trg_after_update_".$trig_table_ref."
        after update on $table_main\r\n
        for each row\r\n
        begin
            insert into $table_log($target_cols) values ($update_log);\r\n
        end\r\n";
        executeQuery($sql);
        
    }
    public function install_adv_triggers($table_main,$table_log){
        $trig_table_ref = explode("_",$table_main);
        unset($trig_table_ref[0]);
        $trig_table_ref = implode("_",$trig_table_ref);


        $sql = "show columns from ".$table_main;
        $result = executeQuery($sql);
        $tbl_name = $result->result_array();

        $target_cols = "executed_function";
        $update_log = "'after update'";
        $insert_log = "'after insert'";
        $log_all_msg_update = "concat(''";
        $log_all_msg_insert = "concat(''";
        for($a = 0; $a<count($tbl_name); $a++){ //yang 0 pasti pk, jadi diskipp soalnya AI. yang terakhir id_log_all, gamau diisi
            $target_cols .= ",".$tbl_name[$a]["Field"];
            $insert_log .= ",new.".$tbl_name[$a]["Field"];
            $update_log .= ",new.".$tbl_name[$a]["Field"];
            $log_all_msg_update .= ",'[".$tbl_name[$a]['Field'].": ',old.".$tbl_name[$a]["Field"].",' => ',new.".$tbl_name[$a]["Field"].",']'";
            $log_all_msg_insert .= ",'[".$tbl_name[$a]['Field'].": ',new.".$tbl_name[$a]["Field"].",']'";
        }
        $log_all_msg_update.= ")";
        $log_all_msg_insert.= ")";
        $sql = "drop trigger if exists trg_after_insert_$trig_table_ref;";
        executeQuery($sql);

        $sql = "
        create trigger trg_after_insert_$trig_table_ref
        after insert on $table_main\r\n
        for each row\r\n
        begin
            insert into $table_log($target_cols) values ($insert_log);\r\n
            select last_insert_id() into @last_id;
            set @log_msg = concat('Data baru ditambahkan pada tabel $table_main. Waktu penambahan: ',now());
            set @log_it = concat('Refrensi log table $table_log dengan ".$tbl_name[0]["Field"]."_log ',@last_id);
            set @log_data = $log_all_msg_insert;
            call insert_log_all(new.id_last_modified,@log_msg,@log_data,@log_it);
        end\r\n";
        executeQuery($sql);

        $sql = "drop trigger if exists trg_after_update_".$trig_table_ref.";";
        executeQuery($sql);

        $sql = "
        create trigger trg_after_update_".$trig_table_ref."
        after update on $table_main\r\n
        for each row\r\n
        begin
            insert into $table_log($target_cols) values ($update_log);\r\n
            select last_insert_id() into @last_id;
            set @log_msg = concat('Data diubah pada tabel $table_main. Waktu perubahan: ',now());
            set @log_it = concat('Refrensi log table $table_log dengan ".$tbl_name[0]["Field"]."_log ',@last_id);
            set @log_data = $log_all_msg_update;
            call insert_log_all(new.id_last_modified,@log_msg,@log_data,@log_it);
        end\r\n";
        executeQuery($sql);
        
    }
    public function install_log_tables($table_main, $table_log){
        
        $sql = "SELECT column_name,column_type
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'indotama' AND TABLE_NAME = '$table_main'; ";
        
        $result = executeQuery($sql);
        $tbl_name = $result->result_array();
        $columns = "id_tbl_log int primary key auto_increment,executed_function varchar(400)";
        for($a = 0; $a<count($tbl_name); $a++){
            $columns .= ",".$tbl_name[$a]["column_name"]." ".$tbl_name[$a]["column_type"];
        }

        $sql = "drop table if exists ".$table_log;
        executeQuery($sql);

        $sql = "create table ".$table_log."($columns)";
        executeQuery($sql);
    }
}