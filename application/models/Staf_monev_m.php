<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staf_monev_m extends CI_Model{

    function __construct(){

    }

    public function get_rup($key)
    {
      // nilai default
  		$limit = 20;
  		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
  			$tahun = $_GET['tahun'];
  		}else{
  			$tahun = date('Y');
  		}

  		$page = (isset($key['page'])) ? $key['page'] : 0 ;
  		$str = array();

  		// parameter pencarian
  		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
  			$tahun = $key['tahun'];
  			$str = "tahun = '$tahun'";
  		}
  		if(isset($key['search']) && $key['search'] != ''){
  			$search = $key['search'];
  			$str = "(kode_rup = '$search' OR nama_paket LIKE '%$search%')";
  		}
  		if(isset($key['search']) && $key['search'] != '' && isset($_GET['tahun']) && $_GET['tahun'] != ''){
  			$tahun = $_GET['tahun'];
  			$search = $key['search'];
  			$str = "tahun = '$tahun' AND (kode_rup = '$search' OR nama_paket LIKE '%$search%')";
  		}


  		$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup,awal_pengadaan,sumber_dana,status_umumkan,status_aktif,metode_pemilihan,nama_kpa,tahun,penyedia_didalam_swakelola');
  		$this->db->from('tb_rup');
  		$this->db->limit($limit, $page);
  		$this->db->where($str);
  		$query = $this->db->get();
  		$data['result'] = $query->result();

  		// tampil data untuk excel
  		if(isset($_GET['action']) && $_GET['action'] == 'print')
  		{
  			$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup,awal_pengadaan,sumber_dana,status_umumkan,status_aktif,metode_pemilihan,nama_kpa,tahun,penyedia_didalam_swakelola');
  			$this->db->from('tb_rup');
  			$this->db->where($str);
  			$query = $this->db->get();
  			$data['result'] = $query->result();
  		}

  		$this->db->select('kode_rup');
  		$this->db->from('tb_rup');
  		$this->db->where($str);
  		$query = $this->db->get();
  		$data['count'] = $query->num_rows();
  		return $data;
    }

    public function get_tpd_list($config)
    {

      // ambil tpd status 9
      if(isset($config['pengembalian_dok']) && $config['pengembalian_dok'] != '')
      {
        $array = explode('_',$_GET['kode_rup']);
        $this->db->where_in('kode_rup',$array);
        $filter['tpd_status'] = 9;
      }

      return $this->db->get('tb_tpd')->result();
    }

    public function get_skpa_list()
    {
      $this->db->order_by('nama ASC');
      return $this->db->get_where('tb_skpa',array())->result();
    }

    public function get_dokumen()
    {
      $id_satker = 0;
      if(isset($_GET['skpa']) && $_GET['skpa'] != ''){
        $id_satker = $_GET['skpa'];
        $str = "SELECT * FROM tb_tpd WHERE tpd_status = 8 AND id_satker = $id_satker";
      }else{
        $str = "SELECT * FROM tb_tpd WHERE tpd_status = 8";
      }

      return $this->db->query($str)->result();
    }

    public function get_paket_review()
    {
      $tahun = date('Y');
      $sql_idsatker = '';
      
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      if(isset($_GET['skpa']) && $_GET['skpa'] != ''){
        $id_satker = $_GET['skpa'];
        $sql_idsatker = " AND b.id_satker = '$id_satker'";
      }

      $is_barangjasa = $this->ion_auth->in_group('barang_jasa');
      $is_konstruksi = $this->ion_auth->in_group('konstruksi');

      if($is_barangjasa == 1){
        $filter = "b.tahun = $tahun AND (b.jenis_pengadaan LIKE '%barang%' OR b.jenis_pengadaan LIKE '%jasa lainnya%' AND a.catatan != 'batal sp')" . $sql_idsatker;
      }
      if($is_konstruksi == 1){
        $filter = "b.tahun = $tahun AND (b.jenis_pengadaan LIKE '%pekerjaan konstruksi%' OR b.jenis_pengadaan LIKE '%jasa konsultansi%') AND a.catatan != 'batal sp'" . $sql_idsatker;
      }

      $this->db->select('a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, c.sp_kelompok, a.tgl_serah_dokumen, b.pagu_rup,
      (SELECT tgl_review FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as tgl_review,
      (SELECT rv.status FROM tb_review_paket rv WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as rv_status,
      (SELECT keterangan FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket_review,
      (SELECT GROUP_CONCAT(hi.id) FROM tb_review_paket hi WHERE hi.kode_rup = a.kode_rup) as histori_id,
      a.tgl_review, a.tgl_selesai, a.status, b.nama_kpa, IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen');

      $this->db->from('tb_review a');
      $this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
      $this->db->join('tb_sp c','a.id_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where($filter);
      $this->db->order_by('a.status ASC');
      $this->db->group_by('a.kode_rup');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function serah_dokumen($kode_rup)
    {
      $key = array('kode_rup'=>$kode_rup);
      $data['tgl_serah_dokumen'] = date('Y-m-d');
      $data['status'] = 0;
      $this->db->update('tb_review',$data,$key);
    }
}
