<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Android_m extends CI_Model{

    function __construct(){

    }

    public function get_rup_tender_metode()
    {
      $tahun = date('Y');

      $str = "SELECT r.metode_pemilihan, COUNT(DISTINCT l.kode_rup) as total_paket, $tahun as tahun FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != '%APBN%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif'))
      GROUP BY r.metode_pemilihan";
      return $this->db->query($str)->result();
    }

    public function get_rup_non_tender_metode()
    {
      $tahun = date('Y');

      $str = "SELECT r.metode_pemilihan, COUNT(DISTINCT l.kode_rup) as total_paket FROM tb_rup r
      LEFT JOIN tb_non_tender l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != '%APBN%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif'))
      GROUP BY r.metode_pemilihan";
      return $this->db->query($str)->result();
    }

    // ---

    public function get_total_tender()
    {
      $tahun = date('Y');

      $str = "SELECT COUNT(a.kode),

      -- paket masuk dan pagu
      (SELECT COUNT(l.kode_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as total_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as total_pagu,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND l.status_lelang = 0 AND l.status_aktif != 'non aktif'
      AND l.ukpbj = '1106.00') as total_belum_tayang,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND l.status_lelang = 1 AND l.menang = 0 AND l.status_aktif != 'non aktif'
      AND l.ukpbj = '1106.00') as total_tayang,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND l.status_lelang = 1 AND l.menang = 5 AND l.status_aktif != 'non aktif'
      AND l.ukpbj = '1106.00') as total_umum_pemenang,

      -- lelang
      (SELECT COUNT(l.kode_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan = 'Tender' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as total_tender,

      (SELECT COUNT(l.kode_rup) FROM tb_lelang l
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.metode_pemilihan LIKE '%Tender Cepat%' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'

      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )
      ) as total_tender_cepat,

      -- non tender (seleksi, dll)

      (SELECT COUNT(l.kode_lelang) FROM tb_non_tender l
      LEFT JOIN tb_rup r ON r.kode_rup = l.kode_rup
      WHERE r.metode_pemilihan = 'Seleksi' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as total_seleksi,

      (SELECT COUNT(l.kode_lelang) FROM tb_non_tender l
      LEFT JOIN tb_rup r ON r.kode_rup = l.kode_rup
      WHERE r.metode_pemilihan = 'Penunjukan Langsung' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as total_penunjukan_langsung,

      (SELECT COUNT(l.kode_lelang) FROM tb_non_tender l
      LEFT JOIN tb_rup r ON r.kode_rup = l.kode_rup
      WHERE r.metode_pemilihan = 'Pengadaan Langsung' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as total_pengadaan_langsung,

      (SELECT COUNT(l.kode_lelang) FROM tb_non_tender l
      LEFT JOIN tb_rup r ON r.kode_rup = l.kode_rup
      WHERE r.metode_pemilihan = 'e-Purchasing' AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak'
      AND l.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (l.status_lelang = 1)
      OR (l.status_lelang = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1) )
      ) as total_epurchasing

      FROM tb_skpa a";

      return $this->db->query($str)->result();
    }

    public function get_rup()
    {
      $tahun = date('Y');

      $str = "SELECT a.kode, a.nama, a.singkatan, a.kode_utama, a.instansi,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as swakelola_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as swakelola_pagu,

      $tahun as tahun_swakelola,

      -- realisasi rup

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_rup r WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun)
      AND r.status_aktif = 'ya' and r.status_umumkan = 'sudah') as rup_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r WHERE r.id_satker = a.kode
      AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun)
      AND r.status_aktif = 'ya' and r.status_umumkan = 'sudah') as rup_pagu,

      $tahun as tahun_rup

      FROM tb_skpa a

      WHERE a.instansi != 'pusat'";

      return $this->db->query($str)->result();
    }

    public function get_realisasi_lelang()
    {
      $tahun = date('Y');

      $str = "SELECT a.kode, a.nama, a.singkatan, a.kode_utama, a.instansi,

      ( SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND l.tahun LIKE '%$tahun%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')) ) as tender,

      (SELECT count(DISTINCT t.kode_lelang) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.status_aktif != 'non aktif')
      OR (t.status_lelang = 1 AND t.paket_status = 1) ) ) as non_tender,

      -- ( SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      -- LEFT JOIN tb_non_tender l ON r.kode_rup = l.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      -- AND l.tahun LIKE '%$tahun%' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      -- OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')) ) as non_tender,

      $tahun as tahun,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah' AND r.status_aktif = 'ya'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as total_pagu

      FROM tb_skpa a WHERE a.instansi != 'pusat' order by a.singkatan ASC";

      return $this->db->query($str)->result();
    }

    public function get_realisasi_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT a.kode, a.nama as skpa,

      -- (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      -- LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      -- WHERE r.id_satker = a.kode
      -- AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      -- OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as tender,

      -- (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      -- LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      -- WHERE r.id_satker = a.kode
      -- AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      -- OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as non_tender,

      $tahun as tahun,

      -- (SELECT SUM(l.pagu) FROM tb_rup r
      -- LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      -- WHERE r.id_satker = a.kode
      -- AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      -- AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      -- OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      -- OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as total_pagu,

      -- Seleksi

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_seleksi,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as pagu_seleksi,

      -- Penunjukan langsung

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Penunjukan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pk_langsung,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Penunjukan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pk_langsung_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pd_langsung,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pd_langsung_pagu,

      -- e Purchasing

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_e_purchasing,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_e_purchasing_pagu

      FROM tb_skpa a WHERE a.instansi != 'pusat'";

      return $this->db->query($str)->result();
    }

    public function xget_realisasi_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT a.kode as id, a.kode, a.nama as skpa, $tahun as tahun,

      (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as total_paket,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_non_tender l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as total_pagu,

      -- Seleksi

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_seleksi,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Seleksi'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as pagu_seleksi,

      -- Penunjukan langsung

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Penunjukan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pk_langsung,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Penunjukan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pk_langsung_pagu,

      -- Pengadaan Langsung

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pd_langsung,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_pd_langsung_pagu,

      -- e Purchasing

      (SELECT COUNT(DISTINCT l.kode_lelang) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_e_purchasing,

      (SELECT SUM(l.pagu) FROM tb_rup r
      LEFT JOIN tb_lelang l ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND r.metode_pemilihan = 'Pengadaan Langsung'
      AND l.tahun LIKE '%$tahun%' AND r.status_umumkan = 'sudah'
      AND r.sumber_dana != 'APBN' AND ( (l.status_lelang = 1 AND l.ukpbj IS NULL)
      OR (l.status_lelang = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
      OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00') )) as paket_e_purchasing_pagu

      FROM tb_skpa a WHERE a.instansi != 'pusat'";

      return $this->db->query($str)->result();
    }

}
