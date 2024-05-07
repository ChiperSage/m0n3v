<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Anggaran extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Anggaran_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'anggaran_tb';
		$data['items'] = $this->Anggaran_m->get_data();
		$this->load->view('monev/index', $data);
	}

	public function sync_struktur_anggaran()
	{
		// $this->db->truncate('tb_struktur_anggaran');
		
		// ambil data json
		// $json = $this->db->get_where('json',array('data'=>'rup_terumumkan'))->row();

		$filename = 'https://isb.lkpp.go.id/isb-2/api/efb35042-c56d-45c0-891e-271583889e40/json/6965/RUP-StrukturAnggaranPD/tipe/4:12/parameter/2023:D1';
		$year = 2023;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->tahun_anggaran; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		// mysqli_query($connection, "DELETE FROM tb_struktur_anggaran WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_struktur_anggaran SET 

		    	tahun_anggaran=?, kd_klpd=?, nama_klpd=?, kd_satker=?, kd_satker_str=?, nama_satker=?, belanja_operasi=?, belanja_modal=?, belanja_btt=?, belanja_non_pengadaan=?, belanja_pengadaan=?, total_belanja=?

    		ON DUPLICATE KEY UPDATE  

		    	tahun_anggaran=VALUES(tahun_anggaran),kd_klpd=VALUES(kd_klpd),nama_klpd=VALUES(nama_klpd),kd_satker=VALUES(kd_satker),kd_satker_str=VALUES(kd_satker_str),nama_satker=VALUES(nama_satker),belanja_operasi=VALUES(belanja_operasi),belanja_modal=VALUES(belanja_modal),
		    	belanja_btt=VALUES(belanja_btt),belanja_non_pengadaan=VALUES(belanja_non_pengadaan),belanja_pengadaan=VALUES(belanja_pengadaan),total_belanja=VALUES(total_belanja)";

		    $stmt = mysqli_prepare($connection, $sql);

		    mysqli_stmt_bind_param($stmt, "ississiiiiii", $val->tahun_anggaran, $val->kd_klpd, $val->nama_klpd, $val->kd_satker, $val->kd_satker_str, $val->nama_satker, $val->belanja_operasi, $val->belanja_modal, $val->belanja_btt, $val->belanja_non_pengadaan, $val->belanja_pengadaan, 
		    	$val->total_belanja);

		    if (mysqli_stmt_execute($stmt)) {
			    echo "Data inserted or updated.";
			} else {
				$error_list = mysqli_stmt_error_list($stmt);
			    foreach ($error_list as $error) {
			        echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    }
			}

		}

		mysqli_close($connection);

		redirect('anggaran');

	}

}