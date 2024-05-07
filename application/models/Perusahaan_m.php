<?php
class Perusahaan_m extends CI_Model {

	public function __construct(){

	}

	public function cari_perusahaan()
	{
		$npwp = $this->input->post('cari_npwp');
		$filter = array('npwp'=>$npwp);
		$result = $this->db->get_where('tb_perusahaan', $filter)->num_rows();
		if($result == 1){
			$sess_data = array('sess_npwp'=>$npwp);
		}else{
			$sess_data = array('sess_npwp'=>0);
		}
		$this->session->set_userdata($sess_data);
	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from('tb_perusahaan');
		// $this->db->where($key);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_perusahaan', $key);
		return $query->row();
	}

	public function create()
	{
		$data['npwp'] = $this->input->post('npwp');
		$data['nama_perusahaan'] = $this->input->post('nama');
		$data['ekuitas'] = $this->input->post('ekuitas');
		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
		$data['kualifikasi'] = $this->input->post('kualifikasi');
		$data['keterangan'] = $this->input->post('keterangan');
		$this->db->insert('tb_perusahaan', $data);

		$data['nip'] = $this->get_cur_nip();
		$data['tanggal'] = date('Y-m-d H:i:s');
		$data['aksi'] = 'insert';
		$this->db->insert('temp_perusahaan', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		if($this->is_used($id) == false){

		$key = array('id' => $id);
		$data['npwp'] = $this->input->post('npwp');
		$data['nama_perusahaan'] = $this->input->post('nama');
		$data['ekuitas'] = $this->input->post('ekuitas');
		$data['jenis_pengadaan'] = $this->input->post('jenis_pengadaan');
		$data['kualifikasi'] = $this->input->post('kualifikasi');
		$data['keterangan'] = $this->input->post('keterangan');
		$this->db->update('tb_perusahaan', $data, $key);

		$data['nip'] = $this->get_cur_nip();
		$data['tanggal'] = date('Y-m-d H:i:s');
		$data['aksi'] = 'udpdate';
		$this->db->insert('temp_perusahaan', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}}
	}

	public function delete($id)
	{
		$filter = array('id'=>$id);
		$this->db->delete('tb_perusahaan',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function is_used($id)
	{
		$npwp = $this->input->post('npwp');
		$count = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp,'id !='=>$id))->num_rows();
		if($count == 1){
			$this->session->set_flashdata('msg','<div class="callout callout-danger">NPWP tersebut sudah di gunakan</div>');
			return true;
		}else{
			return false;
		}
	}

	public function get_cur_nip()
	{
		$id = $this->session->userdata('user_id');
		$user = $this->db->get_where('users',array('id'=>$id))->row();
		if($user->nip != ''){
			return $user->nip;
		}else{
			return $user->id;
		}
	}
}
