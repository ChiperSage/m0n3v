<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Rup_lib {

  public function __construct()
  {

  }

  public function get_rup($config)
  {
    $filter = array();
    return $this->db->get_where('tb_rup',$filter)->result();
  }

  

}
