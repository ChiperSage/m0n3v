<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sanggah extends CI_Controller {

  public function __construct()
	{
		parent::__construct();

		$this->load->model(array('sanggah_m'));

		$this->form_validation->set_error_delimiters(
		$this->config->item('error_start_delimiter', 'ion_auth'),
		$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
			// $this->_sync_sanggah();
			// $this->_sync_sanggah_banding();

			$data['inc'] = 'sanggah_tb';
			$data['paket'] = $this->sanggah_m->get2();

			if(isset($_GET['type']) && $_GET['type'] == 'excel')
			{
				$this->load->view('monev/sanggah_ex',$data);
			}else{
				$this->load->view('monev/index',$data);
			}

			$this->_sync_sanggah();
	}

	public function _sync_sanggah()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/sanggah?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->sgh_id; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_sanggah WHERE sgh_id NOT IN ($id_list) AND left(sgh_tanggal,4) = '$year'");

		foreach ($data as $val)
		{
				$sql = "INSERT INTO tb_sanggah
				SET sgh_id=?, psr_id=?, thp_id=?, san_sgh_id=?, sgh_tanggal=?, rkn_id=?, rkn_nama=?, rkn_alamat=?, lls_id=?
				ON DUPLICATE KEY UPDATE
				sgh_id=values(sgh_id), psr_id=values(psr_id), thp_id=values(thp_id), san_sgh_id=values(san_sgh_id), sgh_tanggal=values(sgh_tanggal), rkn_id=values(rkn_id), rkn_nama=values(rkn_nama), rkn_alamat=values(rkn_alamat), lls_id=values(lls_id)";

				$stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "sssssssss", $val->sgh_id, $val->psr_id, $val->thp_id, $val->san_sgh_id, $val->sgh_tanggal, $val->rkn_id, $val->rkn_nama, $val->rkn_alamat, $val->lls_id);

				mysqli_stmt_execute($stmt);
		}

		$sql = "UPDATE tb_lelang_spse ls SET ls.sanggah = 1 WHERE ls.kode_lelang IN (SELECT s.lls_id FROM tb_sanggah s)";
		$this->db->query($sql);
	}

	public function _sync_sanggah_banding()
	{
			$tahun = date('Y');
			$filename = 'http://123.108.97.216/json/json/sanggah_banding?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun='.$tahun;
			$url = file_get_contents($filename);
			$data = json_decode($url);

			$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));

			$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

			mysqli_query($connection, "DELETE FROM sanggah_banding WHERE kode_lelang NOT IN ($lls_id)");

			foreach ($data as $val)
			{
			    $sql = "INSERT INTO sanggah_banding SET lls_id=?, sgh_waktu_mulai=?, sgh_waktu_selesai=?, audituser=?, jdwl_sgh_id=?, sgh_status=?, sgh_versi=?, sgh_alasan=?
			    ON DUPLICATE KEY UPDATE
					sgh_waktu_mulai=values(sgh_waktu_mulai), sgh_waktu_selesai=values(sgh_waktu_selesai),
					audituser=values(audituser),jdwl_sgh_id=values(jdwl_sgh_id),sgh_status=values(sgh_status),sgh_versi=values(sgh_versi),sgh_alasan=values(sgh_alasan)";

			    $stmt = mysqli_prepare($connection, $sql);

					mysqli_stmt_bind_param($stmt, "isssiiis", $val->lls_id, $val->sgh_waktu_mulai, $val->sgh_waktu_selesai, $val->audituser, $val->jdwl_sgh_id, $val->sgh_status,
					$val->sgh_versi, $val->sgh_alasan);

			    mysqli_stmt_execute($stmt);
			}
	}

}
