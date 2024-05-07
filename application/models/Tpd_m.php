<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tpd_m extends CI_Model{

    function __construct(){

    }

    public function get_settpd()
    {
      return $this->db->get_where('tb_settpd',array())->row('set');
    }

    public function get_daftar_antrian()
    {
      $this->db->select('a.*, b.nama');
      $this->db->join('tb_skpa b','a.id_satker = b.kode','left');
      $this->db->order_by('nomor_antrian ASC');
      return $this->db->get_where('tb_tpd_antrian a',array('tanggal'=>date('Y-m-d')))->result();
    }

    public function dinas_paket()
    {
      // logged user
      $id = $this->session->userdata('user_id');
  		$data_login = $this->ion_auth->user($id)->row();

      $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }

  		$this->db->select('a.*, b.first_name, b.last_name');
      $this->db->from('tb_tpd a');
      $this->db->join('users b','a.petugas_id = b.id','left');
      $this->db->where("(a.id_satker = '$id_satker' AND (a.tpd_status = 6 OR a.tpd_status = 9))");
      // $this->db->where(array('a.id_satker'=>$id_satker));
      // $this->db->or_where(array('a.tpd_status'=>6,'a.tpd_status'=>9));
      $this->db->group_by('a.id');
      $this->db->order_by('a.tanggal DESC');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function get_pakettelahkirim()
    {
      // logged user
      $id = $this->session->userdata('user_id');
      $data_login = $this->ion_auth->user($id)->row();

      $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }

      $this->db->select('a.*, b.first_name, b.last_name');
      $this->db->from('tb_tpd a');
      $this->db->join('users b','a.petugas_id = b.id','left');
      $this->db->where("(a.id_satker = '$id_satker' AND a.tpd_status = 8)");
      $this->db->group_by('a.id');
      $this->db->order_by('a.tanggal DESC');
      $query = $this->db->get();
      return $query->result();
    }

    public function get()
  	{
      // get login data
      $id = $this->session->userdata('user_id');
  		$data_login = $this->ion_auth->user($id)->row();

      $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }

  		$this->db->select('a.*, b.first_name, b.last_name');
      $this->db->from('tb_tpd a');
      $this->db->join('users b','a.petugas_id = b.id','left');
      $this->db->where(array('a.id_satker'=>$id_satker,'a.tpd_status'=>6));
      $this->db->group_by('a.id');
      $this->db->order_by('a.tanggal DESC');
  		$query = $this->db->get();
  		return $query->result();
  	}

    public function tpd_get($var = '')
    {

      // print_r($var);

      $filter = "tpd_status = 7";

      if( !empty($var) ){
        $filter = "jenis_pengadaan LIKE '%$var%' AND tpd_status = 7";
      }

      if( isset($var['id_satker']) && isset($var['tanggal_terima_dok']) ){
        $filter = array('a.id_satker'=>$var['id_satker'],'a.tanggal_terima_dok'=>$var['tanggal_terima_dok'],'a.ba'=>$var['ba'],'a.tpd_status'=>$var['tpd_status']);

      }

      $this->db->select('a.*, b.first_name, b.last_name');
      $this->db->from('tb_tpd a');
      $this->db->join('users b','a.petugas_id = b.id','left');
      $this->db->where($filter);
      $this->db->group_by('a.kode_rup');
      $this->db->order_by('a.tanggal DESC');
      $query = $this->db->get();
      return $query->result();
    }

    public function tpd_detail($kode_rup)
    {
      $tb = 'tb_tpd_checklist b';
      $jenis = $this->db->get_where('tb_tpd',array('kode_rup'=>$kode_rup))->row();
      if(strpos($jenis->jenis_pengadaan, 'Barang') !== false){
        $tb = 'tb_tpd_barang b';
      }elseif (strpos($jenis->jenis_pengadaan, 'Jasa Lainnya') !== false){
        $tb = 'tb_tpd_jasa b';
      }elseif (strpos($jenis->jenis_pengadaan, 'Pekerjaan Konstruksi') !== false) {
        $tb = 'tb_tpd_konstruksi b';
      }elseif (strpos($jenis->jenis_pengadaan, 'Jasa Konsultansi') !== false) {
        $tb = 'tb_tpd_konsultansi b';
      }

      // $tb = '';
      // if(strpos($jenis_pengadaan, "Barang") !== false){
      //   $tb = 'tb_tpd_barang';
      // }elseif(strpos($jenis_pengadaan, "Jasa Lainnya") !== false) {
      //   $tb = 'tb_tpd_jasa';
      // }elseif(strpos($jenis_pengadaan, "Pekerjaan Konstruksi") !== false) {
      //   $tb = 'tb_tpd_konstruksi';
      // }elseif(strpos($jenis_pengadaan, "Jasa Konsultansi") !== false) {
      //   $tb = 'tb_tpd_konsultansi';
      // }

      $this->db->select('a.*, u.*, b.*, a.kode_rup as vkode_rup, a.id_satker as vid_satker');
      $this->db->from('tb_tpd a');
      $this->db->join($tb,'b.kode_rup = a.kode_rup','left');
      $this->db->join('users u','b.id_petugas = u.id','left');
      $this->db->where(array('a.kode_rup'=>$kode_rup));
      $this->db->group_by('a.kode_rup');
      $query = $this->db->get();
      return $query->row();
    }

    public function list_skpa()
    {
      $this->db->select('id_satker,nama_satker');
      $this->db->from('tb_rup');
      // $this->db->where();
      $this->db->group_by('id_satker');
      $this->db->order_by('nama_satker ASC');
      return $this->db->get()->result();
    }

    public function list_rup()
    {
      $tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');
      $tahun = date('Y');

      // get login data
      $id = $this->session->userdata('user_id');
  		$data_login = $this->ion_auth->user($id)->row();

      // $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }

      $str = "SELECT r.*,l.hps from tb_rup r, tb_lelang l
  		WHERE r.id_satker = '$id_satker' AND l.tahun = $tahun AND (r.kode_rup = l.kode_rup AND l.status_lelang = 0 AND l.paket_status = 0)
      AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND r.kode_rup NOT IN (SELECT kode_rup FROM tb_tpd)";

  		$query = $this->db->query($str);
  		return $query->result();
    }

    public function list_rup2()
    {
      // $tahun = date('Y');
      $json = $this->db->get_where('json',array('data'=>'lelang'))->row();
      $tahun = $json->tahun;

      // get login data
      $id = $this->session->userdata('user_id');
      $data_login = $this->ion_auth->user($id)->row();

      // $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }

      // $str = "SELECT l.* FROM tb_lelang l
      // LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      // WHERE (l.status_lelang = 0 AND l.paket_status = 0) AND r.id_satker = '$id_satker'
      // AND r.status_aktif = 'ya' AND (l.ukpbj = '1106' OR l.ukpbj = '1106.00' OR l.ukpbj = '3106' OR l.ukpbj = '3106.00') 
      // AND r.tahun = $tahun AND r.kode_rup NOT IN (SELECT kode_rup FROM tb_tpd) GROUP BY l.kode_rup";

      $str = "(SELECT l.* FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE (l.status_lelang = 0 AND l.paket_status = 0) AND r.id_satker = '$id_satker'
      AND r.status_aktif = 'ya' AND (l.ukpbj = '1106' OR l.ukpbj = '1106.00' OR l.ukpbj = '3106' OR l.ukpbj = '3106.00') 
      AND r.tahun = $tahun AND r.kode_rup NOT IN (SELECT kode_rup FROM tb_tpd) GROUP BY l.kode_rup) 
      UNION 
      (SELECT l.* FROM tb_lelang l
      LEFT JOIN tb_rup_pon r ON l.kode_rup = r.kode_rup
      WHERE (l.status_lelang = 0 AND l.paket_status = 0) AND r.id_satker = '$id_satker'
      AND r.status_aktif = 'ya' AND (l.ukpbj = '1106' OR l.ukpbj = '1106.00' OR l.ukpbj = '3106' OR l.ukpbj = '3106.00') 
      AND r.tahun = $tahun AND r.kode_rup NOT IN (SELECT kode_rup FROM tb_tpd) GROUP BY l.kode_rup)";

      $query = $this->db->query($str);
      return $query->result();
    }

    public function sisa_antrian()
    {
      $max = 50;
      $date = date('Y-m-d');
      $sql = "SELECT COUNT(id) as total FROM tb_tpd_antrian WHERE tanggal = '$date' AND status != 'tidak_ada'";
      $sisa_antrian = ($max - $this->db->query($sql)->row('total'));
      return $sisa_antrian;
    }

    public function contoh_list_paket($tahun)
  	{
  		$str = "SELECT * FROM tb_rup
  		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skn)
  		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
  		AND tahun = '$tahun' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
  		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD')";
  		$query = $this->db->query($str);
  		return $query->result();
  	}

  	public function get_detail($id)
  	{
      $id_satker = $this->login_id_satker();

      $this->db->select('*');
      $this->db->from('tb_tpd');
      $this->db->where(array('id'=>$id,'id_satker'=>$id_satker));
      // $this->db->group_by('id');
      $query = $this->db->get();
      return $query->row();
  	}

  	public function create()
  	{
      $data['tanggal'] = date('Y-m-d H:i:s');
      $data['kode_rup'] = $this->input->post('kode_rup');
      $data['nama_pabung'] = $this->input->post('nama_pabung');
      $data['hp_pabung'] = $this->input->post('hp_pabung');
      $data['nilai_hps'] = $this->input->post('nilai_hps');
      $data['pengelola_teknis_kegiatan'] = $this->input->post('pengelola_teknis_kegiatan');
      $data['hp_pengelola_teknis_kegiatan'] = $this->input->post('hp_ptk');
      $data['id_satker'] = $this->login_id_satker();

      $data['nama_skpa'] = $this->input->post('nama_skpa');
      $data['nama_pa'] = $this->input->post('nama_pa');
      $data['hp_pa'] = $this->input->post('hp_pa');
      $data['nama_paket'] = $this->input->post('nama_paket');
      $data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
      $data['lokasi_pekerjaan'] = $this->input->post('lokasi_pekerjaan');
      $data['sumber_dana'] = $this->input->post('sumber_dana');
      $data['nilai_pagu'] = $this->input->post('nilai_pagu');
      $data['awal_pengadaan'] = $this->input->post('awal_pengadaan');
      $data['akhir_pengadaan'] = $this->input->post('akhir_pengadaan');
      $data['status_pengadaan'] = $this->input->post('status_pengadaan');
      $data['petugas_id'] = $this->session->userdata('user_id');
      $data['tpd_status'] = 6;
      $data['norega1'] = $this->input->post('norega1');
      $data['jenis_dana'] = $this->input->post('jenis_dana');

  		$this->db->insert('tb_tpd', $data);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menambah data.</div>');
  		}
  	}

  	public function update($id)
  	{
      $id_satker = $this->login_id_satker();
  		$key = array('id'=>$id,'id_satker'=>$id_satker);

      $data['tanggal'] = date('Y-m-d H:i:s');
      $data['kode_rup'] = $this->input->post('kode_rup');
      $data['nama_pabung'] = $this->input->post('nama_pabung');
      $data['hp_pabung'] = $this->input->post('hp_pabung');
      $data['nilai_hps'] = $this->input->post('nilai_hps');
      $data['pengelola_teknis_kegiatan'] = $this->input->post('pengelola_teknis_kegiatan');
      $data['hp_pengelola_teknis_kegiatan'] = $this->input->post('hp_ptk');
      $data['id_satker'] = $this->login_id_satker();

      $data['nama_skpa'] = $this->input->post('nama_skpa');
      $data['nama_pa'] = $this->input->post('nama_pa');
      $data['hp_pa'] = $this->input->post('hp_pa');
      $data['nama_paket'] = $this->input->post('nama_paket');
      $data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
      $data['lokasi_pekerjaan'] = $this->input->post('lokasi_pekerjaan');
      $data['sumber_dana'] = $this->input->post('sumber_dana');
      $data['nilai_pagu'] = $this->input->post('nilai_pagu');
      $data['awal_pengadaan'] = $this->input->post('awal_pengadaan');
      $data['akhir_pengadaan'] = $this->input->post('akhir_pengadaan');
      $data['status_pengadaan'] = $this->input->post('status_pengadaan');
      $data['norega1'] = $this->input->post('norega1');

  		$this->db->update('tb_tpd',$data,$key);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengupdate data.</div>');
  		}
  	}

    public function delete($kode_rup)
  	{
      $id_satker = $this->login_id_satker();
  		$filter = "kode_rup = '$kode_rup' AND id_satker = '$id_satker' AND tpd_status = 6";
  		$this->db->delete('tb_tpd',$filter);
  		if($result == 1){
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

    public function login_id_satker()
    {
      $id = $this->session->userdata('user_id');
  		$data_login = $this->ion_auth->user($id)->row();

      $id_satker = 0;
      if($data_login->id_satker != 0 && !empty($data_login->id_satker)){
        $id_satker = $data_login->id_satker;
      }
      return $id_satker;
    }

    public function count_jenis_pengadaan($var)
    {
      return $this->db->get_where('tb_tpd',array('jenis_pengadaan'=>$var))->num_rows();
    }

    public function get_berita_acara($filter)
    {
      $tanggal = date('Y-m-d');
      $id_satker = 0;
      if(isset($_GET['tanggal']) && isset($_GET['skpa'])){
        $tanggal = $_GET['tanggal'];
        $id_satker = $_GET['skpa'];
      }

      $str = "SELECT a.tanggal, a.id_satker, b.nama, a.nama_pabung
        FROM tb_tpd a
        LEFT JOIN tb_skpa b ON a.id_satker = b.kode
        WHERE a.tanggal = '$tanggal' AND a.id_satker = '$id_satker'
        GROUP BY a.tanggal";
      return $this->db->query($str)->row();
    }

    public function get_berita_list($filter)
    {
      $tanggal = date('Y-m-d');
      $id_satker = 0;
      if(isset($_GET['tanggal']) && isset($_GET['skpa'])){
        $tanggal = $_GET['tanggal'];
        $id_satker = $_GET['skpa'];
      }

      $str = "(SELECT a.kode_rup, b.nama_paket, b.jenis_pengadaan FROM tb_tpd_barang a LEFT JOIN tb_tpd b ON a.kode_rup = b.kode_rup
          WHERE b.tanggal = '$tanggal' AND b.id_satker = '$id_satker' AND kelengkapan = '1') UNION
              (SELECT a.kode_rup, b.nama_paket, b.jenis_pengadaan FROM tb_tpd_jasa a LEFT JOIN tb_tpd b ON a.kode_rup = b.kode_rup
          WHERE b.tanggal = '$tanggal' AND b.id_satker = '$id_satker' AND kelengkapan = '1') UNION
              (SELECT a.kode_rup, b.nama_paket, b.jenis_pengadaan FROM tb_tpd_konstruksi a LEFT JOIN tb_tpd b ON a.kode_rup = b.kode_rup
          WHERE b.tanggal = '$tanggal' AND b.id_satker = '$id_satker' AND kelengkapan = '1') UNION
              (SELECT a.kode_rup, b.nama_paket, b.jenis_pengadaan FROM tb_tpd_konsultansi a LEFT JOIN tb_tpd b ON a.kode_rup = b.kode_rup
          WHERE b.tanggal = '$tanggal' AND b.id_satker = '$id_satker' AND kelengkapan = '1')";
      return $this->db->query($str)->result();
    }
}
