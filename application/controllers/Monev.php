<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monev extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model(array('monev_m','monev_non_tender_m','monev_perhari_m','advokasi_m','monev_pokja_m','Pokja_paket_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		// $this->load->library(array('pdfgenerator'));

		$group = array('monev','karo','kabagpp','barang_jasa','konstruksi','staff_monev','admin_karo','user','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function _level($group = 'monev')
	{
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'home';
		$this->load->view('admin/index',$data);
	}

	public function p2k()
	{
		$data['inc'] = 'blank2';
		$this->load->view('admin/index',$data);
	}

	public function berita_acara_reviu()
	{
		$data['inc'] = 'monev_pokja_review';
		$data['review'] = $this->monev_m->get_paket_review_pokja2();
		// $data['review_histori'] = $this->monev_m->get_paket_review_histori();
		$this->load->view('admin/index', $data);
	}

	public function pengembalianreviu()
	{
		$data['inc'] = 'monev_pengembalianreviu';
		$data['pengembalianreviu'] = $this->monev_m->get_pengembalianreviu();
		$this->load->view('admin/index', $data);	
	}

	public function pengembalianreviuupload()
	{
		echo $_SERVER['REAL_DOCUMENT_ROOT'];

		$this->form_validation->set_rules('kode_rup','Pilih Paket','trim|required');
		$this->form_validation->set_rules('tgl_surat','Tgl. Surat','trim|required');
		$this->form_validation->set_rules('nomor_surat','Nomor Surat','trim|required');

		if(empty($_FILES['userfile']['name'])){
			$this->form_validation->set_rules('userfile','Upload File','trim|required');
		}

		if($this->form_validation->run() == false){
			$data['inc'] = 'monev_pengembalianreviu_form';
			$data['listpaketreview'] = $this->monev_m->get_paketreview();
			$this->load->view('admin/index', $data);
		}else{

			//upload file
			$kode_rup = $this->input->post('kode_rup');
			if (!file_exists('/home/monev/pengembalianreviu/' . $kode_rup)) {
				mkdir('/home/monev/pengembalianreviu/' . $kode_rup, 0755, true);
			}

			$config['upload_path'] = '/home/monev/pengembalianreviu/' . $kode_rup;
			$config['allowed_types'] = '*';

			$this->upload->initialize($config);

			if (!$this->upload->do_upload('userfile')){
				$file = false;
			}else{
				$file = $this->upload->data('full_path');
			}

			// if file uploaded insert data
			if($file != false){
				$this->monev_m->insert_pengembalianreviu($file);
			}
			redirect('monev/pengembalianreviu');
		}
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

	public function rup()
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

	public function realisasi_dok_hard()
	{
		$data['inc'] = 'realisasi_dok_hard';
		$data['lap'] =  $this->monev_m->realisasi_lap_dok_hard();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_realisasi_data_tender_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_realisasi_data_tender_spse_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/cetak_tender_sp', $data, false);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image2'){
			$this->load->view('admin/cetak_tender_sp2', $data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function rekap_tpd()
	{
		$var['rekap'] = true;

		$data['inc'] = 'tpd_rekap';
		$data['tpd'] = $this->monev_m->tpd_get($var);

		if(isset($_GET['button']) && $_GET['button'] == 'excel'){
			$this->load->view('admin/tpd_rekap_ex',$data);
		}elseif(isset($_GET['button']) && $_GET['button'] == 'cetak'){
			$this->load->view('admin/cetak_rekap2',$data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function paketbatal($id = 0)
	{
		$data['inc'] = 'monev_paketbatal';
		$data['paket'] = $this->monev_m->get_paketbatal();
		$this->load->view('admin/index', $data);
	}

	public function view_rup()
	{
		$data['page_title'] = 'Monev : Realisasi Jenis Pengadaan (RUP)';
		$data['inc'] = 'monev_view_rup';

		
		$data['barang'] = $this->monev_m->view_jenis_pengadaan('barang');
		$data['jasa'] = $this->monev_m->view_jenis_pengadaan('jasa lainnya');
		$data['konstruksi'] = $this->monev_m->view_jenis_pengadaan('pekerjaan konstruksi');
		$data['konsultansi'] = $this->monev_m->view_jenis_pengadaan('jasa konsultansi');
		// $data['total'] = $this->monev_m->view_jenis_pengadaan_total();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_view_rup_cetak', $data);
		}elseif( isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_view_rup_cetak', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function view_rup_skpa()
	{
		$data['page_title'] = 'Monev : Realisasi Jenis Pengadaan (RUP) Per SKPA';
		$data['inc'] = 'monev_view_rup_skpa';

		//$data['total'] = $this->monev_m->view_persatker_rup_total();
		$data['laporan'] = $this->monev_m->view_persatker_rup();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/cetak_rup_skpa', $data);
		}elseif( isset( $_GET['type'] ) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_view_rup_skpa_ex', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function view_rup_skpa2()
	{
		$data['inc'] = 'monev_view_rup_skpa2';

		// $data['total'] = $this->monev_m->view_persatker_rup_total2();
		$data['laporan'] = $this->monev_m->view_persatker_rup2();

		$this->load->view('admin/index', $data);
	}

	public function view_rup_skpa3()
	{
		$data['page_title'] = 'Monev : Realisasi Jenis Pengadaan (RUP) Per SKPA';
		$data['inc'] = 'monev_view_rup_skpa';

		// $data['total'] = $this->monev_m->view_persatker_rup_total3();
		$data['laporan'] = $this->monev_m->view_persatker_rup3();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/cetak_rup_skpa', $data);
		}elseif( isset( $_GET['type'] ) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_view_rup_skpa_ex', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	// list paket per
	public function listpaketrup()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Tender';
		$data['listpaket'] = $this->monev_m->get_listpaket('rup');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpakettender()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Tender';
		$data['listpaket'] = $this->monev_m->get_listpaket('tender');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpakettendercepat()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Tender Cepat';
		$data['listpaket'] = $this->monev_m->get_listpaket('tendercepat');
		
		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketseleksi()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Seleksi';
		$data['listpaket'] = $this->monev_m->get_listpaket('seleksi');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketpenunjukanlangsung()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Penunjukan Langsung';
		$data['listpaket'] = $this->monev_m->get_listpaket('penunjukanlangsung');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketpengadaanlangsung()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Pengadaan Langsung';
		$data['listpaket'] = $this->monev_m->get_listpaket('pengadaanlangsung');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketepurchasing()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'e-Purchasing';
		$data['listpaket'] = $this->monev_m->get_listpaket('epurchasing');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketdikecualikan()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Dikecualikan';
		$data['listpaket'] = $this->monev_m->get_listpaket('dikecualikan');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpakettipeswakelola()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Tipe Swakelola';
		$data['listpaket'] = $this->monev_m->get_listpaket('tipeswakelola');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}

	public function listpaketpenyediadidalamswakelola()
	{
		$data['inc'] = 'listpakettender_tb';
		$data['datatitle'] = 'Penyedia Didalam Swakelola';
		$data['listpaket'] = $this->monev_m->get_listpaket('penyediadidalamswakelola');

		if(isset($_GET['tahun']) && isset($_GET['type'])){
			$this->load->view('monev/listpakettender_excel', $data);
		}else{
			$this->load->view('monev/index', $data);	
		}
	}



	public function tender_per_skpa()
	{
		$data['inc'] = 'monev_lelang_skpa';
		$data['total'] = $this->monev_m->tender_per_skpa_total();
		$data['laporan'] = $this->monev_m->tender_per_skpa();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/cetak_rup_skpa', $data);
		}elseif( isset( $_GET['type'] ) && $_GET['type'] == 'excel' ){
			$this->load->view('admin/monev_rup_skpa_excel', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function view_realisasi_data_lelang_sp_cetak()
	{
		$data['inc'] = 'monev_view_realisasi_data_lelang_sp';
		$data['total'] = $this->monev_m->get_total();
		$data['lap'] = $this->monev_m->get_laporan();
		$this->load->view('admin/index',$data);
	}

	public function laporan_bps()
	{
		$nomor = 'BPBJ_LAPBPS_'. date('d_m_Y');
		$save = 'BPBJ_LAPBPS_' . date('d_m_Y') . '.png';

		$this->load->library('ciqrcode');

		$config['cacheable'] = true; //boolean, the default is true
		// $config['cachedir'] = './assets/'; //string, the default is application/cache/
		// $config['errorlog'] = './assets/'; //string, the default is application/logs/
		$config['imagedir'] = './assets/images/'; //direktori penyimpanan qr code
		$config['quality'] = true; //boolean, the default is true
		$config['size'] = '1024'; //interger, the default is 1024
		$config['black'] = array(224,255,255); // array, default is array(255,255,255)
		$config['white'] = array(70,130,180); // array, default is array(0,0,0)

		$this->ciqrcode->initialize($config);

		$image_name = $nomor.'.png'; //buat name dari qr code sesuai dengan nim

		$params['data'] = $nomor; //data yang akan di jadikan QR CODE
		$params['level'] = 'H'; //H=High
		$params['size'] = 10;
		$params['savename'] = $config['imagedir'].$save; //simpan image QR CODE ke folder assets/images/

		$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

		// $data['qrcode'] = $config['imagedir'].$save;

		// print_r($params);
		// echo '<img src="'. $config['imagedir'].$save .'"></img>';

		$data['inc'] = 'monev_laporan_bps';
		$data['rekap1'] = $this->monev_m->rekap_jenis_pengadaan1();
		$data['rekap2'] = $this->monev_m->rekap_jenis_pengadaan2();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_laporan_bps_ctk', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function laporan_bps2()
	{
		$data['inc'] = 'monev_laporan_bps2';

		// $data['rekap1'] = $this->monev_m->rekap_jenis_pengadaan1();
		// $data['rekap2'] = $this->monev_m->rekap_jenis_pengadaan2();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_laporan_bps_ctk', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function paket_review()
	{
		$data['inc'] = 'monev_paket_review';
		$data['review'] = $this->monev_m->get_paket_review();
		$this->load->view('admin/index', $data);
	}

	public function history_review()
	{
		$data['inc'] = 'monev_history_review';
		$data['history'] = $this->monev_m->get_history();

		if(isset( $_GET['type'] ) && $_GET['type'] == 'image'){
			$this->load->view('admin/cetak_pokja_history', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf2'){
			$this->load->view('admin/monev_history_cetak2', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_history_cetak', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function advokasi()
	{
		//$this->load->library('pdfgenerator');

		$data['inc'] = 'monev_advokasi';
		$data['advokasi'] = $this->advokasi_m->get(array());

		if(isset($_GET['cetak']) && $_GET['cetak'] == 'pdf')
		{
			$html = $this->load->view('admin/monev_advokasi_cetak', $data, true);
			$filename = 'cetak_advokasi_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	// realisasi perhari
	public function realisasi_data_tender_perhari()
	{
		$data['inc'] = 'monev_realisasi_data_tender_perhari';
		// // $data['total'] = $this->monev_perhari_m->get_total();
		$data['lap'] = $this->monev_perhari_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, false);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function realisasi_data_non_tender_perhari()
	{
		$data['inc'] = 'monev_realisasi_data_non_tender_perhari';

		$data['total'] = $this->monev_perhari_m->get_total_non_tender();
		$data['lap'] = $this->monev_perhari_m->get_laporan_non_tender();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf')
		{
			$this->load->view('admin/monev_realisasi_non_tender_perhari_cetak', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_realisasi_non_tender_perhari_cetak', $data, false);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	// realisasi SP
	public function realisasi_data_tender()
	{
		$data['inc'] = 'monev_realisasi_data_tender';
		$data['total'] = $this->monev_m->get_total();
		$data['lap'] = $this->monev_m->get_laporan();
		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				$this->load->view('admin/cetak_tender', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				$this->load->view('admin/cetak_tender', $data, false);
		}else{
				$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_non_tender()
	{
		$data['inc'] = 'monev_realisasi_data_non_tender';
		$data['total'] = $this->monev_non_tender_m->get_total_non_tender();
		$data['lap'] = $this->monev_non_tender_m->get_laporan_non_tender();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_realisasi_data_non_tender_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_realisasi_data_non_tender_ex', $data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_non_tender_complete()
	{
		$data['inc'] = 'monev_realisasi_data_non_tender_complete';

		// $data['total'] = $this->monev_non_tender_m->get_total_non_tender_complete();
		$data['lap'] = $this->monev_non_tender_m->get_laporan_non_tender_complete();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_realisasi_data_non_tender_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){

		}elseif(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_realisasi_data_non_tender_complete_ex', $data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_tender_sp()
	{
		$data['inc'] = 'monev_realisasi_data_tender_sp';

		$data['total'] = $this->monev_m->get_total();
		$data['lap'] = $this->monev_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				$this->load->view('admin/cetak_tender_sp', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf2'){
				$this->load->view('admin/cetak_tender_sp2', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				$this->load->view('admin/cetak_tender_sp', $data, false);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image2'){
			$this->load->view('admin/cetak_tender_sp2', $data);
		}else{
				$this->load->view('admin/index',$data);
		}
	}

	// -- realisasi data tender spse

	public function realisasi_data_tender_spse()
	{
		$data['inc'] = 'monev_realisasi_data_tender_spse';
		//$data['total'] = $this->monev_m->get_total_spse();
		$data['lap'] =  $this->monev_m->get_laporan_spse();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
				$this->load->view('admin/monev_realisasi_data_tender_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				$this->load->view('admin/monev_realisasi_data_tender_spse_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				$this->load->view('admin/cetak_tender_sp', $data, false);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image2'){
			$this->load->view('admin/cetak_tender_sp2', $data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_tender_spse_tpd()
	{
		$data['inc'] = 'monev_realisasi_data_tender_spse_tpd';
		$data['lap'] =  $this->monev_m->get_laporan_spse_tpd();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
				$this->load->view('admin/monev_realisasi_data_tender_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				$this->load->view('admin/monev_realisasi_data_tender_spse_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				$this->load->view('admin/cetak_tender_sp', $data, false);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image2'){
			$this->load->view('admin/cetak_tender_sp2', $data);
		}else{
			$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_tender_spse_harian()
	{
		$data['inc'] = 'monev_realisasi_data_tender_spse_harian';

		$data['total'] = $this->monev_m->get_total_spse_harian();
		$data['lap'] =  $this->monev_m->get_laporan_spse_harian();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
				$this->load->view('admin/monev_realisasi_data_tender_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				// $this->load->view('admin/monev_realisasi_data_tender_spse_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				// $this->load->view('admin/cetak_tender_sp', $data, false);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image2'){
			// $this->load->view('admin/cetak_tender_sp2', $data);
		}else{
				$this->load->view('admin/index',$data);
		}
	}

	public function realisasi_data_tender_spse_selisih()
	{
		$data['inc'] = 'monev_realisasi_data_tender_spse_selisih';
		$data['selisih'] =  $this->monev_m->realisasi_data_tender_spse_selisih();

		$this->load->view('admin/index',$data);
	}

	// 2 ajax detail

	public function realisasi_data_tender_spse_detail_paket_sp($param)
	{
		$data['detail_paket'] = $this->monev_m->realisasi_data_tender_spse_detail($param);
		$this->load->view('monev/realisasi_data_tender_spse_detail_paket_sp',$data);
	}

	public function realisasi_data_tender_spse_ajax_reviu($param)
	{
		$data['detail_paket'] = $this->monev_m->realisasi_data_tender_spse_ajax_reviu($param);
		$this->load->view('monev/realisasi_data_tender_spse_detail_paket_reviu',$data);
	}

	public function realisasi_data_tender_spse_ajax($param)
	{
		$data['detail_paket'] = $this->monev_m->realisasi_data_tender_spse_detail($param);
		$this->load->view('monev/realisasi_data_tender_spse_detail_paket',$data);
	}

	public function daftarpaketspse_tayang()
	{
		// $data['daftar_paket'] = $this->monev_m->get_daftar_paket_spse($bttm);	
	}

	public function daftar_paket_spse($bttm = 'belum_tayang')
	{
		$data['daftar_paket'] = $this->monev_m->get_daftar_paket_spse($bttm);

		if($bttm != 'belum_tayang' && isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/daftar_paket_spse_excel',$data);
		}elseif($bttm == 'belum_tayang' && !isset($_GET['type'])){
			$data['inc'] = 'daftar_paket_spse_tb_bt';
			$this->load->view('monev/index',$data);
		}elseif($bttm == 'belum_tayang' && $_GET['type'] == 'excel'){
			$this->load->view('monev/daftar_paket_spse_tb_bt_ex',$data);
		}elseif($bttm == 'menang2'){
			$data['inc'] = 'daftar_paket_spse_tb_menang2';
			$this->load->view('monev/index',$data);
		}elseif($bttm == 'menang'){
			$data['inc'] = 'daftar_paket_spse_tb_menang';
			$this->load->view('monev/index',$data);
		}else{
			$data['inc'] = 'daftar_paket_spse_tb';
			$this->load->view('monev/index',$data);
		}
	}

	public function daftarpaketspse_tayang2()
	{
		$var = 'tayang2';
		$data['inc'] = 'daftarpaketspse_tayang2_tb';
		$data['daftar_paket'] = $this->monev_m->get_daftar_paket_spse($var);

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/daftarpaketspse_tayang2_tb_cetak',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function daftar_paket_spse_menang2($bttm = 'menang2')
	{
		$bttm = 'menang2';
		$data['header'] = $bttm;
		$data['daftar_paket'] = $this->monev_m->get_daftar_paket_spse($bttm);

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/daftar_paket_spse_excel_menang2',$data);
		}else{
			$data['inc'] = 'daftar_paket_spse_tb_menang2';
			$this->load->view('monev/index',$data);
		}
	}

	public function distribusi_paket_tender()
	{
		$data['total'] = $this->monev_m->get_distribusi_paket_tender_tt();
		$data['items'] = $this->monev_m->get_distribusi_paket_tender();

		if(isset($_GET['type']) && $_GET['type'] == 'excel')
		{
			$this->load->view('monev/distribusi_paket_tender_ex', $data);
		}else{
			$data['inc'] = 'distribusi_paket_tender_tb';
			$this->load->view('monev/index', $data);
		}
	}

	public function dpt_ajax_detail_paket($data)
	{
		$tahun = date('Y');
		$tahun2 = date('Y') + 1;

		$array = explode('-', $data);
		$id = $array[0];
		$jns = $array[1];

		$sql = "SELECT p.*, r.nama_paket, r.pagu_rup, r.nama_satker, s.sp_tanggal FROM tb_sp s, tb_sp_paket p, tb_rup r 
		WHERE s.sp_id = p.sp_id AND r.kode_rup = p.kode_rup AND (r.tahun = $tahun OR r.tahun = $tahun2) AND p.sp_id = $id AND r.jenis_pengadaan LIKE '%$jns%'";

		if($jns == 'nt'){
			$sql = "SELECT p.*, r.nama_paket, r.pagu_rup, r.nama_satker, s.sp_tanggal 
			FROM tb_sp s, tb_sp_paket p, tb_rup r 
			WHERE s.sp_id = p.sp_id AND r.kode_rup = p.kode_rup AND (r.tahun = $tahun OR r.tahun = $tahun2) AND p.sp_id = $id AND p.nt = 'ya'";
		}

		$view['detail_paket'] = $this->db->query($sql)->result();
		$this->load->view('monev/distribusi_paket_tender_tb_ajax',$view);
	}

	public function info_status_paket_spse()
	{
		$data['inc'] = 'info_status_paket_spse';
		$data['paket'] = $this->monev_m->get_info_status_paket_spse();

		if(isset($_GET['type']) && $_GET['type'] == 'excel')
		{
			$this->load->view('monev/info_status_paket_spse_ex', $data);
		}else{
			$data['inc'] = 'info_status_paket_spse';
			$this->load->view('monev/index', $data);
		}
	}

	public function info_jadwal_paket_spse()
	{
		$data['inc'] = 'info_jadwal_paket_spse';
		$data['paket'] = $this->monev_m->get_info_jadwal_paket_spse();

		if(isset($_GET['type']) && $_GET['type'] == 'excel')
		{
			$this->load->view('monev/info_jadwal_paket_spse_ex', $data);
		}else{
			$data['inc'] = 'info_jadwal_paket_spse';
			$this->load->view('monev/index', $data);
		}
	}

	public function info_status_jadwal_spse_barang()
	{
		$data['paket'] = $this->monev_m->get_info_status_jadwal_spse_barang();

		if(isset($_GET['type']) && $_GET['type'] == 'excel')
		{
			$this->load->view('monev/info_status_paket_spse_ex', $data);
		}else{
			$data['inc'] = 'info_status_jadwal_spse_barang';
			$this->load->view('monev/index', $data);
		}
	}

	public function laporan_bps_spse()
	{
		$this->load->model(array('monev_spse_m'));

		$data['inc'] = 'laporan_bps_spse';
		$data['lap'] = $this->monev_m->laporan_bps_spse_2();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/laporan_bps_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('monev/laporan_bps_spse_pdf', $data);
		}else{
			$this->load->view('monev/index', $data);
		}

	}

	public function laporan_bps_spse2()
	{
		$this->load->model(array('monev_spse_m'));

		// $data['inc'] = 'laporan_bps_spse';
		// $data['lap'] = $this->monev_spse_m->laporan_bps_spse_2();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			// $this->load->view('monev/laporan_bps_spse_ex', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			// $this->load->view('monev/laporan_bps_spse_pdf', $data);
		}else{
			// $this->load->view('monev/index', $data);
		}

		// $start = $month = strtotime('2020-01-01');
		// $end = strtotime('2020-12-01');
		// while($month < $end)
		// {
		//      $bulan = date('Y-m', $month), PHP_EOL;
		// 		 $month = strtotime("+1 month", $month);
		// 		 echo $bulan;
		// }

		// print_r($data);

	}

	// end SPSE

	public function realisasi_data_non_tender_sp()
	{
		$data['inc'] = 'monev_realisasi_data_non_tender_sp';
		$data['total'] = $this->monev_m->get_total_non_tender();
		$data['lap'] = $this->monev_m->get_laporan_non_tender();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
				$this->load->view('admin/cetak_non_tender_sp', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
				$this->load->view('admin/cetak_non_tender_sp', $data, false);
		}else{
				$this->load->view('admin/index',$data);
		}
	}

	public function format_wa()
	{
		$data['inc'] = 'monev_realisasi_data_tender_lap';
		$data['inc'] = 'monev_format_wa';
		$data['total'] = $this->monev_perhari_m->get_format_wa_total();
		$data['lap'] = $this->monev_perhari_m->get_format_wa_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf')
		{
			$html = $this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, true);
			$filename = 'cetak_realisasi_tender_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, false);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function format_wa_spse()
	{
		// $data['inc'] = 'monev_format_wa_spse';
		// $data['total'] = $this->monev_perhari_m->get_format_wa_total_spse();
		// $data['lap'] = $this->monev_perhari_m->get_format_wa_laporan_spse();

		// if(isset($_GET['type']) && $_GET['type'] == 'pdf')
		// {
		// 	$html = $this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, true);
		// 	$filename = 'cetak_realisasi_tender_'.time();
		// 	$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		// }elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
		// 	$this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, false);
		// }else{
		// 	$this->load->view('admin/index', $data);
		// }
	}

	public function realisasi_data_tender_lap()
	{
		$data['inc'] = 'monev_realisasi_data_tender_lap';
		$data['total'] = $this->monev_perhari_m->get_total_simple();
		$data['lap'] = $this->monev_perhari_m->get_laporan_simple();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf')
		{
			$html = $this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, true);
			$filename = 'cetak_realisasi_tender_'.time();
			$this->pdfgenerator->generate($html, $filename, true, 'legal', 'landscape');
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_realisasi_tender_perhari_cetak', $data, false);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function laporan_harian()
	{
		$data['inc'] = 'laporan_harian';
		$data['paket'] = $this->monev_m->ambil_selisih();
		$this->load->view('monev/index', $data);
	}

	// list pokja
	public function pokja_satu()
	{
		$data['inc'] = 'monev_view_pokja';
		$data['total'] = $this->monev_pokja_m->get_total();
		$data['lap'] = $this->monev_pokja_m->get_laporan();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_view_pokja_ctk', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_view_pokja_ctk', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function pokja_dua()
	{
		$data['inc'] = 'monev_skpa_pokja';
		$data['lap'] = $this->monev_pokja_m->get_skpa_pokja();
		$this->load->view('admin/index', $data);
	}

	public function view_pokja_popup($param = '')
	{
		$data['list_paket'] = $this->monev_pokja_m->get_detail_paket($param);
		$this->load->view('admin/monev_view_pokja_popup', $data);
	}

	public function ajax_detail_jadwal($kode_rup = '')
	{
		$data['list_jadwal'] = $this->monev_pokja_m->get_detail_jadwal($kode_rup);
		$this->load->view('admin/monev_view_ajax_jadwal', $data);
	}

	// daftar paket
	public function daftar_paket($var = 'masuk')
	{
		$data['inc'] = 'monev_daftar_paket';
		$data['paket'] = $this->monev_m->get_daftar_paket($var);
		$this->load->view('admin/index',$data);
	}

	public function daftar_paket_batallelang()
	{
		$data['inc'] = 'monev_daftar_paket_batallelang';
		$data['paket'] = $this->monev_m->get_daftar_paket('batal_lelang');
		// $this->load->view('admin/index',$data);

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_daftar_paket_batallelang_ex', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function daftar_paket_status_paket()
	{
		$data['inc'] = 'monev_daftar_paket_status_paket';
		$data['paket'] = $this->monev_m->get_daftar_paket_status_paket();
		$data['paket_batal'] = $this->monev_m->get_daftar_paket_status_paket_batal();

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_daftar_paket_status_paket_pdf', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_daftar_paket_status_paket_excel', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	// sp belum tayang
	public function daftar_paket_sp_bt()
	{
		$data['inc'] = 'monev_daftar_paket_sp_bt';
		$data['paket'] = $this->monev_m->get_daftar_paket_sp_bt();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/monev_daftar_paket_sp_bt_ctk', $data);
			// }elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			// $this->load->view('admin/monev_data_review_ctk', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function daftar_paket_batal()
	{
		$data['inc'] = 'monev_daftar_paket';
		$data['paket'] = $this->monev_m->get_daftar_paket_batal();
		$this->load->view('admin/index',$data);
	}

	public function data_review($var = 'belum')
	{
		$data['inc'] = 'monev_data_review';
		$data['data_review'] = $this->monev_m->get_data_review($var);

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){
			$this->load->view('admin/monev_data_review_ctk', $data);
		}elseif(isset($_GET['type']) && $_GET['type'] == 'image'){
			$this->load->view('admin/monev_data_review_ctk', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function data_review_reviu_ulang($kode_rup = 0)
	{
		$sql = "SELECT rp.* FROM tb_review_paket rp WHERE rp.kode_rup = $kode_rup";
		$data = $this->db->query($sql)->row_array();
		
		unset($data['id']);
		$data['tgl_review'] = date('Y-m-d H:i:s');
		$data['status'] = 'reviu ulang';
		$data['keterangan'] = 'paket ini dilakukan reviu ulang oleh pokja';

		$set['status'] = 0;
		$this->db->update('tb_review',$set,array('kode_rup'=>$kode_rup));
		
		$this->db->insert('tb_review_paket',$data);

		redirect('monev/data_review/selesai');
	}

	public function pegawai_pokja()
	{
		$data['inc'] = 'pegawai_pokja';
		// $data['paket'] = $this->monev_m->get_daftar_paket_batal();
		$this->load->view('monev/index',$data);
	}

	public function ajax_detail_paket_pokja($param = '')
	{
		$data['list_paket'] = $this->monev_pokja_m->get_detail_paket($param);
		$this->load->view('admin/monev_ajax_detail_paket', $data);
	}

	public function ajax_detail_paket($param = '')
	{
		$data['list_paket'] = $this->monev_m->get_detail_paket($param);
		$this->load->view('admin/monev_ajax_detail_paket', $data);
	}

	public function reviubersama(){
		
	}

}
