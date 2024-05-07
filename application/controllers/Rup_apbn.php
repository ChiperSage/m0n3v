<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rup_apbn extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('rup_apbn_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index($page = 0)
	{
		$data['first_url'] = '';
		$data['suffix'] = '';

		if(isset($_GET['search']))
		{
			$data['first_url'] = base_url().'rup_apbn/index/0/?search='.$_GET['search'];
			$data['suffix'] = '/?search='.$_GET['search'];

			$config['search'] = $_GET['search'];
		}

		$config['page'] = $page;
		$rup = $this->rup_apbn_m->get($config);

		$data['base_url'] = base_url('rup_apbn/index');
		$data['total_rows'] = $rup['count'];
		$data['per_page'] = 20;
		$data['uri_segment'] = 3;

		$data['inc'] = 'rup_apbn_table';
		$data['rup'] = $rup['result'];
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required|is_unique[tb_rup.kode_rup]');
		$this->form_validation->set_rules('nama_paket','Nama Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'rup_apbn_form';
			$data['satker'] = $this->rup_apbn_m->get_satker_list();
			$this->load->view('admin/index', $data);
		}else{
			$this->rup_apbn_m->create();
			redirect('rup_apbn');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('nama_paket','Nama Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'rup_apbn_form';
			$data['satker'] = $this->rup_apbn_m->get_satker_list();
			$data['rup_detail'] = $this->rup_apbn_m->get_detail(array('kode_rup'=>$id));
			$this->load->view('admin/index', $data);
		}else{
			$this->rup_apbn_m->update($id);
			redirect('rup_apbn');
		}
	}

	public function delete($id = 0)
	{
		$this->rup_apbn_m->delete($id);
		redirect('rup_apbn');
	}
}
