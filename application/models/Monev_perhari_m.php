<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monev_perhari_m extends CI_Model{

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
    public function view_persatker_rup()
    {
      $tahun = date('Y');

      $str = "SELECT a.singkatan,

      -- PAKET Tender
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Tender Cepat' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND (r.metode_pemilihan = 'Tender' OR r.metode_pemilihan = 'Tender Cepat' OR r.metode_pemilihan = 'Seleksi')
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pt_pagu,

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

      -- Swakelola

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_paket,

      (SELECT SUM(r.pagu_rup) FROM tb_rup r
      WHERE r.id_satker = a.kode
      AND left(r.akhir_pekerjaan,4) = $tahun AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
      AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as sw_pagu

      FROM tb_skpa a
      INNER JOIN tb_rup b ON b.id_satker = a.kode
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function get_total()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

      -- menghitung non tender jumlah paket dan pagu
      -- (SELECT count(t.kode_rup) FROM tb_non_tender t
      -- LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      -- WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      -- ) as tpaket_non_tender,

      -- (SELECT SUM(t.pagu) FROM tb_non_tender t
      -- LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      -- WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN' AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      -- ) as tpagu_non_tender,

      (SELECT COUNT(t.kode_rup) FROM tb_lelang t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun = $tahun AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_lelang t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup AND r.sumber_dana != 'APBN'
      WHERE t.tahun = $tahun AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpagu_non_tender,

      -- selisih total lelang dan non_tender

      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun = $tahun
      AND ( (t.status_lelang = 1 AND t.paket_status = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as total_selisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun = $tahun AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as total_selisih_non_tender,

      -- Menghitung SP TOTAL

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND (pk.paket_status = 2) AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     -- INNER JOIN tb_lelang l ON pk.paket_id = l.kode_rup
     LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

     -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,
     --
     -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     -- AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,
     --
     -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     -- AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,
     --
     -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2
     -- AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,
     --
     -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
     -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
     -- WHERE (left(r.awal_pengadaan,4) = '$tahun' OR left(r.akhir_pekerjaan,4) = '$tahun')
     -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- menghitung TOTAL semua skpa REVIEW

      -- (SELECT Count(t.kode_rup)
      -- FROM tb_review t, tb_rup r
      -- WHERE t.kode_rup = r.kode_rup AND t.status = 5 AND r.sumber_dana != 'APBN') as review_belum,
      --


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

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
      -- INNER JOIN tb_lelang l ON v.kode_rup = l.kode_rup
      LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.sumber_dana != 'APBN') as review_total,

      -- Total Belum Tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') ) as sbt,

      -- total selisih belum tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_selisih_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_selisih_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_selisih_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_selisih_j,

      -- Total Tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN') )) as st,

      -- total selisih tayang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_selisih_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_selisih_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0))
      AND r.jenis_pengadaan LIKE '%barang%') as st_selisih_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_selisih_j,

      -- Total Umum Pemenang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN')) ) as sup,

      -- total selisih umum pemenang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_selisih_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_selisih_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as sup_selisih_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_selisih_j,

      -- total paket batal

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(b.batal_paket) FROM tb_batal b
      INNER JOIN tb_lelang l ON b.batal_paket = l.kode_rup
      LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup) as spb

      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal l ON r.kode_rup = l.batal_paket
      -- WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal l ON r.kode_rup = l.batal_paket
      -- WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      -- WHERE r.jenis_pengadaan LIKE '%barang%') as pb_b,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal l ON r.kode_rup = l.batal_paket
      -- WHERE r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,
      --
      -- (SELECT COUNT(r.kode_rup) FROM tb_rup r
      -- INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket) as spb

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')";

      return $this->db->query($str)->result();
    }

    public function get_laporan()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
        $tahun = $_GET['tahun'];
      }

      $str = "SELECT singkatan, 

      (SELECT COUNT(l.kode_rup) FROM tb_lelang l 
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00' OR l.ukpbj IS NULL) 
      AND ((l.status_lelang = 1 AND l.paket_status = 1) OR (l.status_lelang = 0 AND l.paket_status = 0))) as tpaket,

      (SELECT SUM(l.pagu) FROM tb_lelang l 
      LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND l.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00' OR l.ukpbj IS NULL) 
      AND ((l.status_lelang = 1 AND l.paket_status = 1) OR (l.status_lelang = 0 AND l.paket_status = 0))) as tpagu,

      -- SP

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup 
      WHERE r.tahun = $tahun AND r.sumber_dana != 'APBN' 
      AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- Review

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 5 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun) as review_belum,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 0 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun) as review_pokja,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 1 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun) as review_skpa,

      (SELECT COUNT(DISTINCT v.kode_rup) FROM tb_review v
	    LEFT JOIN tb_rup r ON v.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND v.status = 2 AND r.sumber_dana != 'APBN' AND r.tahun = $tahun) as review_selesai,

      -- belum tayang

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00') OR (t.ukpbj = '1106.00' OR t.ukpbj = '3106.00') )
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0) OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0) )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_kt,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00') OR (t.ukpbj = '1106.00' OR t.ukpbj = '3106.00') )
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0) OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0) )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00') OR (t.ukpbj = '1106.00' OR t.ukpbj = '3106.00') )
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0) OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0) )
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (l.ukpbj = '1106.00' OR l.ukpbj = '3106.00') OR (t.ukpbj = '1106.00' OR t.ukpbj = '3106.00') )
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0) OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0) )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- selisih

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      -- AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_selisih_kt,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      -- AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      -- AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_selisih_ks,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      -- AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      -- AND r.jenis_pengadaan LIKE '%barang%') as bt_selisih_b,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      -- AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      -- AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_selisih_j,

      -- tayang

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Lainnya%') as t_j,

      -- SELISIH

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      -- AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_selisih_kt,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      -- AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as t_selisih_ks,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      -- AND r.jenis_pengadaan LIKE '%barang%') as t_selisih_b,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      -- AND r.jenis_pengadaan LIKE '%jasa lainnya%') as t_selisih_j,

      -- menang

      (SELECT (COUNT(r.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND ll.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT (COUNT(r.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND ll.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT (COUNT(r.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND ll.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT (COUNT(r.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_lelang ll ON l.kode_lelang = ll.kode_lelang
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND ll.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- selisih menang

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      -- AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_selisih_kt,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      -- AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_selisih_ks,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      -- AND r.jenis_pengadaan LIKE '%barang%') as m_selisih_b,

      -- (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      -- LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      -- WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      -- AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_selisih_j,

      -- batal

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      -- AND ( ((t.status_lelang = 1 AND t.status_lelang = 1 AND t.ukpbj = NULL) AND t.paket_status != 0) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00')

      FROM tb_skpa a
      WHERE a.nama NOT LIKE '%biro%'
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";
      return $this->db->query($str)->result();
    }

    public function get_total_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

      -- paket masuk non tender
      (SELECT COUNT(t.kode_rup) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_non_tender,

      (SELECT SUM(t.pagu) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup AND r.sumber_dana != 'APBN'
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpagu_non_tender,

      -- selisih total lelang dan non_tender

      -- (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      -- LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      -- WHERE t.tahun LIKE '%$tahun%'
      -- AND ( (t.status_lelang = 1 AND t.paket_status = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      -- OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      -- ) as total_selisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as total_selisih_non_tender,

    -- Menghitung SP TOTAL

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND (pk.paket_status = 2) AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

     (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
     INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
     LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
     WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
     AND r.sumber_dana != 'APBN' AND pk.paket_status = 2) as tsp,

      -- TOTAL REVIEW

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 5 AND r.sumber_dana != 'APBN') as review_belum,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 0 AND r.sumber_dana != 'APBN') as review_pokja,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 1 AND r.sumber_dana != 'APBN') as review_skpa,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
	    LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE v.status = 2 AND r.sumber_dana != 'APBN' ) as review_selesai,

      (SELECT COUNT(v.kode_rup) FROM tb_review v
      INNER JOIN tb_non_tender t ON v.kode_rup = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.sumber_dana != 'APBN') as review_total,

      -- Total Belum Tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') ) as sbt,

      -- total selisih blm tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sbt_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sbt_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%') as sbt_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sbt_selisih_j,

      -- Total Tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as st_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )) as st,

      -- total selisih tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as st_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as st_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%') as st_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.sumber_dana != 'APBN' AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as st_selisih_j,

      -- Total Umum Pemenang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as sup_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_j,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN')) ) as sup,

      -- total selisih umum pemenang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sup_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sup_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%barang%') as sup_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE ((t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sup_selisih_j,

      -- total paket batal

      (SELECT COUNT(l.batal_paket) FROM tb_batal l
      INNER JOIN tb_non_tender t ON l.batal_paket = t.kode_rup
      LEFT JOIN tb_rup r ON l.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as pb_kt,

      (SELECT COUNT(l.batal_paket) FROM tb_batal l
      INNER JOIN tb_non_tender t ON l.batal_paket = t.kode_rup
      LEFT JOIN tb_rup r ON l.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%') as pb_ks,

      (SELECT COUNT(l.batal_paket) FROM tb_batal l
      INNER JOIN tb_non_tender t ON l.batal_paket = t.kode_rup
      LEFT JOIN tb_rup r ON l.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%barang%') as pb_b,

      (SELECT COUNT(l.batal_paket) FROM tb_batal l
      INNER JOIN tb_non_tender t ON l.batal_paket = t.kode_rup
      LEFT JOIN tb_rup r ON l.batal_paket = r.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%') as pb_j,

      (SELECT COUNT(l.batal_paket) FROM tb_batal l
      INNER JOIN tb_non_tender t ON l.batal_paket = t.kode_rup
      LEFT JOIN tb_rup r ON l.batal_paket = r.kode_rup) as spb

      FROM tb_skpa a

      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup

      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')";

      return $this->db->query($str)->result();
    }

    public function get_laporan_non_tender()
    {
      $tahun = date('Y');

      $str = "SELECT singkatan, COUNT(c.kode_rup) as tpaket, SUM(b.pagu_rup) as tpagu,

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
      LEFT JOIN tb_rup r ON r.kode_rup = t.kode_rup
      WHERE (r.id_satker = a.kode AND t.tahun LIKE '%$tahun%')
      AND ((t.status_lelang = 1 AND t.paket_status = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      ) as tselisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%' AND r.sumber_dana != 'APBN'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tselisih_non_tender,

      -- menghitung REALISASI SP

      -- (SELECT COUNT(pk.paket_id)
      -- FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      -- WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      -- AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      INNER JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND r.sumber_dana != 'APBN' AND pk.paket_status = 2 AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- Menghitung review

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

      -- selisih belum tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as bt_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%') as bt_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_selisih_j,

      -- tayang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%') as t_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Lainnya%') as t_j,

      -- SELISIH TAYANG

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as t_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%barang%') as t_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as t_selisih_j,

      -- menang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- selisih menang

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%Pekerjaan Konstruksi%') as m_selisih_kt,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_selisih_ks,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%barang%') as m_selisih_b,

      (SELECT COUNT(t.kode_rup) FROM tb_rup r
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_selisih_j,

      -- batal

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND r.id_satker = a.kode AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      -- AND ( ((t.status_lelang = 1 AND t.status_lelang = 1 AND t.ukpbj = NULL) AND t.paket_status != 0) OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00')

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      GROUP BY a.kode
      ORDER BY a.singkatan ASC ";
      return $this->db->query($str)->result();
    }

    public function get_format_wa_total()
    {
      $tahun = date('Y');

      $str = "SELECT COUNT(c.kode_rup) as tpaket,

      -- selisih total lelang dan non_tender
      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE t.tahun = '$tahun'
      AND ( (t.status_lelang = 1 AND t.paket_status = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as total_selisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun = '$tahun'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as total_selisih_non_tender,

      -- Total Belum Tayang & Selisih

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE (l.tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')) as total_bt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE (l.tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')) as total_selisih_bt,

      -- Total Tayang & selisih

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN') )) as total_tayang,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN') )) as total_selisih_tayang,

      -- Total Umum Pemenang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      ) as total_menang,

      -- total selisih umum pemenang

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      WHERE ((l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN'))
      ) as total_selisih_menang

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun = '$tahun' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')";

      return $this->db->query($str)->result();
    }

    public function get_format_wa_laporan()
    {
      $tahun = date('Y');

      $str = "SELECT singkatan, COUNT(c.kode_rup) as tpaket,

      -- paket masuk non tender
      (SELECT count(t.kode_rup) FROM tb_non_tender t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun = '$tahun'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as tpaket_non_tender,

      -- selisih paket masuk
      (SELECT count(t.kode_rup) FROM tb_lelang_bck t
      LEFT JOIN tb_rup r ON r.kode_rup = t.kode_rup
      WHERE (r.id_satker = a.kode AND t.tahun = '$tahun')
      AND ((t.status_lelang = 1 AND t.paket_status = 1 AND r.sumber_dana != 'APBN') OR (t.status_lelang = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN'))
      ) as total_selisih_lelang,

      (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      WHERE r.id_satker = a.kode AND t.tahun = '$tahun'
      AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      ) as total_selisih_non_tender,

      -- belum tayang dan selisih
      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ( (l.tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun = '$tahun' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      ) as bt,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN'
      AND ((l.tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun = '$tahun' AND t.status_lelang = 0 AND t.ukpbj = '1106.00')) ) as bt_selisih,

      -- tayang dan selisih
      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      ) as t,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
    ) as t_selisih,

      -- menang dan selisih

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 5))
      ) as m,

      (SELECT (COUNT(l.kode_rup) + COUNT(t.kode_rup)) FROM tb_rup r
      LEFT JOIN tb_lelang_bck l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN' AND ((l.tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 5))
      ) as m_selisih

      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.tahun = '$tahun' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj is null AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";

  

      return $this->db->query($str)->result();
    }

    public function get_format_wa_total_spse()
    {
      $tahun = date('Y');

      $str = "SELECT COUNT(c.kode_lelang) as tpaket,

      -- selisih total lelang dan non_tender
      (SELECT count(t.kode_lelang) FROM tb_lelang_spse_bck t
      WHERE t.ang_tahun = '$tahun'
      AND ( (t.status_lelang = 1 AND t.paket_status = 1) OR (t.status_lelang = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      ) as total_selisih_lelang,

      -- Total Belum Tayang & Selisih

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse_bck r ON r.kode_lelang = l.kode_lelang
      WHERE (l.ang_tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')) as total_bt,

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse_bck r ON r.kode_lelang = l.kode_lelang
      WHERE (l.ang_tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')) as total_selisih_bt,

      -- Total Tayang & selisih

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse r ON r.kode_lelang = l.kode_lelang
      WHERE ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0) )) as total_tayang,

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse_bck r ON r.kode_lelang = l.kode_lelang
      WHERE ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0) )) as total_selisih_tayang,

      -- Total Umum Pemenang

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse r ON r.kode_lelang = l.kode_lelang
      WHERE ((l.status_lelang = 1 AND l.menang = 5))
      ) as total_menang,

      -- total selisih umum pemenang

      (SELECT COUNT(l.kode_lelang) FROM tb_lelang_spse l
      LEFT JOIN tb_lelang_spse_bck r ON r.kode_lelang = l.kode_lelang
      WHERE ((l.status_lelang = 1 AND l.menang = 5))
      ) as total_selisih_menang

      FROM tb_skpa a
      -- LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang_spse c ON a.kode = c.rup_stk_id
      WHERE (c.ang_tahun = '$tahun' AND (c.status_lelang = 1 AND c.status_lelang = 1 AND c.ukpbj is null)
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00'))";

      return $this->db->query($str)->result();
    }

    public function get_format_wa_laporan_spse()
    {
      $tahun = date('Y');

      $str = "SELECT singkatan, COUNT(c.kode_lelang) as tpaket,

      -- paket masuk non tender
      -- (SELECT count(t.kode_rup) FROM tb_non_tender t
      -- LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      -- WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      -- AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00') )
      -- ) as tpaket_non_tender,

      -- selisih paket masuk

      (SELECT count(t.kode_lelang) FROM tb_lelang_spse_bck t
      LEFT JOIN tb_lelang_spse r ON r.kode_lelang = t.kode_lelang
      WHERE (r.rup_stk_id = a.kode AND t.ang_tahun = '$tahun')
      AND ((t.status_lelang = 1 AND t.paket_status = 1) OR (t.status_lelang = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00')
      OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00'))
      ) as total_selisih_lelang,

      -- (SELECT count(t.kode_rup) FROM tb_non_tender_bck t
      -- LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
      -- WHERE r.id_satker = a.kode AND t.tahun LIKE '%$tahun%'
      -- AND ( (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN')
      -- OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') )
      -- ) as total_selisih_non_tender,

      -- belum tayang dan selisih
      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_spse_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode
      AND ( (l.ang_tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      ) as bt,

      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode
      AND ((l.ang_tahun = '$tahun' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 0 AND t.ukpbj = '1106.00')) ) as bt_selisih,

      -- tayang dan selisih
      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode AND ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      ) as t,

      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode AND ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      ) as t_selisih,

      -- menang dan selisih

      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode AND ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 5))
      ) as m,

      (SELECT (COUNT(l.kode_lelang)) FROM tb_lelang_spse r
      LEFT JOIN tb_lelang_bck l ON r.kode_lelang = l.kode_lelang
      -- LEFT JOIN tb_non_tender_bck t ON r.kode_rup = t.kode_rup
      WHERE r.rup_stk_id = a.kode AND ((l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.ang_tahun = '$tahun' AND t.status_lelang = 1 AND t.menang = 5))
      ) as m_selisih

      FROM tb_skpa a
      -- LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang_spse c ON a.kode = c.rup_stk_id
      WHERE (c.ang_tahun = '$tahun' AND (c.status_lelang = 1 AND c.ukpbj is null)
      OR (c.status_lelang = 0 AND c.ukpbj = '1106.00')
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00')
      GROUP BY a.kode
      ORDER BY a.singkatan ASC";

      return $this->db->query($str)->result();
    }

    public function get_subtotal()
    {
      $str = "SELECT singkatan, COUNT(c.kode_rup) as tkt
      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      WHERE (c.status_lelang = 0 AND c.paket_status = 0)
      GROUP BY a.kode";
      return $this->db->query($str)->result();
    }

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

    public function get_daftar_paket($var)
    {
      $tahun = date('Y');
      $where = "";
      $jns = "";
      $jenis = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != '')
      {
        $var1 = $_GET['jenis_pengadaan'];
        $jns = strtolower(str_replace('_', ' ', $var1));
      }

      if($var == 'masuk'){ // MASUK
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun LIKE '%$tahun%' AND (((c.status_lelang != 0 AND c.status_lelang != 2) AND c.paket_status != 0) OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00'))
        OR d.tahun LIKE '%$tahun%' AND (((d.status_lelang != 0 AND d.status_lelang != 2) AND d.paket_status != 0) OR (d.status_lelang = 0 AND d.paket_status = 0 AND d.ukpbj = '1106.00'))";
      }elseif($var == 'belum_tayang'){ // BELUM TAYANG
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND (c.tahun LIKE '%$tahun%' AND c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00')
        OR (d.tahun LIKE '%$tahun%' AND d.status_lelang = 0 AND d.paket_status = 0 AND d.ukpbj = '1106.00')";
      }elseif($var == 'tayang'){ // TAYANG
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND (c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.menang = 0 AND c.ukpbj = '1106.00')
        OR (d.tahun LIKE '%$tahun%' AND d.status_lelang = 1 AND d.menang = 0 AND d.ukpbj = '1106.00')";
      }elseif($var == 'umum_pemenang'){ // UMUM PEMENANG
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND (c.tahun LIKE '%$tahun%' AND c.status_lelang = 1 AND c.menang = 5)
        OR (d.tahun LIKE '%$tahun%' AND d.status_lelang = 1 AND d.menang = 5)";
      }elseif($var == 'batal'){ // BATAL
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun = $tahun AND b.kode_rup IN (SELECT batal_paket FROM tb_batal)";
      }elseif($var == 'tender_ulang'){ //TENDER ULANG
        $where = "WHERE b.jenis_pengadaan LIKE '%$jns%'
        AND c.tahun = $tahun AND c.kode_rup IN (SELECT kode_rup FROM tb_lelang WHERE status_lelang = 2)
        OR d.tahun = $tahun AND d.kode_rup IN (SELECT kode_rup FROM tb_lelang WHERE status_lelang = 2)";
      }

      $str = "SELECT * FROM

      (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan, a.kode as id_satker, '' as keterangan
      FROM tb_skpa a
      INNER JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      LEFT JOIN tb_non_tender d ON b.kode_rup = d.kode_rup
      $where
      GROUP BY a.kode
      UNION

      SELECT c.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan, b.id_satker as id_satker, c.keterangan as keterangan
      FROM tb_rup b
      INNER JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      LEFT JOIN tb_non_tender d ON b.kode_rup = d.kode_rup
      $where
      GROUP BY c.kode_lelang
      UNION

      SELECT d.kode_lelang as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan, b.id_satker as id_satker, d.keterangan as keterangan
      FROM tb_rup b
      LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      INNER JOIN tb_non_tender d ON b.kode_rup = d.kode_rup
      $where
      GROUP BY d.kode_lelang)

      AS tb_join
      ORDER BY id_satker, jenis_pengadaan ASC";

      return $this->db->query($str)->result();
    }

    public function get_daftar_paket_batal()
    {
      $tahun = date('Y');
      $where = "";
      $jns = "";
      $jenis = "";

      if(isset($_GET['jenis_pengadaan']) && $_GET['jenis_pengadaan'] != '')
      {
        $jns = $_GET['jenis_pengadaan'];
        $jns = str_replace('_', ' ', $jns);
      }

      // mengambil daftar paket batal

      $str = "SELECT * FROM

      (SELECT '' as kode_lelang, '' as kode_rup, a.nama as nama_pekerjaan, SUM(b.pagu_rup) as pagu, '' as jenis_pengadaan, a.kode as id_satker, '' as keterangan
      FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
      LEFT JOIN tb_batal bt On b.kode_rup = bt.batal_paket
      WHERE b.jenis_pengadaan LIKE '%$jns%' AND b.kode_rup IN (SELECT batal_paket FROM tb_batal)
      GROUP BY a.kode
      UNION
      SELECT '' as kode_lelang, b.kode_rup as kode_rup, b.nama_paket as nama_pekerjaan, b.pagu_rup as pagu, b.jenis_pengadaan as jenis_pengadaan, b.id_satker as id_satker, bt.batal_keterangan as keterangan
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
      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT * FROM (SELECT v.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, r.nama_kpa as nama_kpa, s.sp_kelompok as kelompok, '' as tgl_review, '' as status, '' as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      GROUP BY v.kode_rup
      UNION
      SELECT h.kode_rup as kode_rup, '' as nama_paket, '' as nama_satker, '' as nama_kpa, '' as kelompok, h.tgl_review as tgl_review, h.status as status, h.keterangan as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      ) as tb_join ORDER BY kode_rup DESC";

      return $this->db->query($str)->result();
    }
}
