<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pp extends CI_Controller {

  public function __construct()
	{
		parent::__construct();

		$this->load->model(array('pp_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		$this->load->library(array('pdfgenerator'));

		$group = array('monev','karo','kabagpp','barang_jasa','konstruksi','staff_monev','admin_karo');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		// $data['inc'] = 'pp_table';
		// $data['pp'] = $this->pp_m->get();
		// $this->load->view('monev/index',$data);
	}

  public function sync()
  {
		// $json = $this->db->get_where('json',array('data'=>'lelang'))->row();

		$filename = 'http://123.108.97.215/json/json/pejabat_pengadaan?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2020';
		$year = date('Y');

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->pkt_id; }, $data));

		// $connection = mysqli_connect("localhost","root","","monev");
		$connection = mysqli_connect("localhost","monev","laserj3tbpbjplatinum","monev");

		mysqli_query($connection, "DELETE FROM tb_pp WHERE pkt_id NOT IN ($id_list) AND tahun = '$year'");
		//mysqli_query($connection, "TRUNCATE TABLE tb_jadwal");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_pp SET pkt_id=?, pp_id=?, peg_id=?, peg_nip=?, peg_nama=?, pkt_nama=?
		    ON DUPLICATE KEY UPDATE
				pkt_id=values(pkt_id), pp_id=values(pp_id), peg_id=values(peg_id),
				peg_nip=values(peg_nip),peg_nama=values(peg_nama),pkt_nama=values(pkt_nama)";
		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iiisss", $val->pkt_id, $val->pp_id, $val->peg_id, $val->peg_nip, $val->peg_nama, $val->pkt_nama);
		    mysqli_stmt_execute($stmt);
    }

  }

}
