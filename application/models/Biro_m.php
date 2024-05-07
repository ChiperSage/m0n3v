<?php
class Biro_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from('tb_biro');
		// $this->db->where($key);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_biro', $key);
		return $query->row();
	}

	public function create()
	{
		$data['biro_nama'] = $this->input->post('nama');
		$data['biro_keterangan'] = $this->input->post('keterangan');
		$this->db->insert('tb_biro', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		$key = array('biro_id' => $id);
    $data['biro_nama'] = $this->input->post('nama');
		$data['biro_keterangan'] = $this->input->post('keterangan');
		$this->db->update('tb_biro', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('biro_id'=>$id);
		$this->db->delete('tb_biro',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}
}
