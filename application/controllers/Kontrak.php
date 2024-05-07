<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kontrak extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// $this->load->model('lelang_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function sync_kontrak()
	{
		$filename = 'http://123.108.97.216/json/json/kontrak?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2021';
        $year = 2021;

        $test = file_get_contents($filename);
        $data = json_decode($test);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kontrak_id; }, $data));

        $connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

        // mysqli_query($connection, "DELETE FROM tb_kontrak WHERE kontrak_id NOT IN ($id_list)");

        foreach ($data as $val)
		{
			$sql = "INSERT INTO tb_kontrak SET kontrak_id=?, lls_id=?, rkn_id=?, kontrak_no=?, kontrak_nilai=?, kontrak_mulai=?, kontrak_akhir=?, ppk_id=?, kontrak_norekening=?, kontrak_tanggal=?,
			kontrak_jabatan_wakil=?, kontrak_tipe_penyedia=?, kontrak_kota=?, kontrak_wakil_penyedia=?, kontrak_namarekening=?,
			kontrak_namapemilikrekening=?, jabatan_ppk_kontrak=?, nilai_pdn=?, nip_ppk_kontrak=?, nama_ppk_kontrak=?

			ON DUPLICATE KEY UPDATE
	        
	        kontrak_id=values(kontrak_id), lls_id=values(lls_id), rkn_id=values(rkn_id), kontrak_no=values(kontrak_no), kontrak_nilai=values(kontrak_nilai), kontrak_mulai=values(kontrak_mulai), kontrak_akhir=values(kontrak_akhir), ppk_id=values(ppk_id), kontrak_norekening=values(kontrak_norekening), kontrak_tanggal=values(kontrak_tanggal), 
	        kontrak_jabatan_wakil=values(kontrak_jabatan_wakil), kontrak_tipe_penyedia=values(kontrak_tipe_penyedia), kontrak_kota=values(kontrak_kota), kontrak_wakil_penyedia=values(kontrak_wakil_penyedia), kontrak_namarekening=values(kontrak_namarekening), 
	        kontrak_namapemilikrekening=values(kontrak_namapemilikrekening), jabatan_ppk_kontrak=values(jabatan_ppk_kontrak), nilai_pdn=values(nilai_pdn), nip_ppk_kontrak=values(nip_ppk_kontrak), nama_ppk_kontrak=values(nama_ppk_kontrak)";

	        $stmt = mysqli_prepare($connection, $sql);

	        mysqli_stmt_bind_param($stmt, "iiisisssissssssssiss", $val->kontrak_id, $val->lls_id, $val->rkn_id, $val->kontrak_no, $val->kontrak_nilai, $val->kontrak_mulai, $val->kontrak_akhir, $val->ppk_id, $val->kontrak_norekening, $val->kontrak_tanggal, 
	        $val->kontrak_jabatan_wakil, $val->kontrak_tipe_penyedia, $val->kontrak_kota, $val->kontrak_wakil_penyedia, $val->kontrak_namarekening, 
	        $val->kontrak_namapemilikrekening, $val->jabatan_ppk_kontrak, $val->nilai_pdn, $val->nip_ppk_kontrak, $val->nama_ppk_kontrak);

	        mysqli_stmt_execute($stmt);
        }
	}

}