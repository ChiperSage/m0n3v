<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peralatan extends CI_Controller {

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
  // 		$data['inc'] = 'sumberdaya_tb';
  // 		$data['sumberdaya'] = $this->monev_m->get_sumberdaya();
		// $this->load->view('monev/index',$data);
  	}

	public function sync()
	{
		//$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		
		$filename = '';
		$year = '';

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->rkn_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_sumber_daya WHERE rkn_id NOT IN ($id_list) AND tahun = $year");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_sumber_daya SET rkn_id=?, pgl_nilai=?, tahun=?
		    	ON DUPLICATE KEY UPDATE
				rkn_id=values(rkn_id), pgl_nilai=values(pgl_nilai), tahun=values(tahun)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iii", $val->rkn_id, $val->pgl_nilai, $year);

		    mysqli_stmt_execute($stmt);
		}

		redirect('sumberdaya');
	}

}
