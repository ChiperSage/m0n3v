<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_skn extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('skn_m'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$array = array();
		$data['inc'] = 'data_skn_table';
		$data['skn'] = $this->skn_m->get();
		$this->load->view('admin/index', $data);
	}

	// public function cari()
	// {
	// 	$npwp = $this->input->post('cari_npwp');
	// 	$result = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp));
	// 	$count = $result->num_rows();
	// 	$detail = $result->result();
	//
	// 	$sess_data = array();
	// 	if($count == 1)
	// 	{
	// 		$sess_data = array('company_logged'=>true,'company_npwp'=>$detail->npwp);
	// 	}else{
	// 		$sess_data = array('company_logged'=>false);
	// 	}
	// 	$this->session->set_userdata($sess_data);
	// 	redirect('skn');
	//
	// }

	public function paket_dropdown($id = 0)
	{
		$str = "SELECT * FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skn) AND
		id_satker = '$id' AND left(akhir_pekerjaan,4) = '2019' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD')";
		$paket = $this->db->query($str)->result();
		// $paket = $this->db->get_where('tb_rup',array('id_satker'=>$id,'left(akhir_pekerjaan,4)'=>'2019'))->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/skn_paket_dropdown',$data);
	}

	public function delete($id = 0)
	{
		$this->skn_m->delete($id);
		redirect('data_skn');
	}

	public function delete_all()
	{
		$this->db->truncate('tb_skn');
		redirect('data_skn');
	}
}
