<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model(array('json_m'));

		$this->form_validation->set_error_delimiters(
      $this->config->item('error_start_delimiter', 'ion_auth'),
      $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');

		// if(!$this->ion_auth->logged_in() && !$this->ion_auth->in_group('admin'))
		// {
		// 	redirect('auth/login', 'refresh');
		// }
	}

  public function index()
	{

	}

	public function rup()
	{
		$data = $this->json_m->get_rup_luar();
		echo json_encode($data);
	}
}
