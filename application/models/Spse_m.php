<?php
class Spse_m extends CI_Model {

	public function __construct(){

	}

	public function get_daftarpaket_menang()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT l.*, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi
			FROM tb_lelang_spse l, tb_skpa a, tb_panitia pt, tb_kategori kg, tb_metode mt, tb_pemenang pm
      	WHERE a.kode = l.rup_stk_id AND l.pnt_id = pt.pnt_id AND l.mtd_pemilihan = mt.mtd_id AND l.kode_lelang = pm.kode_lelang AND l.jenis_pengadaan = kg.kgr_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 GROUP BY l.kode_lelang";

      	// $str = "SELECT l.*, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi

		// 	FROM tb_lelang_spse l
		// 	LEFT JOIN tb_skpa a ON a.kode = l.stk_id
		// 	LEFT JOIN tb_panitia pt ON l.pnt_id = pt.pnt_id  
		// 	LEFT JOIN tb_kategori kg ON l.jenis_pengadaan = kg.kgr_id
		// 	LEFT JOIN tb_metode mt ON l.mtd_pemilihan = mt.mtd_id  
		// 	LEFT JOIN tb_pemenang pm ON l.kode_lelang = pm.kode_lelang 

		// 	WHERE l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 

		// 	GROUP BY l.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_daftarpaket_menang_complete()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT l.*, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi,
			(SELECT pg.pgr_nama FROM tb_pengurus pg WHERE pg.rkn_id = pm.rkn_id AND pg.pgr_jabatan = 'direktur' LIMIT 1) as pgr_nama
			FROM tb_lelang_spse l, tb_skpa a, tb_panitia pt, tb_kategori kg, tb_metode mt, tb_pemenang pm
			WHERE a.kode = l.rup_stk_id AND l.pnt_id = pt.pnt_id AND l.mtd_pemilihan = mt.mtd_id AND l.kode_lelang = pm.kode_lelang AND l.jenis_pengadaan = kg.kgr_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 GROUP BY l.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_daftarpaket_menang_complete2()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT l.*, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, r.tkdn,
			(SELECT pg.pgr_nama FROM tb_pengurus pg WHERE pg.rkn_id = pm.rkn_id AND pg.pgr_jabatan = 'direktur' LIMIT 1) as pgr_nama,
			(SELECT ll.kode_rup FROM tb_lelang ll WHERE ll.kode_lelang = l.kode_lelang LIMIT 1) as kode_rup
			FROM tb_lelang_spse l, tb_skpa a, tb_panitia pt, tb_kategori kg, tb_metode mt, tb_pemenang pm, tb_rup r
			WHERE a.kode = l.rup_stk_id AND l.pnt_id = pt.pnt_id AND l.mtd_pemilihan = mt.mtd_id AND l.kode_lelang = pm.kode_lelang AND l.jenis_pengadaan = kg.kgr_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND kode_rup = r.kode_rup GROUP BY l.kode_lelang";

		// $str = "SELECT l.*, ll.kode_rup, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, r.tkdn, pg.pgr_nama
		// 	FROM tb_lelang_spse l, tb_skpa a, tb_panitia pt, tb_kategori kg, tb_metode mt, tb_pemenang pm, tb_rup r, tb_lelang ll, tb_pengurus pg
		// 	WHERE a.kode = l.rup_stk_id AND l.pnt_id = pt.pnt_id AND l.mtd_pemilihan = mt.mtd_id AND l.kode_lelang = pm.kode_lelang AND l.kode_lelang = ll.kode_lelang AND r.kode_rup = ll.kode_rup AND l.jenis_pengadaan = kg.kgr_id AND l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND kode_rup = r.kode_rup 
		// 	AND (pg.rkn_id = pm.rkn_id AND pg.pgr_jabatan = 'direktur')
		// 	GROUP BY l.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function lama_waktu()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT ll.kode_rup, l.kode_lelang, l.nama_paket, l.hps, l.pagu, s.nama as nama_satker, m.mtd_nama, k.kgr_nama, l.menang,

