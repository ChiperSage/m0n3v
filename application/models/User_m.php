<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_m extends CI_Model{

    function __construct(){

    }

    public function get($filter)
    {
      $this->db->select('a.*');
      $this->db->from('users a');
      $this->db->where($filter);
      return $this->db->get()->result();
    }

    public function get_detail($filter = array())
    {
      $this->db->select('a.*');
      $this->db->from('users a');
      $this->db->where($filter);
      $this->db->limit(1);
      return $this->db->get()->row();
    }

    // public function insert($path)
    // {
    //   $data['nama'] = $this->input->post('nama');
    //   $data['keterangan'] = $this->input->post('keterangan');
    //   $data['file_path'] = '/uploads/regulasi/' . $path;
    //   $this->db->insert('tb_regulasi', $data);

    //   $result = $this->db->affected_rows();
  		// if($result = 1){
  		// 	$this->session->set_flashdata('msg','<div class="callout callout-success">Upload Berhasil</div>');
  		// }
    // }
}
