<?php
defined("BASEPATH") or exit("no direct script");
date_default_timezone_set("asia/jakarta");
class M_log extends ci_model
{
  private $tbl_name = "log_all";
  private $columns = array();

  public function __construct()
  {
    parent::__construct();
    $this->set_column("id_pk_log_all", "ID Log", true);
    $this->set_column("log_all_tgl", "Log Date", false);
    $this->set_column("user_name", "Username", false);
    $this->set_column("log_all_msg", "Log Message", false);
    $this->set_column("log_all_IT", "Log Database", false);
  }
  private function set_column($col_name, $col_disp, $order_by)
  {
    $array = array(
      "col_name" => $col_name,
      "col_disp" => $col_disp,
      "order_by" => $order_by
    );
    $this->columns[count($this->columns)] = $array; //terpaksa karena array merge gabisa.
  }
  public function columns()
  {
    return $this->columns;
  }
  public function content($page = 1, $order_by = 0, $order_direction = "asc", $search_key = "", $data_per_page = "")
  {
    $order_by = $this->columns[$order_by]["col_name"];
    $search_query = "";
    if ($search_key != "") {
      $search_query .= "and
            ( 
                id_pk_log_all like '%" . $search_key . "%' or
                user_name like '%" . $search_key . "%' or
                log_all_tgl like '%" . $search_key . "%' or
                log_all_msg like '%" . $search_key . "%' or
                log_all_data_changes like '%" . $search_key . "%' or
                log_all_IT like '%" . $search_key . "%'
            )";
    }
    $query = "
        select id_pk_log_all,log_all_msg,log_all_data_changes,log_all_it,log_all_user,log_all_tgl,user_name 
        from log_all
        inner join mstr_user on mstr_user.id_pk_user = log_all.log_all_user
        where id_pk_log_all > 0 " . $search_query . "
        order by " . $order_by . " " . $order_direction . " 
        limit 20 offset " . ($page - 1) * $data_per_page;

    $result["data"] = executequery($query);
    //echo $this->db->last_query();
    $query = "
        select id_pk_log_all
        from log_all
        inner join mstr_user on mstr_user.id_pk_user = log_all.log_all_user
        where id_pk_log_all > 0 " . $search_query . "
        order by " . $order_by . " " . $order_direction;
    $result["total_data"] = executequery($query)->num_rows();
    return $result;
  }
}
