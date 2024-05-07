<?php
class Rup_apbn_m extends CI_Model {

	public function __construct(){

	}

	public function get($key)
	{
		$page = (isset($key['page'])) ? $key['page'] : 0 ;
		$str = "sumber_dana = 'APBN' AND status_aktif = 'ya' AND status_umumkan = 'sudah'";

		// if(isset($key['search'])){
		// 	$search = $key['search'];
		// 	$str = "CONCAT(kode_rup, nama_satker, nama_paket) LIKE '%$search%' AND sumber_dana = 'APBN'";
		// }

		$this->db->select('*');
		$this->db->from('tb_rup');
		$this->db->where($str);
		// $this->db->limit(20, $page);
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

	public function get_satker_list()
	{
		$this->db->order_by('nama ASC');
		return $this->db->get('tb_skpa')->result();
	}

	public function create()
	{
		$nama_satker = $this->db->get_where('tb_skpa',array('kode'=>$this->input->post('id_satker')))->row('nama');

		$data['tanggal_terakhir_di_update'] = date('Y');
		$data['kode_kldi'] = $this->input->post('kode_kldi');
		$data['id_satker'] = $this->input->post('id_satker');
		$data['kode_satker_asli'] = $this->input->post('kode_satker_asli');
		$data['jenis'] = $this->input->post('jenis');

		$data['kldi'] = $this->input->post('kldi');
		$data['kode_rup'] = $this->input->post('kode_rup');
		$data['nama_satker'] = $nama_satker;
		$data['nama_paket'] = $this->input->post('nama_paket');
		$data['program'] = $this->input->post('program');

		$data['kode_string_program'] = $this->input->post('kode_string_program');
		$data['kegiatan'] = $this->input->post('kegiatan');
		$data['kode_string_kegiatan'] = $this->input->post('kode_string_kegiatan');
		$data['volume'] = $this->input->post('volume');
		$data['pagu_rup'] = $this->input->post('pagu_rup');

		$data['mak'] = $this->input->post('mak');
		$data['lokasi'] = $this->input->post('lokasi');
		$data['detail_lokasi'] = $this->input->post('detail_lokasi');
		$data['sumber_dana'] = 'APBN';
		$data['metode_pemilihan'] = $this->input->post('metode_pemilihan');

		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
		$data['pagu_perjenis_pengadaan'] = $this->input->post('pagu_perjenis_pengadaan');
		$data['awal_pengadaan'] = $this->input->post('awal_pengadaan');
		$data['akhir_pengadaan'] = $this->input->post('akhir_pengadaan');
		$data['awal_pekerjaan'] = $this->input->post('awal_pekerjaan');
		$data['akhir_pekerjaan'] = $this->input->post('akhir_pekerjaan');

		$data['tanggal_kebutuhan'] = $this->input->post('tanggal_kebutuhan');
		$data['spesifikasi'] = $this->input->post('spesifikasi');
		$data['id_swakelola'] = $this->input->post('id_swakelola');
		$data['nama_kpa'] = $this->input->post('nama_kpa');
		$data['penyedia_didalam_swakelola'] = $this->input->post('penyedia_didalam_swakelola');

		$data['tkdn'] = $this->input->post('tkdn');
		$data['pradipa'] = $this->input->post('pradipa');
		$data['status_aktif'] = $this->input->post('status_aktif');
		$data['status_umumkan'] = $this->input->post('status_umumkan');
		$data['id_client'] = $this->input->post('id_client');

		$this->db->insert('tb_rup', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Tambah Berhasil</div>');
		}
	}

	public function update($id)
	{
		$nama_satker = $this->db->get_where('tb_skpa',array('kode'=>$this->input->post('id_satker')))->row('nama');

		$key = array('kode_rup'=>$id);

		$data['tanggal_terakhir_di_update'] = date('Y');
		$data['kode_kldi'] = $this->input->post('kode_kldi');
		$data['id_satker'] = $this->input->post('id_satker');
		$data['kode_satker_asli'] = $this->input->post('kode_satker_asli');
		$data['jenis'] = $this->input->post('jenis');

		$data['kldi'] = $this->input->post('kldi');
		// $data['kode_rup'] = $this->input->post('kode_rup');
		$data['nama_satker'] = $nama_satker;
		$data['nama_paket'] = $this->input->post('nama_paket');
		$data['program'] = $this->input->post('program');

		$data['kode_string_program'] = $this->input->post('kode_string_program');
		$data['kegiatan'] = $this->input->post('kegiatan');
		$data['kode_string_kegiatan'] = $this->input->post('kode_string_kegiatan');
		$data['volume'] = $this->input->post('volume');
		$data['pagu_rup'] = $this->input->post('pagu_rup');

		$data['mak'] = $this->input->post('mak');
		$data['lokasi'] = $this->input->post('lokasi');
		$data['detail_lokasi'] = $this->input->post('detail_lokasi');
		$data['sumber_dana'] = 'APBN';
		$data['metode_pemilihan'] = $this->input->post('metode_pemilihan');

		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
		$data['pagu_perjenis_pengadaan'] = $this->input->post('pagu_perjenis_pengadaan');
		$data['awal_pengadaan'] = $this->input->post('awal_pengadaan');
		$data['akhir_pengadaan'] = $this->input->post('akhir_pengadaan');
		$data['awal_pekerjaan'] = $this->input->post('awal_pekerjaan');
		$data['akhir_pekerjaan'] = $this->input->post('akhir_pekerjaan');

		$data['tanggal_kebutuhan'] = $this->input->post('tanggal_kebutuhan');
		$data['spesifikasi'] = $this->input->post('spesifikasi');
		$data['id_swakelola'] = $this->input->post('id_swakelola');
		$data['nama_kpa'] = $this->input->post('nama_kpa');
		$data['penyedia_didalam_swakelola'] = $this->input->post('penyedia_didalam_swakelola');

		$data['tkdn'] = $this->input->post('tkdn');
		$data['pradipa'] = $this->input->post('pradipa');
		$data['status_aktif'] = $this->input->post('status_aktif');
		$data['status_umumkan'] = $this->input->post('status_umumkan');
		$data['id_client'] = $this->input->post('id_client');

		$this->db->update('tb_rup', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Update Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('kode_rup'=>$id,'sumber_dana'=>'APBN');
		$this->db->delete('tb_rup',$filter);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Hapus Berhasil</div>');
		}
	}
}
