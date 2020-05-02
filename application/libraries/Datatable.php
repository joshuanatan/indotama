<?php
/**
 * Class ini dibuat untuk mempermudah penggunaan datatable agar tidak perlu melihat pada project-project sebelumnya
 * library ini hanya membantu penyimpanan data sementara dan mengembalikan data tersebut apabila dibutuhkan
 * library ini tidak melakukan koneksi ke database, controller yang akan melakukan koneksi ke database
 */
class Datatable{
    private $CI;
    private $order_by = "";
    private $order_direction = "";
    private $page_number = "";
    private $offset = "";
    private $data_limit = "";
    private $search_field = array(); //ini untuk search, nanti direfer kesini
    private $sort_field = array(); //ini untuk sorting
    private $sort_print_field = array(); //ini untuk tampilan sorting
    private $db_field = array(); //untuk select
    private $search_key = "";
    private $group_by = "";
    //private $display_table_field = array(); //untuk tampil di table display
    
    public function __construct($config){
        $this->CI =& get_instance();
        $this->order_by = $config["order_by"];
        $this->order_direction = $config["order_direction"];
        $this->page_number = $config["page_number"];
        $this->offset = 0;
        $this->data_limit = $config["limit"];
        $this->search_field = $config["search_field"];
        $this->sort_field = $config["sort_field"];
        $this->sort_print_field = $config["sort_print_field"];
        $this->db_field = $config["db_field"];
    }
    public function get_datatable($table,$where){
        $this->CI->db->limit($this->data_limit);
        $this->CI->db->offset($this->offset);
        $this->CI->db->select($this->db_field);

        $this->CI->db->group_start();
        $this->CI->db->where($where);
        $this->CI->db->group_end();
        $or_like = $this->get_or_like();
        if($or_like["is_search"] == 1){
            $this->CI->db->group_start();
            $this->CI->db->or_like($or_like);
            $this->CI->db->group_end();
        }
        if($this->group_by != ""){
            $this->CI->db->group_by($this->group_by);
        }
        $this->CI->db->order_by($this->order_by,$this->order_direction);
        return $this->CI->db->get($table);
    }
    public function print(){
        echo "library loaded";
    }
    public function sort($input_order_by, $input_direction){
        $this->order_by = $input_order_by;
        $this->order_direction = $input_direction;
    }
    public function removeFilter(){
        $this->order_by = "";
        $this->order_direction = "";
        $this->search = "";
    }
    public function get_page_number(){
        return $this->page_number;
    }
    public function get_numbering(){
        if($this->page_number <= 3){
            $data["numbers"] = array(1,2,3,4,5);
            $data["prev"] = 1;
            $data["search"] = 1;
        }
        else{
            for($a = 0; $a<5; $a++){
                $data["numbers"][$a] = $this->page_number+$a-2;
                $data["prev"] = 0;
                $data["search"] = 1;
            }
        }
        return $data;
    }
    public function get_offset(){
        return 10*($this->page_number-1);
    }
    public function get_sort_field(){
        return $this->sort_field;
    }
    public function get_sort_print_field(){
        return $this->sort_print_field;
    }
    public function get_order_field(){
        return $this->order_by;
    }
    public function get_order_direction(){
        return $this->order_direction;
    }
    public function get_or_like(){
        if($this->search_key != "" && count($this->search_field) > 0){
            $field = $this->search_field; //ngambil field apa aja yang mau di cari
            for($a = 0; $a<count($field); $a++){
                $search_variable[$field[$a]] = $this->search_key; // $search_variable["field 1"] = "asdf"
            }
            $search_variable["is_search"] = 1; //kalau ada yang di cari
        }
        else{
            $search_variable["is_search"] = 0; //kalau tidak ada yang di cari
        }
        return $search_variable;
    }
    public function get_dbfield(){
        return $this->db_field;
    }
    public function get_limit(){
        return $this->data_limit;
    }
    public function get_display_table_field(){
        return $this->display_table_field;
    }
    public function get_search_key(){
        return $this->search_key;
    }
    public function set_page_number($page_number){
        $this->page_number = $page_number;
    }
    public function set_offset($offset){
        $this->offset = $offset;
    }
    public function set_sort_field($sort_field){
        $this->sort_field = $sort_field;
    }
    public function set_sort_print_field($sort_print_field){
        $this->sort_print_field = $sort_print_field;
    }
    public function set_order_field($order_by){
        $this->order_by = $order_by;
    }
    public function set_order_direction($order_direction){
        $this->order_direction = $order_direction;
    }
    public function set_dbfield($db_field){
        $this->dbfield = $db_field;
    }
    public function set_limit($data_limit){
        $this->data_limit = $data_limit;
    }
    public function set_display_table_field($display_table_field){
        $this->display_table_field = $display_table_field;
    }
    public function set_search_key($search_key){
        $this->search_key = $search_key;
    }
    public function set_group_by($group_by){
        $this->group_by = $group_by;
    }
}