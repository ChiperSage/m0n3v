<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skpa extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skpa_m'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$array = array();
		$data['inc'] = 'skpa_table';
		$data['skpa'] = $this->skpa_m->get();
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('kode','Kode','trim|required|is_unique[tb_skpa.kode]');
		$this->form_validation->set_rules('nama','Nama','trim|required');
		$this->form_validation->set_rules('singkatan','Singkatan','trim|required|is_unique[tb_skpa.singkatan]');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skpa_form';
			$this->load->view('admin/index', $data);
		}else{
			$this->skpa_m->insert();
			$this->add_user();
			redirect('skpa');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('kode','Kode','trim|required');
		$this->form_validation->set_rules('nama','Nama','trim|required');
		$this->form_validation->set_rules('singkatan','Singkatan','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skpa_form';
			$data['detail'] = $this->skpa_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->skpa_m->update($id);
			redirect('skpa');
		}
	}

	public function add_user()
	{
		$username = $this->input->post('singkatan');
		$password = $this->input->post('kode');
		$email = $this->input->post('singkatan').'@bpbj.com';

		$additional_data = array('first_name' => $this->input->post('nama'),
			'id_satker' => $this->input->post('kode'));
		$group = array('13'); // 13 = skpa.

		if(!$this->ion_auth->username_check($username))
		{
			$this->ion_auth->register($username, $password, $email, $additional_data, $group);
		}
	}

	public function delete($id = 0)
	{
		$this->skpa_m->delete($id);
		redirect('skpa');
	}
}
