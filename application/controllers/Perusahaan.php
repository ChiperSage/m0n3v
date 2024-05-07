<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perusahaan extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('perusahaan_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('pokja'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'perusahaan_table';
		$data['perusahaan'] = $this->perusahaan_m->get();
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('npwp','NPWP','trim|required|is_unique[tb_perusahaan.npwp]');
		$this->form_validation->set_rules('nama','Nama','trim|required');
		$this->form_validation->set_rules('ekuitas','Ekuitas','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'perusahaan_form';
			$data['perusahaan'] = $this->perusahaan_m->get();
			$this->load->view('admin/index', $data);
		}else{
			$this->perusahaan_m->create();
			redirect('perusahaan');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('npwp','NPWP','trim|required');
		$this->form_validation->set_rules('nama','Nama','trim|required');
		$this->form_validation->set_rules('ekuitas','Ekuitas','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'perusahaan_form';
			$data['detail'] = $this->perusahaan_m->get_detail(array('id'=>$id));
			$data['perusahaan'] = $this->perusahaan_m->get();
			$this->load->view('admin/index', $data);
		}else{
			$this->perusahaan_m->update($id);
			redirect('perusahaan');
		}
	}

	public function delete($id = 0)
	{
		$this->perusahaan_m->delete($id);
		redirect('perusahaan');
	}

	public function npwp_exist($id)
	{
		$npwp = $this->input->post('npwp');
		$result = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp,'id !='=>$id))->num_rows();
		if($result = 0){
			return true;
		}else{
			$this->form_validation->set_message('npwp_exist','The %s field already used');
			return false;
		}
	}
}
