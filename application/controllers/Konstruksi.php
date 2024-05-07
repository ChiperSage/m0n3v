<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Konstruksi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('konstruksi_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		if( !$this->ion_auth->logged_in() || !$this->ion_auth->in_group(array('konstruksi','barang_jasa','monev')) )
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'home';
		$this->load->view('admin/index',$data);
	}

	public function sp()
	{
		$data['inc'] = 'konstruksi_sp';
		$data['sp'] = $this->sp_m->get();
		$this->load->view('admin/index', $data);
	}

	public function paket()
	{
		$data['inc'] = 'konstruksi_paket';
		$data['paket'] = $this->konstruksi_m->get_paket();
		$this->load->view('admin/index',$data);
	}

	public function advokasi()
	{
		$data['inc'] = 'konstruksi_advokasi';
		$this->load->view('admin/index',$data);
	}

	public function grafik()
	{
		$data['inc'] = 'konstruksi_grafik';
		$this->load->view('admin/index',$data);
	}
}
