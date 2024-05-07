<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sumberdaya extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    	$this->load->model(array('monev_m'));

		$this->form_validation->set_error_delimiters(
	    $this->config->item('error_start_delimiter', 'ion_auth'),
	    $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('monev','staff_monev','kabagpp');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
  		$data['inc'] = 'sumberdaya_tb';
  		$data['sumberdaya'] = $this->monev_m->get_sumberdaya();
		$this->load->view('monev/index',$data);
  	}

	public function sync_sumberdaya()
	{
		//$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		
		$filename = 'http://123.108.97.216/json/sumberdaya/sumberdaya?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2019';
		$year = '2019';

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->rkn_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_sumber_daya WHERE rkn_id NOT IN ($id_list)");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_sumber_daya SET rkn_id=?, pgl_nilai=?
		    	ON DUPLICATE KEY UPDATE
				rkn_id=values(rkn_id), pgl_nilai=values(pgl_nilai)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "ii", $val->rkn_id, $val->pgl_nilai);

		    mysqli_stmt_execute($stmt);
		}

		redirect('sumberdaya');
	}

	public function sync_peralatan()
	{
		//$json = $this->db->get_where('json',array('data'=>'lelang'))->row();

		$page = $_GET['page']; 
		
		$filename = 'http://123.108.97.216/json/sumberdaya/peralatan?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&page='.$page;
		$year = '2019';

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->rkn_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		// mysqli_query($connection, "DELETE FROM tb_peralatan WHERE rkn_id NOT IN ($id_list)");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_peralatan SET rkn_id=?, data_sikap=?
		    	ON DUPLICATE KEY UPDATE
				rkn_id=values(rkn_id), data_sikap=values(data_sikap)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "is", $val->rkn_id, $val->data_sikap);

		    mysqli_stmt_execute($stmt);
		}

		// redirect('sumberdaya');
	}

	public function sync_pengurus()
	{
		//$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		
		$filename = 'http://123.108.97.216/json/sumberdaya/pengurus?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664';
		$year = '2019';

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->pgr_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_pengurus WHERE pgr_id NOT IN ($id_list)");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_pengurus SET pgr_id=?, rkn_id=?, pgr_nama=?, pgr_jabatan=?, pgr_ktp=?, pgr_alamat=?
		    	ON DUPLICATE KEY UPDATE
				rkn_id=values(rkn_id), pgr_nama=values(pgr_nama), pgr_jabatan=values(pgr_jabatan), pgr_ktp=values(pgr_ktp), pgr_alamat=values(pgr_alamat)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iissss", $val->pgr_id, $val->rkn_id, $val->pgr_nama, $val->pgr_jabatan, $val->pgr_ktp, $val->pgr_alamat);

		    mysqli_stmt_execute($stmt);
		}

		//redirect('sumberdaya');
	}

}
