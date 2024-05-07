<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monev_pokja_m extends CI_Model{

    function __construct(){

    }

    public function get_total()
    {
      $tahun = date('Y');

      $str = "SELECT a.sp_id,

      -- paket masuk semua

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE (left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun')
      AND pk.paket_status = 2) as tpaket_masuk,

      (SELECT SUM(r.pagu_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE (t.tahun LIKE '%$tahun%')
      OR (l.tahun LIKE '%$tahun%')) as tpagu_masuk,

      -- Menghitung SP TOTAL

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tsp_kt,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as tsp_ks,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%') as tsp_b,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%') as tsp_j,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2) as tsp,

      -- menghitung TOTAL REVIEW

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE v.status = 5) as review_belum,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE v.status = 0) as review_pokja,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE v.status = 1) as review_skpa,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE v.status = 2) as review_selesai,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup) as review_total,

      -- Total Belum Tayang
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'
      AND (l.status_lelang = 0 OR t.status_lelang = 0) AND (l.tahun = '$tahun' OR t.tahun = '$tahun')
      AND (l.ukpbj = '1106.00' OR t.ukpbj = '1106.00')) as tbt_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa konsultansi%'
      AND (l.status_lelang = 0 OR t.status_lelang = 0) AND (l.tahun = '$tahun' OR t.tahun = '$tahun')
      AND (l.ukpbj = '1106.00' OR t.ukpbj = '1106.00')) as tbt_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%barang%'
      AND (l.status_lelang = 0 OR t.status_lelang = 0) AND (l.tahun = '$tahun' OR t.tahun = '$tahun')
      AND (l.ukpbj = '1106.00' OR t.ukpbj = '1106.00')) as tbt_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%jasa lainnya%'
      AND (l.status_lelang = 0 OR t.status_lelang = 0) AND (l.tahun = '$tahun' OR t.tahun = '$tahun')
      AND (l.ukpbj = '1106.00' OR t.ukpbj = '1106.00')) as tbt_j,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_status = 2 AND ((l.status_lelang = 0 AND l.tahun LIKE '%$tahun%')
      OR (t.status_lelang = 0 AND t.tahun LIKE '%$tahun%' AND t.ukpbj = '1106.00')) ) as tbt,

      -- (SELECT COUNT(l.kode_rup) FROM tb_rup r
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.ukpbj = '1106.00')
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00'))
      -- AND r.sumber_dana != 'APBN') as tbt,

      -- Total Tayang

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tt_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tt_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%barang%'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tt_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tt_j,

      -- (SELECT COUNT(DISTINCT pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      -- WHERE pk.paket_status = 2 AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tt,

      (SELECT COUNT(DISTINCT r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0))
      AND r.sumber_dana != 'APBN') as tt,

      -- Total Umum Pemenang

      -- (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      -- WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'
      -- AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))) as tm_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.sumber_dana != 'APBN') as tm_kt,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.sumber_dana != 'APBN') as tm_ks,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%barang%'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.sumber_dana != 'APBN') as tm_b,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.sumber_dana != 'APBN') as tm_j,

      -- (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      -- WHERE pk.paket_status = 2 AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      -- OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND t.ukpbj = '1106.00'))) as tm,

      (SELECT COUNT(l.kode_rup) FROM tb_rup r
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.sumber_dana != 'APBN') as tm,

      -- total batal

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as tb_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa konsultansi%') as tb_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE r.jenis_pengadaan LIKE '%barang%') as tb_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE r.jenis_pengadaan LIKE '%jasa lainnya%') as tb_j,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      INNER JOIN tb_batal b ON r.kode_rup = b.batal_paket) as tb

      -- FROM tb_skpa a
      -- LEFT JOIN tb_rup b ON a.kode = b.id_satker
      -- LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      -- WHERE (c.tahun LIKE '%$tahun%' AND b.sumber_dana != 'APBN') AND (c.status_lelang = 1 AND c.status_lelang = 1 AND b.sumber_dana != 'APBN')
      -- OR (c.status_lelang = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      -- OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')
      -- OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND b.sumber_dana != 'APBN')

      FROM tb_sp a
      GROUP BY a.sp_id
      ORDER BY a.sp_id ASC";

      return $this->db->query($str)->result();
    }

    public function get_laporan()
    {
      $tahun = date('Y');

      $str = "SELECT a.sp_id, a.sp_kelompok,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE (left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun')
      AND pk.paket_sp = a.sp_id AND pk.paket_status = 2) as tpaket_masuk,

      (SELECT SUM(r.pagu_rup)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '2019' OR left(r.awal_pekerjaan,4) = '2019' )
      AND sp.sp_id = a.sp_id AND pk.paket_status = 2) as tpagu_masuk,

      -- menghitung SP
      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND sp.sp_id = a.sp_id AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as sp_kt,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND sp.sp_id = a.sp_id AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as sp_ks,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND sp.sp_id = a.sp_id AND r.jenis_pengadaan LIKE '%barang%') as sp_b,

      (SELECT COUNT(pk.paket_id)
      FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT join tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE ( left(r.akhir_pengadaan,4) = '$tahun' OR left(r.awal_pekerjaan,4) = '$tahun' )
      AND pk.paket_status = 2 AND sp.sp_id = a.sp_id AND r.jenis_pengadaan LIKE '%jasa lainnya%') as sp_j,

      -- menghitung review
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE pk.paket_sp = a.sp_id AND v.status = 5) as review_belum,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE pk.paket_sp = a.sp_id AND v.status = 0) as review_pokja,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE pk.paket_sp = a.sp_id AND v.status = 1) as review_skpa,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      INNER JOIN tb_review v ON pk.paket_id = v.kode_rup
      WHERE pk.paket_sp = a.sp_id AND v.status = 2) as review_selesai,

      -- belum tayang
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2 AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%'
      AND ( (l.tahun LIKE '%$tahun%' AND l.ukpbj = '1106.00' AND l.status_lelang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.ukpbj = '1106.00' AND t.status_lelang = 0))) as bt_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as bt_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%barang%') as bt_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as bt_j,

      -- tayang
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      left JOIN tb_rup r ON pk.paket_id = r.kode_rup
      left JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      left JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as t_kt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Konsultansi%') as t_ks,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Barang%') as t_b,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
      AND r.jenis_pengadaan LIKE '%Jasa Lainnya%') as t_j,

      -- menang
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      left JOIN tb_rup r ON pk.paket_id = r.kode_rup
      left JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      left JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND pk.paket_status = 2
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as m_kt,

      (SELECT COUNT(l.kode_rup) + COUNT(t.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as m_ks,

      (SELECT COUNT(l.kode_rup) + COUNT(t.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%barang%') as m_b,

      (SELECT COUNT(l.kode_rup) + COUNT(t.kode_rup) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON pk.paket_id = l.kode_rup
      LEFT JOIN tb_non_tender t ON pk.paket_id = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
      AND r.jenis_pengadaan LIKE '%jasa lainnya%') as m_j,

      -- batal
      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE b.batal_sp = a.sp_id AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE b.batal_sp = a.sp_id AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE b.batal_sp = a.sp_id AND r.jenis_pengadaan LIKE '%barang%') as b_b,

      (SELECT COUNT(r.kode_rup) FROM tb_rup r
      LEFT JOIN tb_batal b ON r.kode_rup = b.batal_paket
      WHERE b.batal_sp = a.sp_id AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      -- (SELECT COUNT(b.batal_paket) FROM tb_batal b
      -- INNER JOIN tb_sp_paket pk ON b.batal_paket = pk.paket_id
      -- LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND pk.paket_sp = a.sp_id AND r.jenis_pengadaan LIKE '%pekerjaan konstruksi%') as b_kt,
      --
      -- (SELECT COUNT(b.batal_paket) FROM tb_batal b
      -- INNER JOIN tb_sp_paket pk ON b.batal_paket = pk.paket_id
      -- LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND pk.paket_sp = a.sp_id AND r.jenis_pengadaan LIKE '%jasa konsultansi%') as b_ks,
      --
      -- (SELECT COUNT(b.batal_paket) FROM tb_batal b
      -- INNER JOIN tb_sp_paket pk ON b.batal_paket = pk.paket_id
      -- LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND pk.paket_sp = a.sp_id AND r.jenis_pengadaan LIKE '%barang%') as b_b,
      --
      -- (SELECT COUNT(b.batal_paket) FROM tb_batal b
      -- INNER JOIN tb_sp_paket pk ON b.batal_paket = pk.paket_id
      -- LEFT JOIN tb_rup r ON b.batal_paket = r.kode_rup
      -- LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      -- LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      -- WHERE (l.tahun LIKE '%$tahun%' OR t.tahun LIKE '%$tahun%') AND pk.paket_sp = a.sp_id AND r.jenis_pengadaan LIKE '%jasa lainnya%') as b_j

      -- FROM tb_sp a
      -- LEFT JOIN tb_sp_paket b ON a.sp_id = b.paket_sp
      -- LEFT JOIN tb_rup c ON b.paket_id = c.kode_rup
      -- LEFT JOIN tb_lelang d ON c.kode_rup = d.kode_rup
      -- WHERE (d.tahun LIKE '%$tahun%' AND c.sumber_dana != 'APBN') AND (d.status_lelang = 1 AND d.status_lelang = 1 AND d.ukpbj is null AND c.sumber_dana != 'APBN')
      -- OR (d.status_lelang = 0 AND d.ukpbj = '1106.00' AND c.sumber_dana != 'APBN')
      -- OR (d.status_lelang = 1 AND d.paket_status = 1 AND d.ukpbj = '1106.00' AND c.sumber_dana != 'APBN')

      FROM tb_sp a
      GROUP BY a.sp_id
      ORDER BY a.sp_id ASC";

      return $this->db->query($str)->result();
    }

    public function get_detail_paket($param)
    {
      $urls = explode('-',$param);

  		$sp_id = $urls[0];
  		$jenis = $urls[1];
  		$jenis_pengadaan = str_replace('_',' ',$urls[2]);

      $tahun = date('Y');

      if($jenis == 'belum_tayang'){
        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(jj.kode_rup) FROM tb_jadwal jj WHERE jj.kode_rup = r.kode_rup) as tjadwal,
        (SELECT j.tahapan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as tahapan,
        (SELECT j.keterangan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as keterangan
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE pk.paket_sp = $sp_id
        AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') )
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";
      }elseif($jenis == 'tayang'){
        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(jj.kode_rup) FROM tb_jadwal jj WHERE jj.kode_rup = r.kode_rup) as tjadwal,
        (SELECT j.tahapan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as tahapan,
        (SELECT j.keterangan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as keterangan
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE pk.paket_sp = $sp_id
        AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0 AND l.ukpbj = '1106.00')
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";
      }elseif($jenis == 'menang'){
        $str = "SELECT r.kode_rup, r.nama_paket, r.pagu_rup, sp.sp_kelompok,
        (SELECT COUNT(jj.kode_rup) FROM tb_jadwal jj WHERE jj.kode_rup = r.kode_rup) as tjadwal,
        (SELECT j.tahapan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as tahapan,
        (SELECT j.keterangan FROM tb_jadwal j WHERE j.kode_rup = r.kode_rup AND j.tahapan = 'TANDATANGAN_KONTRAK') as keterangan
        FROM tb_rup r
        LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
        LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
        LEFT JOIN tb_sp_paket pk ON r.kode_rup = pk.paket_id
        LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
        WHERE pk.paket_sp = $sp_id
        AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
        OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))
        AND r.jenis_pengadaan LIKE '%$jenis_pengadaan%'";
      }

      return $this->db->query($str)->result();
    }

    public function get_detail_jadwal($kode_rup)
    {
      $str = "SELECT j.*, r.nama_paket FROM tb_jadwal j
      LEFT JOIN tb_rup r ON j.kode_rup = r.kode_rup
      WHERE j.kode_rup = '$kode_rup' ORDER BY j.tgl_mulai ASC";
      return $this->db->query($str)->result();
    }

    public function get_skpa_pokja()
    {
      $tahun = date('Y');

      // total
      $str_sp = "SELECT a.sp_id, a.sp_kelompok,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.sumber_dana != 'APBN') as tpokja,

      -- (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      -- LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      -- LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      -- WHERE sp.sp_id = a.sp_id AND r.sumber_dana != 'APBN') as tpokja_paket,

      -- total bt, t, m
      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND r.sumber_dana != 'APBN'
      AND ( (l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.ukpbj = '1106.00')
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 0 AND t.ukpbj = '1106.00') ) ) as tpokja_bt,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND r.sumber_dana != 'APBN'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 0)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.ukpbj = '1106.00'))) as tpokja_t,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
      LEFT JOIN tb_non_tender t ON r.kode_rup = t.kode_rup
      WHERE pk.paket_sp = a.sp_id AND r.sumber_dana != 'APBN'
      AND ((l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.menang = 5)
      OR (t.tahun LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5))) as tpokja_m

      FROM tb_sp a ORDER BY a.sp_kelompok ASC";

      // laporan
      $str_skpa = "SELECT a.singkatan, a.kode,

      (SELECT COUNT(pk.paket_id) FROM tb_sp_paket pk
      LEFT JOIN tb_rup r ON pk.paket_id = r.kode_rup
      WHERE r.id_satker = a.kode AND r.sumber_dana != 'APBN') as tpaket

      FROM tb_skpa a GROUP BY a.kode ORDER BY a.singkatan ASC";

      $data['sp'] = $this->db->query($str_sp)->result();
      $data['skpa'] = $this->db->query($str_skpa)->result();

      return $data;
    }
}
