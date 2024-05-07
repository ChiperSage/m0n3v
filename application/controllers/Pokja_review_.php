<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pokja_review extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('pokja_paket_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('pokja'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'pokja_review';
		$data['review'] = $this->pokja_paket_m->get_paket_review();
		$this->load->view('admin/index', $data);
	}

	public function review($id = 0, $id_sp = 0)
	{
		$this->pokja_paket_m->update_review($id, 1);
	}

	public function selesai($id = 0, $id_sp = 0)
	{
		$this->pokja_paket_m->update_review($id, 2);
	}
}
