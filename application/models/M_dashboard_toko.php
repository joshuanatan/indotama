<?php
class M_dashboard_toko extends CI_Model
{
  private $id_toko;
  public function __construct()
  {
    parent::__construct();
  }
  public function set_id_toko($id_toko)
  {
    $this->id_toko = $id_toko;
  }
}
