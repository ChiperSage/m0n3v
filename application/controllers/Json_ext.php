<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Json_ext extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['Monev_json_m']);
	}

	public function index()
	{

	}

	// public function pemenang()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_pemenang();
	// 		echo json_encode($result);
	// 	}
	// }

	// json baru

	// public function rup_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_rup_list();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function rup_swakelola_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_rup_swakelola_list();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function tender_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_tender_list();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function non_tender_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_non_tender_list();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function realisasi_tender()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_realisasi_tender();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function realisasi_jenis_pengadaan()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_realisasi_jenis_pengadaan();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function pemenang_apba()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_pemenang_apba();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function data_metode()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_data_metode();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function realisasi_jenis_pengadaan_list()
	// {
	// 	$tahun = date('Y');
	// 	if(isset($_GET['tahun'])){
	// 		$tahun = $_GET['tahun'];
	// 	}

	// 	$metode = '';
	// 	if(isset($_GET['metode']) && $_GET['metode'] == 'tender-cepat'){
	// 		$metode = 'Tender Cepat';
	// 	}elseif(isset($_GET['metode']) && $_GET['metode'] == 'tender'){
	// 		$metode = 'Tender';
	// 	}elseif(isset($_GET['metode']) && $_GET['metode'] == 'pengadaan-langsung'){
	// 		$metode = 'Pengadaan Langsung';
	// 	}elseif(isset($_GET['metode']) && $_GET['metode'] == 'penunjukan-langsung'){
	// 		$metode = 'Penunjukan Langsung';
	// 	}elseif(isset($_GET['metode']) && $_GET['metode'] == 'seleksi'){
	// 		$metode = 'Seleksi';
	// 	}elseif(isset($_GET['metode']) && $_GET['metode'] == 'epurchasing'){
	// 		$metode = 'e-Purchasing';
	// 	}

	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_jenis_pengadaan_list($metode,$tahun);
	// 		echo json_encode($result);
	// 	}
	// }

	// public function realisasi_tender_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_realisasi_tender_list();
	// 		echo json_encode($result);
	// 	}
	// }

	// public function realisasi_non_tender_list()
	// {
	// 	if(isset($_GET['x-api-key']) && isset($_GET['tahun']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664' && $_GET['tahun'] != '')
	// 	{
	// 		$result = $this->Monev_json_m->get_realisasi_non_tender_list();
	// 		echo json_encode($result);
	// 	}
	// }

}
