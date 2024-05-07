<?php
class Non_tender_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$filter = array('anggaran'=>$tahun);

		$this->db->select('*');
		$this->db->from('tb_non_tender');
		$this->db->where($filter);
		return $this->db->get()->result();
	}

	public function get_complete()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$filter = array('left(lls_dibuat_tanggal,4)'=>$tahun);

		$this->db->select('*');
		$this->db->from('tb_non_tender_complete');
		$this->db->where($filter);
		return $this->db->get()->result();
	}

	public function get_list_silpa()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT n.*, r.nama_satker as nama_skpa, np.psr_harga, np.psr_harga_terkoreksi, np.kontrak_nilai, s.spk_nilai
		FROM tb_non_tender_complete n
		LEFT JOIN tb_non_tender_pemenang np ON n.kode_lelang = np.kode_lelang
		LEFT JOIN tb_rup r ON n.kode_rup = r.kode_rup
		LEFT JOIN tb_spk s ON np.spk_id = s.spk_id
		WHERE n.anggaran = '$tahun' AND n.status_lelang = 1 AND n.paket_status = 1 AND n.menang = 5 GROUP BY n.kode_lelang";

		return $this->db->query($str)->result();
	}

	public function get_rekap_silpa()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT n.kode_rup, n.kode_lelang, n.nama_paket, r.nama_satker as nama_skpa, sum(n.pagu) as pagu, sum(n.hps) as hps, sum(np.psr_harga) as psr_harga, sum(np.psr_harga_terkoreksi) as psr_harga_terkoreksi, sum(np.kontrak_nilai) as kontrak_nilai, sum(s.spk_nilai) as spk_nilai
		FROM tb_non_tender_complete n
		LEFT JOIN tb_non_tender_pemenang np ON n.kode_lelang = np.kode_lelang
		LEFT JOIN tb_rup r ON n.kode_rup = r.kode_rup
		LEFT JOIN tb_spk s ON np.spk_id = s.spk_id
		WHERE n.anggaran = '$tahun' AND n.status_lelang = 1 AND n.paket_status = 1 AND n.menang = 5 GROUP BY r.id_satker";

		return $this->db->query($str)->result();
	}

	public function get_pencatatan_nontender()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT pn.*, r.nama_satker, k.kgr_nama, m.mtd_nama, (SELECT SUM(rpn.rsk_nilai) FROM tb_realisasi_pencatatan_nontender rpn WHERE pn.kode_rup = rpn.kode_rup) as total_realisasi
		FROM tb_pencatatan_nontender pn, tb_kategori k, tb_metode m, tb_rup r
		WHERE pn.kgr_id = k.kgr_id AND pn.mtd_pemilihan = m.mtd_id AND pn.kode_rup = r.kode_rup AND left(pn.lls_dibuat_tanggal,4) = $tahun
		GROUP BY pn.kode_rup";
		return $this->db->query($sql)->result();
	}

	public function get_total_realisasi_pencatatan_nontender()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT pn.*, r.nama_satker, k.kgr_nama, m.mtd_nama, (SELECT SUM(rpn.rsk_nilai) FROM tb_realisasi_pencatatan_nontender rpn WHERE pn.kode_rup = rpn.kode_rup) as total_realisasi, r.tahun
		FROM tb_pencatatan_nontender pn, tb_kategori k, tb_metode m, tb_rup r
		WHERE pn.kgr_id = k.kgr_id AND pn.mtd_pemilihan = m.mtd_id AND pn.kode_rup = r.kode_rup AND r.tahun = $tahun
		GROUP BY pn.kode_rup";
		return $this->db->query($sql)->result();
	}

	public function get_realisasi_pencatatan_nontender()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$filter = array('r.tahun'=>$tahun);

		$this->db->select('pn.*, r.*');
		$this->db->from('tb_realisasi_pencatatan_nontender pn');
		$this->db->join('tb_rup r', 'pn.kode_rup = r.kode_rup', 'left');
		$this->db->where($filter);
		return $this->db->get()->result();
	}

	// =====

	public function sync()
	{
		$json = $this->db->get_where('json',array('data'=>'non_tender'))->row();

		$year = '2019';

		$filename = $json->url;
		// $filename = "https://inaproc.lkpp.go.id/isb/api/e3cd704d-9478-418a-ae1e-a4fe49d57115/json/23233435/PengumumanPenyediaDaerah1618/tipe/4:12/parameter/2019:D1";

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

		$connection = mysqli_connect("localhost","root","","monev");

		mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND akhir_pekerjaan='$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?,
							kode_rup=?, nama_satker=?, nama_paket=?, program=?, kode_string_program=?, kegiatan=?, kode_string_kegiatan=?, volume=?, pagu_rup=?, mak=?,
							lokasi=?, detail_lokasi=?, sumber_dana=?, metode_pemilihan=?, jenis_pengadaan=?, pagu_perjenis_pengadaan=?, awal_pengadaan=?,
							akhir_pengadaan=?, awal_pekerjaan=?, akhir_pekerjaan=?, tanggal_kebutuhan=?, spesifikasi=?, id_swakelola=?, nama_kpa=?, penyedia_didalam_swakelola=?,
							tkdn=?, pradipa=?, status_aktif=?, status_umumkan=?, id_client=?
		            ON DUPLICATE KEY UPDATE
								tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi),
									nama_satker=values(nama_satker),nama_paket=values(nama_paket),program=values(program),kode_string_program=values(kode_string_program),kegiatan=values(kegiatan),kode_string_kegiatan=values(kode_string_kegiatan),volume=values(volume),pagu_rup=values(pagu_rup),
									mak=values(mak),lokasi=values(lokasi),detail_lokasi=values(detail_lokasi),sumber_dana=values(sumber_dana),metode_pemilihan=values(metode_pemilihan),jenis_pengadaan=values(jenis_pengadaan),pagu_perjenis_pengadaan=values(pagu_perjenis_pengadaan),awal_pengadaan=values(awal_pengadaan),
									akhir_pengadaan=values(akhir_pengadaan),awal_pekerjaan=values(awal_pekerjaan),akhir_pekerjaan=values(akhir_pekerjaan),tanggal_kebutuhan=values(tanggal_kebutuhan),spesifikasi=values(spesifikasi),id_swakelola=values(id_swakelola),nama_kpa=values(nama_kpa),penyedia_didalam_swakelola=values(penyedia_didalam_swakelola),
									tkdn=values(tkdn),pradipa=values(pradipa),status_aktif=values(status_aktif),status_umumkan=values(status_umumkan),id_client=values(id_client)";
		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "isisssisssssssissssssissssssssssssss", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi,
					$val->kode_rup, $val->nama_satker, $val->nama_paket, $val->program, $val->kode_string_program, $val->kegiatan, $val->kode_string_kegiatan, $val->volume, $val->pagu_rup,
					$val->mak, $val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->metode_pemilihan, $val->jenis_pengadaan, $val->pagu_perjenis_pengadaan, $val->awal_pengadaan,
					$val->akhir_pengadaan, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->tanggal_kebutuhan, $val->spesifikasi, $val->id_swakelola, $val->nama_kpa, $val->penyedia_didalam_swakelola,
					$val->tkdn, $val->pradipa, $val->status_aktif, $val->status_umumkan, $val->id_client);
		        mysqli_stmt_execute($stmt);
		}
	}

	public function get_jadwal()
	{
		$this->db->select('*');
		$this->db->from('tb_jadwal');
		// $this->db->where($str);
		// $this->db->limit(20, $page);
		return $this->db->get()->result();
	}

	public function get2($key)
	{
		$page = (isset($key['page'])) ? $key['page'] : 0 ;
		$str = array();

		if(isset($key['search'])){
			$search = $key['search'];
			$str = "kode_lelang = '$search' OR nama_paket LIKE '% $search %'";
		}

		$this->db->select('*');
		$this->db->from('tb_lelang');
		$this->db->where($str);
		$this->db->limit(20, $page);
		$query = $this->db->get();
		$data['result'] = $query->result();

		$this->db->select('kode_lelang');
		$this->db->from('tb_lelang');
		$this->db->where($str);
		$query = $this->db->get();
		$data['count'] = $query->num_rows();
		return $data;
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_rup', $key);
		return $query->row();
	}

	public function get_json($var)
	{
		return $this->db->get_where('json',array('data'=>$var))->row();
	}

	public function create()
	{
		$data['tanggal_terakhir_di_update'] = $this->input->post('tanggal_update');
		$data['nama_paket'] = $this->input->post('nama_paket');
		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');

		$this->db->insert('tb_rup', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		$key = array('id' => $id);
		$data['tanggal_terakhir_di_update'] = $this->input->post('tanggal_update');
		$data['nama_paket'] = $this->input->post('nama_paket');
		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');

		$this->db->update('tb_rup', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('id'=>$id);
		$this->db->delete('tb_rup',$filter);
	}

	public function get_daftarpaket($var) 
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		if($var == 'belumtayang'){
			$sql = "SELECT t.*, r.jenis_pengadaan, r.nama_satker, r.metode_pemilihan FROM tb_rup r
		    LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
		    WHERE t.anggaran LIKE '%$tahun%' AND t.status_lelang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'";
		}elseif($var == 'tayang'){
			$sql = "SELECT t.*, r.jenis_pengadaan, r.nama_satker, r.metode_pemilihan FROM tb_rup r
			   LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
			   WHERE t.anggaran LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 0 AND t.status_aktif != 'non aktif' AND r.sumber_dana != 'APBN'";
		}elseif($var == 'menang'){
			$sql = "SELECT t.*, r.jenis_pengadaan, r.nama_satker, r.metode_pemilihan FROM tb_rup r
		      LEFT JOIN tb_non_tender_complete t ON r.kode_rup = t.kode_rup
		      WHERE t.anggaran LIKE '%$tahun%' AND t.status_lelang = 1 AND t.menang = 5 AND r.sumber_dana != 'APBN' AND t.status_aktif != 'non aktif'";
		}
		return $this->db->query($sql)->result();
	}

	public function get_datapemenang()
	{	
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT p.*, r.nama_satker, r.metode_pemilihan, r.nama_kpa, k.kgr_nama, pg.pgr_nama 
		FROM tb_non_tender_pemenang p
		LEFT JOIN tb_rup r ON p.rup_id = r.kode_rup
		LEFT JOIN tb_kategori k ON p.jenis_pengadaan = k.kgr_id
		LEFT JOIN tb_pengurus pg ON p.rkn_id = pg.rkn_id
		WHERE r.tahun = $tahun AND pg.pgr_jabatan = 'direktur'
		GROUP BY p.kode_lelang
		ORDER BY r.nama_satker ASC";
		return $this->db->query($sql)->result();
	}

	public function get_datapemenang_spk()
	{	
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT p.*, r.nama_satker, r.nama_kpa, r.metode_pemilihan, k.kgr_nama, s.kontrak_id, s.spk_no, s.spk_tgl, s.spk_nilai, s.spk_norekening, s.alasanubah_spk_nilai, s.spk_wakil_penyedia, s.spk_nama_bank, s.alasanubah_spk_nilai, s.spk_wakil_penyedia, s.spk_jabatan_wakil, s.nilai_pdn, r.tkdn, kn.indikator_penilaian, kn.nilai_indikator, kn.total_skors
		FROM tb_non_tender_pemenang p
		LEFT JOIN tb_rup r ON p.rup_id = r.kode_rup 
		LEFT JOIN tb_kategori k ON p.jenis_pengadaan = k.kgr_id 
		LEFT JOIN tb_spk s ON p.spk_id = s.spk_id  
		LEFT JOIN tb_kinerja_nontender kn ON p.kode_lelang = kn.kd_nontender  
		WHERE r.tahun = $tahun
		GROUP BY p.kode_lelang";
		return $this->db->query($sql)->result();
	}
}
