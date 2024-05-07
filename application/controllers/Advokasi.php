<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Advokasi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['advokasi_m','monev_m']);

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		// $this->load->library('pdfgenerator');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group(['advokasi','monev']))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function evaluasi_kegiatan_pbj()
	{
		$bttm = 'evaluasi';
		$data['daftar_paket'] = $this->monev_m->get_daftar_paket_spse($bttm);

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('advokasi/evaluasi_kegiatan_pbj_ex',$data);
		}else{
			$data['inc'] = 'evaluasi_kegiatan_pbj_tb';
			$this->load->view('advokasi/index',$data);
		}
	}

	public function index()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}
		$array = array('left(tanggal,4)'=>$tahun);
		$data['inc'] = 'advokasi_table';
		$data['advokasi'] = $this->advokasi_m->get($array);
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$this->form_validation->set_rules('paket','Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'advokasi_form';
			$data['paket_list'] = $this->advokasi_m->get_paket(date('Y'));
			$data['uraian_list'] = $this->advokasi_m->get_uraian();
			$this->load->view('admin/index', $data);
		}else{
			$this->advokasi_m->create();
			redirect('advokasi');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('paket','Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'advokasi_form';
			$data['paket_list'] = $this->advokasi_m->get_paket(date('Y'));
			$data['uraian_list'] = $this->advokasi_m->get_uraian();
			$data['detail'] = $this->advokasi_m->get_detail(array('advokasi_id' => $id));
			$this->load->view('admin/index', $data);
		}else{
			$this->advokasi_m->update($id);
			redirect('advokasi');
		}
	}

	public function ajax_load_paket($tahun)
	{
		$paket_list = $this->advokasi_m->get_paket($tahun);	

		$field = array();
		foreach ($paket_list as $value) {
			$field[$value->kode_rup] = $value->nama_paket.' - '.$value->kode_rup;
		}
        echo form_dropdown('paket', $field, $paket, 'class="form-control" id="mySelect3"');
	}

	public function delete($id = 0)
	{
		$this->advokasi_m->delete($id);
		redirect('advokasi');
	}

	public function cetak()
	{
		$array = array();
		$data['advokasi'] = $this->advokasi_m->get($array);
		//
		$html = $this->load->view('admin/advokasi_cetak', $data, true);
		$filename = 'cetak_advokasi_'.time();
		$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
	}

	public function datalembaga()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}
		$array = array();
		$data['inc'] = 'datalembaga_tb';
		$data['datalembaga'] = $this->advokasi_m->get_uraian($array);
		$this->load->view('advokasi/index', $data);
	}

	public function datalembagac()
	{
		$this->form_validation->set_rules('uraian','Uraian','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'datalembaga_fm';
			$this->load->view('advokasi/index', $data);
		}else{
			$this->advokasi_m->datalembagac();
			redirect('advokasi/datalembaga');
		}
	}

	public function datalembagau($id = 0)
	{
		$this->form_validation->set_rules('uraian','Uraian','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'datalembaga_fm';
			$data['detail'] = $this->advokasi_m->get_detail_uraian($id);
			$this->load->view('advokasi/index', $data);
		}else{
			$this->advokasi_m->datalembagau($id);
			redirect('advokasi/datalembaga');
		}
	}

	public function datalembagad($id = 0)
	{
		$key = array('id'=>$id);
		$this->db->delete('tb_uraianadvokasi',$key);
		redirect('advokasi/datalembaga');
	}

	public function database_vendor()
	{
		$data['list_rkn'] = $this->advokasi_m->get_database_vendor(); 
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('advokasi/database_vendor_ex',$data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('advokasi/database_vendor_pdf',$data);
		}else{
			$data['inc'] = 'database_vendor_tb';
			$this->load->view('advokasi/index', $data);
		}
	}

	public function data_pengurus()
	{
		$data['inc'] = 'data_pengurus_tb';
		$data['list_pengurus'] = $this->advokasi_m->get_data_pengurus(); 
		$this->load->view('advokasi/index', $data);
	}
}
