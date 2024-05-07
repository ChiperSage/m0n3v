<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sp extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('sp_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);
		$this->load->helper('tgl_indo');

		$this->lang->load('auth');

		$groups = array('admin_karo','karo');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'sp_table';
		$data['sp'] = $this->sp_m->get();
		$this->load->view('admin/index', $data);
	}

	public function ajax_list_anggota($id_sp = 0)
	{
		$this->db->select('a.anggota_nip,b.pokja_nama');
		$this->db->from('tb_sp_anggota a');
		$this->db->join('tb_pokja b','a.anggota_nip = b.pokja_nip','left');
		$this->db->where(array('sp_id'=>$id_sp));
		$data['anggota'] = $this->db->get()->result();
		$this->load->view('admin/sp_ajax_lihat_anggota',$data);
	}

	public function _autoinc()
	{
		// jika sp kosong
		$this->db->select('*');
		$count = $this->db->get('tb_sp')->num_rows();
		if($count == 0)
		{
			return '0001';
		}

		// jika tidak ...
		$this->db->select('max(sp_nomor) as sp_nomor');
		$nomor = $this->db->get('tb_sp')->row('sp_nomor');
		return ltrim((1 . $nomor) + 1,1);
	}

	public function create()
	{
		$this->form_validation->set_rules('tanggal','Tanggal','trim|required');
		$this->form_validation->set_rules('nomor','Nomor','trim|required|is_unique[tb_sp.sp_nomor]');
		$this->form_validation->set_rules('kelompok','Kelompok','trim|required');
		$this->form_validation->set_rules('tahun','Pokja Tahun','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'sp_form';
			$data['autonomor'] = $this->_autoinc();
			$this->load->view('admin/index', $data);
		}else{
			$this->sp_m->create();
			redirect('sp');
		}
	}

	public function update($id = 0)
	{
		$this->_validate($id);
		if($this->_islocked($id) == true){
			redirect('sp');
		}

		$this->form_validation->set_rules('tanggal','Tanggal','trim|required');
		$this->form_validation->set_rules('nomor','Nomor','trim|required');
		$this->form_validation->set_rules('kelompok','Kelompok','trim|required');
		$this->form_validation->set_rules('tahun','Pokja Tahun','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'sp_form';
			$data['detail'] = $this->sp_m->get_detail($id);
			$this->load->view('admin/index', $data);
		}else{
			$this->sp_m->update($id);
			redirect('sp');
		}
	}

	public function delete($id = 0)
	{
		$this->_validate($id);
		if($this->_islocked($id) == true){
			redirect('sp');
		}

		$this->sp_m->delete($id);
		redirect('sp');
	}

	public function cetak($id = 0)
	{
		$this->_secure_cetak($id);

		$sp = $this->sp_m->get_detail($id);

		// get paraf
		$sess_id = $this->session->userdata('user_id');
		$kasubbag = $this->ion_auth->user($sess_id)->row();
		$karo = $this->ion_auth->users(array('group'=>'karo'))->row();
		$kabag = $this->ion_auth->users(array('group'=>'kabagpp'))->row();
		// end get paraf

		$data['sp'] = $this->sp_m->get_detail($id);
		$data['sp_anggota'] = $this->sp_m->get_anggota($id);
		$data['sp_paket'] = $this->sp_m->get_paket($id);
		$data['sp_paket_tanggal'] = $this->sp_m->get_paket_tanggal($id);

		$get_last_date = $this->db->query("SELECT paket_tanggal FROM tb_sp_paket WHERE paket_sp = $id ORDER BY paket_tanggal DESC LIMIT 1")->row('paket_tanggal');

		$data['data_karo'] = $this->sp_m->get_karo($get_last_date);

		$data['paraf_kabag'] = $kabag;
		$data['paraf_kasubbag'] = $kasubbag;
		$data['paraf_karo'] = $karo;

		$this->load->view('admin/sp_cetak', $data, false);
	}

	public function cetak2($id = 0)
	{
		$this->_secure_cetak($id);

		// set status
		// $filter = array('sp_id'=>$id);
		// $data['sp_status'] = 3;
		// $this->db->update('tb_sp',$data,$filter);

		// get paraf
		$sess_id = $this->session->userdata('user_id');
		$kasubbag = $this->ion_auth->user($sess_id)->row();
		$karo = $this->ion_auth->users(array('group'=>'karo'))->row();
		$kabag = $this->ion_auth->users(array('group'=>'kabagpp'))->row();
		// end get paraf

		$this->load->library('pdfgenerator');

		$data['sp'] = $this->sp_m->get_detail($id);
		$data['sp_anggota'] = $this->sp_m->get_anggota($id);
		$data['sp_paket'] = $this->sp_m->get_paket($id);
		$data['sp_paket_tanggal'] = $this->sp_m->get_paket_tanggal($id);

		$data['paraf_kabag'] = $kabag;
		$data['paraf_kasubbag'] = $kasubbag;
		$data['paraf_karo'] = $karo;

		$this->load->view('admin/sp_cetak_', $data, false);

		// $html = $this->load->view('admin/sp_cetak', $data, true);
		// $filename = 'cetak_sp_'.time();
		// $this->pdfgenerator->generate($html, $filename, true, 'legal', 'portrait');
	}

	public function list_paket_pokja()
	{
		$data['inc'] = 'sp_list_paket_pokja';
		$data['paket'] = $this->sp_m->get_list_paket_pokja();
		$this->load->view('admin/index', $data);
	}

	public function batal_paket_pokja()
	{
		$this->sp_m->batal_paket_pokja();
		redirect('sp/list_paket_pokja');
	}

	public function paketbatal($id = 0)
	{
		$data['inc'] = 'sp_paketbatal';
		$data['paket'] = $this->sp_m->get_paketbatal();
		$this->load->view('admin/index', $data);

		// if($id != 0 && is_numeric($id)){
		// 	$this->db->delete('tb_batal',array('id'=>$id));
		// 	redirect('sp/paketbatal');
		// }
	}

	public function paketbatal_delete($id = 0)
	{
		$this->db->delete('tb_batal',array('id'=>$id));
		redirect('sp/paketbatal');
	}

	// anggota
	public function anggota($sp = 0, $id = 0)
	{
		$this->_validate($sp);
		if(is_numeric($sp) != 1 || !isset($sp)){
			redirect('sp');
		}

		if($this->_islocked($sp) == true){
			redirect('sp');
		}

		$this->form_validation->set_rules('nip','NIP','trim|required');
		$this->form_validation->set_rules('jabatan','Jabatan','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'sp_anggota';
			$data['sp'] = $this->sp_m->get_detail($sp);
			$data['pokja_list'] = $this->sp_m->get_pokja_list();
			if($id > 0){
				$data['detail'] = $this->sp_m->get_anggota_detail($id);
			}
			$data['anggota'] = $this->sp_m->get_anggota($sp);
			$this->load->view('admin/index', $data);
		}else{
			$this->sp_m->update_anggota($sp, $id);
			redirect('sp/anggota/'.$sp);
		}
	}

	public function delete_anggota($sp = 0, $id = 0)
	{
		if($this->_islocked($sp) == true){
			redirect('sp');
		}

		$this->sp_m->delete_anggota($id);
		redirect('sp/anggota/'.$sp);
	}

	// paket
	public function paket($sp = 0, $id = 0)
	{
		// $this->_validate($sp);

		$this->form_validation->set_rules('paket_id','Paket','trim|required');

		if($this->form_validation->run() == false)
		{
			$data['inc'] = 'sp_paket';
			$data['sp'] = $this->sp_m->get_detail($sp);
			$data['satker_list'] = $this->sp_m->get_satker_list();
			$data['paket_list'] = $this->sp_m->get_paket_list();
			if($id > 0){
				$data['detail'] = $this->sp_m->get_paket_detail($id);
			}
			$data['paket'] = $this->sp_m->get_paket($sp);
			$this->load->view('admin/index', $data);
		}else{
			$this->sp_m->update_paket($sp, $id);
			redirect('sp/paket/'.$sp);
		}
	}

	public function paket_dropdown_xxx($id = 0)
	{
		$tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');

		// ambil paket dari rup

		$str = "SELECT r.kode_rup as kode_rup, r.nama_paket as nama_paket
		FROM tb_rup r
		WHERE r.id_satker = '$id'
		-- AND (left(r.awal_pengadaan,4) LIKE '%$tahun%' OR left(r.awal_pekerjaan,4) LIKE '%$tahun%' OR left(r.akhir_pekerjaan,4) LIKE '%$tahun%' OR left(r.tanggal_kebutuhan,4) LIKE '%$tahun%' )
		AND r.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND r.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY r.kode_rup";

		// ambil paket dari lelang

		// $str = "SELECT * FROM
		// (SELECT l.kode_rup as kode_rup, l.nama_paket as nama_paket
		// FROM tb_lelang l
		// LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		// WHERE r.id_satker = '$id' AND l.tahun LIKE '%$tahun%' AND (l.ukpbj = '1106.00' OR l.status_lelang = 0)
		// AND l.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		// AND l.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		// GROUP BY l.kode_rup
		//
		// UNION
		//
		// SELECT t.kode_rup as kode_rup, t.nama_paket as nama_paket
		// FROM tb_non_tender_complete t
		// LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
		// WHERE r.id_satker = '$id' AND left(t.lls_dibuat_tanggal,4) = $tahun AND (t.ukpbj = '1106.00' OR t.status_lelang = 0)
		// -- WHERE r.id_satker = '$id' AND t.anggaran LIKE '%$tahun%' AND (t.ukpbj = '1106.00' OR t.status_lelang = 0)
		// AND t.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		// AND t.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		// GROUP BY t.kode_rup) AS tb_join";

		$paket = $this->db->query($str)->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/sp_paket_dropdown',$data);
	}

	public function paket_dropdown_temp($id = 0)
	{
		$tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');

		// ambil paket dari lelang

		// SELECT * FROM
		// (SELECT l.kode_rup as kode_rup, l.nama_paket as nama_paket
		// FROM tb_lelang l
		// LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		// WHERE r.id_satker = '$id' AND l.tahun LIKE '%$tahun%' AND (l.ukpbj = '1106.00' OR l.status_lelang = 0 OR l.paket_status = 0)
		// AND l.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		// AND l.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		// LIMIT 0
		// GROUP BY l.kode_rup

		// UNION

		$str = "SELECT * FROM
		(SELECT l.kode_rup as kode_rup, l.nama_paket as nama_paket
		FROM tb_tpd l
		LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		WHERE l.id_satker = '$id' AND r.tahun = $tahun AND l.tpd_status = 8
		AND l.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND l.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY l.kode_rup

		UNION

		SELECT t.kode_rup as kode_rup, t.nama_paket as nama_paket
		FROM tb_non_tender_complete t
		LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
		WHERE r.id_satker = '$id' AND left(t.lls_dibuat_tanggal,4) = $tahun AND (t.ukpbj = '1106.00' OR t.ukpbj = '3106' OR t.status_lelang = 0)
		AND t.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND t.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY t.kode_rup

		UNION

		SELECT t.kode_rup as kode_rup, r.nama_paket as nama_paket
		FROM tb_tender_offline t
		LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
		WHERE r.id_satker = '$id' AND r.tanggal_terakhir_di_update = $tahun
		AND t.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND t.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY t.kode_rup

		) AS tb_join";

		$paket = $this->db->query($str)->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/sp_paket_dropdown_temp',$data);
	}

	public function paket_dropdown_bck($id = 0)
	{
		$tahun = $this->db->get_where('json',array('data'=>'rup'))->row('tahun');

		// b.sumber_dana != 'APBN'
		// AND c.tahun LIKE '%$tahun%' AND ( (c.status_lelang = 1 AND c.ukpbj IS NULL)
		// OR (c.status_lelang = 0 AND c.paket_status = 0 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif')
		// OR (c.status_lelang = 1 AND c.paket_status = 1 AND c.ukpbj = '1106.00' AND c.status_aktif != 'non aktif') )

		$str = "SELECT * FROM
		(SELECT l.kode_rup as kode_rup, l.nama_paket as nama_paket
		FROM tb_lelang l
		LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		WHERE r.id_satker = '$id'

		AND r.sumber_dana != 'APBN' AND l.tahun LIKE '%$tahun%'
		AND ( (l.status_lelang = 0 AND l.ukpbj IS NULL)
		OR (l.status_lelang = 0 AND l.paket_status = 0 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif')
		OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.ukpbj = '1106.00' AND l.status_aktif != 'non aktif') )

		AND l.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND l.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY l.kode_rup

		UNION

		SELECT t.kode_rup as kode_rup, t.nama_paket as nama_paket
		FROM tb_non_tender t
		LEFT JOIN tb_rup r ON t.kode_rup = r.kode_rup
		WHERE r.id_satker = '$id'

		AND r.sumber_dana != 'APBN' AND t.tahun LIKE '%$tahun%'
		AND ( (t.status_lelang = 0 AND l.ukpbj IS NULL)
		OR (t.status_lelang = 0 AND t.paket_status = 0 AND t.ukpbj = '1106.00' AND t.status_aktif != 'non aktif')
		OR (t.status_lelang = 1 AND t.paket_status = 1 AND t.ukpbj = '1106.00' AND t.status_aktif != 'non aktif') )

		AND t.kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		AND t.kode_rup NOT IN (SELECT batal_paket FROM tb_batal)
		GROUP BY t.kode_rup) AS tb_join";

		$paket = $this->db->query($str)->result();
		$data['paket_list'] = $paket;
		$this->load->view('admin/sp_paket_dropdown',$data);
	}

	public function delete_paket($sp = 0, $id = 0)
	{
		if($this->_islocked($sp) == true){
			redirect('sp');
		}

		$this->sp_m->delete_paket($sp, $id);
		redirect('sp/paket/'.$sp);
	}

	public function _secure_cetak($id = 0)
	{
		if($this->sp_m->secure_cetak($id) != 2){
			redirect('sp');
		}
	}

	public function _islocked($id = 0)
	{
		if($this->sp_m->count(array('sp_id'=>$id,'sp_status'=>2)) == 1){
			return true;
		}else{
			return false;
		}
	}

	public function _validate($id)
	{
		if(!is_numeric($id))
		{
			redirect('sp');
		}
		if($id == 0)
		{
			redirect('sp');
		}
	}
}
