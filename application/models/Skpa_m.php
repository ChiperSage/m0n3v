<?php
class Skpa_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from('tb_skpa a');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($id)
	{
		$this->db->select('*');
		$this->db->from('tb_skpa a');
		$this->db->where(array('id'=>$id));
		$query = $this->db->get();
		return $query->row();
	}

	public function insert()
	{
			$data['kode'] = $this->input->post('kode');
			$data['nama'] = $this->input->post('nama');
			$data['singkatan'] = $this->input->post('singkatan');
			$this->db->insert('tb_skpa', $data);
			$result = $this->db->affected_rows();
			if($result = 1)
			{
					$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menambah data</div>');
			}
	}

	public function update($id)
	{
		if($this->kode_exist($id) == 0){
			$key = array('id'=>$id);
			$data['kode'] = $this->input->post('kode');
	    $data['nama'] = $this->input->post('nama');
			$data['singkatan'] = $this->input->post('singkatan');
			$this->db->update('tb_skpa', $data, $key);
			$result = $this->db->affected_rows();
			if($result = 1)
			{
				$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengupdate data</div>');
			}
		}
	}

	public function kode_exist($id)
	{
		$kode = $this->input->post('kode');
		$result = $this->db->get_where('tb_skpa',array('kode'=>$kode,'id !='=>$id))->num_rows();
		if($result == 1){
			$this->session->set_flashdata('msg','<div class="callout callout-warning">Duplikasi kode satker.</div>');
		}
		return $result;
	}

	public function delete($id)
	{
		$this->db->delete('tb_skpa',array('id'=>$id));
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menghapus data</div>');
		}
	}
}
