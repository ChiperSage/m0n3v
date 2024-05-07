<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spk extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		
	}

	public function sync()
	{
		$year = date('Y');
		$filename = 'http://123.108.97.216/json/json/spk?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->spk_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_spk WHERE spk_id NOT IN ($id_list)");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_spk SET spk_tgl=?, spk_id=?, spk_content=?, kontrak_id=?, spk_no=?, spk_nilai=?, spk_norekening=?, spk_nama_bank=?, alasanubah_spk_nilai=?, spk_wakil_penyedia=?, spk_jabatan_wakil=?, nilai_pdn=?    
		    ON DUPLICATE KEY UPDATE spk_tgl=values(spk_tgl), spk_content=values(spk_content), kontrak_id=values(kontrak_id), spk_no=values(spk_no), spk_nilai=values(spk_nilai), spk_norekening=values(spk_norekening), spk_nama_bank=values(spk_nama_bank), alasanubah_spk_nilai=values(alasanubah_spk_nilai), spk_wakil_penyedia=values(spk_wakil_penyedia), spk_jabatan_wakil=values(spk_jabatan_wakil), nilai_pdn=values(nilai_pdn)";
									
		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "sisssisssssi", $val->spk_tgl, $val->spk_id, $val->spk_content, $val->kontrak_id, $val->spk_no, $val->spk_nilai, $val->spk_norekening, $val->spk_nama_bank, $val->alasanubah_spk_nilai, $val->spk_wakil_penyedia, $val->spk_jabatan_wakil, $val->nilai_pdn);

		    mysqli_stmt_execute($stmt);
		}
	}

}
