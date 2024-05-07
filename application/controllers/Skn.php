<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skn extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skn_m'));

		$filter = array('pokja');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($filter))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$array = array();
		$data['inc'] = 'skn_table';
		$data['skn'] = $this->skn_m->get();
		$this->load->view('admin/index', $data);
	}

	public function cari()
	{
		$npwp = $this->input->post('cari_npwp');
		$result = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp));
		$count = $result->num_rows();
		$detail = $result->result();

		$sess_data = array();
		if($count == 1)
		{
			$sess_data = array('company_logged'=>true,'company_npwp'=>$detail->npwp);
		}else{
			$sess_data = array('company_logged'=>false);
		}
		$this->session->set_userdata($sess_data);
		redirect('skn');

	}

	public function paket_dropdown($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skn) AND id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/skn_paket_dropdown',$data);
	}

	public function paket_dropdown_new($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skn) AND id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/skn_paket_dropdown_new',$data);
	}

	public function create($id = 0)
	{
		if($this->input->post('sumber_dana') == 'apba'){
			$this->form_validation->set_rules('kode_rup','Paket','trim|required');
		}else{
			$this->form_validation->set_rules('nama_paket','Nama Paket','trim|required');
		}

		$this->form_validation->set_rules('npwp','Perusahaan','trim|required');
		$this->form_validation->set_rules('awal_pekerjaan','Awal Pekerjaan','trim|required');
		$this->form_validation->set_rules('akhir_pekerjaan','Akhir Pekerjaan','trim|required');
		// $this->form_validation->set_rules('lokasi','Lokasi','trim|required');
		$this->form_validation->set_rules('nilai_paket','Nilai Kontrak','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skn_form';
			$data['perusahaan_list'] = $this->skn_m->get_perusahaan_list();
			$data['satker_list'] = $this->skn_m->get_satker_list();
			$data['paket_list'] = $this->skn_m->get_paket_list();
			// $data['perusahaan_detail'] = $this->skn_m->detail_perusahaan();
			$this->load->view('admin/index', $data);
		}else{
			$this->skn_m->insert();
			redirect('skn');
		}
	}

	public function update($id = 0)
	{
		if($this->input->post('sumber_dana') == 'apba'){
			$this->form_validation->set_rules('kode_rup','Paket','trim');
		}else{
			// $this->form_validation->set_rules('nama_paket','Nama Paket','trim|required');
		}

		// $this->form_validation->set_rules('npwp','Perusahaan','trim|required');
		// $this->form_validation->set_rules('awal_pekerjaan','Awal Pekerjaan','trim|required');
		// $this->form_validation->set_rules('akhir_pekerjaan','Akhir Pekerjaan','trim|required');
		// $this->form_validation->set_rules('lokasi','Lokasi','trim|required');
		// $this->form_validation->set_rules('nilai_paket','Nilai Kontrak','trim|required');
		$this->form_validation->set_rules('nilai_progres','Nilai Kontrak','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skn_form';
			$data['perusahaan_list'] = $this->skn_m->get_perusahaan_list();
			$data['satker_list'] = $this->skn_m->get_satker_list();
			$data['paket_list'] = $this->skn_m->get_paket_list();

			$data['detail'] = $this->skn_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->skn_m->update($id);
			redirect('skn');
		}
	}

	public function ajax_rup($kode_rup)
	{
		$data = $this->db->get_where('tb_rup',array('kode_rup'=>$kode_rup))->row();
		echo json_encode($data);
	}

	public function delete($id = 0)
	{
		$this->skn_m->delete($id);
		redirect('skn');
	}

	public function valid_date($cur_date)
	{
		$test_date = $cur_date;
		$date = DateTime::createFromFormat('Y-m-d', $test_date);
		$date_errors = DateTime::getLastErrors();
		if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
				$this->form_validation->set_message('valid_date', 'The {field} not valid');
		}
  }
}
