<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rup extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('rup_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev','ppk','staff_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index($page = 0)
	{
		$data['first_url'] = '';
		$data['suffix'] = '';

		if(isset($_GET['search'])){
			$data['first_url'] = base_url().'rup/index/0/?search='.$_GET['search'];
			$data['suffix'] = '/?search='.$_GET['search'];
			$config['search'] = $_GET['search'];
		}
		if(isset($_GET['tahun'])){
			$data['first_url'] = base_url().'rup/index/0/?tahun='.$_GET['tahun'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'];
			$config['tahun'] = $_GET['tahun'];
		}

		if(isset($_GET['search']) && isset($_GET['tahun'])){
			$data['first_url'] = base_url().'rup/index/0/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];

			$config['search'] = $_GET['search'];
			$config['tahun'] = $_GET['tahun'];
		}

		$config['page'] = $page;
		$rup = $this->rup_m->get($config);

		$data['base_url'] = base_url('rup/index');
		$data['total_rows'] = $rup['count'];
		$data['per_page'] = 20;
		$data['uri_segment'] = 3;

		$data['rup'] = $rup['result'];
		if(isset($_GET['action']) && $_GET['action'] == 'print'){
			$this->load->view('admin/rup_table_ex', $data);
		}else{
			$data['inc'] = 'rup_table';
			$this->load->view('admin/index', $data);
		}
	}

	public function ccreate()
	{
	
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		if($year > 2020){
			$this->create_1();
		}elseif($year <= 2020){
			$this->create_2();
		}
	
	}

	public function create2()
	{
		// ambil data json
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, kldi=?, kode_rup=?, nama_satker=?, nama_paket=?, program=?, kode_string_program=?, kegiatan=?, pagu_rup=?, mak=?, sumber_dana=?, metode_pemilihan=?, jenis_pengadaan=?, pagu_perjenis_pengadaan=?, awal_pengadaan=?, awal_pekerjaan=?, nama_kpa=?, penyedia_didalam_swakelola=?, tkdn=?, pradipa=?, status_aktif=?, status_umumkan=?, nama_ppk=?, nip_ppk=?, nip_kpa=?, deskripsi=?, umkm=?, tahun=?
		    ON DUPLICATE KEY UPDATE tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), kldi=values(kldi), nama_satker=values(nama_satker), nama_paket=values(nama_paket), program=values(program), kode_string_program=values(kode_string_program), kegiatan=values(kegiatan), pagu_rup=values(pagu_rup), mak=values(mak), sumber_dana=values(sumber_dana), metode_pemilihan=values(metode_pemilihan), jenis_pengadaan=values(jenis_pengadaan), pagu_perjenis_pengadaan=values(pagu_perjenis_pengadaan), awal_pengadaan=values(awal_pengadaan), awal_pekerjaan=values(awal_pekerjaan), nama_kpa=values(nama_kpa), penyedia_didalam_swakelola=values(penyedia_didalam_swakelola), tkdn=values(tkdn), pradipa=values(pradipa), status_aktif=values(status_aktif), status_umumkan=values(status_umumkan), nama_ppk=values(nama_ppk), nip_ppk=values(nip_ppk), nip_kpa=values(nip_kpa), deskripsi=values(deskripsi), umkm=values(umkm), tahun=values(tahun)";
									
		    $stmt = mysqli_prepare($connection, $sql);

		    $jenis_pengadaan = explode(";", $val->jenis_pengadaan);
		    $pagu_perjenis_pengadaan = explode(";", $val->pagu_perjenis_pengadaan);

			mysqli_stmt_bind_param($stmt, "ssiisssssssissssisssssssssssssi", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->kldi, $val->kode_rup, $val->nama_satker, $val->nama_paket, $val->program, $val->kode_string_program, $val->kegiatan, $val->pagu_rup, $val->mak, $val->sumber_dana, $val->metode_pemilihan, $jenis_pengadaan[0], $pagu_perjenis_pengadaan[0], $val->awal_pengadaan, $val->awal_pekerjaan, $val->nama_kpa, $val->penyedia_didalam_swakelola, $val->tkdn, $val->pradipa, $val->status_aktif, $val->status_umumkan, $val->nama_ppk, $val->nip_ppk, $val->nip_kpa, $val->deskripsi, $val->umkm, $year);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				// $error_list = mysqli_stmt_error_list($stmt);
			    // foreach ($error_list as $error) {
			    //     echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    // }
			}
		}
		
		redirect('rup');
	}

	public function create()
	{
		// ambil data json
		$json = $this->db->get_where('json',array('data'=>'rup_terumumkan'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kd_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup SET 

		    	tahun=?, kode_kldi=?, kldi=?, jenis=?, id_satker=?, nama_satker=?, kode_rup=?, nama_paket=?, volume=?, pagu_rup=?, jenis_pengadaan=?, metode_pemilihan=?, status_aktif=?, status_umumkan=?, nip_ppk=?, nama_ppk=?, sumber_dana=?

    		ON DUPLICATE KEY UPDATE  

		    	tahun=values(tahun), kode_kldi=values(kode_kldi), kldi=values(kldi), jenis=values(jenis), id_satker=values(id_satker), nama_satker=values(nama_satker), kode_rup=values(kode_rup), nama_paket=values(nama_paket), volume=values(volume), pagu_rup=values(pagu_rup), jenis_pengadaan=values(jenis_pengadaan), metode_pemilihan=values(metode_pemilihan), status_aktif=values(status_aktif), status_umumkan=values(status_umumkan), nip_ppk=values(nip_ppk), nama_ppk=values(nama_ppk), sumber_dana=values(sumber_dana)";

		    $stmt = mysqli_prepare($connection, $sql);

		    $status_aktif = ($val->status_aktif_rup == true) ? 'ya' : 'tidak' ;
		    $status_umumkan = ($val->status_umumkan_rup == 'Terumumkan') ? 'sudah' : 'belum' ;
		    $sumber_dana = 'APBD';

		    mysqli_stmt_bind_param($stmt, "isssisississsssss", $val->tahun_anggaran, $val->kd_klpd, $val->nama_klpd, $val->jenis_klpd, $val->kd_satker, $val->nama_satker, $val->kd_rup, $val->nama_paket, $val->volume_pekerjaan, $val->pagu, $val->jenis_pengadaan, $val->metode_pengadaan, $status_aktif, $status_umumkan, $val->nip_ppk, $val->nama_ppk, $sumber_dana);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				$error_list = mysqli_stmt_error_list($stmt);
			    foreach ($error_list as $error) {
			        echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    }
			}

		}

		mysqli_close($connection);

		$this->sync_sumber_dana($year);

		redirect('rup');
	}

	public function sync_sumber_dana($tahun)
	{

		// $json = $this->db->get_where('json',array('data'=>'rup_terumumkan'))->row();

		$filename = 'https://isb.lkpp.go.id/isb-2/api/51435f9c-9ea9-4db4-84fa-ff2c30f23eaa/json/2551/RUP-PaketAnggaranPenyedia/tipe/4:12/parameter/'.$tahun.':D1';
		$year = $tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kd_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		// mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup SET 

		    	kode_rup=?, mak=?, sumber_dana=?

    		ON DUPLICATE KEY UPDATE  

		    	kode_rup=VALUES(kode_rup),mak=VALUES(mak),sumber_dana=VALUES(sumber_dana)";

		    $stmt = mysqli_prepare($connection, $sql);

		    mysqli_stmt_bind_param($stmt, "iss", $val->kd_rup, $val->mak, $val->sumber_dana);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				// $error_list = mysqli_stmt_error_list($stmt);
			    // foreach ($error_list as $error) {
			    //     echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    // }
			}

		}

		mysqli_close($connection);

	}

	public function swakelola($page = 0)
	{
		$data['first_url'] = '';
		$data['suffix'] = '';
		$config['tahun'] = date('Y');

		if(isset($_GET['search'])){
			$data['first_url'] = base_url().'rup/index/0/?search='.$_GET['search'];
			$data['suffix'] = '/?search='.$_GET['search'];
			$config['search'] = $_GET['search'];
		}
		if(isset($_GET['tahun'])){
			$data['first_url'] = base_url().'rup/index/0/?tahun='.$_GET['tahun'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'];
			$config['tahun'] = $_GET['tahun'];
		}

		if(isset($_GET['search']) && isset($_GET['tahun'])){
			$data['first_url'] = base_url().'rup/index/0/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];

			$config['search'] = $_GET['search'];
			$config['tahun'] = $_GET['tahun'];
		}

		$config['page'] = $page;
		$rup = $this->rup_m->get_swakelola($config);

		$data['base_url'] = base_url('rup/swakelola');
		$data['total_rows'] = $rup['count'];
		$data['per_page'] = 20;
		$data['uri_segment'] = 3;

		$data['rup'] = $rup['result'];
		if(isset($_GET['action']) && $_GET['action'] == 'print'){
			$this->load->view('admin/rup_table_swakelola_ex', $data);
		}else{
			$data['inc'] = 'rup_table_swakelola';
			$this->load->view('admin/index', $data);
		}
	}

	public function create_swakelola3()
	{
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();
		// $filename = $json->url;
		$year = $json->tahun;

		$filename = 'https://inaproc.lkpp.go.id/isb/api/491f8a1b-0df9-486c-89ef-bb1b295d1c4e/json/23106260/PengumumanSwakelolaKL1618/tipe/4:12/parameter/'.$year.':D1';

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_rup_swakelola WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup_swakelola SET kode_rup=?, tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?, nama_satker=?,
						nama_paket=?, program=?, kegiatan=?, output=?, suboutput=?, komponen=?, pagu_rup=?, mak=?,
						lokasi=?, detail_lokasi=?, sumber_dana=?, awal_pekerjaan=?, akhir_pekerjaan=?, nama_kpa=?, tipe_swakelola=?, status_aktif=?,
						status_umumkan=?, id_program=?, id_kegiatan=?, id_output=?, id_suboutput=?, id_komponen=?, nama_ppk=?, nomor_renja=?, tahun=?
		    		ON DUPLICATE KEY UPDATE
						tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi), nama_satker=values(nama_satker),
						nama_paket=values(nama_paket), program=values(program), kegiatan=values(kegiatan), output=values(output), suboutput=values(suboutput), komponen=values(komponen), pagu_rup=values(pagu_rup), mak=values(mak),
						lokasi=values(lokasi), detail_lokasi=values(detail_lokasi), sumber_dana=values(sumber_dana), awal_pekerjaan=values(awal_pekerjaan), akhir_pekerjaan=values(akhir_pekerjaan), nama_kpa=values(nama_paket), tipe_swakelola=values(tipe_swakelola), status_aktif=values(status_aktif),
						status_umumkan=values(status_umumkan), id_program=values(id_program), id_kegiatan=values(id_kegiatan), id_output=values(id_output), id_suboutput=values(id_suboutput), id_komponen=values(id_komponen), nama_ppk=values(nama_ppk), nomor_renja=values(nomor_renja), tahun=values(tahun)";
		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "ississsssiiiiiisssssssisssiiiiisi", $val->kode_rup, $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi, $val->nama_satker, $val->nama_paket, $val->program, $val->kegiatan, $val->output, $val->suboutput, $val->komponen, $val->pagu_rup, $val->mak,
			$val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->nama_kpa, $val->tipe_swakelola, $val->status_aktif,
			$val->status_umumkan, $val->id_program, $val->id_kegiatan, $val->id_output, $val->suboutput, $val->id_komponen, $val->nama_ppk, $val->nomor_renja, $year);

		    mysqli_stmt_execute($stmt);

		}

		redirect('rup/swakelola');
	}

	// public function create_swakelola_lama()
	// {
	// 	$json = $this->db->get_where('json',array('data'=>'rup'))->row();
	// 	// $filename = $json->url;
	// 	$year = $json->tahun;

	// 	$filename = "https://inaproc.lkpp.go.id/isb/api/491f8a1b-0df9-486c-89ef-bb1b295d1c4e/json/23106260/PengumumanSwakelolaKL1618/tipe/4:12/parameter/".$year.":D1";
		
	// 	$jsondata = file_get_contents($filename);
	// 	$data = json_decode($jsondata);

	// 	$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

	// 	$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");
		
	// 	mysqli_query($connection, "DELETE FROM tb_rup_swakelola WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

	// 	foreach ($data as $val)
	// 	{
	// 	    $sql = "INSERT INTO tb_rup_swakelola SET kode_rup=?, tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?, nama_satker=?, nama_paket=?, program=?, kegiatan=?, output=?, suboutput=?, pagu_rup=?, mak=?, lokasi=?, detail_lokasi=?, sumber_dana=?, awal_pekerjaan=?, akhir_pekerjaan=?, nama_kpa=?, tipe_swakelola=?, status_aktif=?, status_umumkan=?, nama_ppk=?, nomor_renja=?, tahun=?
	// 	        	ON DUPLICATE KEY UPDATE kode_rup=values(kode_rup), tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi), nama_satker=values(nama_satker), nama_paket=values(nama_paket), program=values(program), kegiatan=values(kegiatan), output=values(output), suboutput=values(suboutput), pagu_rup=values(pagu_rup), mak=values(mak), lokasi=values(lokasi), detail_lokasi=values(detail_lokasi), sumber_dana=values(sumber_dana), awal_pekerjaan=values(awal_pekerjaan), akhir_pekerjaan=values(akhir_pekerjaan), nama_kpa=values(nama_kpa), tipe_swakelola=values(tipe_swakelola), status_aktif=values(status_aktif), status_umumkan=values(status_umumkan), nama_ppk=values(nama_ppk), nomor_renja=values(nomor_renja), tahun=values(tahun)";

	// 	    $stmt = mysqli_prepare($connection, $sql);

	// 		mysqli_stmt_bind_param($stmt, "ississssssssssissssssissssi", $val->kode_rup, $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi, $val->nama_satker, $val->nama_paket, $val->program, $val->kegiatan, $val->output, $val->suboutput, $val->pagu_rup, $val->mak, $val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->nama_kpa, $val->tipe_swakelola, $val->status_aktif, $val->status_umumkan, $val->nama_ppk, $val->nomor_renja, $year);

	// 	    // mysqli_stmt_execute($stmt);

	// 	    if (mysqli_stmt_execute($stmt)) {
	// 		    echo "Data inserted or updated.";
	// 		} else {
	// 			$error_list = mysqli_stmt_error_list($stmt);
	// 		    foreach ($error_list as $error) {
	// 		        echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
	// 		    }
	// 		}
	// 	}

	// 	// redirect('rup/swakelola');
	// }

	public function create_swakelola()
	{
		$json = $this->db->get_where('json',array('data'=>'rup_swa_terumumkan'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		// $filename = "https://isb.lkpp.go.id/isb-2/api/f74202e1-330b-4658-8bec-429b7f2b7827/json/2546/RUP-PaketSwakelola-Terumumkan/tipe/4:12/parameter/2021:D1";
		
		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kd_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");
		
		mysqli_query($connection, "DELETE FROM tb_rup_swakelola WHERE kode_rup NOT IN ($id_list) AND tahun_anggaran = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup_swakelola 

		    SET tahun_anggaran=?, kd_klpd=?, nama_klpd=?, kd_satker=?, nama_satker=?, kode_rup=?, nama_paket=?, pagu_rup=?, tipe_swakelola=?, status_umumkan_rup=? 

		    ON DUPLICATE KEY UPDATE 

		    tahun_anggaran=values(tahun_anggaran), kd_klpd=values(kd_klpd), nama_klpd=values(nama_klpd), kd_satker=values(kd_satker), nama_satker=values(nama_satker), kode_rup=values(kode_rup), nama_paket=values(nama_paket), pagu_rup=values(pagu_rup), tipe_swakelola=values(tipe_swakelola), status_umumkan_rup=values(status_umumkan_rup)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "issisisiss", $val->tahun_anggaran, $val->kd_klpd, $val->nama_klpd, $val->kd_satker, $val->nama_satker, $val->kd_rup, $val->nama_paket, $val->pagu, $val->tipe_swakelola, $val->status_umumkan_rup);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				// $error_list = mysqli_stmt_error_list($stmt);
			    // foreach ($error_list as $error) {
			    //     echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    // }
			}
		}

		// $this->_add_sumber_dana2($year);

		redirect('rup/swakelola');

	}

	public function add_sumber_dana2($tahun)
	{
		// $json = $this->db->get_where('json',array('data'=>'rup_terumumkan'))->row();

		// $filename = 'https://isb.lkpp.go.id/isb-2/api/51435f9c-9ea9-4db4-84fa-ff2c30f23eaa/json/2551/RUP-PaketAnggaranPenyedia/tipe/4:12/parameter/'.$tahun.':D1';
		$filename = 'https://isb.lkpp.go.id/isb-2/api/96067f7d-eaf5-4fc8-ace6-4f2719c6fe16/json/2544/RUP-PaketAnggaranSwakelola/tipe/4:12/parameter/'.$tahun.':D1';
		$year = $tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kd_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		// mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup_swakelola SET kode_rup=?, mak=?, sumber_dana=?

    		ON DUPLICATE KEY UPDATE kode_rup=VALUES(kode_rup),mak=VALUES(mak),sumber_dana=VALUES(sumber_dana)";

		    $stmt = mysqli_prepare($connection, $sql);

		    mysqli_stmt_bind_param($stmt, "iss", $val->kd_rup, $val->mak, $val->sumber_dana);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				$error_list = mysqli_stmt_error_list($stmt);
			    foreach ($error_list as $error) {
			        echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    }
			}

		}

		mysqli_close($connection);	
	}

	public function sync_rup2()
	{
		$filename = 'https://inaproc.lkpp.go.id/isb/api/e3cd704d-9478-418a-ae1e-a4fe49d57115/json/23233435/PengumumanPenyediaDaerah1618/tipe/4:12/parameter/2022:D1';
		$year = 2022;

		$test = file_get_contents($filename);
		$data = json_decode($test,true);

		foreach ($data as $val)
		{
			$val['tahun'] = $year;

			$insert_query= $this->db->insert_string('tmp_rup', $val);
			$insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
			$this->db->query($insert_queryf);
		}

		// $this->db->query("INSERT IGNORE INTO tb_rup
		// 	(SELECT r3.* FROM tmp_rup r3)");

	}

	public function notexist()
	{
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		// mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val){
			$sql = "SELECT kode_rup FROM tb_rup WHERE tahun = 2022";
			$kode_rup = $this->db->query($sql)->row('kode_rup');
		}
		// print_r($arr);
	}



	public function sync_pencatatan_swakelola()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/rup/pencatatan_swakelola?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_pencatatan_swakelola WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_pencatatan_swakelola
					SET kode_rup=?, stk_id=?, kode_lelang=?, swk_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?, pkt_tgl_realisasi=?, tipe_swakelola=?, lls_dibuat_tanggal=?
			    	ON DUPLICATE KEY UPDATE
					kode_rup=values(kode_rup), stk_id=values(stk_id), kode_lelang=values(kode_lelang), swk_id=values(swk_id), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status), pkt_tgl_realisasi=values(pkt_tgl_realisasi), tipe_swakelola=values(tipe_swakelola), lls_dibuat_tanggal=values(lls_dibuat_tanggal)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiiisiiiiiis", $val->kode_rup, $val->stk_id, $val->kode_lelang, $val->swk_id, $val->nama_paket, $val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->pkt_tgl_realisasi, $val->tipe_swakelola, $val->lls_dibuat_tanggal);

		    mysqli_stmt_execute($stmt);
		}

		redirect('rup/pencatatan_swakelola');
	}

	public function sync_realisasi_pencatatan_swakelola()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/rup/realisasi_pencatatan_swakelola?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_realisasi_pencatatan_swakelola WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_realisasi_pencatatan_swakelola
					SET kode_rup=?, stk_id=?, kode_lelang=?, swk_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?, pkt_tgl_realisasi=?, tipe_swakelola=?, lls_dibuat_tanggal=?, rsk_id=?, rsk_nilai=?, rsk_tanggal=?
			    	ON DUPLICATE KEY UPDATE
					kode_rup=values(kode_rup), stk_id=values(stk_id), kode_lelang=values(kode_lelang), swk_id=values(swk_id), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status), pkt_tgl_realisasi=values(pkt_tgl_realisasi), tipe_swakelola=values(tipe_swakelola), lls_dibuat_tanggal=values(lls_dibuat_tanggal), rsk_id=values(rsk_id), rsk_nilai=values(rsk_nilai), rsk_tanggal=values(rsk_tanggal)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiiisiiiiissiis", $val->kode_rup, $val->stk_id, $val->kode_lelang, $val->swk_id, $val->nama_paket, $val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->pkt_tgl_realisasi, $val->tipe_swakelola, $val->lls_dibuat_tanggal, $val->rsk_id, $val->rsk_nilai, $val->rsk_tanggal);

		    mysqli_stmt_execute($stmt);
		}

		redirect('rup/realisasi_pencatatan_swakelola');
	}

	public function pencatatan_swakelola()
	{
		$data['inc'] = 'pencatatan_swakelola_tb';
		$data['paket'] = $this->rup_m->get_pencatatan_swakelola();
		$this->load->view('admin/index', $data);	
	}

	public function realisasi_pencatatan_swakelola()
	{
		$data['inc'] = 'realisasi_pencatatan_swakelola_tb';
		$data['paket'] = $this->rup_m->get_realisasi_pencatatan_swakelola();
		$this->load->view('admin/index', $data);	
	}

	public function total_realisasi_pencatatan_swakelola()
	{
		$data['inc'] = 'total_pencatatan_swakelola_tb';
		$data['paket'] = $this->rup_m->get_total_realisasi_pencatatan_swakelola();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/total_pencatatan_swakelola_ex', $data);			
		}else{
			$this->load->view('admin/index', $data);
		}

	}

	public function ajax_info_paket($kode_rup)
	{
		//echo 'hasil: ' .$kode_rup;

		$sql = "SELECT s.sp_kelompok FROM tb_sp s LEFT JOIN tb_sp_paket sp ON s.sp_id = sp.paket_sp WHERE sp.paket_id = '$kode_rup'";
		echo 'Pokja: '.$this->db->query($sql)->row('sp_kelompok');
	}

}
