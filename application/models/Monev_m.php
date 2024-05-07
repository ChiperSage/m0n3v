<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monev_m extends CI_Model{

    function __construct(){

    }

    public function get_paketreview()
    {
      $tahun = date('Y');

      $sql = "SELECT r.* FROM tb_review rv 
      LEFT JOIN tb_rup r ON rv.kode_rup = r.kode_rup
      WHERE left(rv.tgl_review,4) = $tahun AND rv.status = 1";
      return $this->db->query($sql)->result();
    }

    public function get_pengembalianreviu()
    {
      $sql = "SELECT pr.*, r.nama_paket FROM tb_pengembalianreviu pr
      LEFT JOIN tb_rup r ON pr.kode_rup = r.kode_rup";
      return $this->db->query($sql)->result();
    }

    public function insert_pengembalianreviu($file)
    {
      $data['kode_rup'] = $this->input->post('kode_rup');
      $data['nomor_surat'] = $this->input->post('nomor_surat');
      $data['tanggal_surat'] = $this->input->post('tgl_surat');
      $data['tanggal_pengembalian'] = date('Y-m-d');
      $data['nama_file'] = $file;
      $this->db->insert('tb_pengembalianreviu',$data);

      $key2 = array('kode_rup'=>$this->input->post('kode_rup'));
      $data2['tgl_review'] = date('Y-m-d');
      $this->db->update('tb_review',$data2,$key2);

      $key3 = array('kode_rup'=>$this->input->post('kode_rup'));
      $data3['tgl_review'] = date('Y-m-d');
      $data3['status'] = 'Dokumen Reviu Telah di Kembalikan Oleh SKPA';
      $this->db->update('tb_review_paket',$data3,$key3);
    }

    public function get_paket_review_pokja()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, c.sp_kelompok, a.tgl_serah_dokumen,
      (SELECT rp.tgl_review FROM tb_review_paket rp WHERE rp.kode_rup = a.kode_rup ORDER BY rp.tgl_review DESC LIMIT 1) as tgl_review,
      (SELECT rv.status FROM tb_review_paket rv WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as rv_status,
      (SELECT keterangan FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket_review,
      a.tgl_review, a.tgl_selesai, a.status, b.nama_kpa, IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen,
      (SELECT h.id FROM tb_review_paket h WHERE h.kode_rup = a.kode_rup LIMIT 1) as histori_reviu');

      $this->db->from('tb_review a');
      $this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
      $this->db->join('tb_sp c','a.id_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where(array('d.anggota_jabatan'=>'anggota','left(c.sp_tanggal,4)'=>$tahun));
      $this->db->order_by('a.tgl_review DESC');
      $this->db->group_by('a.kode_rup');
      $query = $this->db->get();
      return $query->result();
    }

    public function get_paket_review_pokja2()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      //$id = $this->session->userdata('user_id');
      //$nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, b.jenis_pengadaan, b.nama_kpa, a.tgl_serah_dokumen, a.tgl_review, a.tgl_selesai, a.status,  c.sp_kelompok,

          IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen, f.tgl_review, f.status as rv_status, f.keterangan as ket_review, f.id as histori_reviu

          FROM tb_review a 

          LEFT JOIN (SELECT kode_rup, nama_paket, nama_satker, jenis_pengadaan, nama_kpa FROM tb_rup) as b ON a.kode_rup = b.kode_rup
          LEFT JOIN (SELECT sp_id, sp_kelompok, sp_tanggal FROM tb_sp) as c ON a.id_sp = c.sp_id
          LEFT JOIN tb_sp_anggota d ON c.sp_id = d.anggota_sp
          LEFT JOIN (SELECT pokja_nip FROM tb_pokja) as e ON d.anggota_nip = e.pokja_nip
          LEFT JOIN (SELECT kode_rup, tgl_review, status, keterangan, id FROM tb_review_paket ORDER BY tgl_review DESC) as f ON a.kode_rup = f.kode_rup

          WHERE d.anggota_jabatan = 'anggota' AND left(c.sp_tanggal,4) = $tahun

          GROUP BY a.kode_rup 
          ORDER BY a.tgl_review DESC";

      $query = $this->db->query($str);
      return $query->result();
    }

    public function get_paket_review_histori()
    {
        $tahun = date('Y');
        if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
          $tahun = $_GET['tahun'];
        }

        $kode_rup = array();

        $sql = "SELECT rv.kode_rup FROM tb_review rv 
              LEFT JOIN tb_rup r ON rv.kode_rup = r.kode_rup 
              WHERE r.tahun = $tahun";
        $result = $this->db->query($sql)->result();

        foreach ($result as $val)
        {
          $sql1 = "SELECT id FROM tb_review_paket WHERE kode_rup = $val->kode_rup";
          $result1 = $this->db->query($sql1)->result_array();

          $kode_rup[$val->kode_rup] = $result1;
        }

        return $kode_rup;
    }


    public function realisasi_total_dok_hard()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode, a.singkatan, COUNT(t.kode_rup) as tpaket,

      -- paket dan pagu
      (SELECT COUNT(DISTINCT(t.kode_rup)) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as tpaket,
      (SELECT SUM(t.nilai_pagu) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as thps,

      -- jenis pengadaan
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND t.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as tkt,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND t.jenis_pengadaan LIKE '%jasa konsultansi%' AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as tks,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND t.jenis_pengadaan LIKE '%barang%' AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as tb,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE t.tpd_status = 8 AND t.jenis_pengadaan LIKE '%jasa lainnya%' AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)) as tj

      FROM tb_skpa a, tb_tpd t, tb_rup r
      WHERE a.kode = t.id_satker AND t.tpd_status = 8 AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun)";
      return $this->db->query($str)->result();
    }

    public function realisasi_lap_dok_hard()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode, a.singkatan,

      -- paket dan pagu
      (SELECT COUNT(DISTINCT(t.kode_rup)) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 AND r.tahun = $tahun) as tpaket,
      (SELECT SUM(t.nilai_pagu) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 AND r.tahun = $tahun) as thps,

      -- jenis pengadaan
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 
        AND t.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.tahun = $tahun) as tkt,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 
        AND t.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.tahun = $tahun) as tks,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 
        AND t.jenis_pengadaan LIKE '%barang%' AND r.tahun = $tahun) as tb,
      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_tpd t, tb_rup r WHERE a.kode = t.id_satker AND t.kode_rup = r.kode_rup AND t.tpd_status = 8 
        AND t.jenis_pengadaan LIKE '%jasa lainnya%' AND r.tahun = $tahun) as tj

      FROM tb_skpa a, tb_tpd t, tb_rup r
      WHERE a.kode = t.id_satker AND t.tpd_status = 8 AND (t.kode_rup = r.kode_rup AND r.tahun = $tahun) AND r.sumber_dana != 'APBN'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function count($tb_name, $filter)
    {
        return $this->db->get_where($tb_name,$filter)->num_rows();
    }

    public function tpd_get($var)
    {
      $date_start = date('Y-m-d');
      $date_end = date('Y-m-d');

      $filter = array('tpd_status'=>7);

      if(!empty($var)){
        $filter = array('jenis_pengadaan'=>$var,'tpd_status'=>7);
      }

      if(isset($var['rekap']) && $var['rekap'] = 1 && isset($_GET['date_start']) && isset($_GET['date_end']))
      {
        $date_start = $_GET['date_start'];
        $date_end = $_GET['date_end'];
      }

      $str = "SELECT a.*, b.first_name, b.last_name, r.metode_pemilihan, r.sumber_dana, l.tahun
        FROM tb_tpd a, users b, tb_rup r, tb_lelang l 
        WHERE a.kode_rup = r.kode_rup AND a.petugas_id = b.id AND r.kode_rup = l.kode_rup AND (a.tanggal_terima_dok >= '$date_start' AND a.tanggal_terima_dok <= '$date_end') AND a.tpd_status = 8 AND r.sumber_dana != 'APBN' GROUP BY a.kode_rup";

      $str2 = "SELECT a.*, b.first_name, b.last_name, r.metode_pemilihan, r.sumber_dana, l.tahun
        FROM tb_tpd a
        LEFT JOIN users b ON a.petugas_id = b.id 
        LEFT JOIN tb_rup r ON a.kode_rup = r.kode_rup
        LEFT JOIN tb_lelang l ON a.kode_rup = l.kode_rup
        WHERE (a.tanggal_terima_dok >= '$date_start' AND a.tanggal_terima_dok <= '$date_end') AND a.tpd_status = 8 GROUP BY a.kode_rup";

      return $this->db->query($str2)->result();
    }

    public function get_paketbatal()
    {
      $this->db->select('a.*,b.nama_paket,c.sp_kelompok,b.jenis_pengadaan');
      $this->db->join('tb_rup b','a.batal_paket = b.kode_rup','left');
      $this->db->join('tb_sp c','a.batal_sp = c.sp_id','left');
      return $this->db->get('tb_batal a')->result();
    }

    public function view_persatker_rup_total2()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender cepat

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung <= 200 juta

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Penunjukan Langsung 200 - 500 juta

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 200000000 OR r.pagu_rup <= 500000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 200000000 OR r.pagu_rup <= 500000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- Penunjukan Langsung 500 jt - 1 M

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 500000000 OR r.pagu_rup <= 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl3_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 500000000 OR r.pagu_rup <= 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl3_pagu,

      -- Penunjukan Langsung > 1 M

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl4_paket,


      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl4_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl5_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl5_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl6_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl6_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Swakelola

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%' OR r.sumber_dana LIKE '%APBN%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%' OR r.sumber_dana LIKE '%APBN%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function view_persatker_rup2()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- PAKET Tender

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Tender Cepat' OR r.metode_pemilihan = 'Seleksi')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Tender Cepat' OR r.metode_pemilihan = 'Seleksi')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender cepat

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung <= 200 juta

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Penunjukan Langsung < 200 - 500 juta

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 200000000 OR r.pagu_rup <= 500000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 200000000 OR r.pagu_rup <= 500000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- Penunjukan Langsung 500 jt - 1 M

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 500000000 OR r.pagu_rup <= 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl3_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 500000000 OR r.pagu_rup <= 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl3_pagu,

      -- Penunjukan Langsung > 1 M

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl4_paket,


      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan = 'Penunjukan Langsung' AND (r.pagu_rup > 1000000000))
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl4_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl5_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl5_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Swakelola

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu

      FROM tb_skpa a
      -- INNER JOIN tb_rup b ON b.id_satker = a.kode
      LEFT JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat' AND b.tahun = $tahun
      
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }


    // menghitung persatker rup
    public function view_persatker_rup_total()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- tender cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Dikecualikan

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_pagu,

      -- tipe swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
      WHERE r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
      WHERE r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function view_persatker_rup_total3()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- tender cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl1_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola != 'ya') as pl2_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Dikecualikan

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_pagu,

      -- tipe swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.tipe_swakelola = 1)
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.tipe_swakelola = 1)
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat' AND b.tahun = $tahun
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function view_persatker_rup()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tc_pagu,

      -- Seleksi > 100 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Seleksi')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah') AND r.pagu_rup >= 100000000
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as seleksi1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Seleksi')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah') AND r.pagu_rup >= 100000000
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as seleksi1_pagu,

      -- Seleksi < 100 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah') AND r.pagu_rup < 100000000
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah') AND r.pagu_rup < 100000000
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')) as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')) as ep_pagu,

      -- Dikecualikan
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')) as dk_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')) as dk_pagu,

      -- swakelola (tipe)
      (SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
      WHERE r.kd_satker = a.kode
      AND r.tahun_anggaran = $tahun AND r.status_umumkan_rup = 'Terumumkan') as tipe_sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
      WHERE r.kd_satker = a.kode
      AND r.tahun_anggaran = $tahun AND r.status_umumkan_rup = 'Terumumkan') as tipe_sw_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- penyedia ddlm swa jamak
       (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.penyedia_didalam_swakelola = 'ya' AND r.metode_pemilihan = 'Pembayaran untuk Kontrak Tahun Jamak') as penyedia_dlm_swa_jamak_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.penyedia_didalam_swakelola = 'ya' AND r.metode_pemilihan = 'Pembayaran untuk Kontrak Tahun Jamak') as penyedia_dlm_swa_jamak_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%Lainnya%' OR r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tt_pagu

      FROM tb_skpa a
      WHERE a.instansi != 'pusat' AND a.nama NOT LIKE '%biro%' 
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();

    }

    public function view_persatker_rup3()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Tender Cepat%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Tender Cepat%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Dikecualikan
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_pagu,

      -- swakelola (tipe)
      (SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function get_listpaket($getdata)
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      if($getdata == 'tender'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'Tender')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'tendercepat'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'Tender Cepat')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'seleksi'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'Seleksi')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'penunjukanlangsung'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'Penunjukan Langsung')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'pengadaanlangsung'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'Pengadaan Langsung')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'epurchasing'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'e-Purchasing')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'dikecualikan'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE (r.metode_pemilihan = 'dikecualikan')
        AND r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'tipeswakelola'){
        $str = "SELECT r.* FROM tb_rup_swakelola r
        WHERE r.tahun_anggaran = $tahun AND r.status_aktif_rup = 'ya' AND r.status_umumkan_rup = 'sudah'
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'penyediadidalamswakelola'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah') AND r.penyedia_didalam_swakelola = 'ya'
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }elseif($getdata == 'rup'){
        $str = "SELECT r.* FROM tb_rup r
        WHERE r.tahun = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
        AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')";
      }

      return $this->db->query($str)->result();
    }
    

    public function tender_per_skpa_total()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender Cepat' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender Cepat' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_pagu,

      -- Seleksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Seleksi' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Seleksi' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_pagu,

      -- Penunjukan Langsung (NON TENDER)

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Penunjukan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Penunjukan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Pengadaan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Pengadaan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'e-Purchasing' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'e-Purchasing' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'ya'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'ya'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%')
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%')
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'";
      return $this->db->query($str)->result();
    }

    public function view_persatker_rup3xx()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Tender Cepat%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Tender Cepat%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Seleksi%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- Dikecualikan
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dk_pagu,

      -- swakelola (tipe)
      (SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function tender_per_skpa_total3()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender Cepat' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender Cepat' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_pagu,

      -- Seleksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Seleksi' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Seleksi' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_pagu,

      -- Penunjukan Langsung (NON TENDER)

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Penunjukan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Penunjukan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Pengadaan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Pengadaan Langsung' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'e-Purchasing' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 ) )
      ) as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'e-Purchasing' AND l.tahun LIKE '%$tahun%'
      AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'ya'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND (r.status_umumkan = 'sudah')
      AND r.penyedia_didalam_swakelola = 'ya'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%')
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%')
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'";
      return $this->db->query($str)->result();
    }

    public function tender_per_skpa()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Tender'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Tender'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Tender Cepat'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Tender Cepat'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as tc_pagu,

      -- Seleksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung')
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung')
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND left(r.awal_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya'
      --
      AND ( (l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND ((l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_non_tender t ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah' AND r.sumber_dana != 'APBN'
      AND ((l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00')

      OR (t.status_lelang = 1)
      OR (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function get_total()
    {
      $tahun = date('Y');

      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT kode, COUNT(DISTINCT c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

      -- menghitung non tender jumlah paket dan pagu

      (SELECT count(t.kode_rup) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') ) ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') ) ) as tpagu_non_tender,

      -- menghitung paket masuk perhari

      -- FROM tb_skpa a
      -- LEFT JOIN tb_rup b ON a.kode = b.id_satker
      -- LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      -- WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND ((c.status_lelang != 0 AND c.status_lelang != 2 AND b.sumber_dana != 'APBN')
      -- AND c.paket_status != 0) OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN') OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')

      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_selisih,

      (SELECT SUM(t.pagu) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpagu_selisih,

      -- SP

    (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
    LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE left(s.sp_tanggal,4) = '$tahun'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

    (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
    LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE left(s.sp_tanggal,4) = '$tahun'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

    (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
    LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE left(s.sp_tanggal,4) = '$tahun'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

    (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
    LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
    LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE left(s.sp_tanggal,4) = '$tahun'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE left(s.sp_tanggal,4) = '$tahun'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- REVIEW

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = 5) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = 0) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = 1) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = 2) as review_selesai,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as review_total,

      -- (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_sp_paket pk
      -- LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      -- LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      -- LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      -- WHERE left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = 2) as review_selesai,

      -- SP Belum Tayang

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_bt_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_bt_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as tsp_bt_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_bt_j,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as tsp_bt,

      -- Total Belum Tayang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as sbt,

      -- Total Tayang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as st,

      -- Total Umum Pemenang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as sup,

      -- total batal sp

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as sp_batal,

      -- total batal lelang

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%') as pb

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE b.sumber_dana != 'APBN'
      AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )";

      return $this->db->query($str)->result();
    }

    public function get_laporan()
    {
      $tahun = date('Y');

      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT kode, singkatan,

      -- COUNT(c.kode_rup) as tpaket1, SUM(b.pagu_rup) as tpagu1,

      -- paket masuk
      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND l.tahun LIKE '%$tahun%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif'))) as tpaket,

      (SELECT SUM(r.pagu_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND l.tahun LIKE '%$tahun%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif'))) as tpagu,

      -- paket masuk non tender (pagu dan paket)
      (SELECT COUNT(t.kode_rup) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpagu_non_tender,

      -- menghitung selisih (lelang bck)
      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as tselisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tselisih_non_tender,

      -- menghitung REALISASI SP

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND l.status_aktif != 'non aktif'
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND l.status_aktif != 'non aktif'
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND l.status_aktif != 'non aktif'
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND l.status_aktif != 'non aktif'
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      -- review

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.id_satker = a.kode AND left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND v.status = 5) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.id_satker = a.kode AND left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND v.status = 0) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.id_satker = a.kode AND left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND v.status = 1) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.id_satker = a.kode AND left(v.tgl_review,4) = $tahun AND r.sumber_dana != 'APBN' AND v.status = 2) as review_selesai,


      -- sp belum tayang

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_bt_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_bt_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sp_bt_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_bt_j,

      -- belum tayang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- tayang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%Jasa Lainnya%') as t_j,

      -- SELISIH

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_selisih_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as t_selisih_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%') as t_selisih_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as t_selisih_j,

      -- menang

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif = 'aktif' AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- sp batal

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      -- batal

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE b.sumber_dana != 'APBN'
      AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )

      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();

    }

    // LELANG SPSE

    public function get_total_spse()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode,

      -- total paket dan pagu

      -- (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse ls
      -- WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND ls.ang_tahun = '$tahun' AND (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1 AND (ls.menang = 0 OR ls.menang = 5)) 
      -- AND ls.jenis_pengadaan IN (0,1,2,3,5) )) as tpaket,

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse ls
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND ls.ang_tahun = '$tahun' AND ((ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1 AND (ls.menang = 0 OR ls.menang = 5))) AND ls.jenis_pengadaan IN (0,1,2,3,5)) as tpaket,

      (SELECT SUM(ls.pagu) FROM tb_lelang_spse ls
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND ls.ang_tahun = '$tahun' AND ((ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5) OR (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1)) 
      AND ls.jenis_pengadaan IN (0,1,2,3,5) ) as tpagu,

      -- total SP

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      (SELECT SUM(r.pagu_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2) as tsp_pagu, 

      -- review 

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 5 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 0 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 1 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 2 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_selesai,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_total,

      -- total belum Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 2) as sbt_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sbt_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 0) as sbt_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 3) as sbt_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan IN (0,1,2,3,5) ) as sbt,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE (l.ukpbj = '1106' OR l.ukpbj = '3106.00') AND l.ang_tahun = $tahun AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan IN (0,1,2,3,5) ) as sbt_pagu,

      -- total Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 2) as st_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as st_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 0) as st_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 3) as st_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0) as st,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '3106') AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0) as st_pagu,

      -- total menang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup_pagu,

      -- total batal lelang

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as lelang_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as lelang_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as lelang_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as lelang_batal_j,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as lelang_batal,

      (SELECT SUM(l.pagu) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as lelang_batal_pagu,

      -- total batal sp

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%') as sp_batal,

      -- total lhp

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 2) as t_lhp_kt,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND (ls.jenis_pengadaan = 1 OR ls.jenis_pengadaan = 5)) as t_lhp_ks,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 0) as t_lhp_b,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 3) as t_lhp_j,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun) as t_lhp

      FROM tb_skpa a, tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id LIMIT 1";
      return $this->db->query($str)->result();
    }

    public function get_laporan_spse()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode, a.singkatan,

      -- paket dan pagu

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id AND ls.ang_tahun = '$tahun' AND (ls.ukpbj = '1106' OR ls.ukpbj = '3106')
      AND ((ls.status_lelang = 0) AND (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1)) 
      AND ls.jenis_pengadaan IN (0,1,2,3,5)) as tpaket,

      (SELECT SUM(ls.pagu) FROM tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id AND ls.ang_tahun = '$tahun' AND (ls.ukpbj = '1106' OR ls.ukpbj = '3106')
      AND ((ls.status_lelang = 0) AND (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1)) 
      AND ls.jenis_pengadaan IN (0,1,2,3,5)) as tpagu,

      -- SP

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      (SELECT SUM(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp,

      -- review

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 5 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 0 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 1 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 2 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_selesai,

      -- belum Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 2) as sbt_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sbt_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 0) as sbt_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 3) as sbt_j,

      -- Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 2) as st_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as st_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 0) as st_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 3) as st_j,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106'))) as st_pagu,

      -- menang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup_pagu,

      -- batal lelang

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      (SELECT SUM(r.pagu_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan, pagu_rup FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as sp_batal_pagu,

      -- batal sp

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      -- lhp

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 2) as lhp_kt,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND (ls.jenis_pengadaan = 1 OR ls.jenis_pengadaan = 5)) as lhp_ks,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 0) as lhp_b,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 3) as lhp_j,

      (SELECT SUM(ls.pagu) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode) as lhp_pagu

      FROM tb_skpa a, tb_lelang_spse ls
      WHERE  a.kode = ls.rup_stk_id AND ls.ang_tahun = '$tahun' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1))
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function get_laporan_spse_tpd()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode, a.singkatan,

      -- paket dan pagu
      (SELECT COUNT(DISTINCT(t.kode_rup)) 
      FROM tb_tpd t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND t.tpd_status = 8 AND r.tahun = $tahun) as tpaket,

      (SELECT SUM(r.pagu_rup) 
      FROM tb_tpd t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND t.tpd_status = 8 AND r.tahun = $tahun) as tpagu,

      -- SP

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      (SELECT SUM(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun, jenis_pengadaan FROM tb_rup) as r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND r.tahun = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp,

      -- review

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 5 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 0 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 1 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, tahun FROM tb_rup) as r ON v.kode_rup = r.kode_rup
      LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
      WHERE a.kode = r.id_satker AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND pk.paket_status = 2 AND v.status = 2 AND (v.catatan != 'batal sp' OR v.catatan != 'tarik dokumen') ) as review_selesai,

      -- belum Tayang

      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_tpd t ON ll.kode_rup = t.kode_rup
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 2) as sbt_kt,

      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_tpd t ON ll.kode_rup = t.kode_rup
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sbt_ks,

      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_tpd t ON ll.kode_rup = t.kode_rup
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 0) as sbt_b,

      (SELECT COUNT(DISTINCT t.kode_rup) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_tpd t ON ll.kode_rup = t.kode_rup
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 0 AND l.paket_status = 0 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 3) as sbt_j,

      -- Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 2) as st_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as st_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 0) as st_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.jenis_pengadaan = 3) as st_j,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106'))) as st_pagu,

      -- menang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND (l.jenis_pengadaan = 1 OR l.jenis_pengadaan = 5)) as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j,

      (SELECT SUM(l.pagu) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup_pagu,

      -- batal lelang

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      (SELECT SUM(r.pagu_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN (SELECT kode_rup, id_satker, sumber_dana, jenis_pengadaan, pagu_rup FROM tb_rup) as r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as sp_batal_pagu,

      -- batal sp

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      -- lhp

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 2) as lhp_kt,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND (ls.jenis_pengadaan = 1 OR ls.jenis_pengadaan = 5)) as lhp_ks,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 0) as lhp_b,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 3) as lhp_j,

      (SELECT SUM(ls.pagu) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode) as lhp_pagu

      FROM tb_skpa a, tb_lelang_spse ls
      WHERE  a.kode = ls.rup_stk_id AND ls.ang_tahun = '$tahun' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1))
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }


    // LELANG SPSE HARIAN

    public function get_total_spse_harian()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode,

      -- total paket pagu dan selisih

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse ls
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpaket,

      (SELECT SUM(ls.pagu) FROM tb_lelang_spse ls
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpagu,

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse_bck ls
      WHERE (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpaket_selisih,

      -- total SP

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      AND pk.paket_status = 2) as tsp,

      -- (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_sp_paket pk
      -- LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      -- LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND l.status_aktif != 'non aktif'
      -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      -- total belum Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 2) as sbt_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 1) as sbt_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 0) as sbt_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 3) as sbt_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0) as sbt,

      -- total belum Tayang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 2) as sbt_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 1) as sbt_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 0) as sbt_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 3) as sbt_j_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.status_lelang = 0 AND l.paket_status = 0) as sbt_selisih,

      -- total Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 2) as st_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 1) as st_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 0) as st_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 3) as st_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0) as st,

      -- total tayang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 2) as st_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 1) as st_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 0) as st_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 3) as st_j_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0) as st_selisih,

      -- total menang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 1) as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup,

      -- total menang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 1) as sup_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) as sup_selisih,

      -- total batal sp

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as sp_batal,

      -- total batal lelang

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%') as pb,

      -- total lhp

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 2) as t_lhp_kt,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 1) as t_lhp_ks,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 0) as t_lhp_b,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.jenis_pengadaan = 3) as t_lhp_j,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun) as t_lhp

      FROM tb_skpa a, tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id LIMIT 1";
      return $this->db->query($str)->result();
    }

    public function get_laporan_spse_harian()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT a.kode, a.singkatan,

      -- paket dan pagu

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1 AND ls.ukpbj IS NULL)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpaket,

      (SELECT SUM(ls.pagu) FROM tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1 AND ls.ukpbj IS NULL)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpagu,

      (SELECT COUNT(DISTINCT(ls.kode_lelang)) FROM tb_lelang_spse_bck ls
      WHERE a.kode = ls.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1 AND ls.ukpbj IS NULL)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0)
      OR (ls.status_lelang = 1 AND ls.paket_status = 1))) as tpaket_selisih,

      -- SP

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      -- AND (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      -- AND (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      -- AND (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE a.kode = r.id_satker AND r.sumber_dana != 'APBN' AND pk.nt != 'ya' AND left(s.sp_tanggal,4) = '$tahun'
      -- AND (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      -- belum Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 2) as sbt_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 1) as sbt_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 0) as sbt_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 3) as sbt_j,

      -- belum tayang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 2) as sbt_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 1) as sbt_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 0) as sbt_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.jenis_pengadaan = 3) as sbt_j_selisih,

      -- Tayang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 2) as st_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 1) as st_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 0) as st_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 3) as st_j,

      -- tayang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 2) as st_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 1) as st_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 0) as st_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.jenis_pengadaan = 3) as st_j_selisih,

      -- menang

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 1) as sup_ks,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j,

      -- menang selisih

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 2) as sup_kt_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 1) as sup_ks_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 0) as sup_b_selisih,

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_lelang_spse_bck l
      WHERE a.kode = l.rup_stk_id AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.jenis_pengadaan = 3) as sup_j_selisih,

      -- batal sp

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_batal_kt,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_batal_ks,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_batal_b,

      (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
      l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_batal_j,

      -- batal lelang

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE a.kode = r.id_satker AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      -- lhp

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 2) as lhp_kt,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 1) as lhp_ks,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 0) as lhp_b,

      (SELECT COUNT(DISTINCT lh.kode_lelang) FROM tb_lhp lh, tb_lelang_spse ls
      WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun AND ls.rup_stk_id = a.kode AND ls.jenis_pengadaan = 3) as lhp_j

      FROM tb_skpa a, tb_lelang_spse ls
      WHERE a.kode = ls.rup_stk_id AND (ls.ukpbj = '1106' OR ls.ukpbj = '1306') AND ls.ang_tahun LIKE '%$tahun%' AND ((ls.status_lelang = 1)
      OR (ls.status_lelang = 0 AND ls.paket_status = 0) OR (ls.status_lelang = 1 AND ls.paket_status = 1))
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function realisasi_data_tender_spse_selisih()
    {
      $sql = "SELECT ls.* FROM tb_lelang_spse ls WHERE ls.kode_lelang NOT IN
      (SELECT lsb.kode_lelang FROM tb_lelang_bck lsb)";
      return $this->db->query($sql)->result();
    }

    public function realisasi_data_tender_spse_ajax_reviu($param)
    {
        $vars = explode('-',$param);

        $kode = $vars[0];
        $status = $vars[1];
        $tahun = $vars[2];

        $sql = "SELECT v.kode_rup, r.nama_paket, 0 as hps, '' as rkn_nama, '' as rkn_npwp, s.sp_kelompok
        FROM tb_review v
        LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup      
        LEFT JOIN tb_sp_paket pk ON v.kode_rup = pk.paket_id
        LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
        WHERE r.id_satker = '$kode' AND r.tahun = $tahun AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND v.status = '$status' AND pk.nt != 'ya'";
        return $this->db->query($sql)->result();
    }

    public function realisasi_data_tender_spse_detail($param)
    {
      if(isset($vars[3])){
        $tahun = $vars[3];
      }else{
        $tahun = date('Y');
      }

  		$vars = explode('-',$param);

  		$kode = $vars[0];
  		$paket = $vars[1];
      $jenis_pengadaan = $vars[2];

      
  		if($jenis_pengadaan == 'b'){
  			$jenis_pengadaan = 0;
        $jenis_pengadaan2 = 0;
  			$jenis_pengadaan_sp = 'barang';
  		}elseif($jenis_pengadaan == 'ks'){
        $jenis_pengadaan = 1;
  			$jenis_pengadaan2 = 5;
  			$jenis_pengadaan_sp = 'jasa konsultansi';
  		}elseif($jenis_pengadaan == 'kt'){
  			$jenis_pengadaan = 2;
        $jenis_pengadaan2 = 2;
  			$jenis_pengadaan_sp = 'pekerjaan konstruksi';
  		}elseif($jenis_pengadaan == 'j'){
  			$jenis_pengadaan = 3;
        $jenis_pengadaan2 = 3;
  			$jenis_pengadaan_sp = 'jasa lainnya';
  		}

  		if($paket == 'sp'){
  			$str = "SELECT pk.paket_id as kode_lelang, r.nama_paket, r.pagu_rup as hps, s.sp_tanggal, '' as rkn_nama, '' as rkn_npwp
  			FROM tb_sp_paket pk, tb_sp s, tb_rup r
  			WHERE s.sp_id = pk.paket_sp AND r.id_satker = '$kode' AND pk.paket_id = r.kode_rup AND r.sumber_dana != 'APBN' AND pk.nt != 'ya'
  			AND (left(r.tahun,4) = '$tahun' OR left(r.tahun,4) = '$tahun')
  			AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%$jenis_pengadaan_sp%'";
  		}elseif($paket == 'bt'){
  			$str = "SELECT DISTINCT(l.kode_lelang), l.*, '' as sp_tanggal, '' as rkn_nama, '' as rkn_npwp
  				FROM tb_lelang_spse l
  				WHERE l.rup_stk_id = '$kode' AND l.jenis_pengadaan = '$jenis_pengadaan'
  				AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106'";
  		}elseif ($paket == 't') {
  			$str = "SELECT DISTINCT(l.kode_lelang), l.*, '' as sp_tanggal, '' as rkn_nama, '' as rkn_npwp
  				FROM tb_lelang_spse l
  				WHERE l.rup_stk_id = '$kode' AND (l.jenis_pengadaan = $jenis_pengadaan OR l.jenis_pengadaan = $jenis_pengadaan2)
  				AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0";
  		}elseif ($paket == 'm') {
  			$str = "SELECT DISTINCT(l.kode_lelang), l.*, '' as sp_tanggal, p.rkn_nama, p.rkn_npwp
  				FROM tb_lelang_spse l
  				LEFT JOIN tb_pemenang p ON l.kode_lelang = p.kode_lelang
  				WHERE l.rup_stk_id = '$kode' AND l.jenis_pengadaan = '$jenis_pengadaan'
  				AND l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5";
  		}elseif ($paket == 'batal_lelang') {
          $str = "SELECT DISTINCT(l.kode_lelang), l.nama_paket, l.hps, l.pagu FROM tb_sp_paket pk
          LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
          LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
          WHERE r.id_satker = '$kode' AND ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1
          AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
          AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%$jenis_pengadaan_sp%' ORDER BY l.kode_lelang ASC";
      }elseif ($paket == 'batal_sp') {
          $str = "SELECT DISTINCT(l.kode_lelang), l.nama_paket, l.hps, l.pagu FROM tb_batal b
          INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
          LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
          WHERE r.id_satker = '$kode' AND l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%$jenis_pengadaan_sp%'";
      }

  		return $this->db->query($str)->result();
    }

    public function get_daftar_paket_spse($var)
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      if($var == 'belum_tayang'){

        $str = "SELECT * FROM

        (SELECT a.singkatan as singkatan, a.nama as skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, ls.pagu as pagu, ls.hps as hps, ls.sbd_id as sumber_dana, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, s.sp_kelompok

        FROM tb_lelang_spse ls

        LEFT JOIN tb_lelang l ON ls.kode_lelang = l.kode_lelang
        LEFT JOIN tb_skpa a ON ls.rup_stk_id = a.kode 
        LEFT JOIN tb_kategori kg ON ls.jenis_pengadaan = kg.kgr_id
        LEFT JOIN tb_metode mt ON ls.mtd_pemilihan = mt.mtd_id

        LEFT JOIN tb_sp_paket sp ON l.kode_rup = sp.kode_rup
        LEFT JOIN tb_sp s ON sp.sp_id = s.sp_id

        WHERE ls.ang_tahun = '$tahun' AND ls.status_lelang = 0 AND ls.paket_status = 0 AND (ls.ukpbj = '1106' OR ls.ukpbj = '3106') 

        GROUP BY ls.kode_lelang)

        as tbtemp ORDER BY singkatan, kode_lelang ASC";

      }elseif($var == 'tayang'){

        // $str = "SELECT singkatan, kode_lelang, nama_paket, nama_skpa, pagu, hps, sbd_id, pokja, jenis_pengadaan, metode_pemilihan FROM

        // (SELECT a.singkatan as singkatan, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, a.nama as nama_skpa, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, ls.sbd_id, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, 

        // 'oooo' as akhir_masa_sanggah1

        // -- IF(mt.mtd_id != 9, (SELECT js.tgl_selesai FROM tb_jadwal_spse js WHERE js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = ls.kode_lelang ORDER BY js.tgl_selesai DESC LIMIT 1), '0000') as akhir_masa_sanggah1

        // FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_kategori kg, tb_metode mt
        // WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun = '$tahun'
        // AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 0 
        // -- AND ((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND ls.kode_lelang = js.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'PENGUMUMAN_LELANG' AND ls.kode_lelang = js.kode_lelang))
        // GROUP BY ls.kode_lelang)

        // as tbtemp ORDER BY singkatan, kode_lelang ASC";

        $str = "SELECT
            a.singkatan AS singkatan,
            ls.kode_lelang AS kode_lelang,
            ls.nama_paket AS nama_paket,
            a.nama AS nama_skpa,
            pt.pnt_nama AS pokja,
            ls.pagu AS pagu,
            ls.hps AS hps,
            ls.sbd_id,
            kg.kgr_nama AS jenis_pengadaan,
            mt.mtd_nama AS metode_pemilihan,
            'oooo' AS akhir_masa_sanggah1
        FROM
            tb_lelang_spse ls
        LEFT JOIN tb_skpa a ON a.kode = ls.rup_stk_id
        LEFT JOIN tb_panitia pt ON ls.pnt_id = pt.pnt_id
        LEFT JOIN tb_kategori kg ON ls.jenis_pengadaan = kg.kgr_id
        LEFT JOIN tb_metode mt ON ls.mtd_pemilihan = mt.mtd_id
        WHERE
            ls.ang_tahun = $tahun
            AND ls.status_lelang = 1
            AND ls.paket_status = 1
            AND ls.menang = 0
        GROUP BY
            ls.kode_lelang
        ORDER BY
            singkatan,
            kode_lelang ASC";

      }elseif($var == 'menang'){

        $str = "SELECT a.singkatan as singkatan, ls.sanggah, a.nama as nama_skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, kg.kgr_nama as jenis_pengadaan,
        mt.mtd_nama as metode_pemilihan, js.tgl_selesai as akhir_masa_sanggah1, 
        (SELECT js2.tgl_selesai FROM tb_jadwal_spse js2 WHERE js2.kode_lelang = ls.kode_lelang AND js2.tahapan = 'SANGGAH') as akhir_masa_sanggah2
        , ls.sbd_id
        FROM tb_skpa a, tb_lelang_spse ls, tb_pemenang pm, tb_kategori kg, tb_metode mt, tb_jadwal_spse js, tb_panitia pt
        WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id
        AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun = '$tahun' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5 AND
        ( (mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang) 
          OR (mt.mtd_id != 9 AND js.tahapan = 'PENETAPAN_PEMENANG_AKHIR' AND js.kode_lelang = pm.kode_lelang) )

        GROUP BY ls.kode_lelang ORDER BY ls.pagu DESC";

      }elseif($var == 'evaluasi'){

        $triwulan = isset($_GET['triwulan']) ? $_GET['triwulan'] : '' ;

        $date1 = $tahun . '-01-01';
        $date2 = $tahun . '-03-01';

        $str = "SELECT a.nama as nama_skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga,
        js.tgl_selesai as pelaksanaan, ls.sbd_id
        FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt, tb_jadwal_spse js
        WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id
        AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun LIKE '%$tahun%' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5 AND 
        ((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'TANDATANGAN_KONTRAK' AND js.kode_lelang = pm.kode_lelang))

        GROUP BY ls.kode_lelang";

      }elseif($var == 'menang2'){

        $str = "SELECT a.singkatan as singkatan, a.nama as nama_skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, kg.kgr_nama as jenis_pengadaan,
        mt.mtd_nama as metode_pemilihan, js.tgl_mulai as penetapan_pemenang_mulai, js.tgl_selesai as penetapan_pemenang_selesai, ls.sbd_id as sumber_dana

        FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt, tb_jadwal_spse js

        WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id AND ls.mtd_pemilihan = mt.mtd_id
        AND ls.ang_tahun = '$tahun' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5
        AND ((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'PENETAPAN_PEMENANG_AKHIR' AND js.kode_lelang = pm.kode_lelang))

        GROUP BY ls.kode_lelang";

      }elseif($var == 'menang_apbd'){

        $str = "SELECT a.singkatan as singkatan, a.nama as nama_skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, kg.kgr_nama as jenis_pengadaan,
        mt.mtd_nama as metode_pemilihan, js.tgl_selesai as akhir_masa_sanggah1
        FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt, tb_jadwal_spse js
        WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id AND ls.sbd_id = 'APBD'
        AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun LIKE '%$tahun%' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5 AND
        ((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'SANGGAH' AND js.kode_lelang = pm.kode_lelang))

        GROUP BY ls.kode_lelang";

      }elseif($var == 'tayang2'){

        $str = "SELECT ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, ls.pagu as pagu, ls.hps as hps, k.kgr_nama as jenis_pengadaan, js.tgl_mulai, js.tgl_selesai
        FROM tb_lelang_spse ls, tb_metode mt, tb_jadwal_spse js, tb_kategori k
        WHERE ls.ang_tahun = '$tahun' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 0 
        AND ((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND ls.kode_lelang = js.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'PENGUMUMAN_LELANG' AND ls.kode_lelang = js.kode_lelang)) AND ls.jenis_pengadaan = k.kgr_id
        GROUP BY ls.kode_lelang";

      }

      return $this->db->query($str)->result();
    }

    public function get_distribusi_paket_tender_tt()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT s.sp_id,

          (SELECT COUNT(DISTINCT(pk.paket_id))
          FROM tb_sp s, tb_sp_paket pk, tb_rup r
          WHERE
          s.tahun = $tahun
          -- (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
          AND pk.paket_status = 2 AND s.sp_id = pk.paket_sp AND pk.paket_sp = s.sp_id
          AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as total_kt,

          (SELECT COUNT(DISTINCT(pk.paket_id))
          FROM tb_sp s, tb_sp_paket pk, tb_rup r
          WHERE
          s.tahun = $tahun
          -- (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
          AND pk.paket_status = 2 AND s.sp_id = pk.paket_sp AND pk.paket_sp = s.sp_id
          AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as total_ks,

          (SELECT COUNT(DISTINCT(pk.paket_id))
          FROM tb_sp s, tb_sp_paket pk, tb_rup r
          WHERE
          s.tahun = $tahun
          -- (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
          AND pk.paket_status = 2 AND s.sp_id = pk.paket_sp AND pk.paket_sp = s.sp_id
          AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%barang%') as total_b,

          (SELECT COUNT(DISTINCT(pk.paket_id))
          FROM tb_sp s, tb_sp_paket pk, tb_rup r
          WHERE
          s.tahun = $tahun
          -- (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
          AND pk.paket_status = 2 AND s.sp_id = pk.paket_sp AND pk.paket_sp = s.sp_id
          AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%jasa lainnya%') as total_j,

          (SELECT COUNT(DISTINCT(pk.paket_id))
          FROM tb_sp s, tb_sp_paket pk, tb_rup r
          WHERE
          s.tahun = $tahun
          -- (left(r.awal_pekerjaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
          AND pk.paket_status = 2 AND s.sp_id = pk.paket_sp AND pk.paket_sp = s.sp_id
          AND pk.paket_id = r.kode_rup) as total

          FROM tb_sp s LIMIT 1";

      return $this->db->query($str)->result();
    }


    public function get_distribusi_paket_tender()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT * FROM
        (SELECT s.sp_id as id, s.sp_kelompok as nama, '' as nip, '' as nama_pokja,

        -- total
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as total_kt,
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as total_ks,
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%barang%') as total_b,
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup AND r.jenis_pengadaan LIKE '%jasa lainnya%') as total_j,
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup) as total_paket,
        (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk, tb_rup r
        WHERE pk.paket_sp = s.sp_id AND pk.paket_status = 2 AND pk.paket_id = r.kode_rup AND pk.nt = 'ya') as total_nt

        FROM tb_sp s, tb_sp_anggota sa, tb_pokja pj
        WHERE
        s.tahun = $tahun
        -- AND s.sp_id = pk.paket_sp AND pk.paket_id = r.kode_rup
        AND s.sp_id = sa.sp_id AND sa.anggota_nip = pj.pokja_nip GROUP BY s.sp_id

        UNION

        SELECT s.sp_id as id, pj.pokja_nama as nama, sa.anggota_nip as nip, pj.pokja_nama as nama_pokja,
        '' as total_kt, '' as total_ks, '' as total_b, '' as total_j, '' as total_paket, '' as total_nt
        FROM tb_sp s, tb_sp_anggota sa, tb_pokja pj
        WHERE
        s.tahun = $tahun
        -- AND s.sp_id = pk.paket_sp AND pk.paket_id = r.kode_rup
        AND s.sp_id = sa.sp_id AND sa.anggota_nip = pj.pokja_nip)
        as tbtemp ORDER by id, nama_pokja ASC";

      return $this->db->query($str)->result();
    }

    public function get_info_status_paket_spse()
    {
      $tahun = date('Y');
      $now = date('Y-m-d H:i:s');

      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT l.kode_lelang, l.nama_paket, l.hps, l.pagu, pn.pnt_nama, s.nama as nama_satker,

      (SELECT j.tahapan FROM tb_jadwal_spse j where j.kode_lelang = l.kode_lelang AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as status_tender,

      (SELECT j.tgl_mulai FROM tb_jadwal_spse j where j.kode_lelang = l.kode_lelang AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as tgl_mulai,
      (SELECT j.tgl_selesai FROM tb_jadwal_spse j where j.kode_lelang = l.kode_lelang AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as tgl_selesai,
      (SELECT j.keterangan FROM tb_jadwal_spse j where j.kode_lelang = l.kode_lelang AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESc LIMIT 1) as keterangan

      FROM tb_lelang_spse l, tb_panitia pn, tb_skpa s
      WHERE l.ang_tahun = '$tahun' AND l.pnt_id = pn.pnt_id AND l.rup_stk_id = s.kode
      AND ((l.status_lelang = 0 AND l.paket_status = 0 AND (l.ukpbj = '1106' OR l.ukpbj = '3106') AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.status_aktif != 'non aktif'))";

      return $this->db->query($str)->result();
    }

    public function get_info_jadwal_paket_spse()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT l.kode_lelang, l.nama_paket, l.hps, l.pagu, pn.pnt_nama, s.nama as nama_satker, m.mtd_nama, k.kgr_nama, l.menang,
      
      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PENJELASAN'), (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_LELANG' OR j.tahapan = 'UMUM_PRAKUALIFIKASI'))) as tgl_mulai_tayang,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PENJELASAN'), (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_LELANG' OR j.tahapan = 'UMUM_PRAKUALIFIKASI'))) as tgl_selesai_tayang,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'EVALUASI_PENAWARAN_KUALIFIKASI' OR j.tahapan = 'PEMBUKAAN_DAN_EVALUASI_PENAWARAN_ADM_TEKNIS') )) as tgl_mulai_evaluasi,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'EVALUASI_PENAWARAN_KUALIFIKASI' OR j.tahapan = 'PEMBUKAAN_DAN_EVALUASI_PENAWARAN_ADM_TEKNIS') )) as tgl_selesai_evaluasi,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_PEMENANG_AKHIR' OR j.tahapan = 'PENETAPAN_DAN_PENGUMUMAN_PEMENANG_AKHIR') )) as tgl_mulai_pemenang,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_PEMENANG_AKHIR' OR j.tahapan = 'PENETAPAN_DAN_PENGUMUMAN_PEMENANG_AKHIR') )) as tgl_selesai_pemenang,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'SANGGAH' OR j.tahapan = 'SANGGAH_ADM_TEKNIS') )) as tgl_mulai_sanggah,

      IF(l.mtd_pemilihan = 9, (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'SANGGAH' OR j.tahapan = 'SANGGAH_ADM_TEKNIS') )) as tgl_selesai_sanggah

      FROM tb_lelang_spse l, tb_panitia pn, tb_skpa s, tb_metode m, tb_kategori k

      WHERE l.ang_tahun = $tahun AND l.mtd_pemilihan = m.mtd_id AND l.jenis_pengadaan = k.kgr_id
      AND l.pnt_id = pn.pnt_id AND l.rup_stk_id = s.kode AND (l.status_lelang = 1 AND l.paket_status = 1 AND (l.ukpbj = '1106' OR l.ukpbj = '3106')) AND l.status_aktif != 'non aktif'
      ";

      return $this->db->query($str)->result();
    }

    public function get_info_status_jadwal_spse_barang()
    {
      $tahun = date('Y');
      $now = date('Y-m-d H:i:s');

      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT l.kode_lelang, l.nama_paket, l.hps, l.pagu, pn.pnt_nama, s.nama as nama_satker,
      
      (SELECT j.tgl_mulai, j.tgl_selesai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_LELANG' OR j.tahapan = 'EVALUASI_PENAWARAN_KUALIFIKASI' OR j.tahapan = 'PENGUMUMAN_PEMENANG_AKHIR' OR j.tahapan = 'SANGGAH')) as tahapan 

      FROM tb_lelang_spse l, tb_panitia pn, tb_skpa s
      WHERE l.ang_tahun = '2022' AND l.pnt_id = pn.pnt_id AND l.rup_stk_id = s.kode
      AND (l.status_lelang = 1 AND l.paket_status = 1 AND (l.ukpbj = '1106' OR l.ukpbj = '3106') AND l.status_aktif != 'non aktif')";

      return $this->db->query($str)->result();
    }

    // end spse

    public function get_total_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT kode,

      (SELECT count(DISTINCT t.kode_lelang) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.anggaran LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.anggaran LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) )
      ) as tpagu_non_tender,

      -- menghitung paket masuk perhari

      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_selisih,

      (SELECT SUM(t.pagu) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpagu_selisih,

      -- SP

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND (pk.paket_status = 2) AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE t.tahun LIKE '%$tahun%'
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- menghitung TOTAL REVIEW

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.sumber_dana != 'APBN') as review_total,

      -- Total Belum Tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(DISTINCT t.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      ) as sbt,

      -- Total Tayang
      
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(DISTINCT t.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN') as st,

      -- Total Umum Pemenang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif' AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(DISTINCT t.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as sup,

      -- TOTAL BATAL

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as pb_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as pb_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%barang%' AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as pb_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%' AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as pb_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif') as spb

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_non_tender c ON b.kode_rup = c.kode_rup
      WHERE c.anggaran LIKE '%$tahun%' AND b.sumber_dana != 'APBN'
      AND ( (c.status_lelang = 1)
      OR (c.status_lelang = 0 AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 1 AND c.paket_status = 1) )";

      return $this->db->query($str)->result();
    }

    public function get_laporan_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT kode, singkatan,

      -- paket masuk non tender (pagu dan paket)
      (SELECT count(t.kode_lelang) FROM tb_non_tender_complete t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif') OR (t.status_lelang = 1) )
      ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender_complete t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif') OR (t.status_lelang = 1) )
      ) as tpagu_non_tender,

      -- menghitung selisih (lelang bck)
      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      AND ( (t.status_lelang = 1 AND t.status_lelang = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as tselisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
     ) as tselisih_non_tender,

      -- menghitung REALISASI SP
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender_complete t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender_complete t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender_complete t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender_complete t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE t.tahun LIKE '%$tahun%'
      -- ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender_complete t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      -- belum tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%Jasa Lainnya%') as t_j,

      -- SELISIH
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_selisih_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as t_selisih_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%') as t_selisih_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as t_selisih_j,

      -- menang
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode
      AND t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND t.status_aktif != 'non aktif'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- batal
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";

      return $this->db->query($str)->result();
    }

    public function rekap_jenis_pengadaan1_temp()
    {
      $year1 = date('Y') - 1;
      $year2 = date('Y');

      $tgl1 = date('Y-01');

      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        $year1 = $tahun - 1;
        $year2 = $tahun;
        // $tgl1 = $tahun.'-01';

        if($triwulan == 1 && $tahun != 0){
          $tgl1 = $tahun.'-01';
        }

      }

      $str = "SELECT rr.bulan,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpagu_j,

      -- pekerjaan_konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.bulan,7) ))) as tpagu_total

      FROM tb_bulan rr WHERE left(rr.bulan,7) = '$tgl1'
      GROUP BY left(rr.bulan,7)
      ORDER BY rr.bulan ASC";
      return $this->db->query($str)->result();
    }

    public function rekap_jenis_pengadaan2_temp()
    {
      $year = date('Y');

      if(isset($_GET['tahun'])){
        $year = $_GET['tahun'];
      }

      $tgl1 = date('Y-02-').'01';
      $tgl2 = date('Y-m-t',strtotime(date('Y-m').'-01'));

      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        if($triwulan == 1 && $tahun != 0){
          $tgl1 = $tahun.'-02-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'03-01'));
        }elseif($triwulan == 2 && $tahun != 0){
          $tgl1 = $tahun.date('-').'04-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'06-01'));
        }elseif($triwulan == 3 && $tahun != 0){
          $tgl1 = $tahun.date('-').'07-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'09-01'));
        }elseif($triwulan == 4 && $tahun != 0){
          $tgl1 = $tahun.date('-').'10-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'12-01'));
        }

      }

      $str = "SELECT rr.akhir_pekerjaan,

      -- hitung paket dan pagu jasa lainnya
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7))) as tpagu_j,

      -- pekerjaan konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7)) as tpagu_total

      FROM tb_rup rr
      WHERE (rr.akhir_pekerjaan BETWEEN '$tgl1' AND '$tgl2')
      GROUP BY left(rr.akhir_pekerjaan,7)
      ORDER BY rr.akhir_pekerjaan ASC";
      return $this->db->query($str)->result();
    }

    public function rekap_jenis_pengadaan1()
    {
      $year1 = date('Y') - 1;
      $year2 = date('Y');

      $tgl1 = date('Y-01');

      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        $year1 = $tahun - 1;
        $year2 = $tahun;
        // $tgl1 = $tahun.'-01';

        if($triwulan == 1 && $tahun != 0){
          $tgl1 = $tahun.'-01';
        }

      }

      // rr.awal_pekerjaan

      $str = "SELECT rr.awal_pekerjaan,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_j,

      -- pekerjaan_konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_total

      FROM tb_bulan rr WHERE
      -- left(rr.awal_pekerjaan,7) = '$tgl1'
      (rr.awal_pekerjaan BETWEEN '$tgl1' AND '$tgl2')
      -- GROUP BY left(rr.awal_pekerjaan,7)
      ORDER BY rr.awal_pekerjaan ASC";
      return $this->db->query($str)->result();
    }

    public function rekap_jenis_pengadaan2()
    {
      $year = date('Y');

      if(isset($_GET['tahun'])){
        $year = $_GET['tahun'];
      }

      $tgl1 = date('Y-02-').'01';
      $tgl2 = date('Y-m-t',strtotime(date('Y-m').'-01'));

      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        if($triwulan == 1 && $tahun != 0){
          $tgl1 = $tahun.'-02-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'03-01'));
        }elseif($triwulan == 2 && $tahun != 0){
          $tgl1 = $tahun.date('-').'04-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'06-01'));
        }elseif($triwulan == 3 && $tahun != 0){
          $tgl1 = $tahun.date('-').'07-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'09-01'));
        }elseif($triwulan == 4 && $tahun != 0){
          $tgl1 = $tahun.date('-').'10-01';
          $tgl2 = $tahun.date('-m-t',strtotime(date('Y-').'12-01'));
        }

      }

      $str = "SELECT rr.awal_pekerjaan,

      -- hitung paket dan pagu jasa lainnya
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7))) as tpagu_j,

      -- pekerjaan konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE ( (l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') )
      AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND j.tahapan = 'TANDATANGAN_KONTRAK' AND left(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7)) as tpagu_total

      FROM tb_bulan rr
      -- FROM tb_rup rr
      WHERE (rr.akhir_pekerjaan BETWEEN '$tgl1' AND '$tgl2')
      -- GROUP BY left(rr.akhir_pekerjaan,7)
      ORDER BY rr.akhir_pekerjaan ASC";
      return $this->db->query($str)->result();
    }

    public function view_jenis_pengadaan_total()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT COUNT(DISTINCT kode_rup) as tpaket, SUM(pagu_rup) as tpagu,

      -- menghitung REALISASI JENIS PENGADAAN (TOTAL) PADA RUP (paket < 200jt, > 200jt, dst)

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE (left(awal_pekerjaan,4) = $tahun)
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpaket2,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE (left(awal_pekerjaan,4) = $tahun)
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpagu2,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE (left(awal_pekerjaan,4) = $tahun)
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpaket3,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpagu3,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpaket4,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpagu4,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpaket5,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpagu5,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND penyedia_didalam_swakelola = 'ya') as tpaket6,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(awal_pekerjaan,4) = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND penyedia_didalam_swakelola = 'ya') as tpagu6

      FROM tb_rup
      WHERE (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND status_aktif = 'ya'
      AND status_umumkan = 'sudah' AND (left(awal_pekerjaan,4) = $tahun)";

      return $this->db->query($str)->row();
    }

    public function laporan_bps_spse_1()
    {
      $year1 = date('Y') - 1;
      $year2 = date('Y');

      $tgl1 = date('Y-01');

      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        $year1 = $tahun - 1;
        $year2 = $tahun;
        // $tgl1 = $tahun.'-01';

        if($triwulan == 1 && $tahun != 0){
          $tgl1 = $tahun.'-01';
        }

      }

      $str = "SELECT rr.awal_pekerjaan,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_j,

      -- pekerjaan_konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.awal_pekerjaan,7) ))) as tpagu_total

      FROM tb_rup rr WHERE left(rr.awal_pekerjaan,7) = '$tgl1'
      GROUP BY left(rr.awal_pekerjaan,7)
      ORDER BY rr.awal_pekerjaan ASC";

      return $this->db->query($str)->result();
    }

    public function laporan_bps_spse_2()
    {
      // tahun dinamis
      $year = date('Y');
      if(isset($_GET['tahun'])){
        $year = $_GET['tahun'];
      }

      // set default
      $tgl1 = $year.'-01-01';
      $a_date = $year."-03-01";
      $date = new DateTime($a_date);
      $date->modify('last day of this month');
      $tgl2 = $date->format('Y-m-d');

      // pilihan tahun dan triwulan
      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        if($triwulan == 1 && $tahun != 0){

          $tgl1 = $tahun.'-01-01';
          $a_date = $tahun."-03-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 2 && $tahun != 0){

          $tgl1 = $tahun.'-04-01';
          $a_date = $tahun."-06-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 3 && $tahun != 0){

          $tgl1 = $tahun.'-07-01';
          $a_date = $tahun."-09-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 4 && $tahun != 0){

          $tgl1 = $tahun.'-10-01';
          $a_date = $tahun."-12-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }

      }

      $str_ok = "SELECT temp.tgl_mulai,

  	  (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 0) as b_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 0) as b_total_pagu,

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND (lss.jenis_pengadaan = 1 OR lss.jenis_pengadaan = 5) ) as ks_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND (lss.jenis_pengadaan = 1 OR lss.jenis_pengadaan = 5) ) as ks_total_pagu,

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 2) as kt_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 2) as kt_total_pagu,

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 3) as j_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.jenis_pengadaan = 3) as j_total_pagu,

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))) as total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE (lss.kode_lelang = jss.kode_lelang AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(temp.tgl_mulai,7))
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))) as total_pagu

      from tb_temp_bps temp
      where temp.tgl_mulai BETWEEN '$tgl1' AND '$a_date'
      order by temp.tgl_mulai ASC";

      return $this->db->query($str_ok)->result();
    }

    public function view_jenis_pengadaan($var)
    {
      // $tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');
      $tahun = strval(date('Y'));
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT COUNT(DISTINCT kode_rup) as tpaket, SUM(pagu_rup) as tpagu,

      -- menghitung REALISASI JENIS PENGADAAN PADA RUP (paket < 200jt, > 200jt, dst)
      (SELECT COUNT(kode_rup) FROM tb_rup WHERE tahun = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%') as tpaket2,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE tahun = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%') as tpagu2,

      --
      (SELECT COUNT(kode_rup) FROM tb_rup WHERE tahun = $tahun
      AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpaket3,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpagu3,

      --
      (SELECT COUNT(kode_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpaket4,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpagu4,

      --
      (SELECT COUNT(kode_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpaket5,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD$') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpagu5,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND jenis_pengadaan LIKE '%$var%' AND penyedia_didalam_swakelola = 'ya') as tpaket6,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND jenis_pengadaan LIKE '%$var%' AND penyedia_didalam_swakelola = 'ya') as tpagu6

      FROM tb_rup
      WHERE (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND jenis_pengadaan LIKE '%$var%'
      AND status_aktif = 'ya' AND status_umumkan = 'sudah' AND tahun = $tahun";

      return $this->db->query($str)->row();
    }

    public function get_daftar_paket_sp_bt()
    {
      $tahun = date('Y');
      $jns = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != ''){
        $jns = $_GET['jenis_pengadaan'];
      }

      $str = "SELECT l.kode_lelang, r.kode_rup, r.nama_paket as nama_pekerjaan, r.pagu_rup as pagu,
      r.jenis_pengadaan as jenis_pengadaan, '' as id_satker, l.keterangan as keterangan, '' as status, '' as ket,
      (SELECT sp.sp_kelompok FROM tb_sp sp WHERE sp.sp_id = pk.paket_sp) as kelompok
      FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND r.sumber_dana != 'APBN' AND l.tahun = '$tahun'
      AND r.jenis_pengadaan LIKE '%$jns%'";
      return $this->db->query($str)->result();
    }

    public function get_daftar_paket($var)
    {
      $tahun  = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $where  = "";
      $jns    = "";
      $jenis  = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != '')
      {
        $jns = $_GET['jenis_pengadaan'];
      }

      if($var == 'masuk'){ // masuk

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
        OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
        OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
        OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
        OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )
        GROUP BY b.kode_rup) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'status_paket'){ // paket sanggah



      }elseif($var == 'belum_di_sp'){ // belum tayang telah sp

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND b.kode_rup NOT IN (SELECT pk.paket_id FROM tb_sp_paket pk WHERE pk.paket_status = 2)
        AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
        OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
        OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND b.kode_rup NOT IN (SELECT pk.paket_id FROM tb_sp_paket pk WHERE pk.paket_status = 2)
        AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
        OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
        OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )
        GROUP BY b.kode_rup) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'belum_tayang_telah_sp'){ // belum tayang telah sp

        // SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
        // LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
        // LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
        // WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
        // AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        LEFT JOIN tb_sp_paket d ON c.kode_rup = d.paket_id
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun LIKE '%$tahun%' AND d.paket_status = 2 AND c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif' AND c.ukpbj = '1106.00'
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, d.paket_id as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        LEFT JOIN tb_sp_paket d ON c.kode_rup = d.paket_id
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun LIKE '%$tahun%' AND d.paket_status = 2 AND c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif' AND c.ukpbj = '1106.00'
        GROUP BY d.paket_id) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'belum_tayang'){ // belum tayang

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif' AND c.ukpbj = '1106.00'))
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif' AND c.ukpbj = '1106.00'))
        GROUP BY b.kode_rup) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'tayang'){ // tayang

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.paket_status = 1 AND c.menang = 0 AND c.status_aktif = 'aktif'))
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.paket_status = 1 AND c.menang = 0 AND c.status_aktif = 'aktif'))
        GROUP BY b.kode_rup) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'umum_pemenang'){ // umum pemenang

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan, '' as nama_satker,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.paket_status = 1 AND c.menang = 5 AND c.status_aktif = 'aktif'))
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan, a.nama as nama_satker,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.paket_status = 1 AND c.menang = 5 AND c.status_aktif = 'aktif'))
        GROUP BY b.kode_rup) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'batal_sp'){ // batal lelang

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        INNER JOIN tb_batal d ON c.kode_rup = d.batal_paket
        WHERE c.tahun LIKE '%$tahun%' AND b.jenis_pengadaan LIKE '%$jns%'
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_rup b
        -- LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        INNER JOIN tb_batal d ON c.kode_rup = d.batal_paket
        WHERE c.tahun LIKE '%$tahun%' AND b.jenis_pengadaan LIKE '%$jns%'
        GROUP BY d.batal_paket) AS tb_join
        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'batal_lelang'){ // batal sp

        // SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
        // LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
        // WHERE ((l.paket_status != 1 AND l.status_lelang != 0 AND l.status_aktif != 'non aktif') OR (l.paket_status = 1 AND l.status_lelang = 2 AND l.status_aktif != 'non aktif')) AND
        // (left(r.awal_pengadaan,4) = '2019' OR left(r.akhir_pekerjaan,4) = '2019') AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
        // AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'

        $list_paket = "SELECT * FROM

        (SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.nama_satker as nama_satker, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        LEFT JOIN tb_sp_paket d ON c.kode_rup = d.paket_id
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.paket_status != 1 AND c.status_lelang != 0 AND c.status_aktif != 'non aktif') OR (c.paket_status = 1 AND c.status_lelang = 2 AND c.status_aktif != 'non aktif'))
        AND c.tahun LIKE '%$tahun%'
        GROUP BY c.kode_lelang) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

      }elseif($var == 'tender_ulang'){ // tender ulang

        $list_paket = "SELECT * FROM

        (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
        a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        LEFT JOIN tb_sp_paket d ON c.kode_rup = d.paket_id
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.paket_status != 1 AND c.status_lelang != 0 AND c.status_aktif != 'non aktif') OR (c.paket_status = 1 AND c.status_lelang = 2 AND c.status_aktif != 'non aktif'))
        AND c.tahun LIKE '%$tahun%' AND d.paket_status = 2
        GROUP BY a.kode

        UNION

        SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan,
        b.id_satker as id_satker, c.keterangan as keterangan,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
        (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup LIMIT 1) as kelompok
        FROM tb_skpa a
        LEFT JOIN tb_rup b ON a.kode = b.id_satker
        LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
        LEFT JOIN tb_sp_paket d ON c.kode_rup = d.paket_id
        WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.paket_status != 1 AND c.status_lelang != 0 AND c.status_aktif != 'non aktif') OR (c.paket_status = 1 AND c.status_lelang = 2 AND c.status_aktif != 'non aktif'))
        AND c.tahun LIKE '%$tahun%' AND d.paket_status = 2
        GROUP BY d.paket_id) AS tb_join

        ORDER BY id_satker, jenis_pengadaan ASC";

        // $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        // AND (c.tahun = $tahun AND c.kode_rup IN (SELECT kode_rup FROM tb_lelang WHERE status_lelang = 2))";

      }

      return $this->db->query($list_paket)->result();
    }

    public function get_daftar_paket_status_paket()
    {
      $tahun = date('Y');
      $now = date('Y-m-d H:i:s');

      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT l.*, r.sumber_dana, pk.paket_status as pk_paket_status, v.status as status_review, l.paket_status, s.sp_kelompok,
      (SELECT j.tahapan FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as status_tender,
      (SELECT j.tgl_mulai FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as tgl_mulai,
      (SELECT j.tgl_selesai FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESc LIMIT 1) as tgl_selesai,
      (SELECT j.keterangan FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESc LIMIT 1) as keterangan
      from tb_lelang l
      left join tb_rup r ON l.kode_rup = r.kode_rup
      inner join tb_sp_paket pk ON l.kode_rup = pk.paket_id
      left join tb_sp s ON pk.paket_sp = s.sp_id
      left join tb_review v ON l.kode_rup = v.kode_rup
      where r.sumber_dana != 'APBN'
      AND l.tahun LIKE '%$tahun%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif') )
      -- where j.tgl_mulai <= NOW() AND j.tgl_selesai >= NOW() AND l.status_aktif != 'non aktif'
      group by l.kode_rup";
      return $this->db->query($str)->result();
    }

    public function get_daftar_paket_status_paket_batal()
    {
      $tahun = date('Y');
      $now = date('Y-m-d H:i:s');

      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT l.*, r.sumber_dana, pk.paket_status as pk_paket_status, v.status as status_review, l.paket_status, s.sp_kelompok,
      (SELECT j.tahapan FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as status_tender,
      (SELECT j.tgl_mulai FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESC LIMIT 1) as tgl_mulai,
      (SELECT j.tgl_selesai FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESc LIMIT 1) as tgl_selesai,
      (SELECT j.keterangan FROM tb_jadwal j where j.kode_rup = l.kode_rup AND ((j.tgl_mulai < '$now' AND j.tgl_selesai >= '$now') OR j.tgl_mulai < '$now') order by j.tgl_mulai DESc LIMIT 1) as keterangan
      from tb_lelang l
      left join tb_rup r ON l.kode_rup = r.kode_rup
      inner join tb_batal b ON l.kode_rup = b.batal_paket
      left join tb_sp_paket pk ON l.kode_rup = pk.paket_id
      left join tb_sp s ON pk.paket_sp = s.sp_id
      left join tb_review v ON l.kode_rup = v.kode_rup
      where r.sumber_dana != 'APBN'
      AND l.tahun LIKE '%$tahun%'
      -- where j.tgl_mulai <= NOW() AND j.tgl_selesai >= NOW() AND l.status_aktif != 'non aktif'
      group by l.kode_rup";
      return $this->db->query($str)->result();
    }

    public function get_daftar_paket_batal()
    {
      $tahun  = date('Y');
      $where  = "";
      $jns    = "";
      $jenis  = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != '')
      {
        $jns = $_GET['jenis_pengadaan'];
        $jns = str_replace('_', ' ', $jns);
      }

      // mengambil daftar paket batal
      $str = "SELECT * FROM
      (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan, a.kode as id_satker, '' as keterangan, '' as kelompok
      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_batal bt On b.kode_rup = bt.batal_paket
      WHERE b.jenis_pengadaan LIKE '%$jns%' AND b.kode_rup IN (SELECT batal_paket FROM tb_batal)
      GROUP BY a.kode
      UNION
      SELECT '' as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan, b.id_satker as id_satker, bt.batal_keterangan as keterangan,
      (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup) as kelompok
      FROM tb_rup b
      LEFT JOIN tb_batal bt On b.kode_rup = bt.batal_paket
      WHERE b.jenis_pengadaan LIKE '%$jns%' AND b.kode_rup IN (SELECT batal_paket FROM tb_batal)
      GROUP BY b.kode_rup)

      AS tb_join
      ORDER BY id_satker, jenis_pengadaan ASC";

      return $this->db->query($str)->result();
    }

    public function get_paket_review()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      // $id = $this->session->userdata('user_id');
      // $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      // $this->db->select('a.kode_rup, r.nama_paket, r.nama_satker, s.sp_kelompok,
      // a.tgl_review, a.tgl_selesai, r.nama_kpa');
      // $this->db->from('tb_review a');
      // $this->db->join('tb_rup r','a.kode_rup = r.kode_rup','left');
      // $this->db->join('tb_sp s','a.id_sp = s.sp_id','left');
      // $this->db->group_by('a.kode_rup');
      // $this->db->order_by('r.nama_satker ASC');
  		// $query = $this->db->get();
  		// return $query->result();

      $str = "SELECT r.kode_rup, r.nama_paket, r.nama_satker, s.sp_kelompok,
      v.tgl_review, v.tgl_selesai, r.nama_kpa FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      LEFT JOIN tb_review v ON pk.paket_id = v.kode_rup
      LEFT JOIN tb_sp s ON pk.paket_sp = s.sp_id
      WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 GROUP BY v.kode_rup ORDER BY r.nama_satker ASC";

      return $this->db->query($str)->result();
    }

    public function get_history()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      // $id = $this->session->userdata('user_id');
      // $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT * FROM
      (SELECT v.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, r.nama_kpa as nama_kpa, s.sp_kelompok as kelompok, '' as tgl_review, '' as status, '' as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun') AND v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      GROUP BY v.kode_rup
      UNION
      SELECT h.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, '' as nama_kpa, '' as kelompok, h.tgl_review as tgl_review, h.status as status, h.keterangan as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun') AND v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp)
      as tb_join ORDER BY kode_rup DESC";

      return $this->db->query($str)->result();
    }

    public function get_data_review($var)
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      if($var == 'belum'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 5 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'pokja'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 0 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'skpa'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 1 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'selesai'){
        $str = "SELECT DISTINCT(r.kode_rup), r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok, t.status as reviu_status,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 2 AND r.sumber_dana != 'APBN' AND left(t.tgl_review,4) = $tahun
        ORDER BY sp.sp_kelompok ASC";
      }
      return $this->db->query($str)->result();
    }

    public function get_detail_paket($param)
    {
      $urls = explode('-',$param);

  		$id_satker = $urls[0]; // id_satker
  		$jenis = $urls[1]; // belum_tayang, tayang, umum_pemenang
  		$jenis_pengadaan = str_replace('_',' ',$urls[2]); // jenis pengadaan

      $tahun = date('Y');

      if($jenis == 'sp_belum_tayang'){

        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(j.kode_rup) FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup) as tjadwal,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE r.id_satker = $id_satker AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'
        AND l.tahun LIKE '%$tahun%' AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.paket_status = 0
        AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' group by l.kode_rup";

      }elseif($jenis == 'belum_tayang'){

        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(j.kode_rup) FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup) as tjadwal,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE r.id_satker = $id_satker AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'
        AND l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0
        AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' group by l.kode_rup";

      }elseif($jenis == 'tayang'){

        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(j.kode_rup) FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup) as tjadwal,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE r.id_satker = $id_satker AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'
        AND l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0
        AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN' GROUP BY l.kode_rup";

      }elseif($jenis == 'menang'){

        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(j.kode_rup) FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup) as tjadwal,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE r.id_satker = $id_satker AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'
        AND l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5
        AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN'
        GROUP by l.kode_rup";

      }

      return $this->db->query($str)->result();
    }

    public function ambil_selisih()
    {
      $tahun = date('Y');
      $today = date('Y-m-d');

      // ambil data selisih belum tayang
      $sql = "INSERT INTO tb_selisih_spse (kode_lelang, keterangan, tanggal)

      SELECT l.kode_lelang, 'belum tayang' as keterangan, NOW() as tanggal FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106' AND l.kode_lelang NOT IN
      (SELECT lb.kode_lelang FROM tb_lelang_spse_bck lb
      WHERE lb.ang_tahun LIKE '%$tahun%' AND lb.status_lelang = 0 AND lb.paket_status = 0 AND lb.ukpbj = '1106')

      ON DUPLICATE KEY UPDATE keterangan='belum tayang', tanggal=NOW()";
      $this->db->query($sql);

      // tayang
      $sql = "INSERT INTO tb_selisih_spse (kode_lelang, keterangan, tanggal)

      SELECT l.kode_lelang, 'tayang' as keterangan, NOW() as tanggal FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.kode_lelang NOT IN
      (SELECT lb.kode_lelang FROM tb_lelang_spse_bck lb
      WHERE lb.ang_tahun LIKE '%$tahun%' AND lb.status_lelang = 1 AND lb.paket_status = 1 AND lb.menang = 0)

      ON DUPLICATE KEY UPDATE keterangan='tayang', tanggal=NOW()";
      $this->db->query($sql);

      // menang
      $sql = "INSERT INTO tb_selisih_spse (kode_lelang, keterangan, tanggal)

      SELECT l.kode_lelang, 'menang' as keterangan, NOW() as tanggal FROM tb_lelang_spse l
      WHERE l.ang_tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.kode_lelang NOT IN
      (SELECT lb.kode_lelang FROM tb_lelang_spse_bck lb
      WHERE lb.ang_tahun LIKE '%$tahun%' AND lb.status_lelang = 1 AND lb.paket_status = 1 AND lb.menang = 5)

      ON DUPLICATE KEY UPDATE keterangan='menang', tanggal=NOW()";
      $this->db->query($sql);

      // tampilkan data
      $str = "SELECT ss.kode_lelang, ls.nama_paket, ss.keterangan, ss.tanggal
      FROM tb_selisih_spse ss, tb_lelang_spse ls WHERE ss.kode_lelang = ls.kode_lelang AND ss.tanggal='$today'";
      return $this->db->query($str)->result();
    }

    public function get_karo($tanggal_sp)
    {
      $str = "SELECT * FROM users WHERE tanggal_awal != '0000-00-00' AND tanggal_akhir = '0000-00-00'
      AND tanggal_awal <= '$tanggal_sp' ORDER BY id DESC LIMIT 1";
      return $this->db->query($str)->row();
    }

    public function get_sumberdaya()
    {
      $str = "SELECT * FROM tb_sumber_daya";
      return $this->db->query($str)->result();
    }
}
