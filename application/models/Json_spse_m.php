<?php
class Json_spse_m extends CI_Model {

	public function __construct(){

	}

  public function get_daftar_paket_spse($var)
  {
    $tahun = date('Y');

    if($var == 'belum_tayang'){

      $str = "SELECT singkatan, kode_lelang, nama_paket, pagu, hps FROM

      (SELECT a.singkatan as singkatan, '' as kode_lelang, a.nama as nama_paket, '' as pokja, 0 as pagu, 0 as hps, '' as jenis_pengadaan
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_kategori kg
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 0 AND ls.paket_status = 0 AND ls.ukpbj = '1106'
      GROUP BY a.kode

      UNION

      SELECT a.singkatan as singkatan, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, kg.kgr_nama as jenis_pengadaan
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_kategori kg
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 0 AND ls.paket_status = 0 AND ls.ukpbj = '1106'
      GROUP BY ls.kode_lelang)

      as tbtemp ORDER BY singkatan, kode_lelang ASC";

    }elseif($var == 'tayang'){

      $str = "SELECT singkatan, kode_lelang, nama_paket, pagu, hps FROM

      (SELECT a.singkatan as singkatan, '' as kode_lelang, a.nama as nama_paket, '' as pokja, 0 as pagu, 0 as hps, '' as jenis_pengadaan
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_kategori kg
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 0
      GROUP BY a.kode

      UNION

      SELECT a.singkatan as singkatan, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, kg.kgr_nama as jenis_pengadaan
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_kategori kg
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 0
      GROUP BY ls.kode_lelang)

      as tbtemp ORDER BY singkatan, kode_lelang ASC";

    }elseif($var == 'menang'){

      $str = "SELECT singkatan, kode_lelang, nama_paket, pagu, hps FROM

      (SELECT a.singkatan as singkatan, '' as kode_lelang, a.nama as nama_paket, '' as pokja, 0 as pagu, 0 as hps, '' as rkn_nama, '' as psr_harga, '' as psr_harga_terkoreksi, '' as jenis_pengadaan, '' as metode_pemilihan,
      '' as akhir_masa_sanggah1, '' as akhir_masa_sanggah2
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5
      GROUP BY a.kode

      UNION

      SELECT a.singkatan as singkatan, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga, pm.psr_harga_terkoreksi, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan,
      IF(mt.mtd_id = 9, (SELECT js.tgl_selesai FROM tb_jadwal_spse js WHERE js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang ORDER BY js.tgl_selesai DESC LIMIT 1), '') as akhir_masa_sanggah1,
      IF(mt.mtd_id != 9, (SELECT js.tgl_selesai FROM tb_jadwal_spse js WHERE js.tahapan = 'SANGGAH' AND js.kode_lelang = pm.kode_lelang ORDER BY js.tgl_selesai DESC LIMIT 1), '') as akhir_masa_sanggah2
      FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt
      WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun LIKE '%$tahun%'
      AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5
      GROUP BY ls.kode_lelang)

      as tbtemp ORDER BY singkatan, kode_lelang ASC";

    }

    return $this->db->query($str)->result();
  }
}
