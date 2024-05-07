<?php
class Epurchasing_m extends CI_Model {

	public function __construct(){

	}

	public function get_data()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT pe.kd_rup, k.nama_instansi_katalog, pe.nama_paket, r.pagu_rup, r.nama_satker, pe.no_paket, pe.kuantitas, pk.nama_penyedia, pk.penyedia_umkm, pe.harga_satuan, pe.total_harga, pe.tahun_anggaran, k.jenis_katalog, k.nama_komoditas, pe.status_paket, pe.paket_status_str, pe.tanggal_buat_paket, pe.tanggal_edit_paket, r.tkdn

		FROM tb_paketepurchasing pe
		
		LEFT JOIN (SELECT kode_rup, nama_satker, pagu_rup, tkdn FROM tb_rup WHERE tahun = $tahun) AS r ON pe.kd_rup = r.kode_rup

		LEFT JOIN (SELECT kd_komoditas, jenis_katalog, nama_komoditas, nama_instansi_katalog FROM tb_komoditas) AS k ON pe.kd_komoditas = k.kd_komoditas

		LEFT JOIN (SELECT kd_penyedia, nama_penyedia, penyedia_umkm FROM tb_penyedia_ekatalog) AS pk ON pe.kd_penyedia = pk.kd_penyedia
		
		WHERE pe.tahun_anggaran = $tahun";

		return $this->db->query($sql)->result();
	}

	public function get_realisasi()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT s.kode, s.nama as nama_satker, 

		count(data_rup.kode_rup) as total_paket, sum(data_rup.pagu_rup) as total_pagu,

		lokal_paket.total_paket as paket_lokal, 
		lokal.total_harga as realisasi_lokal, 
		
		sektoral_paket.total_paket as paket_sektoral, 
		sektoral.total_harga as realisasi_sektoral, 

		nasional_paket.total_paket as paket_nasional,
		nasional.total_harga as realisasi_nasional

		FROM tb_skpa s

		LEFT JOIN (SELECT id_satker, kode_rup, pagu_rup FROM tb_rup WHERE tahun = $tahun AND metode_pemilihan LIKE '%e-Purchasing%' 
		AND (status_aktif = 'ya' AND status_umumkan = 'sudah')) as data_rup ON s.kode = data_rup.id_satker

		LEFT JOIN (SELECT COUNT(distinct a.kd_rup) as total_paket, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Lokal' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) lokal_paket ON s.kode = lokal_paket.satker_id

		LEFT JOIN (SELECT sum(a.total_harga) as total_harga, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Lokal' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) lokal ON s.kode = lokal.satker_id

		LEFT JOIN (SELECT COUNT(distinct a.kd_rup) as total_paket, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Sektoral' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) sektoral_paket ON s.kode = sektoral_paket.satker_id

		LEFT JOIN (SELECT sum(a.total_harga) as total_harga, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Sektoral' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) sektoral ON s.kode = sektoral.satker_id

		LEFT JOIN (SELECT COUNT(distinct a.kd_rup) as total_paket, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Nasional' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) nasional_paket ON s.kode = nasional_paket.satker_id

		LEFT JOIN (SELECT sum(a.total_harga) as total_harga, a.satker_id FROM tb_paketepurchasing a, tb_komoditas b WHERE a.kd_komoditas = b.kd_komoditas AND b.jenis_katalog = 'Nasional' AND a.tahun_anggaran = $tahun GROUP BY a.satker_id) nasional ON s.kode = nasional.satker_id

		-- LEFT JOIN (SELECT a.pagu_rup, a.id_satker FROM tb_rup a WHERE a.tahun = $tahun AND a.status_aktif = 'ya' AND a.status_umumkan = 'sudah') r ON s.kode = r.id_satker

		WHERE s.instansi != 'pusat' AND s.nama NOT LIKE '%biro%'
						
		GROUP BY s.kode
		ORDER BY s.nama ASC";

		return $this->db->query($sql)->result();
	}

	public function total_pagu()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT id_satker, sum(pagu_rup) as pagu_rup FROM tb_rup WHERE tahun = $tahun AND metode_pemilihan LIKE '%e-Purchasing%' GROUP BY id_satker";

		$pagu_skpa = array();
		$result = $this->db->query($sql)->result();
		foreach ($result as $val)
		{
			$pagu_skpa[$val->id_satker] = $val->pagu_rup;
		}
		return $pagu_skpa;

	}

}
