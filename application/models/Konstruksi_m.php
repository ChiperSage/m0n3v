<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Konstruksi_m extends CI_Model{

    function __construct(){

    }

    public function get_paket()
    {
      // $filter1 = array('b.jenis_pengadaan'=>'pekerjaan konstruksi');
      // $this->db->select('a.*,b.nama_paket,b.jenis_pengadaan');
      // $this->db->join('tb_rup b','a.paket_id = b.kode_rup','left');
      // $this->db->where($filter1);
      // $this->db->group_by('a.paket_id');
      // return $this->db->get('tb_sp_paket a')->result();

      $str = "SELECT a.*, b.nama_paket, b.jenis_pengadaan FROM tb_sp_paket a, tb_rup b WHERE a.paket_id = b.kode_rup AND b.jenis_pengadaan LIKE '%konstruksi%'";
      return $this->db->query($str)->result();
    }
}
