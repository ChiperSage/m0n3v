<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lhp extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
    	$this->load->model(array('lhp_m','global_m'));

		$this->form_validation->set_error_delimiters(
	    $this->config->item('error_start_delimiter', 'ion_auth'),
	    $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$group = array('monev','staff_monev','kabagpp','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($group))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
  		$data['inc'] = 'lhp_fm';
		$this->load->view('monev/index',$data);
  	}

	public function data()
	{
		$data['inc'] = 'lhp_tb';
		$data['data_lhp'] = $this->lhp_m->get_data_lhp();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/lhp_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function blm_lhp()
	{

		$data['data_lhp'] = $this->lhp_m->get_data_blm_lhp();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/lhp_blm_tb_ex',$data);
		}else{
			$data['inc'] = 'lhp_blm_tb';
			$this->load->view('monev/index',$data);
		}
	}

	public function input()
	{
		$cari_paket = array();
		if(isset($_POST['pencarian']) && $_POST['pencarian'] != ''){
			$cari_paket = $this->lhp_m->get_cari_paket_lhp(0);
		}else{
			$this->form_validation->set_rules('kode_lelang','Kode Lelang','trim|required');
			$this->form_validation->set_rules('dpp','DPP','trim|required');

			if(empty($_FILES['berkas1']['name'])){
				$this->form_validation->set_rules('berkas1','BAHP','trim|required');
			}
			if(empty($_FILES['berkas2']['name'])){
				$this->form_validation->set_rules('berkas2','BAEP','trim|required');
			}
			if(empty($_FILES['berkas3']['name'])){
				$this->form_validation->set_rules('berkas3','Summary','trim|required');
			}
			if(empty($_FILES['berkas4']['name'])){
				$this->form_validation->set_rules('berkas4','Penawaran','trim|required');
			}
			if(empty($_FILES['berkas5']['name'])){
				$this->form_validation->set_rules('berkas5','Surat Pengantar LHP','trim|required');
			}
		}

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'lhp_fm';
			$data['cari_paket'] = $cari_paket;
			$this->load->view('monev/index',$data);
		}else{
			$allfileuploaded = array();

			if($this->lhp_m->is_kode_lelang_exist($this->input->post('kode_lelang')) != true)
			{
				$berkas1 = $this->do_upload('berkas1');
				$berkas2 = $this->do_upload('berkas2');
				$berkas3 = $this->do_upload('berkas3');
				$berkas4 = $this->do_upload('berkas4');
				$berkas5 = $this->do_upload('berkas5');

				$allfileuploaded[] = $berkas1;
				$allfileuploaded[] = $berkas2;
				$allfileuploaded[] = $berkas3;
				$allfileuploaded[] = $berkas4;
				$allfileuploaded[] = $berkas5;
			}

			// kode_lhp
			$data['kode_lhp'] = $this->get_kode_lhp($this->input->post('kode_lelang'));
			$data['kode_lelang'] = $this->input->post('kode_lelang');
			$data['dpp'] = $this->input->post('dpp');
			$data['bahp'] = $berkas1;
			$data['baep'] = $berkas2;
			$data['summary'] = $berkas3;
			$data['penawaran'] = $berkas4;
			$data['pokja'] = $this->input->post('pokja');
			$data['tgl_terima'] = date('Y-m-d');
			$data['tgl_serah'] = '0000-00-00';
			$data['keterangan_terima'] = 'sudah';
			$data['keterangan_serah'] = 'belum ambil';

			if(!in_array('nofile',$allfileuploaded))
			{
				$this->lhp_m->insert($data);
			}else{
				$this->session->set_flashdata('msg','<div class="callout callout-danger">Gagal</div>');
			}

			redirect('lhp/input');
		}

	}

	public function update($kode_lelang = 0)
	{
		$cari_paket = array();
		$cari_paket = $this->lhp_m->get_cari_paket_lhp($kode_lelang);

		if(isset($_POST['pencarian']) && $_POST['pencarian'] != ''){
			$cari_paket = $this->lhp_m->get_cari_paket_lhp($kode_lelang);
		}else{
			$this->form_validation->set_rules('kode_lelang','Kode Lelang','trim|required');
			$this->form_validation->set_rules('dpp','DPP','trim|required');

			if(empty($_FILES['berkas1']['name'])){
				$this->form_validation->set_rules('berkas1','BAHP','trim|required');
			}
			if(empty($_FILES['berkas2']['name'])){
				$this->form_validation->set_rules('berkas2','BAEP','trim|required');
			}
			if(empty($_FILES['berkas3']['name'])){
				$this->form_validation->set_rules('berkas3','Summary','trim|required');
			}
			if(empty($_FILES['berkas4']['name'])){
				$this->form_validation->set_rules('berkas4','Penawaran','trim|required');
			}
			if(empty($_FILES['berkas5']['name'])){
				$this->form_validation->set_rules('berkas5','Surat Pengantar LHP','trim|required');
			}
		}

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'lhp_fm';
			$data['cari_paket'] = $cari_paket;
			$this->load->view('monev/index',$data);
		}else{
			$allfileuploaded = array();
			$berkas1 = $this->do_upload('berkas1');
			$berkas2 = $this->do_upload('berkas2');
			$berkas3 = $this->do_upload('berkas3');
			$berkas4 = $this->do_upload('berkas4');
			$berkas5 = $this->do_upload('berkas5');

			// kode_lhp
			$data['kode_lhp'] = $this->get_kode_lhp($this->input->post('kode_lelang'));
			$data['kode_lelang'] = $this->input->post('kode_lelang');
			$data['dpp'] = $this->input->post('dpp');
			$data['bahp'] = $berkas1;
			$data['baep'] = $berkas2;
			$data['summary'] = $berkas3;
			$data['penawaran'] = $berkas4;
			$data['pokja'] = $this->input->post('pokja');
			$data['tgl_terima'] = date('Y-m-d');
			$data['tgl_serah'] = '0000-00-00';
			$data['keterangan_terima'] = 'sudah';
			$data['keterangan_serah'] = 'belum kirim';

			$this->lhp_m->update($kode_lelang, $data);
			redirect('lhp/input');
		}

	}

	public function delete($kode_lelang = 0)
	{
		$this->lhp_m->delete($kode_lelang);
		redirect('lhp/data');
	}

	public function do_upload($berkas)
	{
		$kode_lelang = $this->input->post('kode_lelang');
		if (!file_exists('/var/www/html/bpbj/uploads/lhp/' . $kode_lelang)) {
			mkdir('/var/www/html/bpbj/uploads/lhp/' . $kode_lelang, 0755, true);
		}

		$config['upload_path'] = '/var/www/html/bpbj/uploads/lhp/' . $kode_lelang;
		$config['allowed_types'] = '*';

		$this->upload->initialize($config);

		if (!$this->upload->do_upload($berkas)){
			return 'nofile';
		}else{
			return $this->upload->data('full_path');
		}
	}

	public function kirim($kode_lelang)
	{
		$filter = array('kode_lelang'=>$kode_lelang);
		$data['tgl_serah'] = date('Y-m-d');
		$data['keterangan_serah'] = 'sudah ambil';
		$this->db->update('tb_lhp',$data,$filter);
		redirect('lhp/data');
	}

	public function download($kode_lelang)
	{
		$this->load->library('zip');
		$dir = '/var/www/html/bpbj/uploads/lhp/' . $kode_lelang .'/';

		if(file_exists($dir)){
			$files = scandir($dir);

			unset($files[0]);
			unset($files[1]);
			foreach ($files as $value) {
				$this->zip->add_data($value, 'file pdf');
			}
		}
		$this->zip->download('dokumen_lhp_' . $kode_lelang . '.zip');
	}

	public function get_kode_lhp($kode_lelang)
	{
		// ambil singkatan
		$str = "SELECT kg.singkatan FROM tb_lelang_spse ls, tb_kategori kg WHERE ls.jenis_pengadaan = kg.kgr_id AND kode_lelang = '$kode_lelang'";
		$singkatan = $this->db->query($str)->row('singkatan');

		$tahun = date('Y');
		$k = $singkatan.'/';

		$count = $this->db->get_where('tb_lhp',array())->num_rows();

		if($count == 0){
			$num = 1;
			$kode_lhp = 'LHP-'.$num.'/'.'T/' . $k . 'BPBJ/' . $tahun;
		}else{

			// $num = 13;
			$total_row = $this->db->get_where('tb_lhp', array())->num_rows();
			$num = $total_row + 1;

			$str = "SELECT * FROM tb_lhp ORDER BY kode_lhp DESC LIMIT 1";
			$lhp = $this->db->query($str)->row();
			$substring = substr($lhp->kode_lelang,4,1);
			// $num = $n + 1;
			$kode_lhp = 'LHP-'. $num.'/'.'T/' . $k . 'BPBJ/' . $tahun;
			// $num = $num + 1;

			// kode baru
			$result = $this->db->query("SELECT kode_lhp FROM tb_lhp WHERE kode_lhp = '$kode_lhp'")->num_rows();
			if($result == 1){
				$num = $num + 1;
			}
			$kode_lhp = 'LHP-'. $num.'/'.'T/' . $k . 'BPBJ/' . $tahun;
		}

		return $kode_lhp;
	}

	public function pengantar()
	{
		$data['list_skpa'] = $this->lhp_m->get_skpa();
		$data['data_lhp'] = $this->lhp_m->list_paket_blm_krm();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/lhp_blm_tb_ex',$data);
		}else{
			$data['inc'] = 'lhp_pengantar';
			$this->load->view('monev/index',$data);
		}
	}

	public function pengantar_cetak()
	{
		$data['nama_skpa'] = $this->global_m->get_nama_skpa($_GET['skpa']);
		$data['list_paket'] = $this->lhp_m->list_paket_pilihan();
		$this->load->view('monev/lhp_pengantar_cetak', $data);
	}

	public function ajax_cari_paket($kode_lelang = 0)
	{
		// $result = $this->db->get_where('tb_lelang_spse', array('kode_lelang'=>$kode_lelang))->row();
		$str = "SELECT ls.kode_lelang, ls.nama_paket, ls.pagu, ls.hps, k.kgr_nama, m.mtd_nama, sa.nama as nama_satker, p.pnt_nama
				FROM tb_lelang_spse ls, tb_kategori k, tb_metode m, tb_panitia p, tb_skpa sa
				WHERE ls.kode_lelang = '$kode_lelang' AND ls.jenis_pengadaan = k.kgr_id AND ls.mtd_pemilihan = m.mtd_id
				AND ls.rup_stk_id = sa.kode AND ls.pnt_id = p.pnt_id";
		$result = $this->db->query($str)->row();
		echo json_encode($result);
	}

}
