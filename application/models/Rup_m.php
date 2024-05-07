<?php
class Rup_m extends CI_Model {

	public function __construct(){

	}

	public function sync()
	{
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();

		$year = '2019';

		$filename = $json->url;
		// $filename = "https://inaproc.lkpp.go.id/isb/api/e3cd704d-9478-418a-ae1e-a4fe49d57115/json/23233435/PengumumanPenyediaDaerah1618/tipe/4:12/parameter/2019:D1";

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

		$connection = mysqli_connect("localhost","root","un1t4lbpbjplatinum","monev");

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

	public function get2($key)
	{
		$tahun = date('Y');

		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup');
		$this->db->from('tb_rup');
		$this->db->where("LEFT(awal_pekerjaan,4) = '$tahun'");
		$query = $this->db->get();
		$data['result'] = $query->result();

		// $this->db->select('kode_rup');
		// $this->db->from('tb_rup');
		// $this->db->where("LEFT(awal_pekerjaan,4) = '$tahun'");
		// $query = $this->db->get();
		// $data['count'] = $query->num_rows();

		$data['count'] = 0;
		return $data;
	}

	public function get($key)
	{

		// nilai default
		$limit = 20;
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}else{
			$tahun = date('Y');
		}

		$page = (isset($key['page'])) ? $key['page'] : 0 ;
		$str = array('tahun'=>$tahun);

		// parameter pencarian
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $key['tahun'];
			$str = "tahun = '$tahun'";
		}
		if(isset($key['search']) && $key['search'] != ''){
			$search = $key['search'];
			$str = "(kode_rup = '$search' OR nama_paket LIKE '%$search%')";
		}
		if(isset($key['search']) && $key['search'] != '' && isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
			$search = $key['search'];
			$str = "tahun = '$tahun' AND (kode_rup = '$search' OR nama_paket LIKE '%$search%')";
		}


		$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup,awal_pengadaan,sumber_dana,status_umumkan,status_aktif,metode_pemilihan,nama_kpa,tahun,penyedia_didalam_swakelola,tkdn');
		$this->db->from('tb_rup');
		$this->db->limit($limit, $page);
		$this->db->where($str);
		$query = $this->db->get();
		$data['result'] = $query->result();

		// tampil data untuk excel
		if(isset($_GET['action']) && $_GET['action'] == 'print')
		{
			$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup,awal_pengadaan,sumber_dana,status_umumkan,status_aktif,metode_pemilihan,nama_kpa,tahun,penyedia_didalam_swakelola,tkdn,umkm');
			$this->db->from('tb_rup');
			$this->db->where($str);
			$query = $this->db->get();
			$data['result'] = $query->result();
		}

		$this->db->select('kode_rup');
		$this->db->from('tb_rup');
		$this->db->where($str);
		$query = $this->db->get();
		$data['count'] = $query->num_rows();
		return $data;
	}

	public function get_swakelola($key)
	{
		$tahun = date('Y');

		$limit = 20;
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$page = (isset($key['page'])) ? $key['page'] : 0 ;
		$str = array();

		// parameter pencarian
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $key['tahun'];
			$str = "tahun_anggaran = '$tahun'";
		}
		if(isset($key['search']) && $key['search'] != ''){
			$search = $key['search'];
			$str = "(kode_rup = '$search' OR nama_paket LIKE '%$search%')";
		}
		if(isset($key['search']) && $key['search'] != '' && isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
			$search = $key['search'];
			$str = "tahun_anggaran = '$tahun' AND (kode_rup = '$search' OR nama_paket LIKE '%$search%')";
		}

		$this->db->select('*');
		$this->db->from('tb_rup_swakelola');
		$this->db->where($str);
		$this->db->limit($limit, $page);
		$query = $this->db->get();
		$data['result'] = $query->result();

		// tampil data untuk excel
		if(isset($_GET['action']) && $_GET['action'] == 'print')
		{
			$this->db->select('*');
			$this->db->from('tb_rup_swakelola');
			$this->db->where($str);
			$query = $this->db->get();
			$data['result'] = $query->result();
		}

		$this->db->select('kode_rup');
		$this->db->from('tb_rup_swakelola');
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

	public function get_pencatatan_swakelola()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT * FROM tb_pencatatan_swakelola ps 
		LEFT JOIN tb_rup_swakelola r ON ps.kode_rup = r.kode_rup
		WHERE left(ps.lls_dibuat_tanggal,4) = $tahun";
		return $this->db->query($sql)->result();
	}

	public function get_realisasi_pencatatan_swakelola()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT ps.*, r.tahun_anggaran FROM tb_realisasi_pencatatan_swakelola ps 
		LEFT JOIN tb_rup_swakelola r ON ps.kode_rup = r.kode_rup
		WHERE r.tahun_anggaran = $tahun";
		return $this->db->query($sql)->result();
	}

	public function get_total_realisasi_pencatatan_swakelola()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$sql = "SELECT ps.kode_rup, ps.kode_lelang, ps.nama_paket, ps.pagu, r.nama_satker, SUM(ps.rsk_nilai) as total_realisasi, r.tahun_anggaran
			FROM tb_realisasi_pencatatan_swakelola ps
			LEFT JOIN tb_rup_swakelola r ON ps.kode_rup = r.kode_rup
			WHERE r.tahun_anggaran = $tahun
			GROUP BY ps.kode_rup";

		return $this->db->query($sql)->result();
	}
}
