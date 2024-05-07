l.<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
      WHERE (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- tender cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- swakelola

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
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
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
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

      -- PAKET Tender
      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      --
      AND ( (l.status_lelang = 1 AND l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- swakelola

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function tender_per_skpa()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND (l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN')
      AND ( (l.status_lelang = 1 AND l.status_lelang = 1 AND l.ukpbj is null AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') ) ) as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

      -- Tender Cepat
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender Cepat')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tc_pagu,

      -- Penunjukan Langsung > 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup > 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl_pagu,

      -- Penunjukan Langsung <= 200 juta
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Penunjukan Langsung' AND r.pagu_rup <= 200000000)
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl1_pagu,

      -- Pengadaan Langsung
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pl2_pagu,

      -- e-Purchasing
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'e-Purchasing'
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as ep_pagu,

      -- swakelola
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as tt_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      WHERE a.instansi != 'pusat'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function get_total()
    {
      $tahun = date('Y');

      $str = "SELECT kode, COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

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

      -- Menghitung SP TOTAL

      -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- menghitung TOTAL semua skpa REVIEW

      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE ( left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun' )
      -- AND v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup WHERE r.sumber_dana != 'APBN') as review_total,

      -- SP Belum Tayang

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_bt_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_bt_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as tsp_bt_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_bt_j,

      (SELECT COUNT(DISTINCT(pk.paket_id)) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE pk.paket_status = 2 AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as tsp_bt,

      -- Total Belum Tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as sbt,
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as sbt,

      -- Total Tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN') as st,

      -- Total Umum Pemenang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN') as sup,

      -- total batal

      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(DISTINCT b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%') as spb

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN')
      AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN' AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')";

      return $this->db->query($str)->result();
    }

    public function get_laporan()
    {
      $tahun = date('Y');

      $str = "SELECT kode, singkatan, COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

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

      -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      WHERE r.id_satker = a.kode AND (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- Menghitung review

      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE ( left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun' ) AND r.id_satker = a.kode
      -- AND v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE ( left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun' ) AND r.id_satker = a.kode
      -- AND v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE ( left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun' ) AND r.id_satker = a.kode
      -- AND v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE ( left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun' ) AND r.id_satker = a.kode
      -- AND v.status = 2 AND r.sumber_dana != 'APBN') as review_selesai,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      -- sp belum tayang

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00'
      AND r.sumber_dana != 'APBN' AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_bt_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00'
      AND r.sumber_dana != 'APBN' AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_bt_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00'
      AND r.sumber_dana != 'APBN' AND r.jenis_pengadaan LIKE '%barang%') as sp_bt_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.id_satker = a.kode AND pk.paket_status = 2 AND l.status_lelang = 0 AND l.ukpbj = '1106.00'
      AND r.sumber_dana != 'APBN' AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_bt_j,

      -- belum tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.status_aktif = '' AND l.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- tayang
      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
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
      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- batal
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN')
      AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";

      return $this->db->query($str)->result();
    }

    public function get_total_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT kode,

      -- menghitung non tender jumlah paket dan pagu
      (SELECT count(t.kode_rup) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') ) ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') ) ) as tpagu_non_tender,

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

      -- Menghitung SP TOTAL
     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,
     -- AND ((l.status_lelang = 0 AND l.ukpbj = '1106.00') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.ukpbj = '1106.00') OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.paket_status = 1))) as tsp_kt,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,
     -- AND ((l.status_lelang = 0 AND l.ukpbj = '1106.00') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.ukpbj = '1106.00') OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.paket_status = 1))) as tsp_ks,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,
     -- AND ((l.status_lelang = 0 AND l.ukpbj = '1106.00') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.ukpbj = '1106.00') OR (t.status_lelang = 1 AND t.ukpbj = '1106.00') OR (l.status_lelang = 1 AND l.paket_status = 1))) as tsp_b,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND (pk.paket_status = 2) AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE t.tahun LIKE '%$tahun%'
     -- ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- menghitung TOTAL REVIEW

      -- (SELECT Count(t.kode_rup)
      -- FROM tb_review t, tb_rup r
      -- WHERE t.kode_rup = r.kode_rup AND t.status = 5 AND r.sumber_dana != 'APBN') as review_belum,
      --
      -- (SELECT Count(t.kode_rup)
  	  -- FROM tb_review t, tb_rup r
  	  -- WHERE t.kode_rup = r.kode_rup AND t.status = 0 AND r.sumber_dana != 'APBN' ) as review_pokja,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      -- WHERE v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,
      --
      -- (SELECT COUNT(v.kode_rup) FROM tb_review v
	    -- LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup WHERE r.sumber_dana != 'APBN') as review_total,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      LEFT JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.sumber_dana != 'APBN') as review_total,


      -- Total Belum Tayang
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as sbt,

      -- Total Tayang
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as st,

      -- Total Umum Pemenang
      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN') as sup,

      -- TOTAL BATAL

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      INNER JOIN tb_batal l ON t.kode_rup = l.batal_paket) as spb

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_non_tender c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')";

      return $this->db->query($str)->result();
    }

    public function get_laporan_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT kode, singkatan,

      -- paket masuk lelang (pagu dan paket)
      -- COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

      -- paket masuk non tender (pagu dan paket)
      (SELECT count(t.kode_rup) FROM tb_non_tender t
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
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE t.tahun LIKE '%$tahun%'
      -- ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- Menghitung review
      -- (SELECT Count(t.kode_rup)
      -- FROM tb_review t, tb_rup r
      -- WHERE r.id_satker = a.kode AND t.kode_rup = r.kode_rup AND t.status = 5 AND r.sumber_dana != 'APBN' ) as review_belum,
      --
      -- (SELECT Count(t.kode_rup)
  	  -- FROM tb_review t, tb_rup r
  	  -- WHERE r.id_satker = a.kode AND t.kode_rup = r.kode_rup AND t.status = 0 AND r.sumber_dana != 'APBN' ) as review_pokja,
      --
	    -- (SELECT Count(t.kode_rup)
	    -- FROM tb_review t, tb_rup r
	    -- WHERE r.id_satker = a.kode AND t.kode_rup = r.kode_rup AND t.status = 1 AND r.sumber_dana != 'APBN' ) as review_skpa,
      --
	    -- (SELECT Count(t.kode_rup)
	    -- FROM tb_review t, tb_rup r
      -- WHERE r.id_satker = a.kode AND t.kode_rup = r.kode_rup AND t.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      -- belum tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00')
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00')
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
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5)
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5)
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5)
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- batal

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker

      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";

      return $this->db->query($str)->result();
    }

    // public function get_subtotal()
    // {
    //   $str = "SELECT singkatan, COUNT(c.kode_rup) as tkt
    //   FROM tb_skpa a
    //   LEFT JOIN tb_rup b ON a.kode = b.id_satker
    //   LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
    //   WHERE (c.status_lelang = 0 AND c.paket_status = 0)
    //   GROUP BY a.kode";
    //
    //   return $this->db->query($str)->result();
    // }

    public function view_jenis_pengadaan_total()
    {
      // $tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');
      $tahun = date('Y');

      $str = "SELECT COUNT(kode_rup) as tpaket, SUM(pagu_rup) as tpagu,

      -- menghitung REALISASI JENIS PENGADAAN (TOTAL) PADA RUP (paket < 200jt, > 200jt, dst)

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpaket2,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpagu2,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpaket3,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpagu3,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpaket4,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpagu4,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpaket5,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpagu5,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND penyedia_didalam_swakelola = 'ya') as tpaket6,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND penyedia_didalam_swakelola = 'ya') as tpagu6

      FROM tb_rup
      WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND pagu_rup < 100000000";

      return $this->db->query($str)->row();
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

      $str = "SELECT rr.akhir_pekerjaan,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpaket_j,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpagu_j,

      -- pekerjaan_konstruksi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpaket_kt,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpagu_kt,

      -- barang
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpaket_b,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpagu_b,

      -- jasa konsultansi
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpaket_ks,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%' AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpagu_ks,

      -- total
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpaket_total,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      LEFT JOIN tb_jadwal j ON r.kode_rup = j.kode_rup
      WHERE r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND ((l.status_lelang = 1) OR (t.status_lelang = 1 AND t.ukpbj = '1106.00'))
      AND ((l.tahun = '$year2' OR t.tahun = '$year2') AND (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,4) = '$year1')
      OR (j.tahapan = 'TANDATANGAN_KONTRAK' AND LEFT(j.tgl_mulai,7) = left(rr.akhir_pekerjaan,7) ))) as tpagu_total

      FROM tb_rup rr WHERE left(rr.akhir_pekerjaan,7) = '$tgl1'
      GROUP BY left(rr.akhir_pekerjaan,7)
      ORDER BY rr.akhir_pekerjaan ASC";
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

    public function view_jenis_pengadaan($var)
    {
      // $tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');
      $tahun = date('Y');

      $str = "SELECT COUNT(kode_rup) as tpaket, SUM(pagu_rup) as tpagu,

      -- menghitung REALISASI JENIS PENGADAAN PADA RUP (paket < 200jt, > 200jt, dst)

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpaket2,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 100000000 AND pagu_rup <= 200000000) as tpagu2,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpaket3,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 200000000 AND pagu_rup <= 2500000000) as tpagu3,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpaket4,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup > 2500000000 AND pagu_rup <= 50000000000) as tpagu4,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpaket5,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD$') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND (pagu_rup > 50000000000 AND pagu_rup <= 100000000000)) as tpagu5,

      --

      (SELECT COUNT(kode_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND jenis_pengadaan LIKE '%$var%' AND penyedia_didalam_swakelola = 'ya') as tpaket6,

      (SELECT SUM(pagu_rup) FROM tb_rup WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%')
      AND jenis_pengadaan LIKE '%$var%' AND penyedia_didalam_swakelola = 'ya') as tpagu6

      FROM tb_rup
      WHERE left(akhir_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana LIKE '%APBD%' OR sumber_dana LIKE '%BLUD%') AND penyedia_didalam_swakelola = 'tidak'
      AND jenis_pengadaan LIKE '%$var%' AND pagu_rup < 100000000";

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
      $tahun = date('Y');
      $where = "";
      $jns = "";
      $jenis = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != '')
      {
        // $var1 = $_GET['jenis_pengadaan'];
        $jns = $_GET['jenis_pengadaan'];
        // $jns = strtolower(str_replace('_',' ',$var1));
        // $jns = 'pekerjaan konstruksi';
      }

      if($var == 'masuk'){ // MASUK
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.ukpbj IS NULL AND b.sumber_dana != 'APBN')
        OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif' AND b.sumber_dana != 'APBN') OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN'))";
      }elseif($var == 'belum_tayang'){ // BELUM TAYANG
        $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 0 AND c.menang = 0 AND c.status_aktif = '' AND c.ukpbj = '1106.00'))";
      }elseif($var == 'tayang'){ // TAYANG
        $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.menang = 0 AND c.status_aktif = 'aktif' AND c.ukpbj = '1106.00'))";
      }elseif($var == 'umum_pemenang'){ // UMUM PEMENANG
        $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND ((c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.menang = 5 AND c.status_aktif = 'aktif'))";
      }elseif($var == 'batal'){ // BATAL
        $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun = $tahun AND b.kode_rup IN (SELECT batal_paket FROM tb_batal)";
      }elseif($var == 'tender_ulang'){ //TENDER ULANG
        $where = "WHERE b.sumber_dana != 'APBN' AND b.jenis_pengadaan LIKE '%$jns%'
        AND (c.tahun = $tahun AND c.kode_rup IN (SELECT kode_rup FROM tb_lelang WHERE status_lelang = 2))";
      }

      $str = "SELECT * FROM
      (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan,
      a.kode as id_satker, '' as keterangan, '' as status, '' as ket, '' as kelompok
      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      -- LEFT JOIN tb_non_tender d ON b.kode_rup = d.kode_rup
      $where
      GROUP BY a.kode
      UNION
      SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu,
      b.jenis_pengadaan as jenis_pengadaan, b.id_satker as id_satker, c.keterangan as keterangan,
      (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
      (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = b.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket,
      (SELECT s.sp_kelompok FROM tb_sp s, tb_sp_paket pk WHERE s.sp_id = pk.paket_sp AND pk.paket_id = b.kode_rup) as kelompok
      FROM tb_rup b
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      $where
      )
      AS tb_join
      ORDER BY id_satker, jenis_pengadaan ASC";

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
      // $id = $this->session->userdata('user_id');
      // $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.kode_rup, r.nama_paket, r.nama_satker, s.sp_kelompok,
      a.tgl_review, a.tgl_selesai, r.nama_kpa');
      $this->db->from('tb_review a');
      $this->db->join('tb_rup r','a.kode_rup = r.kode_rup','left');
      $this->db->join('tb_sp s','a.id_sp = s.sp_id','left');
      $this->db->group_by('a.kode_rup');
      $this->db->order_by('r.nama_satker ASC');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function get_history()
    {
      // $id = $this->session->userdata('user_id');
      // $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT * FROM
      (SELECT v.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, r.nama_kpa as nama_kpa, s.sp_kelompok as kelompok, '' as tgl_review, '' as status, '' as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      GROUP BY v.kode_rup
      UNION
      SELECT h.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, '' as nama_kpa, '' as kelompok, h.tgl_review as tgl_review, h.status as status, h.keterangan as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp)
      as tb_join ORDER BY kode_rup DESC";

      return $this->db->query($str)->result();
    }

    public function get_data_review($var)
    {
      if($var == 'belum'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 5 AND r.sumber_dana != 'APBN'
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'pokja'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 0 AND r.sumber_dana != 'APBN'
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'skpa'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 1 AND r.sumber_dana != 'APBN'
        ORDER BY sp.sp_kelompok ASC";
      }elseif($var == 'selesai'){
        $str = "SELECT r.kode_rup, s.singkatan, r.nama_paket, r.nama_satker, sp.sp_kelompok,
        (SELECT h.status FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as status,
        (SELECT h.keterangan FROM tb_review_paket h WHERE h.kode_rup = r.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket
        FROM tb_review t, tb_rup r, tb_skpa s, tb_sp sp, tb_sp_paket pk
        WHERE t.kode_rup = r.kode_rup AND r.id_satker = s.kode AND r.kode_rup = pk.paket_id AND pk.paket_sp = sp.sp_id AND t.status = 2 AND r.sumber_dana != 'APBN'
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
        WHERE r.id_satker = $id_satker
        AND pk.paket_status = 2 AND l.status_lelang = 0 AND r.sumber_dana != 'APBN'
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";

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
        WHERE r.id_satker = $id_satker
        AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";

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
        WHERE r.id_satker = $id_satker AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";

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
        WHERE r.id_satker = $id_satker AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";

      }

      return $this->db->query($str)->result();
    }
}
