<?php
class Paket_m extends CI_Model {

	public function __construct(){

	}

	public function get($key)
	{
		$this->db->select('a.*, b.first_name, b.last_name,');
		$this->db->from('tb_paket a');
		$this->db->join('users b','a.paket_pejabat = b.id','left');
		$this->db->where($key);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_user()
	{
		$this->db->select('*');
		$this->db->from('users');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_paket', $key);
		return $query->row();
	}

	public function create()
	{
		$data['paket_nama'] = $this->input->post('nama');
		$data['paket_bidang'] = $this->input->post('bidang');
		$data['paket_pagu'] = $this->input->post('pagu');
		$data['paket_metode'] = $this->input->post('metode');
		$data['paket_lokasi'] = $this->input->post('lokasi');
		$data['paket_status'] = $this->input->post('status');
		$data['paket_pejabat'] = $this->input->post('pejabat');
		$data['paket_keterangan'] = $this->input->post('keterangan');
		$this->db->insert('tb_paket', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		$key = array('paket_id' => $id);
		$data['paket_nama'] = $this->input->post('nama');
		$data['paket_bidang'] = $this->input->post('bidang');
		$data['paket_pagu'] = $this->input->post('pagu');
		$data['paket_metode'] = $this->input->post('metode');
		$data['paket_lokasi'] = $this->input->post('lokasi');
		$data['paket_status'] = $this->input->post('status');
		$data['paket_pejabat'] = $this->input->post('pejabat');
		$data['paket_keterangan'] = $this->input->post('keterangan');
		$this->db->update('tb_paket', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('paket_id'=>$id);
		$this->db->delete('tb_paket',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}
}
