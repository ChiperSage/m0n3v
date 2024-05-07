<?php
class Monev_json_m extends CI_Model {

	public function __construct(){

	}

	  public function get_pemenang()
	  {
	    $tahun = date('Y');
	    if(isset($_GET['tahun'])){
	      $tahun = $_GET['tahun'];
	    }
	    return $this->db->get_where('tb_pemenang',array('ang_tahun'=>$tahun))->result();
	  }

  	public function get_rup_list()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT * FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah' GROUP BY kode_rup";
		$query = $this->db->query($str);
		return $query->result_array();
	}

	public function get_rup_swakelola_list()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT * FROM tb_rup WHERE tahun = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah' GROUP BY kode_rup";
		$query = $this->db->query($str);
		return $query->result_array();
	}

	public function get_tender_list()
	{
		// (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		// LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		// WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as total_paket_belum_tayang,
		//
		// (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		// LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		// WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as total_paket_tayang,
		//
		// (SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		// LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		// WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as total_paket_umum_menang

		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT a.nama, b.kode_rup, c.*, b.id_satker

		FROM tb_skpa a
		LEFT JOIN tb_rup b ON a.kode = b.id_satker
		LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
		WHERE b.sumber_dana != 'APBN'
		AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
		OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
		OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')) GROUP BY c.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_non_tender_list()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT a.nama, b.kode_rup, c.*, b.id_satker

		FROM tb_skpa a
		LEFT JOIN tb_rup b ON a.kode = b.id_satker
		LEFT JOIN tb_non_tender c ON b.kode_rup = c.kode_rup
		WHERE b.sumber_dana != 'APBN'
		AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1)
		OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif')
		OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.status_aktif != 'non aktif')) GROUP BY c.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_data_metode()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}
		return $this->db->get_where('tb_metode',array())->result();
	}

	public function get_realisasi_tender()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT 'realisasi_tender' as keterangan, COUNT(DISTINCT c.kode_rup) as total_paket, SUM(b.pagu_rup) as total_pagu,

		(SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 0 AND l.paket_status = 0 AND l.status_aktif != 'non aktif' AND l.ukpbj = '1106.00' AND r.sumber_dana != 'APBN') as total_paket_belum_tayang,

		(SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as total_paket_tayang,

		(SELECT COUNT(DISTINCT l.kode_rup) FROM tb_rup r
		LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		WHERE l.tahun LIKE '%$tahun%' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND l.status_aktif = 'aktif' AND r.sumber_dana != 'APBN') as total_paket_umum_menang

		FROM tb_skpa a
		LEFT JOIN tb_rup b ON a.kode = b.id_satker
		LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
		WHERE b.sumber_dana != 'APBN'
		AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
		OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
		OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif'))";

		return $this->db->query($str)->result();
	}

	public function get_realisasi_jenis_pengadaanxx()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT 'realisasi_jenis_pengadaan' as keterangan,

		-- PAKET Tender
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan = 'Tender')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan = 'Tender')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_pagu,

		-- tender cepat
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan = 'Tender Cepat')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_cepat_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan = 'Tender Cepat')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_cepat_pagu,

		-- Penunjukan Langsung > 200 juta
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Seleksi%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as seleksi_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Seleksi%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as seleksi_pagu,

		-- Penunjukan Langsung <= 200 juta
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as penunjukan_langsung_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as penunjukan_langsung_pagu,

		-- Pengadaan Langsung

		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pengadaan_langsung_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pengadaan_langsung_pagu,

		-- e-Purchasing

		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as epurchasing_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.metode_pemilihan LIKE '%e-Purchasing%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as epurchasing_pagu,

		-- Dikecualikan

		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dikecualikan_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.metode_pemilihan LIKE '%Dikecualikan%' AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dikecualikan_pagu,

		-- tipe swakelola
		(SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
		WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.tipe_swakelola = 1)
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
		WHERE (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah' AND r.tipe_swakelola = 1)
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_sw_pagu,

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
		ORDER BY a.singkatan ASC LIMIT 1";
		return $this->db->query($str)->result();
	}

	public function get_pemenang_apba()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT a.singkatan as singkatan, a.nama as nama_skpa, ls.kode_lelang as kode_lelang, ls.nama_paket as nama_paket, pt.pnt_nama as pokja, ls.pagu as pagu, ls.hps as hps, pm.rkn_nama as rkn_nama, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, kg.kgr_nama as jenis_pengadaan,
		mt.mtd_nama as metode_pemilihan, js.tgl_selesai as akhir_masa_sanggah1
		FROM tb_skpa a, tb_lelang_spse ls, tb_panitia pt, tb_pemenang pm, tb_kategori kg, tb_metode mt, tb_jadwal_spse js
		WHERE a.kode = ls.rup_stk_id AND ls.pnt_id = pt.pnt_id AND ls.kode_lelang = pm.kode_lelang AND ls.jenis_pengadaan = kg.kgr_id AND ls.sbd_id = 'APBD'
		AND ls.mtd_pemilihan = mt.mtd_id AND ls.ang_tahun LIKE '%$tahun%' AND ls.status_lelang = 1 AND ls.paket_status = 1 AND ls.menang = 5 AND
		((mt.mtd_id = 9 AND js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang) OR (mt.mtd_id != 9 AND js.tahapan = 'SANGGAH' AND js.kode_lelang = pm.kode_lelang)) GROUP BY ls.kode_lelang";
		return $this->db->query($str)->result();
	}

	public function get_realisasi_jenis_pengadaan()
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
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan = 'Tender')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_pagu,

		-- Tender Cepat
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Tender Cepat%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_cepat_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Tender Cepat%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as tender_cepat_pagu,

		-- Penunjukan Langsung > 200 juta
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Seleksi%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as seleksi_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Seleksi%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as seleksi_pagu,

		-- Penunjukan Langsung <= 200 juta
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as penunjukan_langsung_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (r.metode_pemilihan LIKE '%Penunjukan Langsung%')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as penunjukan_langsung_pagu,

		-- Pengadaan Langsung
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pengadaan_langsung_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND (r.metode_pemilihan LIKE '%Pengadaan Langsung%' OR r.metode_pemilihan LIKE '%Dikecualikan%' OR r.metode_pemilihan = '-')
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as pengadaan_langsung_pagu,

		-- e-Purchasing
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as epurchasing_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%e-Purchasing%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as epurchasing_pagu,

		-- Dikecualikan
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dikecualikan_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode AND r.metode_pemilihan LIKE '%Dikecualikan%'
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak') as dikecualikan_pagu,

		-- swakelola (tipe)
		(SELECT COUNT(r.kode_rup) FROM tb_rup_swakelola r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_swakelola_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup_swakelola r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND r.status_aktif = 'ya' AND r.status_umumkan = 'sudah'
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%')) as tipe_swakelola_pagu,

		-- swakelola
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as swakelola_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'ya') as swakelola_pagu,

		-- total
		(SELECT COUNT(r.kode_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as total_paket,

		(SELECT SUM(r.pagu_rup) FROM tb_rup r
		WHERE r.id_satker = a.kode
		AND (left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') ) as total_pagu

		FROM tb_skpa a
		INNER JOIN tb_rup b ON b.id_satker = a.kode
		WHERE a.instansi != 'pusat'
		GROUP BY a.kode
		ORDER BY a.singkatan ASC ";
		return $this->db->query($str)->result();
	}

	public function get_jenis_pengadaan_list($metode,$tahun)
	{
		$str = "SELECT r.kode_rup, r.nama_paket, r.nama_satker, r.id_satker, r.pagu_rup, r.metode_pemilihan, mt.mtd_id, left(r.awal_pengadaan,4) as tahun_anggaran
		FROM tb_rup r, tb_metode mt
		WHERE r.metode_pemilihan = mt.mtd_nama AND
		-- (r.metode_pemilihan = '$metode') AND
		(left(r.awal_pekerjaan,4) = $tahun OR left(r.akhir_pekerjaan,4) = $tahun) AND (r.status_aktif = 'ya' AND r.status_umumkan = 'sudah')
		AND (r.sumber_dana LIKE '%APBD%' OR r.sumber_dana LIKE '%BLUD%') AND r.penyedia_didalam_swakelola = 'tidak' GROUP BY r.kode_rup";
		return $this->db->query($str)->result();
	}

	public function get_realisasi_tender_list()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT a.kode as kode_satker, b.kode_rup, c.kode_lelang, c.nama_paket, c.pagu, c.hps, a.nama, d.mtd_id, c.tahun as tahun_anggaran
			FROM tb_skpa a
      LEFT JOIN tb_rup b ON a.kode = b.id_satker
			LEFT JOIN tb_lelang c ON b.kode_rup = c.kode_rup
      LEFT JOIN tb_metode d ON b.metode_pemilihan = d.mtd_nama
      WHERE b.sumber_dana != 'APBN'
      AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
      OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
      OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')) GROUP BY b.kode_rup";

		return $this->db->query($str)->result();
	}

	public function get_realisasi_non_tender_list()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT a.kode as kode_satker, b.kode_rup, c.kode_lelang, c.nama_paket, c.pagu, c.hps, a.nama, d.mtd_id, c.tahun as tahun_anggaran
			FROM tb_skpa a
			LEFT JOIN tb_rup b ON a.kode = b.id_satker
			LEFT JOIN tb_non_tender c ON b.kode_rup = c.kode_rup
			LEFT JOIN tb_metode d ON b.metode_pemilihan = d.mtd_nama
			WHERE c.anggaran LIKE '%$tahun%' AND b.sumber_dana != 'APBN'
			AND ( (c.status_lelang = 0 AND c.status_aktif != 'non aktif')
			OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.status_aktif != 'non aktif')
			OR (c.status_lelang = 1 AND c.paket_status = 1) )";
		return $this->db->query($str)->result();
	}
}
