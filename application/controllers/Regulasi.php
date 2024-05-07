<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Regulasi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('regulasi_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('pokja','barang_jasa','konstruksi');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'regulasi_table';
		$data['regulasi'] = $this->regulasi_m->get();
		$this->load->view('admin/index',$data);
	}

	public function upload()
	{
		$this->form_validation->set_rules('nama','Nama File','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'regulasi_form';
			$this->load->view('admin/index',$data);
		}else{

			$file = $this->_upload();
			if( $file != false ){
					$this->regulasi_m->insert($file);
			}
			redirect('regulasi');
		}
	}

	public function download()
	{

	}

	public function _upload()
	{
		$config['file_name'] = $this->input->post('nama');
		$config['upload_path'] = './uploads/regulasi/';
		$config['allowed_types'] = '*';
		// $config['max_size'] = 100;
		// $config['max_width'] = 1024;
		// $config['max_height'] = 768;
		// $this->load->library('upload', $config);

		$this->upload->initialize($config);

		if(!$this->upload->do_upload('userfile'))
		{
				// $error = array('error' => $this->upload->display_errors());
				// $this->load->view('upload_form', $error);
				return false;
		}else{
				// $data = array('upload_data' => $this->upload->data());
				//$this->load->view('upload_success', $data);
				// return $this->upload->data('');
				return $this->upload->data('file_name');
		}
	}

}
