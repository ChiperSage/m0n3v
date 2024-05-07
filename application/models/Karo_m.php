<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Karo_m extends CI_Model{

    function __construct(){

    }

    public function get_sp()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $this->db->select('a.*, (SELECT COUNT(paket_id) FROM tb_sp_paket WHERE paket_status != 2 AND paket_sp = a.sp_id) as tstatus,
        (SELECT pokja_nama FROM tb_sp_anggota d
        LEFT JOIN tb_pokja e ON d.anggota_nip = e.pokja_nip
        WHERE d.anggota_sp = a.sp_id AND anggota_jabatan = "ketua") as ketua_pokja,
        (SELECT COUNT(b.id) FROM tb_sp_anggota b WHERE a.sp_id = b.anggota_sp) as tanggota,
        (SELECT COUNT(c.id) FROM tb_sp_paket c WHERE a.sp_id = c.paket_sp) as tpaket,
        (SELECT COUNT(b.id) FROM tb_sp_anggota b WHERE a.sp_id = b.anggota_sp AND b.anggota_keterangan != "-") as tketerangan');
      $this->db->from('tb_sp a');
      $this->db->where(array('a.tahun'=>$tahun));
      $this->db->group_by('a.sp_nomor');
  		$query = $this->db->get();
  		return $query->result();
    }

    // list anggota sp
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

    public function update_sp_anggota($data, $filter)
    {
      $this->db->update('tb_sp_anggota',$data,$filter);
    }

    public function get_paket($sp)
    {
      $this->db->select('a.*,c.nama_paket,b.sp_kelompok,b.sp_status');
      $this->db->from('tb_sp_paket a');
      $this->db->where(array('paket_sp'=>$sp));
      $this->db->join('tb_sp b','a.paket_sp = b.sp_id','left');
      $this->db->join('tb_rup c','a.paket_id = c.kode_rup','left');
      $this->db->group_by('a.id');
      $query = $this->db->get();
      return $query->result();
    }

    public function sp_confirm($id)
    {
      // karo konfirm semua paket
      $key1 = array('sp_id'=>$id);
      $data1 = array('sp_status'=>2);
      $this->db->update('tb_sp',$data1,$key1);

      $key = array('paket_sp'=>$id);
  		$data = array('paket_status'=>2);
  		$this->db->update('tb_sp_paket',$data,$key);

      // masukkan semua ke tb_review
      $str = "INSERT INTO tb_review (kode_rup,id_sp,tgl_review,tgl_selesai,status)
      SELECT paket_id,paket_sp, NOW() as tgl_review, '0000-00-00' as tgl_selesai, 5 as status FROM tb_sp_paket
      WHERE paket_sp = '$id' AND paket_id NOT IN (SELECT kode_rup FROM tb_review)";
      $this->db->query($str);

      // set temp
      $str = "INSERT INTO tb_sp_paket_temp (sp_id,kode_rup,paket_keterangan,paket_tanggal,paket_status,paket_log,nt,paket_sp,paket_id,keterangan)
      SELECT sp.sp_id,sp.kode_rup,sp.paket_keterangan,sp.paket_tanggal,sp.paket_status,sp.paket_log,sp.nt,sp.paket_sp,sp.paket_id, 'sp' as keterangan FROM tb_sp_paket sp
      WHERE sp.sp_id = '$id'";
      $this->db->query($str);
    }

    public function get_paketbatal()
    {
      $this->db->select('a.*,b.nama_paket,c.sp_kelompok,b.jenis_pengadaan');
      $this->db->join('tb_rup b','a.batal_paket = b.kode_rup','left');
      $this->db->join('tb_sp c','a.batal_sp = c.sp_id','left');
      return $this->db->get('tb_batal a')->result();
    }

    public function count($filter)
    {
      return $this->db->get_where('tb_sp',$filter)->num_rows();
    }

    public function get_paket_review()
    {
      $tahun = date('Y');

      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, c.sp_kelompok,
      (SELECT tgl_review FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as tgl_review,
      (SELECT rv.status FROM tb_review_paket rv WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as rv_status,
      (SELECT keterangan FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket_review,
      a.tgl_review, a.tgl_selesai, a.status, b.nama_kpa');
      $this->db->from('tb_review a');
      $this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
      $this->db->join('tb_sp c','a.id_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where(array('d.anggota_jabatan'=>'anggota','d.anggota_nip'=>$nip, 'left(c.sp_tanggal,4)'=>$tahun));
      $this->db->order_by('a.status ASC');
      $this->db->group_by('a.kode_rup');
  		$query = $this->db->get();
  		return $query->result();
    }
}
