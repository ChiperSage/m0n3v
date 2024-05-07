<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spse extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('Spse_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('monev','karo','kabagpp','barang_jasa','konstruksi','staff_monev','admin_karo','user','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		
	}

	public function daftarpaket_menang()
	{	
		$data['inc'] = 'spse_daftarpaket_menang';
		$data['page_title'] = 'Menang';
		$data['daftar_paket'] = $this->Spse_m->get_daftarpaket_menang();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/spse_daftarpaket_menang_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function daftarpaket_menang_complete()
	{	
		$data['inc'] = 'spse_daftarpaket_menang_complete';
		$data['page_title'] = 'Menang : Complete';
		$data['daftar_paket'] = $this->Spse_m->get_daftarpaket_menang_complete();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/spse_daftarpaket_menang_complete_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function daftarpaket_menang_complete2()
	{	
		$this->load->helper('kinerja_tender');

		$data['inc'] = 'spse_daftarpaket_menang_complete2';
		$data['page_title'] = 'Menang : Complete 2';
		$data['daftar_paket'] = $this->Spse_m->get_daftarpaket_menangg_complete2();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/spse_daftarpaket_menang_complete_ex2',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function lama_waktu()
	{
		$data['inc'] = 'spse_lamawaktu';
		$data['page_title'] = 'Monev : Lama Waktu';
		$data['daftar_paket'] = $this->Spse_m->lama_waktu();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/spse_lamawaktu_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function list_silpa()
	{
		$data['inc'] = 'silpa_list';
		$data['page_title'] = 'List : SILPA';
		$data['daftar_paket'] = $this->Spse_m->get_list_silpa();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/silpa_list_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function rekap_silpa()
	{
		$data['inc'] = 'silpa_rekap';
		$data['page_title'] = 'List : SILPA';
		$data['daftar_paket'] = $this->Spse_m->get_rekap_silpa();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/silpa_rekap_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

}
