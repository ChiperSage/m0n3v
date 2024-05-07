<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settpd extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    	$this->load->model(array('settpd_m'));

		$this->form_validation->set_error_delimiters(
      	$this->config->item('error_start_delimiter', 'ion_auth'),
      	$this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() && !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

  	public function index()
	{
		$data['inc'] = 'settpd_tb';
		$data['data'] = $this->settpd_m->get_all();
		$this->load->view('monev/index',$data);
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('set','Set','trim');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'settpd_fm';
			$data['detail'] = $this->settpd_m->get_detail($id);
			$this->load->view('monev/index',$data);
		}else{
			$this->settpd_m->update($id);
			redirect('settpd');
		}
	}
}
