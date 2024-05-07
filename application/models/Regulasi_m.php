<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Regulasi_m extends CI_Model{

    function __construct(){

    }

    public function get()
    {
      return $this->db->get('tb_regulasi')->result();
    }

    public function get_detail($id)
    {

    }

    public function insert($path)
    {
      $data['nama'] = $this->input->post('nama');
      $data['keterangan'] = $this->input->post('keterangan');
      $data['file_path'] = '/uploads/regulasi/' . $path;
      $this->db->insert('tb_regulasi', $data);

      $result = $this->db->affected_rows();
  		if($result = 1){
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Upload Berhasil</div>');
  		}
    }
}
