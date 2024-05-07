<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Json extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model(array('json_m'));

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
		$data['inc'] = 'json_table';
		$data['json'] = $this->json_m->get();
		$this->load->view('admin/index',$data);
	}

	public function create()
	{
		$this->form_validation->set_rules('data','Jenis Data','trim|is_unique[json.data]');
		$this->form_validation->set_rules('url','Url','trim');
		// $this->form_validation->set_rules('lelang','Lelang Json','trim');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'json_form';
			$this->load->view('admin/index',$data);
		}else{
			$this->json_m->update($id);
			redirect('json');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('data','Jenis Data','trim');
		$this->form_validation->set_rules('url','Url','trim');
		// $this->form_validation->set_rules('lelang','Lelang Json','trim');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'json_form';
			$data['detail'] = $this->json_m->get_detail($id);
			$this->load->view('admin/index',$data);
		}else{
			$this->json_m->update($id);
			redirect('json');
		}
	}

	public function delete($id = 0)
	{
		$this->json_m->delete($id);
		redirect('json');
	}
}
