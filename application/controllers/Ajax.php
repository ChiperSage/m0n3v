<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Ajax_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev','ppk','staff_monev','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		
	}

	public function detail_history($kode_rup)
	{
		$sql = "SELECT id FROM tb_review_paket WHERE kode_rup = $kode_rup ORDER BY id DESC";
		$result = $this->db->query($sql)->result();
		//print_r($result);

		foreach ($result as $val) {
			echo anchor('monev/review_history_cetak/'.$val->id,$val->id,'target="_blank"').'<br/>';
		}

	}

}
