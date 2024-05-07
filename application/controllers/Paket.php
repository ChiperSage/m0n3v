<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paket extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('paket_m'));

		// $this->load->database();
		// $this->load->library(array('ion_auth','form_validation'));
		// $this->load->helper(array('url','language'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'paket_table';
		$data['user'] = $this->paket_m->get_user();
		$data['paket'] = $this->paket_m->get(array());
		$this->load->view('admin/index',$data);
	}

	public function create()
	{
		$this->form_validation->set_rules('nama','Paket Pekerjaan','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'paket_form';
			$data['user_list'] = $this->paket_m->get_user();
			$this->load->view('admin/index', $data);
		}else{
			$this->paket_m->create();
			redirect('paket');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('nama','Paket Pekerjaan','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'paket_form';
			$data['user_list'] = $this->paket_m->get_user();
			$data['paket_detail'] = $this->paket_m->get_detail(array('paket_id'=>$id));
			$this->load->view('admin/index', $data);
		}else{
			$this->paket_m->update($id);
			redirect('paket');
		}
	}

	public function delete($id = 0)
	{
		$this->paket_m->delete($id);
		redirect('paket');
	}

}
