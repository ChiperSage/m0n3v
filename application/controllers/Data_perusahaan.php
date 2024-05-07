<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_perusahaan extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('perusahaan_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		if(!$this->ion_auth->logged_in() && !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'data_perusahaan_table';
		$data['perusahaan'] = $this->perusahaan_m->get();
		$this->load->view('admin/index', $data);
	}

	public function delete($id = 0)
	{
		$this->perusahaan_m->delete($id);
		redirect('data_perusahaan');
	}

	public function delete_all()
	{
		$this->db->truncate('tb_perusahaan');
		redirect('data_perusahaan');
	}

	// public function npwp_exist($id = 0)
	// {
	// 	$result = true;
	// 	$npwp = $this->input->post('npwp');
	// 	$result = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp,'id !='=>$id))->num_rows();
	// 	if($result == 1){
	// 		$this->form_validation->set_message('npwp_exist','NPWP already used');
	// 		return false;
	// 	}
	// }
}
