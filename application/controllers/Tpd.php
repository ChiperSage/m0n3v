<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tpd extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['tpd_m','Jam_m']);

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('skpa'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$date = date('Y-m-d');

		$data['inc'] = 'tpd_table';
		$data['jam'] = $this->Jam_m->get();
		$data['tpd'] = $this->tpd_m->dinas_paket();
		$data['sisa_antrian'] = $this->tpd_m->sisa_antrian();
		$this->load->view('admin/index', $data);

		// $paket_pilihan = array();
		$paket_pilihan = $_REQUEST['paket_pilihan'];

		if(count($paket_pilihan) > 0){

			$id_antrian = $this->_get_id_antrian();

			foreach ($paket_pilihan as $val) {
				$key = array('kode_rup'=>$val);
				// $set = array('id_antrian'=>$id_antrian,'tpd_status'=>7,'tanggal_antrian'=>$date);
				$set = array('id_antrian'=>$id_antrian,'tpd_status'=>7,'tanggal_antrian'=>$date,'tanggal_pengembalian'=>'0000-00-00 00:00:00','alasan'=>'','ba'=>'belum');
				$this->db->update('tb_tpd',$set,$key);
			}

			redirect('tpd/tiket/'.$id_antrian);

		}
	}

	public function pakettelahkirim()
	{
		$data['inc'] = 'tpd_pakettelahkirim';
		$data['tpd'] = $this->tpd_m->get_pakettelahkirim();
		$this->load->view('admin/index', $data);
	}

	public function create()
	{
		$disabled = false;
		$set = $this->tpd_m->get_settpd();

		$awal_date = '0000-00-00';

		if($awal_date == $set){
			$disabled = true;
		}

		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required|is_unique[tb_tpd.kode_rup]',array('is_unique'=>'Paket Tersebut Sudah Pernah di Input'));
		$this->form_validation->set_rules('nama_pabung','Nama PABUNG','trim|required');
		$this->form_validation->set_rules('nilai_hps','Nilai HPS','trim|required');
		$this->form_validation->set_rules('pengelola_teknis_kegiatan','PTK','trim|required');
		$this->form_validation->set_rules('status_pengadaan','Status Pengadaan','trim|required');
		$this->form_validation->set_rules('norega1','No. Registrasi A1','trim|required|callback_norega1_check');
		$this->form_validation->set_rules('jenis_dana','Jenis Dana','trim|required');

		if($this->form_validation->run() == false)
		{

			$data['inc'] = 'tpd_form';
			$data['disabled'] = $disabled;
			$data['list_rup2'] = $this->tpd_m->list_rup2();
			$this->load->view('admin/index', $data);

		}else{

			$this->tpd_m->create();
			redirect('tpd');

		}
	}

	public function update($id = 0)
	{
		$this->form_validation->set_rules('kode_rup','Kode RUP','trim|required');
		$this->form_validation->set_rules('nama_pabung','Nama PABUNG','trim|required');
		$this->form_validation->set_rules('nilai_hps','Nilai HPS','trim|required');
		$this->form_validation->set_rules('pengelola_teknis_kegiatan','PTK','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'tpd_form';
			$data['list_rup2'] = $this->tpd_m->list_rup2();
			$data['detail'] = $this->tpd_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->tpd_m->update($id);
			redirect('tpd');
		}
	}

	public function norega1_check()
	{
		$allvalid = array();

		$var = $this->input->post('norega1');

	    $sub1 = substr($var,0,4);
	    $sub2 = substr($var,4,1);
	    $sub3 = substr($var,5,1);
	   	$sub4 = substr($var,6,1);
	    $sub5 = substr($var,7,2);

	    if( is_numeric($sub1) && ($sub2 == '.') && ($sub3 == 'A') && ($sub4 == '.') && is_numeric($sub5) ){
	      	return true;
	    }else{
	    	$this->form_validation->set_message('norega1', 'No. RegA1 Tidak Valid');
	    	return false;
	    }
	}

	// ajax
	public function tampilpaket($kode_rup)
	{
		$str = "SELECT r.*,l.hps FROM tb_rup r
		LEFT JOIN tb_lelang l ON r.kode_rup = l.kode_rup
		WHERE r.kode_rup = $kode_rup";
		$data = $this->db->query($str)->row();
		echo json_encode($data);
	}

	public function _get_id_antrian()
	{
		$tanggal = date('Y-m-d');

		$key = array('tanggal'=>$tanggal);
		$result = $this->db->get_where('tb_tpd_antrian',$key)->num_rows();

		$last_no = $this->db->query("SELECT nomor_antrian FROM tb_tpd_antrian ORDER BY id DESC LIMIT 1")->row('nomor_antrian');
		$last_date = $this->db->query("SELECT tanggal FROM tb_tpd_antrian ORDER BY id DESC LIMIT 1")->row('tanggal');

		// batas antrian
		$limit_sampai = 1040;

		if($result == 0){

			$nomor = 1001;
			$tanggal = date('Y-m-d');

		}elseif($result > 0 && $last_no != $limit_sampai){

			$last_nomor = $this->db->query("SELECT nomor_antrian FROM tb_tpd_antrian ORDER BY id DESC LIMIT 1")->row('nomor_antrian');

			$tanggal = $last_date;
			$nomor = ($last_nomor + 1);

		}elseif($last_no == $limit_sampai){

			// jika sudah penuh hari ini

			// $datetime = new DateTime($last_date);
			// $datetime->modify('+1 day');
			//
			// $tanggal = $datetime->format('Y-m-d');
			// $nomor = 1001;

		}

		// ambil id_satker sesuai login
		$sess_id = $this->session->userdata('user_id');
		$sess_data = $this->ion_auth->user($sess_id)->row();
		$id_satker = $sess_data->id_satker;

		$data['tanggal'] = $tanggal;
		$data['nomor_antrian'] = $nomor;
		$data['id_satker'] = $id_satker;
		$this->db->insert('tb_tpd_antrian',$data);

		return $this->db->insert_id();

		// $data['inc'] = 'tpd_ambil_nomor';
		// $this->load->view('admin/index',$data);
	}

	public function tiket($id_antrian = 0)
	{
		$query_list_paket = "SELECT * FROM tb_tpd a WHERE a.id_antrian = $id_antrian";
		$data_paket = $this->db->query($query_list_paket)->result();

		$this->db->select('a.*,b.nama as nama_satker');
		$this->db->join('tb_skpa b','a.id_satker = b.kode','left');
		$data_tiket = $this->db->get_where('tb_tpd_antrian a',array('a.id'=>$id_antrian))->row();

		if(isset($_GET['print']) && $_GET['print'] == 'true'){
			$data['data_paket'] = $data_paket;
			$data['data_tiket'] = $data_tiket;
			$this->load->view('admin/tpd_ambil_nomor_ctk',$data);
		}else{
			$data['inc'] = 'tpd_ambil_nomor';
			$data['data_paket'] = $data_paket;
			$data['data_tiket'] = $data_tiket;
			$this->load->view('admin/index',$data);
		}
	}

	public function delete($kode_rup = 0)
	{
		$this->tpd_m->delete($kode_rup);
		redirect('tpd');
	}
}
