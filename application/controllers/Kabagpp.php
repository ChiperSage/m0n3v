<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kabagpp extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('kabagpp_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('kabagpp'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$this->data['inc'] = 'home';
		$this->load->view('admin/index',$this->data);
	}

	public function sp()
	{
		$data['inc'] = 'kabagpp_sp';
		$data['sp'] = $this->kabagpp_m->get_sp();
		$this->load->view('admin/index', $data);
	}

	public function sp_anggota($sp)
	{
		$data['inc'] = 'kabagpp_anggota';
		$data['anggota'] = $this->kabagpp_m->get_anggota($sp);
		$this->load->view('admin/index', $data);
	}

	public function confirm($id, $val)
	{
		$this->kabagpp_m->sp_confirm($id,$val);
		redirect('kabagpp/sp');
	}

	public function paketbatal($id = 0)
	{
		$data['inc'] = 'kabagpp_paketbatal';
		$data['paket'] = $this->kabagpp_m->get_paketbatal();
		$this->load->view('admin/index', $data);

		if($id != 0 && is_numeric($id)){
			$this->db->delete('tb_batal',array('id'=>$id));
			redirect('kabagpp/paketbatal');
		}
	}
}
