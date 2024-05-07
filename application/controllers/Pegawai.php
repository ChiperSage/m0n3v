<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawai extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model(array('pegawai_m'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth'));

		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group(array('monev','ppk')))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$this->sync_data();

		$data['inc'] = 'pegawai_pokja';
		$data['pegawai'] = $this->pegawai_m->get_pegawai_pokja();
		$this->load->view('monev/index', $data);
	}

	public function rekap()
	{
		$data['rekap'] = $this->pegawai_m->get_pegawai_rekaphonor();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/pegawai_rekaphonor_ex', $data);
		}else{
			$data['inc'] = 'pegawai_rekaphonor';
			$this->load->view('monev/index', $data);
		}
	}

	public function rekap2()
	{
		$data['rekap'] = $this->pegawai_m->get_pegawai_rekaphonor2();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/pegawai_rekaphonor_ex', $data);
		}else{
			$data['inc'] = 'pegawai_rekaphonor2';
			$this->load->view('monev/index', $data);
		}
	}

	public function list_paket($nip = 0)
	{

		if(!empty($_POST['check_list'])){
			foreach($_POST['check_list'] as $selected){

				$arr = explode('-',$selected);
				$kode_lelang = $arr[0];
				$jml_bayar = $arr[1];

				$key = array('nip'=>$nip,'kode_lelang'=>$kode_lelang);
				$result = $this->db->get_where('tb_bayar',$key)->num_rows();

				if($result == 0){
					$bayar['nip'] = $nip;
					$bayar['kode_lelang'] = $kode_lelang;
					$bayar['tgl_bayar'] = date('Y-m-d H:i:s');
					$bayar['jml_bayar'] = $jml_bayar;
					$bayar['status'] = 1;
					$this->db->insert('tb_bayar', $bayar);
				}

			}
		}

		$data['inc'] = 'pegawai_listpaket';
		$data['pegawai'] = $this->pegawai_m->get_data_pokja($nip);
		$data['listpaket'] = $this->pegawai_m->get_listpaket($nip);
		$data['listpaket_total'] = $this->pegawai_m->get_listpaket_total($nip);
		$data['total_paket'] = $this->pegawai_m->get_total_paket($nip);
		$data['total_paket_bayar'] = 0;
		$data['total_bayar'] = $this->pegawai_m->get_total_bayar($nip);
		$data['pejabat_ppk'] = $this->pegawai_m->get_pejabat_ppk();
		
		$this->load->view('monev/index', $data);
	}

	public function bayar_honor($nip = 0)
	{
		$data['pegawai'] = $this->pegawai_m->get_data_pokja($nip);
		$data['listpaket'] = $this->pegawai_m->get_listpaket($nip);
		$data['total_honor'] = $this->pegawai_m->get_listpaket_total($nip);
		$data['total_bayar'] = $this->pegawai_m->get_total_bayar($nip);
		$data['bayar'] = $this->pegawai_m->get_data_bayar($nip);
		$data['pejabat_ppk'] = $this->pegawai_m->get_pejabat_ppk();

		$this->load->view('monev/pegawai_bayar_honor_pdf', $data);
	}

	public function bayar_honor2($nip = 0)
	{
		$data['pegawai'] = $this->pegawai_m->get_data_pokja($nip);
		$data['listpaket'] = $this->pegawai_m->get_listpaket($nip);
		$data['total_honor'] = $this->pegawai_m->get_listpaket_total($nip);
		$data['total_bayar'] = $this->pegawai_m->get_total_bayar($nip);
		$data['bayar'] = $this->pegawai_m->get_data_bayar($nip);

		$this->form_validation->set_rules('jml_bayar','Jumlah Bayar','trim|required');

		if(isset($_GET['type']) && $_GET['type'] == 'pdf'){

			$this->load->view('monev/pegawai_bayar_honor_pdf', $data);

		}else{

			if($this->form_validation->run() == false){
				$data['inc'] = 'pegawai_bayar_honor';
				$this->load->view('monev/index', $data);
			}else{
				$this->pegawai_m->insert_bayar($nip);
				redirect('pegawai/bayar_honor/'.$nip);
			}

		}

	}

  public function sync_data()
  {
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		$this->db->delete('tb_pegawai_pokja',array('pnt_tahun'=>$year));

		$url = 'http://123.108.97.215/json/json/pegawai?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->pnt_id; }, $data));
		$connection = mysqli_connect("localhost","root","laserj3tbpbjplatinum","monev");

		mysqli_query($connection, "DELETE FROM tb_pegawai_pokja WHERE pnt_id NOT IN ($id_list) AND pnt_tahun = '$year'");

		foreach ($data as $val)
		{
				$sql = "INSERT INTO tb_pegawai_pokja
				SET pnt_id=?, peg_id=?, peg_nip=?, peg_nama=?, agc_id=?, peg_golongan=?, peg_pangkat=?, pnt_nama=?, pnt_tahun=?
				ON DUPLICATE KEY UPDATE pnt_id=values(pnt_id), peg_id=values(peg_id), peg_nip=values(peg_nip), peg_nama=values(peg_nama), agc_id=values(agc_id),
        peg_golongan=values(peg_golongan), peg_pangkat=values(peg_pangkat), pnt_nama=values(pnt_nama), pnt_tahun=values(pnt_tahun)";

				$stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iississsi", $val->pnt_id, $val->peg_id, $val->peg_nip, $val->peg_nama, $val->agc_id, $val->peg_golongan,
        $val->peg_pangkat, $val->pnt_nama, $val->pnt_tahun);
				mysqli_stmt_execute($stmt);
		}
  }

	public function honor()
	{
		$data['inc'] = 'pegawai_honor_tb';
		$data['honor'] = $this->pegawai_m->get_pegawai_honor();
		$this->load->view('monev/index', $data);

		if(isset($_GET['delete']) && $_GET['delete'] != ''){
			$id = $_GET['delete'];
			$this->pegawai_m->honor_delete($id);
		}
	}

	public function insert_honor()
	{
		$this->form_validation->set_rules('jenis_pengadaan','Jenis Pengadaan','trim|required');
		$this->form_validation->set_rules('nilai1','Nilai 1','trim|required|numeric');
		$this->form_validation->set_rules('nilai2','Nilai 2','trim|required|numeric');
		$this->form_validation->set_rules('honor','Honor','trim|required|numeric');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pegawai_honor_form';
			$this->load->view('monev/index', $data);
		}else{
			$this->pegawai_m->honor_insert();
			redirect('pegawai/honor');
		}
	}

	public function update_honor($id)
	{
		$this->form_validation->set_rules('jenis_pengadaan','Jenis Pengadaan','trim|required');
		$this->form_validation->set_rules('nilai1','Nilai 1','trim|required|numeric');
		$this->form_validation->set_rules('nilai2','Nilai 2','trim|required|numeric');
		$this->form_validation->set_rules('honor','Honor','trim|required|numeric');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'pegawai_honor_form';
			$data['honor'] = $this->pegawai_m->get_pegawai_honor_detail($id);
			$this->load->view('monev/index', $data);
		}else{
			$this->pegawai_m->honor_update($id);
			redirect('pegawai/honor');
		}
	}

	public function delete_honor($id){
			$this->pegawai_m->honor_delete($id);
	}

}
