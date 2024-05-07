<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pokja_paket extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Pokja_paket_m','User_m'));

		$this->form_validation->set_error_delimiters(
		$this->config->item('error_start_delimiter', 'ion_auth'),
		$this->config->item('error_end_delimiter', 'ion_auth'));
		
		$this->lang->load('auth');

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('pokja'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'pokja_paket';
		$data['paket'] = $this->Pokja_paket_m->get_paket();
		$this->load->view('admin/index', $data);
	}

	public function review()
	{
		$data['inc'] = 'pokja_review';
		$data['review'] = $this->Pokja_paket_m->get_paket_review2();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/pokja_review_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			// $html = $this->load->view('admin/pokja_review_cetak', $data, true);
			// $filename = 'cetak_pokja_history_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function review2()
	{
		$data['inc'] = 'pokja_review';
		$data['review'] = $this->Pokja_paket_m->get_paket_review2();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/pokja_review_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			// $html = $this->load->view('admin/pokja_review_cetak', $data, true);
			// $filename = 'cetak_pokja_history_'.time();
			// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function ba_histori()
	{
		$data['inc'] = 'ba_review_histori';
		$data['review'] = $this->Pokja_paket_m->get_ba_review_histori();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/pokja_review_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$html = $this->load->view('admin/pokja_review_cetak', $data, true);
			$filename = 'cetak_pokja_history_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function ba_review()
	{
		$data['inc'] = 'pokja_ba_review';
		$data['review'] = $this->Pokja_paket_m->get_paket_review();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/pokja_review_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$html = $this->load->view('admin/pokja_review_cetak', $data, true);
			$filename = 'cetak_pokja_history_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function display_image()
	{
		header('Content-type: image/jpeg');
		readfile($_GET['image']);
	}

	public function reviu_bersama($kode_rup)
	{
		if(empty($_FILES['foto1']['name'])){
			$this->form_validation->set_rules('foto1','Foto Absen','trim|required');
		}
		if(empty($_FILES['foto2']['name'])){
			$this->form_validation->set_rules('foto2','Reviu 1','trim|required');
		}
		if(empty($_FILES['foto3']['name'])){
			$this->form_validation->set_rules('foto3','Reviu 2','trim|required');
		}
		if(empty($_FILES['foto4']['name'])){
			$this->form_validation->set_rules('foto4','Reviu 3','trim|required');
		}

		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required');
		$this->form_validation->set_rules('namapa','Nama PA','trim|required');
		$this->form_validation->set_rules('namapptk','Nama PPTK','trim|required');
		$this->form_validation->set_rules('undreviu','Und Reviu','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pokja_reviu_bersama_fm';
			$this->load->view('admin/index', $data);
		}else{
			$id = 0;
			// $id = $this->Pokja_paket_m->reviu_bersama_insert();	
			
			$upload_result[] = $this->reviu_bersama_upload('foto1',$kode_rup,$id);
			$upload_result[] = $this->reviu_bersama_upload('foto2',$kode_rup,$id);
			$upload_result[] = $this->reviu_bersama_upload('foto3',$kode_rup,$id);
			$upload_result[] = $this->reviu_bersama_upload('foto4',$kode_rup,$id);

			if(!in_array('noimage.png',$upload_result))
			{
				$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
				$this->Pokja_paket_m->reviu_bersama_insert($upload_result);
			}else{
				$this->session->set_flashdata('msg','<div class="callout callout-danger">Gagal</div>');
			}

			redirect('pokja_paket/review');
		}	
	}

	public function reviu_bersama_upload($berkas, $kode_rup, $id)
	{
		if (!file_exists('/var/www/html/bpbj/monev/reviubersama/' . $kode_rup)) {
			mkdir('/var/www/html/bpbj/monev/reviubersama/' . $kode_rup, 0755, true);
		}

		$config['upload_path'] = './monev/reviubersama/' . $kode_rup;
		$config['allowed_types'] = '*';

		// $config['max_size'] = '1024';

		$this->upload->initialize($config);

		if (!$this->upload->do_upload($berkas)){
			$path = 'noimage.png';
		}else{
			$path = $this->upload->data('full_path');
		}

		return $path;
	}

	public function reviu_bersama_print($id = 0)
	{
		$data['detail'] = $this->Pokja_paket_m->reviu_bersama_get($id);
		$data['pokja_list'] = $this->Pokja_paket_m->get_pokja_list($id);
		$this->load->view('admin/pokja_reviu_bersama_out', $data);
	}

	public function review_history_cetak($id = 0)
	{
		$detail = $this->Pokja_paket_m->get_last_history_review($id);
		$data['detail'] = $detail;
		$data['data_karo'] = $this->Pokja_paket_m->get_paraf_karo();

		if($detail->review_status == 2 && $detail->posisi == 2){
			$this->load->view('admin/pokja_review_cetak2', $data);
		}else{
			$this->load->view('admin/pokja_review_cetak1', $data);
		}
	}

	// module baru

	public function review_pokja($kode_rup = 0)
	{
		$this->Pokja_paket_m->review_dokumen($kode_rup);

		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required');
		$this->form_validation->set_rules('tanggal','Tanggal','trim|required');

		$this->form_validation->set_rules('nama_kpa','Nama KPA','trim|required');
		$this->form_validation->set_rules('hps','HPS','trim|required');
		$this->form_validation->set_rules('jangka_waktu_pelaksanaan','Jangka Waktu Pelaksanaan','trim|required');
		$this->form_validation->set_rules('jenis_kontrak','Jenis Kontrak','trim|required');

		$this->form_validation->set_rules('review_spesifikasi_teknis','Spesifikasi Teknis','trim|required');
		$this->form_validation->set_rules('review_hps','HPS','trim|required');
		$this->form_validation->set_rules('review_rancangan_kontrak','Rancangan Kontrak','trim|required');
		$this->form_validation->set_rules('review_dokumen_anggaran','Dokumen Anggaran','trim|required');
		$this->form_validation->set_rules('review_id_rup','ID RUP','trim|required');
		$this->form_validation->set_rules('review_waktu_penggunaan','Waktu Penggunaan','trim|required');
		$this->form_validation->set_rules('review_analisis_pasar','Analisis Pasar','trim|required');
		$this->form_validation->set_rules('review_komitmen_reviu','Komitmen Reviu','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pokja_review_pokja';
			$data['detail'] = $this->Pokja_paket_m->get_detail_review($kode_rup);
			$this->load->view('admin/index', $data);
		}else{
			$this->Pokja_paket_m->update_review_pokja($kode_rup);
			redirect('pokja_paket/review');
		}
	}

	public function review_cetak($kode_rup = 0)
	{
		$detail = $this->Pokja_paket_m->get_last_review($kode_rup);
		$nomor = '602.PBJ_'.$detail->id.'_'.date('m').'_BA_REVIU_'.$detail->sp_kelompok.'_'.date('Y');

		// membuat qrcode
		// $this->load->library('ciqrcode');

		// $config['cacheable'] = true; //boolean, the default is true
	 	// $config['cachedir'] = 'assets/'; //string, the default is application/cache/
	 	// $config['errorlog'] = 'assets/'; //string, the default is application/logs/
	 	// $config['imagedir'] = 'assets/images/'; //direktori penyimpanan qr code
	 	// $config['quality'] = true; //boolean, the default is true
	 	// $config['size'] = '1024'; //interger, the default is 1024
	 	// $config['black'] = array(224,255,255); // array, default is array(255,255,255)
	 	// $config['white'] = array(70,130,180); // array, default is array(0,0,0)

		// $this->ciqrcode->initialize($config);

	 	$image_name = $nomor.'.png'; //buat name dari qr code sesuai dengan nim

	 	// $params['data'] = $nomor; //data yang akan di jadikan QR CODE
	 	// $params['level'] = 'H'; //H=High
	 	// $params['size'] = 10;
	 	// $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
	 	// $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

		// $data['qrcode'] = $config['imagedir'].$image_name;

		$data['detail'] = $this->Pokja_paket_m->get_last_review($kode_rup);

		// print_r($data['detail']);

		$jns = $this->db->get_where('tb_rup',array('kode_rup'=>$kode_rup))->row('jenis_pengadaan');

		if($jns == 'Barang'){
			$data['paraf_kasubbag'] = $this->User_m->get_detail(array('username'=>'barangjasa'));
		}
		
		// $data['data_karo'] = $this->Pokja_paket_m->get_paraf_karo();
		$data['data_karo'] = $this->User_m->get_detail(array('aktif'=>'1'));
		$data['paraf_kabag'] = $this->User_m->get_detail(array('username'=>'kabagpp'));
		$data['paraf_karo'] = $this->User_m->get_detail(array('username'=>'karo.bpbj'));

		// re-update tabel review
		$key_review = array('kode_rup'=>$kode_rup);
		$data_review['nomor'] = $nomor;
		//$data_review['qrcode'] = $config['imagedir'].$image_name;

		$this->db->update('tb_review',$data_review,$key_review);
		$this->db->update('tb_review_paket',$data_review,array('id'=>$detail->id));

		if($this->uri->segment(4) == 1){
			$this->load->view('admin/pokja_review_cetak', $data);
		}else{
			$this->load->view('admin/pokja_review_final_cetak', $data);
		}
	}

	public function review_cetak_mpdf($kode_rup = 0)
	{
		$detail = $this->Pokja_paket_m->get_last_review($kode_rup);
		$nomor = '602.PBJ_'.$detail->id.'_03_BA_REVIU_'.$detail->sp_kelompok.'_'.date('Y');

		// membuat qrcode
		$this->load->library('ciqrcode');

		$config['cacheable'] = true; //boolean, the default is true
	    $config['cachedir'] = 'assets/'; //string, the default is application/cache/
	    $config['errorlog'] = 'assets/'; //string, the default is application/logs/
	    $config['imagedir'] = 'assets/images/'; //direktori penyimpanan qr code
	    $config['quality'] = true; //boolean, the default is true
	    $config['size'] = '1024'; //interger, the default is 1024
	    $config['black'] = array(224,255,255); // array, default is array(255,255,255)
	    $config['white'] = array(70,130,180); // array, default is array(0,0,0)

		$this->ciqrcode->initialize($config);

	    $image_name = $nomor.'.png'; //buat name dari qr code sesuai dengan nim

	    $params['data'] = $nomor; //data yang akan di jadikan QR CODE
	    $params['level'] = 'H'; //H=High
	    $params['size'] = 10;
	    $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
	    $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

		$data['qrcode'] = $config['imagedir'].$image_name;
		$data['detail'] = $this->Pokja_paket_m->get_last_review($kode_rup);

		$jns = $this->db->get_where('tb_rup',array('kode_rup'=>$kode_rup))->row('jenis_pengadaan');

		if($jns == 'Barang'){
			$data['paraf_kasubbag'] = $this->User_m->get_detail(array('username'=>'barangjasa'));
		}
		
		$data['paraf_kabag'] = $this->User_m->get_detail(array('username'=>'kabagpp'));
		$data['paraf_karo'] = $this->User_m->get_detail(array('username'=>'karo.bpbj'));

		// re-update tabel review
		$key_review = array('kode_rup'=>$kode_rup);
		$data_review['nomor'] = $nomor;
		$data_review['qrcode'] = $config['imagedir'].$image_name;
		$this->db->update('tb_review',$data_review,$key_review);

		if($this->uri->segment(4) == 1){
			$this->load->view('admin/pokja_review_cetak', $data);
		}else{
			$this->load->view('admin/pokja_review_final_cetak', $data);
		}

		require_once './vendor/autoload.php';

		$mpdf = new \Mpdf\Mpdf(['tempDir' => '/mpdf/mpdf/tmp/mpdf']);
		$mpdf->WriteHTML('...');

		$mpdf->AddPage();
		$mpdf->WriteHTML('...');

		$mpdf->Output();
	}

	// end module

	public function history()
	{
		$data['inc'] = 'pokja_history';
		$data['history'] = $this->Pokja_paket_m->get_history();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/cetak_pokja_history', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$html = $this->load->view('admin/cetak_pokja_history', $data, true);
			$filename = 'cetak_pokja_history_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function review_update()
	{
		$kode_rup = $this->input->post('kode_rup');
		$status = $this->input->post('status');
		$keterangan = $this->input->post('keterangan');

		// ubah status tb_review
		$data['tgl_review'] = date('Y-m-d');
		$data['status'] = $status;
		$this->db->update('tb_review', $data, array('kode_rup'=>$kode_rup));

		// insert ke tb_review_paket
		if($status == 0){
			$ket_status = 'review pokja';
		}elseif ($status == 1){
			$ket_status = 'review skpa';
			$posisi = 1;
		}elseif ($status == 2){
			$ket_status = 'review selesai';
			$posisi = 2;
		}elseif ($status == 5){
			$ket_status = 'review ulang';
		}

		$data['kode_rup'] = $kode_rup;
		$data['tgl_review'] = date('Y-m-d H:i:s');
		$data['status'] = $ket_status;
		$data['keterangan'] = $keterangan;
		$data['posisi'] = $posisi;
		$this->db->insert('tb_review_paket',$data);

		redirect('pokja_paket/review');
	}

	public function batal()
	{
		$this->Pokja_paket_m->batal();
		redirect('pokja_paket');
	}

	public function ajax_setuju_batal($setuju = '')
	{
		$array = explode('-',$setuju);
		$data['nip'] = $array[0];
		$data['kode_rup'] = $array[1];
		$data['hapus'] = ($array[2] == 1) ? 0 : 1 ;
		$count = $this->db->get_where('tb_hapus',array('nip'=>$data['nip'],'kode_rup'=>$data['kode_rup']))->num_rows();
		if($count == 0){
			$this->db->insert('tb_hapus',$data);
		}else{
			$this->db->update('tb_hapus',$data,array('nip'=>$data['nip'],'kode_rup'=>$data['kode_rup']));
		}
	}

	public function _generate_qrcode($text)
	{
	    $this->load->library('Ciqrcode');

	    header("Content-Type: image/png");
	    $params['data'] = $text;
	    return $this->ciqrcode->generate($params); 
	}
}
