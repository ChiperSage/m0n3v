<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skp extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skp_m'));

		$filter = array('pokja');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($filter))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$array = array();
		$data['inc'] = 'skp_table';
		$data['skp'] = $this->skp_m->get();
		$this->load->view('admin/index', $data);
	}

	public function cari()
	{
		$npwp = $this->input->post('cari_npwp');
		$count = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp))->num_rows();

		$sess_data = array();
		if($count == 1){
			$sess_data = array('company_logged'=>2,'company_npwp'=>$npwp);
		}elseif($count == 0){
			$sess_data = array('company_logged'=>1);
		}
		$this->session->set_userdata($sess_data);
		redirect('skp');
	}

	public function paket_dropdown($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skp) AND id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();

		$data['paket_list'] = $paket;
		$this->load->view('admin/skp_paket_dropdown',$data);
	}

	public function paket_dropdown_new($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skp) AND id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();

		$data['paket_list'] = $paket;
		$this->load->view('admin/skp_paket_dropdown_new',$data);
	}

	public function create($id = 0)
	{
		$this->form_validation->set_rules('npwp','Nama Depan','trim|required');
		// $this->form_validation->set_rules('kode_rup','Nama Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skp_form';
			// $data['perusahaan_detail'] = $this->skp_m->detail_perusahaan();
			$data['perusahaan_list'] = $this->skp_m->get_perusahaan_list();
			$data['satker_list'] = $this->skp_m->get_satker_list();
			$data['paket_list'] = $this->skp_m->get_paket_list();

			$this->load->view('admin/index', $data);
		}else{
			$this->skp_m->insert();
			redirect('skp');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('npwp','Nama Depan','trim|required');
		// $this->form_validation->set_rules('kode_rup','Nama Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skp_form';
			// $data['perusahaan_detail'] = $this->skp_m->detail_perusahaan();
			$data['perusahaan_list'] = $this->skp_m->get_perusahaan_list();
			$data['satker_list'] = $this->skp_m->get_satker_list();
			$data['paket_list'] = $this->skp_m->get_paket_list();

			$data['detail'] = $this->skp_m->get_detail($id);

			$this->load->view('admin/index', $data);
		}else{
			$this->skp_m->update($id);
			redirect('skp');
		}
	}

	public function delete($id = 0)
	{
		$this->skp_m->delete($id);
		redirect('skp');
	}
}
