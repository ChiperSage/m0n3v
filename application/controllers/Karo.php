<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Karo extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('karo_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('karo'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'home';
		$this->load->view('admin/index',$data);
	}

	public function sp()
	{
		$data['inc'] = 'karo_sp';
		$data['sp'] = $this->karo_m->get_sp();
		$this->load->view('admin/index', $data);
	}

	public function anggota($sp = 0)
	{
		if($this->_islocked($sp) == true){
			redirect('karo/sp');
		}

		$data['inc'] = 'karo_anggota';
		$data['anggota'] = $this->karo_m->get_anggota($sp);
		$this->load->view('admin/index', $data);
	}

	public function anggota_ganti($sp = 0)
	{
		if($this->_islocked($sp) == true){
			redirect('karo/sp');
		}

		$id = $this->input->post('id');
		$filter = array('anggota_sp'=>$sp,'id'=>$id);

		$data['id'] = $this->input->post('id');
		$data['anggota_keterangan'] = $this->input->post('keterangan');
		if($this->input->post('keterangan') == ''){
			$data['anggota_keterangan'] = '-';
		}

		$this->karo_m->update_sp_anggota($data, $filter);
		redirect('karo/anggota/'.$sp);
	}

	public function paket($sp = 0)
	{
		$data['inc'] = 'karo_paket';
		$data['paket'] = $this->karo_m->get_paket($sp);
		$this->load->view('admin/index', $data);
	}

	public function paketbatal($id = 0)
	{
		$data['inc'] = 'karo_paketbatal';
		$data['paket'] = $this->karo_m->get_paketbatal();
		$this->load->view('admin/index', $data);

		// if($id != 0 && is_numeric($id)){
		// 	$this->db->delete('tb_batal',array('id'=>$id));
		// 	redirect('kabagpp/paketbatal');
		// }
	}

	// karo konfirm
	public function confirm($id)
	{
		$this->karo_m->sp_confirm($id);
		redirect('karo/sp');
	}

	public function advokasi()
	{
		$this->load->library('pdfgenerator');
		
		$data['inc'] = 'karo_advokasi';
		$data['advokasi'] = $this->advokasi_m->get(array());

		if(isset($_GET['cetak']) && $_GET['cetak'] == 'pdf')
		{
			$html = $this->load->view('admin/karo_advokasi_cetak', $data, true);
			$filename = 'cetak_advokasi_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function _islocked($sp)
	{
		$result = $this->karo_m->count(array('sp_id'=>$sp,'sp_status'=>2));
		if($result == 1){
			return true;
		}else{
			return false;
		}
	}
}
