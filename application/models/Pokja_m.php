<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pokja_m extends CI_Model{

    function __construct(){

    }

    public function get()
    {
      $this->db->select('*');
      $this->db->from('tb_pokja');
  		$query = $this->db->get();
  		return $query->result();
    }

  	public function get_detail($id)
  	{
      $key = array('pokja_id'=>$id);
  		return $this->db->get_where('tb_pokja', $key)->row();
  	}

    public function isunique($id, $nip, $method)
    {
      return false;

      $this->db->select('COUNT(pokja_nip)');
      $this->db->where(array('pokja_nip'=>$nip));
      $result = $this->db->get('tb_pokja')->num_rows();
      if($result > 0)
      {
        return true;
      }

      if($method == 'update')
      {
        $this->db->select('COUNT(pokja_nip)');
        $this->db->where(array('pokja_nip'=>$nip,'pokja_id !='=>$id));
        $result = $this->db->get('tb_pokja')->num_rows();
        if($result > 0)
        {
          return true;
        }
      }
    }

    public function get_sp()
  	{
  		$this->db->select('*,b.paket_nama');
      $this->db->from('tb_sp a');
      $this->db->join('tb_paket b','a.sp_paket = b.paket_id','left');
  		$query = $this->db->get();
  		return $query->result();
  	}

  	public function create()
  	{
  		$data['pokja_nip'] = $this->input->post('nip');
      $data['pokja_nama'] = $this->input->post('nama');
      $data['pokja_email'] = $this->input->post('email');
      $data['pokja_pangkat'] = $this->input->post('pangkat');
      $data['pokja_golongan'] = $this->input->post('golongan');
      $data['pokja_tahun'] = $this->input->post('tahun');
      $data['pokja_status'] = $this->input->post('status');

  		$this->db->insert('tb_pokja', $data);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

  	public function update($id)
  	{
  		$key = array('pokja_id' => $id);
      $data['pokja_nip'] = $this->input->post('nip');
      $data['pokja_nama'] = $this->input->post('nama');
      $data['pokja_email'] = $this->input->post('email');
      $data['pokja_pangkat'] = $this->input->post('pangkat');
      $data['pokja_golongan'] = $this->input->post('golongan');
      $data['pokja_tahun'] = $this->input->post('tahun');
      $data['pokja_status'] = $this->input->post('status');

  		$this->db->update('tb_pokja', $data, $key);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

  	public function delete($id)
    {
  		$filter = array('pokja_id'=>$id);
      $this->db->delete('tb_pokja',$filter);
      $this->db->affected_rows();
      if($result = 1){
    		$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
    	}
  	}
}
