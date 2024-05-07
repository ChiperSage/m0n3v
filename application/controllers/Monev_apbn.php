<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monev_apbn extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('monev_apbn_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'monev_realisasi_apbn_sp';
		$data['total'] = $this->monev_apbn_m->get_total();
		$data['lap'] = $this->monev_apbn_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_realisasi_apbn_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_realisasi_apbn_cetak', $data, false);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function view_rup()
	{
		$data['inc'] = 'monev_view_rup';
		$data['total'] = $this->monev_m->view_jenis_pengadaan_total();
		$data['barang'] = $this->monev_m->view_jenis_pengadaan('barang');
		$data['jasa'] = $this->monev_m->view_jenis_pengadaan('jasa lainnya');
		$data['konstruksi'] = $this->monev_m->view_jenis_pengadaan('pekerjaan konstruksi');
		$data['konsultansi'] = $this->monev_m->view_jenis_pengadaan('jasa konsultansi');
		$this->load->view('admin/index',$data);
	}

	public function view_rup_skpa()
	{
		$data['inc'] = 'monev_view_rup_skpa';
		$data['laporan'] = $this->monev_m->view_persatker_rup();
		$this->load->view('admin/index',$data);
	}

	public function view_realisasi_data_lelang()
	{
		$data['inc'] = 'monev_view_realisasi_data_lelang';
		$data['total'] = $this->monev_m->get_total();
		$data['subtotal'] = $this->monev_m->get_subtotal();
		$data['lap'] = $this->monev_m->get_laporan();
		$this->load->view('admin/index',$data);
	}

	public function view_realisasi_data_lelang_sp()
	{
		$data['inc'] = 'monev_view_realisasi_data_lelang_sp';
		$data['total'] = $this->monev_m->get_total();
		$data['subtotal'] = $this->monev_m->get_subtotal();
		$data['lap'] = $this->monev_m->get_laporan();
		$this->load->view('admin/index',$data);
	}

	public function view_realisasi_data_lelang_sp_cetak()
	{
		$data['inc'] = 'monev_view_realisasi_data_lelang_sp';
		$data['total'] = $this->monev_m->get_total();
		$data['subtotal'] = $this->monev_m->get_subtotal();
		$data['lap'] = $this->monev_m->get_laporan();
		$this->load->view('admin/index',$data);
	}

}
