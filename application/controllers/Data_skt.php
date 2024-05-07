<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_skt extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skt_m'));

		$this->form_validation->set_error_delimiters(
		$this->config->item('error_start_delimiter', 'ion_auth'),
		$this->config->item('error_end_delimiter', 'ion_auth'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'data_skt_table2';
		$data['skt'] = $this->skt_m->get();
		$this->load->view('admin/index', $data);
	}

	public function delete($id = 0)
	{
		$this->skt_m->delete($id);
		redirect('data_skt');
	}

	public function delete_all()
	{
		$this->db->truncate('tb_skt');
		redirect('data_skt');
	}
}