			(SELECT t.tanggal_terima_dok FROM tb_tpd t WHERE t.kode_rup = ll.kode_rup) as tgl_dok_masuk,

			(SELECT sp.paket_tanggal FROM tb_sp_paket sp WHERE sp.kode_rup = ll.kode_rup) as tgl_sp,

			IF(l.mtd_pemilihan = 9, (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND j.tahapan = 'PEMASUKAN_PENAWARAN'), (SELECT j.tgl_mulai FROM tb_jadwal_spse j WHERE j.kode_lelang = l.kode_lelang AND (j.tahapan = 'PENGUMUMAN_PEMENANG_AKHIR' OR j.tahapan = 'PENETAPAN_DAN_PENGUMUMAN_PEMENANG_AKHIR') )) as tgl_mulai_pemenang

			FROM tb_lelang_spse l, tb_panitia pn, tb_skpa s, tb_metode m, tb_kategori k, tb_lelang ll

			WHERE l.ang_tahun = $tahun AND l.mtd_pemilihan = m.mtd_id AND l.jenis_pengadaan = k.kgr_id AND 

			l.pnt_id = pn.pnt_id AND l.rup_stk_id = s.kode AND (l.status_lelang = 1 AND l.paket_status = 1) AND l.status_aktif != 'non aktif' AND l.kode_lelang = ll.kode_lelang AND l.menang = 5 AND l.nama_paket NOT LIKE '%MYC%'";
		return $this->db->query($sql)->result();
	}

	public function get_daftarpaket_menangg_complete2()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT l.kode_lelang, ll.kode_rup, l.nama_paket, l.hps, l.pagu, l.*, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, r.tkdn, pg.pgr_nama,
			k.kontrak_no, k.kontrak_nilai, k.nilai_pdn, k.nama_ppk_kontrak, kt.indikator_penilaian, kt.nilai_indikator, kt.total_skors

			-- (SELECT pg.pgr_nama FROM tb_pengurus pg WHERE pg.rkn_id = pm.rkn_id AND pg.pgr_jabatan = 'direktur' LIMIT 1) as pgr_nama,
			-- (SELECT ll.kode_rup FROM tb_lelang ll WHERE ll.kode_lelang = l.kode_lelang LIMIT 1) as kode_rup

			FROM tb_lelang_spse l
			
			LEFT JOIN tb_lelang ll ON ll.kode_lelang = l.kode_lelang
			LEFT JOIN tb_skpa a ON l.rup_stk_id = a.kode
			LEFT JOIN tb_panitia pt ON l.pnt_id = pt.pnt_id
			LEFT JOIN tb_kategori kg ON l.jenis_pengadaan = kg.kgr_id
			LEFT JOIN tb_metode mt ON l.mtd_pemilihan = mt.mtd_id 
			LEFT JOIN tb_pemenang pm ON l.kode_lelang = pm.kode_lelang 
			LEFT JOIN tb_pengurus pg ON pg.rkn_id = pm.rkn_id
			LEFT JOIN tb_rup r ON ll.kode_rup = r.kode_rup
			LEFT JOIN tb_kontrak k ON l.kode_lelang = k.lls_id
			LEFT JOIN tb_kinerja_tender kt ON l.kode_lelang = kt.kd_tender

			WHERE (l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) OR 
			(l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 AND (pg.pgr_jabatan LIKE '%Direktur%' OR pg.pgr_jabatan LIKE '%Direktur Utama%'))
			GROUP BY l.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_list_silpa()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT l.kode_lelang, ll.kode_rup, l.nama_paket, l.hps, l.pagu, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, pm.rkn_nama as rkn_nama, pm.rkn_alamat, pm.rkn_telepon, pm.rkn_mobile_phone, pm.rkn_email, pm.rkn_npwp, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi, r.tkdn, pg.pgr_nama,
			k.kontrak_no, k.kontrak_nilai, k.nilai_pdn, k.nama_ppk_kontrak, kt.indikator_penilaian, kt.nilai_indikator, kt.total_skors

			-- (SELECT pg.pgr_nama FROM tb_pengurus pg WHERE pg.rkn_id = pm.rkn_id AND pg.pgr_jabatan = 'direktur' LIMIT 1) as pgr_nama,
			-- (SELECT ll.kode_rup FROM tb_lelang ll WHERE ll.kode_lelang = l.kode_lelang LIMIT 1) as kode_rup

			FROM tb_lelang_spse l
			
			LEFT JOIN tb_lelang ll ON ll.kode_lelang = l.kode_lelang
			LEFT JOIN tb_skpa a ON l.rup_stk_id = a.kode
			LEFT JOIN tb_panitia pt ON l.pnt_id = pt.pnt_id
			LEFT JOIN tb_kategori kg ON l.jenis_pengadaan = kg.kgr_id
			LEFT JOIN tb_metode mt ON l.mtd_pemilihan = mt.mtd_id 
			LEFT JOIN tb_pemenang pm ON l.kode_lelang = pm.kode_lelang 
			LEFT JOIN tb_pengurus pg ON pg.rkn_id = pm.rkn_id
			LEFT JOIN tb_rup r ON ll.kode_rup = r.kode_rup
			LEFT JOIN tb_kontrak k ON l.kode_lelang = k.lls_id
			LEFT JOIN tb_kinerja_tender kt ON l.kode_lelang = kt.kd_tender

			WHERE l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 GROUP BY l.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_rekap_silpa()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT tb_temp.*, sum(tb_temp.pagu) as pagu, count(tb_temp.kode_lelang) as jumlah_paket, SUM(tb_temp.kontrak_nilai1) as kontrak_nilai1, sum(tb_temp.silpa) as silpa

		FROM (

			SELECT l.kode_lelang, ll.kode_rup, l.pagu, l.hps, a.nama as nama_skpa, pt.pnt_nama as pokja, kg.kgr_nama as jenis_pengadaan, mt.mtd_nama as metode_pemilihan, r.tkdn, pg.pgr_nama, pm.psr_harga, pm.nev_harga_terkoreksi, pm.nev_harga_negosiasi,
			
			k.kontrak_no, k.kontrak_nilai, k.nilai_pdn, k.nama_ppk_kontrak, kt.indikator_penilaian, kt.nilai_indikator, kt.total_skors,

			COALESCE(k.kontrak_nilai, pm.nev_harga_negosiasi, pm.nev_harga_terkoreksi) as kontrak_nilai1, (l.pagu - COALESCE(k.kontrak_nilai, pm.nev_harga_negosiasi, pm.nev_harga_terkoreksi)) as silpa, a.kode, a.nama

			FROM tb_lelang_spse l
			
			LEFT JOIN tb_lelang ll ON ll.kode_lelang = l.kode_lelang
			LEFT JOIN tb_skpa a ON l.rup_stk_id = a.kode
			LEFT JOIN tb_panitia pt ON l.pnt_id = pt.pnt_id
			LEFT JOIN tb_kategori kg ON l.jenis_pengadaan = kg.kgr_id
			LEFT JOIN tb_metode mt ON l.mtd_pemilihan = mt.mtd_id 
			LEFT JOIN tb_pemenang pm ON l.kode_lelang = pm.kode_lelang 
			LEFT JOIN tb_pengurus pg ON pg.rkn_id = pm.rkn_id
			LEFT JOIN tb_rup r ON ll.kode_rup = r.kode_rup
			LEFT JOIN tb_kontrak k ON l.kode_lelang = k.lls_id
			LEFT JOIN tb_kinerja_tender kt ON l.kode_lelang = kt.kd_tender

			WHERE l.ang_tahun = '$tahun' AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5 GROUP BY l.kode_lelang) as tb_temp GROUP BY tb_temp.kode ORDER BY tb_temp.nama ASC";

		return $this->db->query($str)->result();
	}

}
