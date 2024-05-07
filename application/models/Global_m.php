<?php
class Global_m extends CI_Model {

	public function __construct(){

	}

	public function get_nama_skpa($kode)
	{
		$this->db->select('nama');
		$this->db->from('tb_skpa');
		$this->db->where(array('kode'=>$kode));
		$query = $this->db->get();
		return $query->row('nama');
	}
}
