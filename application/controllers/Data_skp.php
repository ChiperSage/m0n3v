<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_skp extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skp_m'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$array = array();
		$data['inc'] = 'data_skp_table';
		$data['skp'] = $this->skp_m->get();
		$this->load->view('admin/index', $data);
	}

	public function paket_dropdown($id = 0)
	{
		$str = "SELECT * FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skp) AND
		id_satker = '$id' AND left(akhir_pekerjaan,4) = '2019' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD')";
		$paket = $this->db->query($str)->result();
		// $paket = $this->db->get_where('tb_rup',array('id_satker'=>$id,'left(akhir_pekerjaan,4)'=>'2019'))->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/skp_paket_dropdown',$data);
	}

	public function delete($id = 0)
	{
		$this->skp_m->delete($id);
		redirect('data_skp');
	}

	public function delete_all()
	{
		$this->db->truncate('tb_skp');
		redirect('data_skp');
	}
}
