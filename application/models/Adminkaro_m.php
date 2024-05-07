<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adminkaro_m extends CI_Model{

    function __construct(){

    }

    public function get_pokja_daftar()
    {
      $this->db->select('*');
      $this->db->from('tb_pokja');
      $this->db->group_by('pokja_nip');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function insert_tender_offline()
    {
      $result=$this->db->get_where('tb_tender_offline',array('kode_rup'=>$this->input->post('kode_rup')))->num_rows();
      if($result == 0){

      $data['kode_rup'] = $this->input->post('kode_rup');
      $data['kode_lelang'] = $this->input->post('kode_rup');
      $data['hps'] = $this->input->post('hps');
      $data['status_lelang'] = 1;
      $data['paket_status'] = 1;
      $data['keterangan'] = 'offline';
      $this->db->insert('tb_tender_offline',$data);
      if($this->db->affected_rows() >= 1){
        $this->session->set_flashdata('msg','<p class="text-success">Tambah data berhasil</p>');
      }

      }else{
        $this->session->set_flashdata('msg','<p class="text-danger">Data sudah ada</p>');
      }
    }

    public function rup_cari()
    {
      $kode_rup = isset($_GET['kode_rup_cari']) ? $_GET['kode_rup_cari'] : 0 ;
      return $this->db->get_where('tb_rup',array('kode_rup'=>$kode_rup))->row();

      // $year = date('Y');
      // $this->db->select('kode_rup, nama_paket, status_aktif, status_umumkan');
      // $this->db->from('tb_rup');
      // $this->db->where("tanggal_terakhir_di_update = '$year' OR left(tanggal_kebutuhan,4) = $year");
      // return $this->db->get()->result();
    }

    public function get_pokja_penerima()
    {
      $tahun = date('Y');

      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $sql = "SELECT pj.*,

      -- (SELECT u.active FROM users u WHERE pj.pokja_nip = u.nip) as status,

      (SELECT COUNT(b.anggota_nip) FROM tb_sp a, tb_sp_anggota b
      WHERE pj.pokja_nip = b.anggota_nip AND a.sp_id = b.anggota_sp AND left(a.sp_tanggal,4) = $tahun) as tkelompok,

      (SELECT COUNT(c.paket_id) FROM tb_sp a, tb_sp_anggota b, tb_sp_paket c
      WHERE pj.pokja_nip = b.anggota_nip AND c.paket_status = 2 AND a.sp_id = b.anggota_sp AND a.sp_id = c.paket_sp AND left(a.sp_tanggal,4) = $tahun) as tpaket

      FROM tb_pokja pj
      LEFT JOIN tb_sp_anggota sa ON pj.pokja_nip = sa.anggota_nip
      LEFT JOIN tb_sp sp ON sa.anggota_sp = sp.sp_id
      GROUP BY pj.pokja_nip";

      return $this->db->query($sql)->result();
    }

    public function list_paket_sp()
    {
      $tahun = date('Y');

      $str = "SELECT pk.*, r.nama_paket, r.pagu_rup, sp.sp_kelompok
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE left(sp.sp_tanggal,4) = $tahun
      GROUP BY pk.paket_id ORDER BY pk.paket_tanggal DESC";
      return $this->db->query($str)->result();
    }
}
