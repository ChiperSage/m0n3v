<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skt extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skt_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

			$filter = array('pokja');
			if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($filter))
			{
				redirect('auth/login', 'refresh');
			}
	}

	public function index()
	{
		$data['skt'] = $this->skt_m->get();
		
		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/skt_table_cetak', $data);
		}else{
			$data['inc'] = 'skt_table';
			$this->load->view('admin/index', $data);
		}
	}

	public function create()
	{
		$this->form_validation->set_rules('no_registrasi','No. Reg','trim|required');
		$this->form_validation->set_rules('npwp_pribadi','NPWP Pribadi','trim|required|numeric|max_length[15]');
		$this->form_validation->set_rules('npwp','Perusahaan','trim|required');
		$this->form_validation->set_rules('nama','Nama','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skt_form';
			$data['perusahaan_list'] = $this->skt_m->perusahaan_list();
			$data['satker_list'] = $this->skt_m->get_satker_list();
			$this->load->view('admin/index', $data);
		}else{
			$this->skt_m->create();
			redirect('skt');
		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('no_registrasi','No. Reg','trim|required');
		$this->form_validation->set_rules('npwp_pribadi','NPWP Pribadi','trim|required|numeric|max_length[15]');
		$this->form_validation->set_rules('npwp','Perusahaan','trim|required');
		$this->form_validation->set_rules('nama','Nama','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'skt_form';
			$data['perusahaan_list'] = $this->skt_m->perusahaan_list();
			$data['satker_list'] = $this->skt_m->get_satker_list();
			$data['detail'] = $this->skt_m->get_detail(array('id'=>$id));
			$this->load->view('admin/index', $data);
		}else{
			$this->skt_m->update($id);
			redirect('skt');
		}
	}

	public function paket_dropdown($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana LIKE '%APBD%' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();

		$data['paket_list'] = $paket;
		$this->load->view('admin/skt_paket_dropdown',$data);
	}

	public function paket_dropdown_new($id = 0)
	{
		$tahun = date('Y');

		$str = "SELECT kode_rup, nama_paket, pagu_rup FROM tb_rup
		WHERE id_satker = $id
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(awal_pekerjaan,4) = $tahun AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana LIKE '%APBD%' OR sumber_dana = 'BLUD') ORDER BY nama_paket ASC";
		$paket = $this->db->query($str)->result();

		$data['paket_list'] = $paket;
		$this->load->view('admin/skt_paket_dropdown_new',$data);
	}

	public function delete($id = 0)
	{
		$this->skt_m->delete($id);
		redirect('skt');
	}
}
