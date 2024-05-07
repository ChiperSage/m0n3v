<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpa_lib{

  public $CI;

  public function __construct()
  {
    $this->CI =& get_instance();
    $CI->load->database();
  }

  public function get_skpa_dropdown()
  {
    // $CI->db->select('*');
    // $CI->db->from('tb_skpa');
    // $CI->db->group_by('kode');
    // return $CI->db->get()->result();
  }

}
