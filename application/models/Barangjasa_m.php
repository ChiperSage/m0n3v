<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Barangjasa_m extends CI_Model{

    function __construct(){

    }

    public function get_kontak_tpd()
    {
      $tahun = date('Y');
      $sql = "SELECT * FROM tb_tpd WHERE left(tanggal,4) = $tahun";
      return $this->db->query($sql)->result();
    }

    // CRUD sp
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

    public function get_detail($id)
  	{
      $key = array('sp_id'=>$id);
  		return $this->db->get_where('tb_sp', $key)->row();
  	}

    public function create_sp()
  	{
  		$data['sp_nomor'] = $this->input->post('nomor');
      $data['sp_tanggal'] = $this->input->post('tanggal');
      $data['sp_kelompok'] = $this->input->post('kelompok');
  		$this->db->insert('tb_sp', $data);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

  	public function update_sp($id)
  	{
  		$key = array('sp_id' => $id);
      $data['sp_nomor'] = $this->input->post('nomor');
      $data['sp_tanggal'] = $this->input->post('tanggal');
      $data['sp_kelompok'] = $this->input->post('kelompok');

  		$this->db->update('tb_sp', $data, $key);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

    public function delete_sp($id)
  	{
  		$filter = array('sp_id'=>$id);
  		$this->db->delete('tb_sp',$filter);
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

    // end crud sp

    public function get_paket()
    {
      // $filter1 = array('b.jenis_pengadaan'=>'Barang');
      // $this->db->select('a.*,b.nama_paket,b.jenis_pengadaan');
      // $this->db->join('tb_rup b','a.paket_id = b.kode_rup','left');
      // $this->db->where($filter1);
      // $this->db->group_by('a.paket_id');
      // return $this->db->get('tb_sp_paket a')->result();

      $str = "SELECT a.*, b.nama_paket, b.jenis_pengadaan FROM tb_sp_paket a, tb_rup b WHERE a.paket_id = b.kode_rup AND b.jenis_pengadaan LIKE '%barang%'";
      return $this->db->query($str)->result();
    }

    // public function get_paket()
    // {
    //   $filter1 = array('b.jenis_pengadaan'=>'konstruksi');
    //   $this->db->select('a.*,b.nama_paket,b.jenis_pengadaan');
    //   $this->db->join('tb_rup b','a.paket_id = b.kode_rup','left');
    //   $this->db->where($filter1);
    //   $this->db->group_by('a.paket_id');
    //   return $this->db->get('tb_sp_paket a')->result();
    // }
}
