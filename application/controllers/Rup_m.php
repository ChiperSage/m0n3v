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

	public function get($key)
	{
		$tahun = date('Y');

		$page = (isset($key['page'])) ? $key['page'] : 0 ;
		$str = array('left(awal_pengadaan,4)'=>$tahun);

		if(isset($key['search'])){
			$search = $key['search'];
			$str = "kode_rup = '$search' OR nama_paket LIKE '%$search%'";
		}
		
		if(isset($key['tahun'])){
			$tahun = $key['tahun'];
			$str = "left(awal_pengadaan,4) = '$tahun'";
		}

		$this->db->select('kode_rup,nama_paket,jenis_pengadaan,nama_satker,pagu_rup');
		$this->db->from('tb_rup');
		$this->db->where($str);
		$this->db->limit(20, $page);
		$query = $this->db->get();
		$data['result'] = $query->result();

		$this->db->select('kode_rup');
		$this->db->from('tb_rup');
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
}
