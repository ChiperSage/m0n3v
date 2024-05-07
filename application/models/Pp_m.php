<?php
class Pp_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$this->db->select('*');
		$this->db->from('tb_pp');
		$query = $this->db->get();
		return $query->result();
	}

}
