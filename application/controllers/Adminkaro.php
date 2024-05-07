<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Adminkaro extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model(array('adminkaro_m'));

		$this->form_validation->set_error_delimiters(
      $this->config->item('error_start_delimiter', 'ion_auth'),
      $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$groups = array('admin_karo','karo','kabagpp','monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

  public function index()
	{
			$data['inc'] = 'home';
			$this->load->view('admin/index',$data);
	}

	public function pokja_daftar()
	{
		$data['inc'] = 'Adminkaro_pokja_daftar';
		$data['pokja'] = $this->adminkaro_m->get_pokja_daftar();
		$this->load->view('admin/index',$data);
	}

	public function tender_offline()
	{
		// $data['inc'] = 'tender_offline';
		// $data['pencarian'] = $this->adminkaro_m->rup_cari();
		// $this->load->view('adminkaro/index',$data);

		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required');
		$this->form_validation->set_rules('hps','HPS','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'tender_offline';
			$data['pencarian'] = $this->adminkaro_m->rup_cari();
			$this->load->view('adminkaro/index',$data);
		}else{
			$this->adminkaro_m->insert_tender_offline();
			redirect('adminkaro/tender_offline');
		}
	}

	public function pokja_penerima()
	{
		$data['inc'] = 'Adminkaro_pokja_penerima';
		$data['pokja'] = $this->adminkaro_m->get_pokja_penerima();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/adminkaro_pokja_penerima_ex',$data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function list_paket_sp()
	{
		$data['inc'] = 'list_paket_sp';
		$data['paket'] = $this->adminkaro_m->list_paket_sp();
		$this->load->view('adminkaro/index',$data);
	}
}
