<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monev_m extends CI_Model{

    function __construct(){

    }

    public function count($tb_name, $filter)
    {
        return $this->db->get_where($tb_name,$filter)->num_rows();
    }

    public function get_paketbatal()
    {
      $this->db->select('a.*,b.nama_paket,c.sp_kelompok,b.jenis_pengadaan');
      $this->db->join('tb_rup b','a.batal_paket = b.kode_rup','left');
      $this->db->join('tb_sp c','a.batal_sp = c.sp_id','left');
      return $this->db->get('tb_batal a')->result();
    }

    // menghitung persatker rup
    public function view_persatker_rup_total()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- tender cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Seleksi%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function view_persatker_rup()
    {
      $tahun = date('Y');

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
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
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
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
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
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
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
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
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
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.uk