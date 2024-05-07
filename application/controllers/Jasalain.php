<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jasalain extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model('jasalain_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('jasa_lain'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'home';
		$this->load->view('admin/index', $data);
	}

	public function sp()
	{
		$data['inc'] = 'jasalain_sp';
		$data['sp'] = $this->jasalain_m->get_sp();
		$this->load->view('admin/index', $data);
	}

	public function paket()
	{
		$data['inc'] = 'jasalain_paket';
		$this->load->view('admin/index', $data);
	}

	public function advokasi()
	{
		$data['inc'] = 'jasalain_advokasi';
		$this->load->view('admin/index', $data);
	}

	public function laporan()
	{
		$data['inc'] = 'jasalain_laporan';
		$this->load->view('admin/index', $data);
	}

	public function grafik()
	{
		$data['inc'] = 'jasalain_grafik';
		$this->load->view('admin/index', $data);
	}

}
