<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jasalain_m extends CI_Model{

    function __construct(){

    }

    public function get_sp()
    {
      $this->db->select('a.*,
        (SELECT pokja_nama FROM tb_sp_anggota d
        LEFT JOIN tb_pokja e ON d.anggota_nip = e.pokja_nip
        WHERE d.anggota_sp = a.sp_id AND anggota_jabatan = "ketua") as ketua_pokja,
        (SELECT COUNT(b.id) FROM tb_sp_anggota b WHERE a.sp_id = b.anggota_sp) as tanggota,
        (SELECT COUNT(c.id) FROM tb_sp_paket c WHERE a.sp_id = c.paket_sp) as tpaket');
      $this->db->from('tb_sp a');
      $this->db->group_by('a.sp_nomor');
  		$query = $this->db->get();
  		return $query->result();
    }
}
