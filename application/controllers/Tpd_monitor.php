<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tpd_monitor extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('tpd_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');
		// if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('skpa'))
		// {
		// 	redirect('auth/login', 'refresh');
		// }
	}

	public function index()
	{
		// $data['inc'] = 'tpd_monitor';
    $tanggal = date('Y-m-d');
    $str = "SELECT * FROM tb_tpd_antrian WHERE (status = 'proses' OR status != '') AND tanggal = '$tanggal' ORDER BY tanggal_update DESC LIMIT 2";
    $result = $this->db->query($str)->result();

    $data['nomor_antrian'] = $result;
		$this->load->view('admin/tpd_monitor', $data);
	}

}
