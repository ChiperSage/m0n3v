<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Non_tender extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('non_tender_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function sync_kinerja_nontender()
	{
		$filename = 'https://isb.lkpp.go.id/isb-2/api/acb4fd5f-7dc3-46ed-a603-f3940f9c50b7/json/2752/SiKAP-PenilaianKinerjaPenyedia-NonTender/tipe/4:12/parameter/2022:D1';
        $year = 2022;

        $test = file_get_contents($filename);
        $data = json_decode($test);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kd_nontender; }, $data));

        $connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

        mysqli_query($connection, "DELETE FROM tb_kinerja_nontender WHERE kd_nontender NOT IN ($id_list) AND tahun_anggaran = '$year'");

        foreach ($data as $val)
		{
		$sql = "INSERT INTO tb_kinerja_nontender SET tahun_anggaran=?, kd_klpd=?, kd_satker=?, kd_lpse=?, kd_nontender=?, nama_paket=?, mtd_pemilihan=?, nip_ppk=?, nama_ppk=?, kd_penyedia=?, nama_penyedia=?, npwp_penyedia=?, indikator_penilaian=?, nilai_indikator=?, total_skors=?

            ON DUPLICATE KEY UPDATE
            
            tahun_anggaran=values(tahun_anggaran), kd_klpd=values(kd_klpd), kd_satker=values(kd_satker), kd_lpse=values(kd_lpse), kd_nontender=values(kd_nontender), nama_paket=values(nama_paket),
        	mtd_pemilihan=values(mtd_pemilihan), nip_ppk=values(nip_ppk), nama_ppk=values(nama_ppk), kd_penyedia=values(kd_penyedia), nama_penyedia=values(nama_penyedia), npwp_penyedia=values(npwp_penyedia), indikator_penilaian=values(indikator_penilaian), nilai_indikator=values(nilai_indikator), total_skors=values(total_skors)";

			$stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "issiissssisssii", $val->tahun_anggaran, $val->kd_klpd, $val->kd_satker, $val->kd_lpse, $val->kd_nontender, $val->nama_paket, $val->mtd_pemilihan, $val->nip_ppk, $val->nama_ppk, $val->kd_penyedia, $val->nama_penyedia, $val->npwp_penyedia, $val->indikator_penilaian, $val->nilai_indikator, $val->total_skors);

			mysqli_stmt_execute($stmt);

        }
	}

	public function index($page = 0)
	{
		$array = array();
		$data['inc'] = 'nontender_table';
		$data['lelang'] = $this->non_tender_m->get();
		$this->load->view('admin/index', $data);
	}

	public function list_silpa()
	{
		$data['inc'] = 'listsilpa_nontender';
		$data['page_title'] = 'List : SILPA Non Tender';

		$data['daftar_paket'] = $this->non_tender_m->get_list_silpa();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/listsilpa_nontender_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function rekap_silpa()
	{
		$data['inc'] = 'rekapsilpa_nontender';
		$data['page_title'] = 'List : SILPA Non Tender';

		$data['daftar_paket'] = $this->non_tender_m->get_rekap_silpa();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/rekapsilpa_nontender_ex',$data);
		}else{
			$this->load->view('monev/index',$data);
		}
	}

	public function index_complete()
	{
		$array = array();
		$data['inc'] = 'nontender_complete_table';
		$data['lelang'] = $this->non_tender_m->get_complete();
		$this->load->view('admin/index', $data);
	}

	public function _copy_data()
	{
		$this->db->empty_table('tb_non_tender_bck');

		$str = "INSERT INTO tb_non_tender_bck
		(SELECT * FROM tb_non_tender)";
		$this->db->query($str);
	}

	public function sync_non_tender()
	{
		$json = $this->db->get_where('json',array('data'=>'non_tender'))->row();
		$url = $json->url;
		$year = $json->tahun;

		// $url = 'http://123.108.97.216/json/json/non_tender?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);


		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_non_tender WHERE kode_lelang NOT IN ($id_list) AND anggaran = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_non_tender
				SET kode_lelang=?, stk_id=?, kode_rup=?, pkt_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?,
				ukpbj=?, kgr_id=?, mtd_pemilihan=?, anggaran=?, kontrak_id=?, kontrak_nilai=?
		    ON DUPLICATE KEY UPDATE
				kode_lelang=values(kode_lelang), stk_id=values(stk_id), kode_rup=values(kode_rup), pkt_id=values(pkt_id), nama_paket=values(nama_paket),
				pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status),
				ukpbj=values(ukpbj), kgr_id=values(kgr_id), mtd_pemilihan=values(mtd_pemilihan), anggaran=values(anggaran), kontrak_id=values(kontrak_id),
				kontrak_nilai=values(kontrak_nilai)";

		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iiiisiiiiiiisii", $val->kode_lelang, $val->stk_id, $val->kode_rup, $val->pkt_id, $val->nama_paket,
				$val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->kgr_id, $val->mtd_pemilihan, $val->anggaran, $val->kontrak_id,
				$val->kontrak_nilai);
		    mysqli_stmt_execute($stmt);
		}

		$this->sync_non_tender_jadwal($year);
		$this->update_status_aktif();
		$this->cari_pemenang();

		$this->sync_ambil_pemenang();

		redirect('non_tender/index');
	}

	public function sync_non_tender_complete()
	{
		$json = $this->db->get_where('json',array('data'=>'non_tender'))->row();
		$year = $json->tahun;

		$url = 'http://123.108.97.216/json/json/non_tender_complete?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun='.$year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_non_tender_complete WHERE kode_lelang NOT IN ($id_list) AND left(lls_dibuat_tanggal,4) = '$year'");
		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_non_tender_complete
				SET kode_lelang=?, stk_id=?, kode_rup=?, pkt_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?,
				ukpbj=?, kgr_id=?, mtd_pemilihan=?, anggaran=?, lls_dibuat_tanggal=?
		    ON DUPLICATE KEY UPDATE
				kode_lelang=values(kode_lelang), stk_id=values(stk_id), kode_rup=values(kode_rup), pkt_id=values(pkt_id), nama_paket=values(nama_paket),
				pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status),
				ukpbj=values(ukpbj), kgr_id=values(kgr_id), mtd_pemilihan=values(mtd_pemilihan), anggaran=values(anggaran), lls_dibuat_tanggal=values(lls_dibuat_tanggal)";

		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iiiisiiiiiiiss", $val->kode_lelang, $val->stk_id, $val->kode_rup, $val->pkt_id, $val->nama_paket,
				$val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->kgr_id, $val->mtd_pemilihan, $val->anggaran, $val->lls_dibuat_tanggal);
		    mysqli_stmt_execute($stmt);
		}

		$this->update_status_aktif_complete();

		$this->sync_ambil_pemenang();
		$this->cari_pemenang_complete();
		// $this->sync_panitia();
		
		redirect('non_tender/index_complete');
	}

	public function sync_ambil_pemenang()
	{
		$json = $this->db->get_where('json',array('data'=>'non_tender'))->row();
		$year = $json->tahun;

		$url = 'http://123.108.97.216/json/json/nontender_pemenang?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun='.$year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_non_tender_pemenang WHERE kode_lelang NOT IN ($id_list) AND anggaran = $year");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_non_tender_pemenang
			SET kode_lelang=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?, ukpbj=?, jadwal=?, pkt_lokasi=?, rkn_id=?, psr_identitas=?, psr_harga=?, psr_harga_terkoreksi=?, pkt_tgl_buat=?, jenis_pengadaan=?, anggaran=?, kontrak_id=?, kontrak_nilai=?, spk_id=?, spk_nilai=?, ppk_id=?, pp_id=?, pnt_id=?, pkt_id=?, rup_id=?, stk_id=?, peg_id=?, peg_nip=?, peg_nama=?
		    ON DUPLICATE KEY UPDATE
			kode_lelang=values(kode_lelang), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status), ukpbj=values(ukpbj), jadwal=values(jadwal), pkt_lokasi=values(pkt_lokasi), rkn_id=values(rkn_id), psr_identitas=values(psr_identitas), psr_harga=values(psr_harga), psr_harga_terkoreksi=values(psr_harga_terkoreksi), pkt_tgl_buat=values(pkt_tgl_buat), jenis_pengadaan=values(jenis_pengadaan), anggaran=values(anggaran), kontrak_id=values(kontrak_id), kontrak_nilai=values(kontrak_nilai), spk_id=values(spk_id), spk_nilai=values(spk_nilai), ppk_id=values(ppk_id), pp_id=values(pp_id), pnt_id=values(pnt_id), pkt_id=values(pkt_id), rup_id=values(rup_id), stk_id=values(stk_id), peg_id=values(peg_id), peg_nip=values(peg_nip), peg_nama=values(peg_nama)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "isiiiiissisiisiiiiiiiiiiiisss", $val->kode_lelang, $val->nama_paket, $val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->jadwal, $val->pkt_lokasi, $val->rkn_id, $val->psr_identitas, $val->psr_harga, $val->psr_harga_terkoreksi, $val->pkt_tgl_buat, $val->jenis_pengadaan, $val->anggaran, $val->kontrak_id, $val->kontrak_nilai, $val->spk_id, $val->spk_nilai, $val->ppk_id, $val->pp_id, $val->pnt_id, $val->pkt_id, $val->rup_id, $val->stk_id, $val->peg_id, $val->peg_nip, $val->peg_nama);
		    mysqli_stmt_execute($stmt);
		}
	}

	public function sync_non_tender_jadwal($year)
	{
		// $this->db->query("DELETE FROM tb_jadwal_non_tender WHERE (LEFT(tgl_mulai,4) = $year OR LEFT(tgl_selesai,4) = $year) ");

		$url = 'http://123.108.97.216/json/json/non_tender_jadwal?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_jadwal_non_tender WHERE kode_lelang NOT IN ($id_list) AND (LEFT(tgl_mulai,4) = $year OR LEFT(tgl_selesai,4) = $year)");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_jadwal_non_tender
				SET kode_lelang=?, tahapan=?, tgl_mulai=?, tgl_selesai=?, keterangan=?
		    	ON DUPLICATE KEY UPDATE
				tahapan=values(tahapan),tgl_mulai=values(tgl_mulai),tgl_selesai=values(tgl_selesai),keterangan=values(keterangan)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "issss", $val->kode_lelang, $val->tahapan, $val->tgl_mulai, $val->tgl_selesai, $val->keterangan);
		    mysqli_stmt_execute($stmt);
		}
	}

	public function pencatatan_nontender()
	{
		$data['inc'] = 'pencatatan_nontender_tb';
		$data['paket'] = $this->non_tender_m->get_pencatatan_nontender();
		// $this->load->view('admin/index', $data);	

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/pencatatan_nontender_ex', $data);			
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function realisasi_pencatatan_nontender()
	{
		$data['inc'] = 'realisasi_pencatatan_nontender_tb';
		$data['paket'] = $this->non_tender_m->get_realisasi_pencatatan_nontender();
		// $this->load->view('admin/index', $data);	

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/realisasi_pencatatan_nontender_ex', $data);			
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function total_realisasi_pencatatan_nontender()
	{
		$data['inc'] = 'total_pencatatan_nontender_tb';
		$data['paket'] = $this->non_tender_m->get_total_realisasi_pencatatan_nontender();

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('admin/total_pencatatan_nontender_ex', $data);			
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function sync_pencatatan_nontender()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/pencatatan_nontender?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_pencatatan_nontender WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_pencatatan_nontender
					SET kode_rup=?, stk_id=?, kode_lelang=?, pkt_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?, ukpbj=?, kgr_id=?, pkt_jenis=?, mtd_pemilihan=?, lls_dibuat_tanggal=?
			    	ON DUPLICATE KEY UPDATE
					kode_rup=values(kode_rup), stk_id=values(stk_id), kode_lelang=values(kode_lelang), pkt_id=values(pkt_id), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status), ukpbj=values(ukpbj), kgr_id=values(kgr_id), pkt_jenis=values(pkt_jenis), mtd_pemilihan=values(mtd_pemilihan), lls_dibuat_tanggal=values(lls_dibuat_tanggal)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiiisiiiiiiiis", $val->kode_rup, $val->stk_id, $val->kode_lelang, $val->pkt_id, $val->nama_paket, $val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->kgr_id, $val->pkt_jenis, $val->mtd_pemilihan, $val->lls_dibuat_tanggal);

		    mysqli_stmt_execute($stmt);
		}

		redirect('non_tender/pencatatan_nontender');
	}

	public function sync_realisasi_pencatatan_nontender()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/realisasi_pencatatan_nontender?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->rsk_id; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_realisasi_pencatatan_nontender WHERE rsk_id NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_realisasi_pencatatan_nontender
					SET kode_rup=?, stk_id=?, kode_lelang=?, pkt_id=?, nama_paket=?, pagu=?, hps=?, status_lelang=?, paket_status=?, ukpbj=?, kgr_id=?, pkt_jenis=?, mtd_pemilihan=?, lls_dibuat_tanggal=?, rsk_id=?, rsk_nilai=?, rsk_tanggal=?, rsk_keterangan=?
			    	ON DUPLICATE KEY UPDATE
					kode_rup=values(kode_rup), stk_id=values(stk_id), kode_lelang=values(kode_lelang), pkt_id=values(pkt_id), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), status_lelang=values(status_lelang), paket_status=values(paket_status), ukpbj=values(ukpbj), kgr_id=values(kgr_id), pkt_jenis=values(pkt_jenis), mtd_pemilihan=values(mtd_pemilihan), lls_dibuat_tanggal=values(lls_dibuat_tanggal), rsk_nilai=values(rsk_nilai), rsk_tanggal=values(rsk_tanggal), rsk_keterangan=values(rsk_keterangan)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiiisiiiiiiiisiiss", $val->kode_rup, $val->stk_id, $val->kode_lelang, $val->pkt_id, $val->nama_paket, $val->pagu, $val->hps, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->kgr_id, $val->pkt_jenis, $val->mtd_pemilihan, $val->lls_dibuat_tanggal, $val->rsk_id, $val->rsk_nilai, $val->rsk_tanggal, $val->rsk_keterangan);

		    mysqli_stmt_execute($stmt);
		}

		redirect('non_tender/realisasi_pencatatan_nontender');
	}

	public function update_status_aktif()
	{
		// mengupdate status aktif
		$str = "UPDATE tb_non_tender l
		LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		SET l.status_aktif = 'aktif'
		WHERE (l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN')
		OR (l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN')";
		$this->db->query($str);

		// mengupdate status balikkan (non aktif)
		$str = "UPDATE tb_non_tender SET status_aktif = 'non aktif'
		WHERE status_lelang != 1 AND kode_rup IN
		(SELECT kode_rup FROM (SELECT l.* FROM tb_non_tender l WHERE (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0)
		OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) AND l.status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status non aktif
		$str = "UPDATE tb_non_tender l SET l.status_aktif = 'non aktif'
		WHERE l.status_aktif != 'aktif' AND l.kode_rup IN
		(SELECT kode_rup FROM (SELECT * FROM tb_non_tender WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status default
		$str = "UPDATE tb_non_tender l SET l.status_aktif = ''
		WHERE l.status_aktif != 'aktif' AND l.kode_rup NOT IN
		(SELECT kode_rup FROM (SELECT * FROM tb_non_tender WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);
	}

	public function cari_pemenang()
	{
		$str = "SELECT l.kode_lelang, l.kode_rup, l.status_lelang, j.tgl_mulai FROM tb_non_tender l
		LEFT JOIN tb_jadwal_non_tender j ON l.kode_lelang = j.kode_lelang
		WHERE j.tahapan = 'TANDATANGAN_SPK' AND (l.kode_lelang = j.kode_lelang)
		GROUP BY l.kode_lelang";
		$menang = $this->db->query($str)->result();

		foreach ($menang as $value) {

			$datetime1 = new DateTime ($value->tgl_mulai);
			$tgl_sekarang = new DateTime (date('Y-m-d H:i:s'));

			if( ($datetime1 <= $tgl_sekarang) && ($datetime1 !== NULL) ) {
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang'=>1);
				$data['menang'] = 5;
				$this->db->update('tb_non_tender',$data,$key);
			}elseif( ($datetime1 > $tgl_sekarang) && ($datetime1 !== NULL) ){
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang !='=>1);
				$data['menang'] = 0;
				$this->db->update('tb_non_tender',$data,$key);
			}elseif($datetime1 == NULL){
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang !='=>1);
				$data['menang'] = 0;
				$this->db->update('tb_non_tender',$data,$key);
			}

		}
	}

	public function cari_pemenang_complete()
	{
		$tahun = date('Y');

		$str = "UPDATE tb_non_tender_complete SET menang = 5 WHERE kode_lelang IN
		(SELECT tp.kode_lelang FROM tb_non_tender_pemenang tp, tb_non_tender nt WHERE (tp.kode_lelang = nt.kode_lelang) 
		AND tp.anggaran = $tahun AND nt.anggaran = $tahun)";

		$this->db->query($str);
	}

	public function cari_pemenang_complete2()
	{
		$str = "SELECT l.kode_lelang, l.kode_rup, l.status_lelang, j.tgl_mulai FROM tb_non_tender_complete l
		LEFT JOIN tb_jadwal_non_tender j ON l.kode_lelang = j.kode_lelang
		WHERE j.tahapan = 'TANDATANGAN_SPK' AND (l.kode_lelang = j.kode_lelang)
		GROUP BY l.kode_lelang";
		$menang = $this->db->query($str)->result();

		foreach ($menang as $value) {

			$datetime1 = new DateTime ($value->tgl_mulai);
			$tgl_sekarang = new DateTime (date('Y-m-d H:i:s'));

			if( ($datetime1 <= $tgl_sekarang) && ($datetime1 !== NULL) ) {
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang'=>1);
				$data['menang'] = 5;
				$this->db->update('tb_non_tender_complete',$data,$key);
			}elseif( ($datetime1 > $tgl_sekarang) && ($datetime1 !== NULL) ){
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang !='=>1);
				$data['menang'] = 0;
				$this->db->update('tb_non_tender_complete',$data,$key);
			}elseif($datetime1 == NULL){
				$key = array('kode_lelang'=>$value->kode_lelang,'status_lelang !='=>1);
				$data['menang'] = 0;
				$this->db->update('tb_non_tender_complete',$data,$key);
			}

		}
	}

	public function update_status_aktif_complete()
	{
		// mengupdate status aktif
		$str = "UPDATE tb_non_tender_complete l
		LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		SET l.status_aktif = 'aktif'
		WHERE (l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN')
		OR (l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN')";
		$this->db->query($str);

		// mengupdate status balikkan (non aktif)
		$str = "UPDATE tb_non_tender_complete SET status_aktif = 'non aktif'
		WHERE status_lelang != 1 AND kode_rup IN
		(SELECT kode_rup FROM (SELECT l.* FROM tb_non_tender_complete l WHERE (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0)
		OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) AND l.status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status non aktif
		$str = "UPDATE tb_non_tender_complete l SET l.status_aktif = 'non aktif'
		WHERE l.status_aktif != 'aktif' AND l.kode_rup IN
		(SELECT kode_rup FROM (SELECT * FROM tb_non_tender_complete WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status default
		$str = "UPDATE tb_non_tender_complete l SET l.status_aktif = ''
		WHERE l.status_aktif != 'aktif' AND l.kode_rup NOT IN
		(SELECT kode_rup FROM (SELECT * FROM tb_non_tender_complete WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);
	}



	public function belum_tayang()
	{
		$data['inc'] = 'nontender_daftarpaket_tb';
		$data['listpaket'] = $this->non_tender_m->get_daftarpaket('belumtayang');
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/nontender_daftarpaket_ex', $data);			
		}else{
			$this->load->view('monev/index', $data);
		}
	}

	public function tayang()
	{
		$data['inc'] = 'nontender_daftarpaket_tb';
		$data['listpaket'] = $this->non_tender_m->get_daftarpaket('tayang');

		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/nontender_daftarpaket_ex', $data);			
		}else{
			$this->load->view('monev/index', $data);
		}
	}

	public function menang()
	{
		$data['inc'] = 'nontender_daftarpaket_tb';
		$data['listpaket'] = $this->non_tender_m->get_daftarpaket('menang');
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/nontender_daftarpaket_ex', $data);			
		}else{
			$this->load->view('monev/index', $data);
		}
	}

	public function data_pemenang()
	{
		$data['inc'] = 'nontender_data_pemenang_tb';
		$data['listpaket'] = $this->non_tender_m->get_datapemenang();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/nontender_datapemenang_ex', $data);			
		}else{
			$this->load->view('monev/index', $data);
		}
	}

	public function data_pemenang_spk()
	{
		$this->load->helper('kinerja_tender_helper');

		$data['inc'] = 'nontender_data_pemenang_spk_tb';
		$data['listpaket'] = $this->non_tender_m->get_datapemenang_spk();
		
		if(isset($_GET['type']) && $_GET['type'] == 'excel'){
			$this->load->view('monev/nontender_datapemenang_spk_ex', $data);			
		}else{
			$this->load->view('monev/index', $data);
		}

		$this->sync_spk();
	}

	public function sync_spk()
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
		    $sql = "INSERT INTO tb_spk SET spk_tgl=?, spk_id=?, spk_content=?, kontrak_id=?, spk_no=?, spk_nilai=?, spk_norekening=?, spk_nama_bank=?, alasanubah_spk_nilai=?, spk_wakil_penyedia=?, spk_jabatan_wakil=?  
		    ON DUPLICATE KEY UPDATE spk_tgl=values(spk_tgl), spk_content=values(spk_content), kontrak_id=values(kontrak_id), spk_no=values(spk_no), spk_nilai=values(spk_nilai), spk_norekening=values(spk_norekening), spk_nama_bank=values(spk_nama_bank), alasanubah_spk_nilai=values(alasanubah_spk_nilai), spk_wakil_penyedia=values(spk_wakil_penyedia), spk_jabatan_wakil=values(spk_jabatan_wakil)";
									
		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "sisssssssss", $val->spk_tgl, $val->spk_id, $val->spk_content, $val->kontrak_id, $val->spk_no, $val->spk_nilai, $val->spk_norekening, $val->spk_nama_bank, $val->alasanubah_spk_nilai, $val->spk_wakil_penyedia, $val->spk_jabatan_wakil);

		    mysqli_stmt_execute($stmt);
		}
	}
}
