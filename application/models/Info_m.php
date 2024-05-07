<?php
class Info_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from('tb_info');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_info', $key);
		return $query->row();
	}

	public function create()
	{
		$data['info'] = $this->input->post('info');
		$data['status'] = $this->input->post('status');
		$this->db->insert('tb_info', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		$key = array('id' => $id);
		$data['info'] = $this->input->post('info');
		$data['status'] = $this->input->post('status');
		$this->db->update('tb_info', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('id'=>$id);
		$this->db->delete('tb_info',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}
}
