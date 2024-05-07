<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barangjasa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    	$this->load->model('barangjasa_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('barang_jasa','konstruksi','monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'home';
		$this->load->view('admin/index', $data);
	}

	public function kontak()
	{
		$data['inc'] = 'tpd_kontak_tb';
		$data['kontak'] = $this->barangjasa_m->get_kontak_tpd();
		$this->load->view('kasubbag/index', $data);	
	}

	public function sp()
	{
		$data['inc'] = 'barangjasa_sp';
		$data['sp'] = $this->barangjasa_m->get_sp();
		$this->load->view('admin/index', $data);
	}

	public function _autoinc()
	{
		$this->db->select('max(sp_nomor) as sp_nomor');
		$nomor = $this->db->get('tb_sp')->row('sp_nomor');
		return ltrim((1 . $nomor) + 1,1);
	}

	public function sp_create()
	{
		$this->form_validation->set_rules('tanggal','Tanggal','trim|required');
		$this->form_validation->set_rules('nomor','Nomor','trim|required');
		$this->form_validation->set_rules('kelompok','Kelompok','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'barangjasa_sp_form';
			$data['autonomor'] = $this->_autoinc();
			$this->load->view('admin/index', $data);
		}else{
			$this->barangjasa_m->create_sp();
			redirect('barangjasa');
		}
	}

	public function sp_update($id = 0)
	{
		$this->_validate($id);

		$this->form_validation->set_rules('tanggal','Tanggal','trim|required');
		$this->form_validation->set_rules('nomor','Nomor','trim|required');
		$this->form_validation->set_rules('kelompok','Kelompok','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'barangjasa_sp_form';
			$data['detail'] = $this->barangjasa_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->barangjasa_m->update_sp($id);
			redirect('barangjasa');
		}
	}

	public function sp_delete($id = 0)
	{
		$this->_validate($id);

		$this->barangjasa_m->delete_sp($id);
		redirect('barangjasa');
	}

	public function _validate($id)
	{
		if(!is_numeric($id))
		{
			redirect('barangjasa');
		}

		if($id == 0)
		{
			redirect('barangjasa');
		}
	}
	// END CRUD

	// sp anggota
	public function anggota($sp = 0, $id = 0)
	{
		$this->_validate($sp);

		if(is_numeric($sp) != 1 || !isset($sp)){
			redirect('sp');
		}

		$this->form_validation->set_rules('nip','NIP','trim|required');
		$this->form_validation->set_rules('jabatan','Jabatan','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'sp_anggota';
			$data['sp'] = $this->barangjasa_m->get_detail($sp);
			$data['pokja_list'] = $this->barangjasa_m->get_pokja_list();
			if($id > 0){
				$data['detail'] = $this->barangjasa_m->get_anggota_detail($id);
			}
			$data['anggota'] = $this->barangjasa_m->get_anggota($sp);
			$this->load->view('admin/index', $data);
		}else{
			$this->barangjasa_m->update_anggota($sp, $id);
			redirect('barangjasa/spanggota/'.$sp);
		}
	}

	public function delete_anggota($sp = 0, $id = 0)
	{
		$this->barangjasa_m->delete_anggota($sp, $id);
		redirect('barangjasa/spanggota/'.$sp);
	}
	// sp anggota end

	public function paket()
	{
		$data['inc'] = 'barangjasa_paket';
		$data['paket'] = $this->barangjasa_m->get_paket();
		$this->load->view('admin/index',$data);
	}

  public function advokasi()
  {
    $data['inc'] = 'barangjasa_advokasi';
		$this->load->view('admin/index', $data);
  }

  public function laporan()
  {
    $data['inc'] = 'barangjasa_laporan';
		$this->load->view('admin/index', $data);
  }

  public function grafik()
  {
    $data['inc'] = 'barangjasa_grafik';
		$this->load->view('admin/index', $data);
  }

}
