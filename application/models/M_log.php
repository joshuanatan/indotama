<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_log extends ci_model{
    private $tbl_name = "log_all";
    private $columns = array();

    public function __construct(){
        parent::__construct();
        $this->set_column("id_log_all","ID Log",true);
        $this->set_column("log_date","Log Date",false);
        $this->set_column("log","Log Message",false);
        $this->set_column("user_name","Username",false);

        $this->brg_create_date = date("y-m-d h:i:s");
        $this->brg_last_modified = date("y-m-d h:i:s");
        $this->id_create_data = $this->session->id_user;
        $this->id_last_modified = $this->session->id_user;
    }
    private function set_column($col_name,$col_disp,$order_by){
        $array = array(
            "col_name" => $col_name,
            "col_disp" => $col_disp,
            "order_by" => $order_by
        );
        $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
    }
    public function columns(){
        return $this->columns;
    }
    public function content($page = 1,$order_by = 0, $order_direction = "asc", $search_key = "",$data_per_page = ""){
        $order_by = $this->columns[$order_by]["col_name"];
        $search_query = "";
        if($search_key != ""){
            $search_query .= "and
            ( 
                id_log_all like '%".$search_key."%' or
                log_date like '%".$search_key."%' or
                log like '%".$search_key."%' or
                user_name like '%".$search_key."%'
            )";
        }
        $query = "
        select id_log_all,log_date,log,user_name 
        from log_all
        inner join mstr_user on mstr_user.id_pk_user = log_all.id_user
        where id_log_all > 0 ".$search_query."
        order by ".$order_by." ".$order_direction." 
        limit 20 offset ".($page-1)*$data_per_page;

        $result["data"] = executequery($query);
        //echo $this->db->last_query();
        $query = "
        select id_log_all,log_date,log,user_name 
        from log_all
        inner join mstr_user on mstr_user.id_pk_user = log_all.id_user
        where id_log_all > 0 ".$search_query."
        order by ".$order_by." ".$order_direction;
        $result["total_data"] = executequery($query)->num_rows();
        return $result;
    }
}