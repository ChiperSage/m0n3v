<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kabagpp_m extends CI_Model{

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

    public function get_anggota($sp)
    {
      $this->db->select('*');
      $this->db->from('tb_sp_anggota a');
      $this->db->where(array('anggota_sp'=>$sp));
      $this->db->join('tb_sp b','a.anggota_sp = b.sp_id');
      $this->db->join('tb_pokja c','a.anggota_nip = c.pokja_nip');
      $this->db->group_by('a.id');
      $query = $this->db->get();
      return $query->result();
    }

    public function sp_confirm($id, $val)
    {
      if($val == 1){
        $val = 0;
      }else{
        $val = 1;
      }
      $key = array('sp_id'=>$id,'sp_status <'=>2);
  		$data = array('sp_status'=>$val);
  		$this->db->update('tb_sp',$data,$key);
    }

    public function get_paketbatal()
    {
      $this->db->select('a.*,b.nama_paket,c.sp_kelompok,b.jenis_pengadaan');
      $this->db->join('tb_rup b','a.batal_paket = b.kode_rup','left');
      $this->db->join('tb_sp c','a.batal_sp = c.sp_id','left');
      return $this->db->get('tb_batal a')->result();
    }
}
