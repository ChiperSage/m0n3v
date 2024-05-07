<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Info extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('info_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('monev'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'info_table';
		$data['info'] = $this->info_m->get();
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('info','Info','trim|required');
		$this->form_validation->set_rules('status','Status','trim');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'info_form';
			$this->load->view('admin/index', $data);
		}else{
			$this->info_m->create();
			redirect('info');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('info','Info','trim|required');
		$this->form_validation->set_rules('status','Status','trim');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'info_form';
			$data['detail'] = $this->info_m->get_detail(array('id'=>$id));
			$this->load->view('admin/index', $data);
		}else{
			$this->info_m->update($id);
			redirect('info');
		}
	}

	public function delete($id = 0)
	{
		$this->db->delete('tb_info',array('id'=>$id));
		redirect('info');
	}
}
