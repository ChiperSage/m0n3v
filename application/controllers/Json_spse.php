<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Json_spse extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    $this->load->model(array('json_spse_m'));
	}

  public function index()
	{

	}

  public function daftar_paket_belum_tayang()
	{
    if(isset($_GET['x-api-key']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664'){
      $result = $this->json_spse_m->get_daftar_paket_spse('belum_tayang');
      echo json_encode($result);
    }
	}

  public function daftar_paket_tayang()
	{
    if(isset($_GET['x-api-key']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664'){
      $result = $this->json_spse_m->get_daftar_paket_spse('tayang');
      echo json_encode($result);
    }
	}

  public function daftar_paket_menang()
	{
    if(isset($_GET['x-api-key']) && $_GET['x-api-key'] == '6842cbf4ba070a2b5dbb1b45bd416664'){
      $result = $this->json_spse_m->get_daftar_paket_spse('menang');
      echo json_encode($result);
    }
	}

}
