<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pokja extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('pokja_m','ion_auth_model'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('kabagpp'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'pokja_table';
		$data['pokja'] = $this->pokja_m->get();
		$this->load->view('admin/index', $data);
	}

	public function anggota()
	{
		$data['inc'] = 'pokja_table';
		$data['pokja'] = $this->pokja_m->get();
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('nip','NIP','trim|required|is_unique[tb_pokja.pokja_nip]');
		$this->form_validation->set_rules('nama','Nama','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pokja_form';
			$this->load->view('admin/index', $data);
		}else{
			$this->pokja_m->create();

			// tambah 1 pengguna
			$nama = explode(' ',$this->input->post('nama'));
			// $username = mysql_real_escape_string(strtolower($nama[0]));
			$username = str_replace(' ','',$this->input->post('nip'));
			$password = str_replace(' ','',$this->input->post('nip'));
    	$nip = $this->input->post('nip');
    	$email = $this->input->post('email');
    	$additional_data = array(
      	'first_name' => $this->input->post('nama'),
      	'nip' => $nip
      	);
    	$group = array('11'); // 11 = pokja.

			if(!$this->ion_auth->username_check($username) || !$this->ion_auth->nip_check($nip))
			{
    		$this->ion_auth->register($username, $password, $email, $additional_data, $group);
			}

			// upload foto disini
			$this->upload_foto($this->input->post('nip'));

			redirect('pokja');
		}
	}

	public function update($id = 0)
	{
		$this->_validate($id);

		$this->form_validation->set_rules('nip','NIP','trim|required');
		$this->form_validation->set_rules('nama','Nama','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pokja_form';
			$data['detail'] = $this->pokja_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->pokja_m->update($id);

			// upload foto disini
			$this->upload_foto($this->input->post('nip'));

			redirect('pokja');
		}
	}

	public function delete($id = 0)
	{
		$this->pokja_m->delete($id);
		redirect('pokja');
	}

	public function upload_foto($nip)
	{
		$name = str_replace(' ','-',$nip);

		$config['file_name'] = $name;
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'jpg|png|jpeg';

		// $config['max_size'] = 100;
		// $config['max_width'] = 1024;
		// $config['max_height'] = 768;

		$config['overwrite'] = true;
		$this->upload->initialize($config);
		if($this->upload->do_upload('userfile'))
		{

			$uploaded = $this->upload->data();
			$key = array('pokja_nip'=>$nip);
			$data['pokja_foto'] = '/uploads/' . $uploaded['file_name'];
			$this->db->update('tb_pokja',$data,$key);

		}
	}

	public function _validate($id)
	{
		if($id == 0 || !is_numeric($id)){
			redirect('pokja');
		}
	}
}
