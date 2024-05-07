<?php
class Json_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		return $this->db->get_where('json',array())->result();
	}

	public function get_detail($id)
	{
		$query = $this->db->get_where('json', array('id'=>$id));
		return $query->row();
	}

	public function get_rup_luar()
	{
		$filter = array();
		$query = $this->db->get_where('tb_rup_luar', $filter);
		return $query->result_array();
	}

	public function update($id)
	{
		$data['data'] = $this->input->post('data');
		$data['url'] = $this->input->post('url');
		$data['tahun'] = $this->input->post('tahun');
		if($id == 0){
			$this->db->insert('json', $data);
		}else{
			$filter = array('id'=>$id);
			$this->db->update('json', $data, $filter);
		}
	}

	public function delete($id)
	{
		$filter = array('id'=>$id);
		$this->db->delete('json',$filter);
	}
}
