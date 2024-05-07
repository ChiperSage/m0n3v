<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cetak extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model(array('monev_m','monev_perhari_m'));

		$this->form_validation->set_error_delimiters(
      $this->config->item('error_start_delimiter', 'ion_auth'),
      $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$this->load->library('pdfgenerator');

		$group = array('monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

  public function index()
  {

  }

	public function rup()
	{
		$data['total'] = $this->monev_m->view_jenis_pengadaan_total();
		$data['barang'] = $this->monev_m->view_jenis_pengadaan('barang');
		$data['jasa'] = $this->monev_m->view_jenis_pengadaan('jasa lainnya');
		$data['konstruksi'] = $this->monev_m->view_jenis_pengadaan('pekerjaan konstruksi');
		$data['konsultansi'] = $this->monev_m->view_jenis_pengadaan('jasa konsultansi');

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){

			$this->load->view('admin/cetak_rup', $data);

			// $html = $this->load->view('admin/cetak_rup', $data, true);
			// $filename = 'cetak_rup_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/cetak_rup', $data, false);
		}

	}

	public function rup_skpa()
	{
		$data['laporan'] = $this->monev_m->view_persatker_rup();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){

			$html = $this->load->view('admin/cetak_rup_skpa', $data);

			// $html = $this->load->view('admin/cetak_rup_skpa', $data, true);
			// $filename = 'cetak_rup_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');

		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){

			$this->load->view('admin/cetak_rup_skpa', $data, false);

		}
	}

	public function tender()
	{
		$data['total'] = $this->monev_m->get_total();
		// $data['subtotal'] = $this->monev_m->get_subtotal();
		$data['lap'] = $this->monev_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){

			$this->load->view('admin/cetak_tender', $data);
			$filename = 'cetak_rup_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');

		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){

			$this->load->view('admin/cetak_tender', $data, false);

		}

	}

	public function tender_sp()
	{
		$data['total'] = $this->monev_m->get_total();
		$data['lap'] = $this->monev_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){

			$this->load->view('admin/cetak_tender_sp', $data);

			// $html = $this->load->view('admin/cetak_tender_sp', $data, true);
			// $filename = 'cetak_rup_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');

		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){

			$this->load->view('admin/cetak_tender_sp', $data, false);

		}

	}

	// daftar paket per jenis (barang, jasa, dll)
	public function daftar_paket($jenis = '')
	{
		$data['paket'] = $this->monev_m->get_daftar_paket($jenis);
		$this->load->view('admin/cetak_daftar_paket', $data);

		// $html = $this->load->view('admin/cetak_daftar_paket', $data, true);
		// $filename = 'cetak_rup_'.time();
		// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
	}

	public function review()
	{
		$data['review'] = $this->monev_m->get_paket_review();

		$this->load->view('admin/cetak_paket_review', $data, false);
		$filename = 'cetak_review_'.time();
		// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
	}
}
