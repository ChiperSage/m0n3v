<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

class Tpd_check extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('tpd_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('tpd'))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'tpd_home';

		$data['tbarang'] = $this->tpd_m->count_jenis_pengadaan('Barang');
		$data['tjasa'] = $this->tpd_m->count_jenis_pengadaan('Jasa Lainnya');
		$data['tkonsultansi'] = $this->tpd_m->count_jenis_pengadaan('Jasa Konsultansi');
		$data['tkonstruksi'] = $this->tpd_m->count_jenis_pengadaan('Pekerjaan Konstruksi');

		$data['tpd'] = $this->tpd_m->tpd_get();

		$this->load->view('admin/index', $data);
	}

	public function proses()
	{
		$tanggal = date('Y-m-d');

		$user_id = $this->session->userdata('user_id');
		$data_login = $this->ion_auth->user($id)->row();

		$str = "SELECT * FROM tb_tpd_antrian WHERE tanggal = '$tanggal' AND (status != 'proses' OR status != 'ada' OR status != 'tidak_ada')";
		$count = $this->db->query($str)->num_rows();

		if($count >= 1){
			$str = "SELECT * FROM tb_tpd_antrian WHERE tanggal = '$tanggal' AND (status != 'proses' OR status != 'ada' OR status != 'tidak_ada') LIMIT 1 ORDER BY nomor_antrian DESC";
			$val = $this->db->query($str)->row();

			redirect('tpd_check/daftar_antrian_set/?status=proses&id='.$val->id);
		}else{
			redirect('tpd_check');
		}
	}

	public function jenis($jenis_pengadaan = '')
	{
		$data['inc'] = 'tpd_home';

		$data['tbarang'] = $this->tpd_m->count_jenis_pengadaan('Barang');
		$data['tjasa'] = $this->tpd_m->count_jenis_pengadaan('Jasa Lainnya');
		$data['tkonsultansi'] = $this->tpd_m->count_jenis_pengadaan('Jasa Konsultansi');
		$data['tkonstruksi'] = $this->tpd_m->count_jenis_pengadaan('Pekerjaan Konstruksi');

		$jenis = str_replace('_',' ',$jenis_pengadaan);
		$data['tpd'] = $this->tpd_m->tpd_get($jenis);

		$this->load->view('admin/index', $data);
	}

	public function lepas_antrian_semua($id_antrian = 0)
	{
		$set['tpd_status'] = 6;
		$set['id_antrian'] = '';
		$set['ba'] = '';
		$this->db->update('tb_tpd',$set,array('id_antrian'=>$id_antrian,'tpd_status'=>7));
		redirect('tpd_check');
	}

	public function lepas_antrian($kode_rup = 0)
	{
		$set['tpd_status'] = 6;
		$set['id_antrian'] = '';
		$set['ba'] = '';
		$this->db->update('tb_tpd',$set,array('kode_rup'=>$kode_rup,'tpd_status'=>7));
		redirect('tpd_check');
	}

	public function daftar_antrian()
	{
		$user_id = $this->session->userdata('user_id');
		$data_login = $this->ion_auth->user($user_id)->row();

		$data['inc'] = 'tpd_daftar_antrian';
		$data['daftar_antrian'] = $this->tpd_m->get_daftar_antrian();
		$this->load->view('admin/index', $data);
	}

	public function daftar_antrian_set()
	{
		$user_id = $this->session->userdata('user_id');
		$data_login = $this->ion_auth->user($id)->row();

		if(isset($_GET['status']) && $_GET['status'] == 'proses'){
			$key = array('id'=>$_GET['id']);
			$set['tpd'] = $data_login->last_name;
			$set['status'] = 'proses';
			$set['tanggal_update'] = date('Y-m-d H:i:s');
			$this->db->update('tb_tpd_antrian', $set, $key);
		}

		if(isset($_GET['status']) && $_GET['status'] == 'ada'){
			$key = array('id'=>$_GET['id']);
			$set['tpd'] = $data_login->last_name;
			$set['status'] = 'ada';
			$set['tanggal_update'] = date('Y-m-d H:i:s');
			$this->db->update('tb_tpd_antrian', $set, $key);
		}

		if(isset($_GET['status']) && $_GET['status'] == 'tidak_ada'){
			$key = array('id'=>$_GET['id']);
			$set['tpd'] = $data_login->last_name;
			$set['status'] = 'tidak_ada';
			$set['tanggal_update'] = date('Y-m-d H:i:s');
			$this->db->update('tb_tpd_antrian', $set, $key);

			$set2['tpd_status'] = 6;
			$set2['id_antrian'] = '';
			$this->db->update('tb_tpd',$set2,array('id_antrian'=>$_GET['id']));
		}

		if(isset($_GET['status']) && $_GET['status'] == 'lepas'){
			$key = array('id'=>$_GET['id']);
			$set['tpd'] = '';
			$set['status'] = '';
			$set['tanggal_update'] = '0000-00-00 00:00:00';
			$this->db->update('tb_tpd_antrian', $set, $key);
		}

		redirect('tpd_check/daftar_antrian');
	}

	public function update($kode_rup = 0)
	{
		$jenis = $this->db->get_where('tb_tpd',array('kode_rup'=>$kode_rup))->row();
		$form = strtolower(str_replace(' ','_',$jenis->jenis_pengadaan));

		if(strpos($form,';')){
			$filter = explode(';',$form);
			$form = $filter[0];
		}

		if(file_exists(APPPATH . 'views/admin/tpd_'.$form.'.php')){
			$data['inc'] = 'tpd_'.$form;
		}

		$data['detail'] = $this->tpd_m->tpd_detail($kode_rup);
		$this->load->view('admin/index', $data);
	}

	public function ajax_update($kode_rup = 0, $col = '', $var = '')
	{
		$jenis = $this->db->get_where('tb_tpd',array('kode_rup'=>$kode_rup))->row();
		$jenis_pengadaan = $jenis->jenis_pengadaan;

		$tb = '';
		if(strpos($jenis_pengadaan, "Barang") !== false){
			$tb = 'tb_tpd_barang';
		}elseif(strpos($jenis_pengadaan, "Jasa Lainnya") !== false) {
			$tb = 'tb_tpd_jasa';
		}elseif(strpos($jenis_pengadaan, "Pekerjaan Konstruksi") !== false) {
			$tb = 'tb_tpd_konstruksi';
		}elseif(strpos($jenis_pengadaan, "Jasa Konsultansi") !== false) {
			$tb = 'tb_tpd_konsultansi';
		}

		if($var == 'true'){
			$val = 1;
		}elseif ($var == 'false') {
			$val = 0;
		}else{
			$val = str_replace('%20', ' ', $var);
			// $val = $var;
		}

		$filter = array('kode_rup'=>$kode_rup);
		$count = $this->db->get_where($tb,$filter)->num_rows();
		if($count == 1){
			$data[$col] = $val;
			$data['tanggal'] = date('Y-m-d');
			$data['id_petugas'] = $this->session->userdata('user_id');
			$this->db->update($tb,$data,$filter);
		}else{
			$data['kode_rup'] = $kode_rup;
			$data['tanggal'] = date('Y-m-d');
			$data['id_petugas'] = $this->session->userdata('user_id');
			$data[$col] = $val;
			$this->db->insert($tb,$data);
		}

		if($this->db->affected_rows() == 1){
			echo 1;
		}

		if($col == 'kelengkapan' && $val == 1){
			$set['tpd_status'] = 8;
			$set['tanggal_terima_dok'] = date('Y-m-d');
			$set['waktu_terima_dok'] = date('H:i:s');
			$set['petugas_id'] = $this->session->userdata('user_id');
			$this->db->update('tb_tpd',$set,array('kode_rup'=>$kode_rup));
		}elseif($col == 'kelengkapan' && $val == 0){
			$set['tpd_status'] = 6;
			$set['petugas_id'] = $this->session->userdata('user_id');
			$this->db->update('tb_tpd',$set,array('kode_rup'=>$kode_rup));
		}
	}

	public function ajax_update2($kode_rup = 0)
	{
		$tahun = $_POST['keterangan'];
		$sql = "UPDATE tb_rup2 SET tahun = '$tahun' WHERE kode_rup = $kode_rup";
		$this->db->query($sql);
	}

	public function berita2()
	{
		$data['list_skpa'] = $this->tpd_m->list_skpa();

		$data['inc'] = 'tpd_berita';
		$this->load->view('admin/index', $data);
	}

	public function berita()
	{
		$data['list_skpa'] = $this->tpd_m->list_skpa();

		if(isset($_GET['tanggal']) && isset($_GET['skpa'])){
			$config['tpd_status'] = 8;
			$config['tanggal_terima_dok'] = $_GET['tanggal'];
			$config['id_satker'] = $_GET['skpa'];
			$config['ba'] = 'belum';
		}
		$data['tpd'] = $this->tpd_m->tpd_get($config);

		$data['inc'] = 'tpd_berita2';
		$this->load->view('admin/index', $data);
	}

	// print berita acara
	public function berita_acara()
	{
		if( isset($_GET['tanggal']) && isset($_GET['skpa']) && isset($_GET['kode_rup']) )
		{
			$tanggal = $_GET['tanggal'];
			$skpa = $_GET['skpa'];
			$kode_rup = str_replace('_', ',', $_GET['kode_rup']);

			$var_kode = explode('_', $_GET['kode_rup']);
			foreach ($var_kode as $value) {
				$set['ba'] = 'sudah';
				$key['kode_rup'] = $value;
				$this->db->update('tb_tpd',$set,$key);
			}

			if(count($var_kode) == 1){
				$kode_rup = $_GET['kode_rup'];
			}

		}

		$str = "SELECT a.kode_rup, a.nama_paket, a.jenis_pengadaan, a.status_pengadaan, a.nilai_pagu, a.nilai_hps, a.nama_pabung, a.nama_skpa, a.waktu_terima_dok,
				COALESCE(concat(f.first_name),concat(g.first_name),concat(h.first_name),concat(i.first_name)) as petugas
				FROM tb_tpd a
				LEFT JOIN tb_tpd_barang b ON a.kode_rup = b.kode_rup
				LEFT JOIN tb_tpd_jasa c ON a.kode_rup = c.kode_rup
				LEFT JOIN tb_tpd_konstruksi d ON a.kode_rup = d.kode_rup
				LEFT JOIN tb_tpd_konsultansi e ON a.kode_rup = e.kode_rup
				LEFT JOIN users f ON b.id_petugas = f.id
				LEFT JOIN users g ON c.id_petugas = g.id
				LEFT JOIN users h ON d.id_petugas = h.id
				LEFT JOIN users i ON e.id_petugas = i.id
				WHERE a.tanggal_terima_dok = '$tanggal' AND a.id_satker = '$skpa' AND a.kode_rup IN ($kode_rup)
				AND (c.kelengkapan = 1 OR b.kelengkapan = 1 OR d.kelengkapan = 1 OR e.kelengkapan = 1)
				GROUP BY a.kode_rup";

		$data['berita'] = $this->db->query($str)->result();
		$this->load->view('admin/tpd_cetak_berita', $data);
	}

	public function cetak2($kode_rup = 0)
	{
		$jenis = $this->db->get_where('tb_tpd',array('kode_rup'=>$kode_rup))->row();
		$form = strtolower(str_replace(' ', '_', $jenis->jenis_pengadaan));

		if(strpos($form,';')){
			$filter = explode(';',$form);
			$form = $filter[0];
		}

		if(file_exists(APPPATH . 'views/admin/tpd_'.$form.'.php')){
			$file = 'tpd_cetak_'.$form;
		}

		$data['tpd'] = $this->tpd_m->tpd_detail($kode_rup);
		$content = $this->load->view('admin/'.$file, $data, false);

		// try {
    // 		ob_start();
		// 		$content = ob_get_clean();
		//     $html2pdf = new Html2Pdf('P', 'A4', 'en');
		//     $html2pdf->writeHTML($content);
		//     $html2pdf->output();
		// } catch (Html2PdfException $e) {
		//     $html2pdf->clean();
		//     $formatter = new ExceptionFormatter($e);
		//     echo $formatter->getHtmlMessage();
		// }
	}
}
