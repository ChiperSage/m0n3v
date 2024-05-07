<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staf_monev extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['staf_monev_m','Pokja_paket_m']);

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('barang_jasa','konstruksi','staff_monev','pokja');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{

	}

	public function rup()
	{
		$data['first_url'] = '';
		$data['suffix'] = '';

		if(isset($_GET['search'])){
			$data['first_url'] = base_url().'staf_monev/rup/0/?search='.$_GET['search'];
			$data['suffix'] = '/?search='.$_GET['search'];
			$config['search'] = $_GET['search'];
		}
		if(isset($_GET['tahun'])){
			$data['first_url'] = base_url().'staf_monev/rup/0/?tahun='.$_GET['tahun'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'];
			$config['tahun'] = $_GET['tahun'];
		}

		if(isset($_GET['search']) && isset($_GET['tahun'])){
			$data['first_url'] = base_url().'staf_monev/rup/0/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];
			$data['suffix'] = '/?tahun='.$_GET['tahun'].'&search='.$_GET['search'];

			$config['search'] = $_GET['search'];
			$config['tahun'] = $_GET['tahun'];
		}

		$config['page'] = $page;
		$rup = $this->staf_monev_m->get_rup($config);

		$data['base_url'] = base_url('staf_monev/rup');
		$data['total_rows'] = $rup['count'];
		$data['per_page'] = 20;
		$data['uri_segment'] = 3;

		$data['rup'] = $rup['result'];
		if(isset($_GET['action']) && $_GET['action'] == 'print'){
			$this->load->view('staf_monev/rup_table_ex', $data);
		}else{
			$data['inc'] = 'rup_table';
			$this->load->view('staf_monev/index', $data);
		}
	}

	public function pengembalian_dok()
	{
		$data['inc'] = 'staf_monev_pengembalian_dok';
		$data['skpa'] = $this->staf_monev_m->get_skpa_list();
		$data['dokumen'] = $this->staf_monev_m->get_dokumen();
		$this->load->view('admin/index', $data);
	}

	public function alasan_pengembalian()
	{
		// $this->form_validation->set_rules('kode_rup','Kode RUP','trim|required|is_unique[tb_tpd.kode_rup]',array('is_unique'=>'Paket Tersebut Sudah Pernah di Input'));
		$this->form_validation->set_rules('nama_pengambil','Nama Pengambil','trim|required');
		$this->form_validation->set_rules('alasan','Alasan','trim|required');

		if($this->form_validation->run() == false)
		{

			$data['inc'] = 'staf_monev_pengembalian_form';
			$this->load->view('admin/index', $data);

		}else{

			$success = 0;
			$kode_rup = explode('_',$_POST['kode_rup']);
			foreach($kode_rup as $val){
				$key['kode_rup'] = $val;
				$set['tpd_status'] = 9;
				$set['tanggal_pengembalian'] = date('Y-m-d H:i:s');
				$set['nama_ambil_dok'] = $_POST['nama_pengambil'];
				$set['alasan'] = $_POST['alasan'];
				$this->db->update('tb_tpd',$set,$key);
				$success = $success + $this->db->affected_rows();

				// move 
				$qmove = "INSERT INTO tb_tpd_tmp 
				SELECT a.* FROM tb_tpd a WHERE a.kode_rup = $val";
				$this->db->query($qmove);

				// delete dari tpd
				$qdel = "DELETE FROM tb_tpd WHERE kode_rup = $val";
				$this->db->query($qdel);

				// delete dari checklist
				$qdelc = "DELETE FROM tb_tpd_barang WHERE kode_rup = $val";
				$this->db->query($qdelc);

				$qdelc = "DELETE FROM tb_tpd_jasa WHERE kode_rup = $val";
				$this->db->query($qdelc);

				$qdelc = "DELETE FROM tb_tpd_konstruksi WHERE kode_rup = $val";
				$this->db->query($qdelc);

				$qdelc = "DELETE FROM tb_tpd_konsultansi WHERE kode_rup = $val";
				$this->db->query($qdelc);

				// set di reviu
				$key_reviu['kode_rup'] = $kode_rup;
				$data_reviu['catatan'] = 'tarik dokumen';
				$this->db->update('tb_reviu',$data_reviu,$key_reviu);
			}

			redirect('staf_monev/pengembalian_dok_cetak/?kode_rup=' . $_POST['kode_rup']);
		}
	}

	public function pengembalian_dok_cetak()
	{
		$config['pengembalian_dok'] = $_GET['kode_rup'];
		$data['berita'] = $this->staf_monev_m->get_tpd_list($config);
		$this->load->view('admin/staf_monev_pengembalian_cetak', $data);
	}

	public function review()
	{
	    $data['inc'] = 'staf_monev_review';
	    $data['skpa'] = $this->staf_monev_m->get_skpa_list();
		$data['review'] = $this->staf_monev_m->get_paket_review(); //batal sp
		$this->load->view('admin/index', $data);
	}

  	public function review_history_cetak($id = 0)
	{
		$detail = $this->Pokja_paket_m->get_last_history_review($id);
		$data['detail'] = $detail;

		if($detail->review_status == 2 && $detail->posisi == 2){
			$this->load->view('admin/pokja_review_cetak2', $data);
		}else{
			$this->load->view('admin/pokja_review_cetak1', $data);
		}
	}

	public function serah_dokumen($kode_rup = 0)
	{
		$this->staf_monev_m->serah_dokumen($kode_rup);
		redirect('staf_monev/review');
	}

}
