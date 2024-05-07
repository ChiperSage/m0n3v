<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lelang extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('lelang_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('admin','monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index($page = 0)
	{
		$data['inc'] = 'lelang_table';
		$data['lelang'] = $this->lelang_m->get();
		$this->load->view('admin/index', $data);
	}

	public function sync_kinerja_tender()
	{
		$filename = 'https://isb.lkpp.go.id/isb-2/api/e62527be-96f8-4fb1-9ffe-14eb3840bbd8/json/2751/SIKaP-PenilaianKinerjaPenyedia-Tender/tipe/4:12/parameter/2022:D1';
        $year = 2022;

        $test = file_get_contents($filename);
        $data = json_decode($test);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kd_tender; }, $data));

        $connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

        mysqli_query($connection, "DELETE FROM tb_kinerja_tender WHERE kd_tender NOT IN ($id_list) AND tahun_anggaran = '$year'");

        foreach ($data as $val)
        {
            $sql = "INSERT INTO tb_kinerja_tender SET tahun_anggaran=?, kd_klpd=?, kd_satker=?, kd_lpse=?, kd_tender=?, nama_paket=?, mtd_pemilihan=?, nip_ppk=?, nama_ppk=?, kd_penyedia=?, nama_penyedia=?, npwp_penyedia=?, indikator_penilaian=?, nilai_indikator=?, total_skors=?

            ON DUPLICATE KEY UPDATE
            
            tahun_anggaran=values(tahun_anggaran), kd_klpd=values(kd_klpd), kd_satker=values(kd_satker), kd_lpse=values(kd_lpse), kd_tender=values(kd_tender), nama_paket=values(nama_paket),
            mtd_pemilihan=values(mtd_pemilihan), nip_ppk=values(nip_ppk), nama_ppk=values(nama_ppk), kd_penyedia=values(kd_penyedia), nama_penyedia=values(nama_penyedia), npwp_penyedia=values(npwp_penyedia), indikator_penilaian=values(indikator_penilaian), nilai_indikator=values(nilai_indikator), total_skors=values(total_skors)";

            $stmt = mysqli_prepare($connection, $sql);

            mysqli_stmt_bind_param($stmt, "issiissssisssii", $val->tahun_anggaran, $val->kd_klpd, $val->kd_satker, $val->kd_lpse, $val->kd_tender, $val->nama_paket, $val->mtd_pemilihan, $val->nip_ppk, $val->nama_ppk, $val->kd_penyedia, $val->nama_penyedia, $val->npwp_penyedia, $val->indikator_penilaian, $val->nilai_indikator, $val->total_skors);

            mysqli_stmt_execute($stmt);
        }
	}

	public function _copy_data()
	{
		$this->db->empty_table('tb_lelang_bck');

		$str = "INSERT IGNORE INTO tb_lelang_bck
		(SELECT * FROM tb_lelang WHERE kode_lelang)";
		$this->db->query($str);
	}

	public function create()
	{
		// link json dinamis
		$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_lelang WHERE kode_lelang NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
			$jadwal = (empty($val->jadwal)) ? '' : json_encode($val->jadwal) ;

			if(count($val->jadwal) != 0){

					$data_jadwal = $val->jadwal;
					foreach ($data_jadwal as $val_jadwal)
					{
						$sql1 = "INSERT INTO tb_jadwal (kode_rup,kode_lelang,tahapan,tgl_mulai,tgl_selesai,keterangan)
						VALUES ('$val->kode_rup','$val->kode_lelang','$val_jadwal->tahapan','$val_jadwal->tgl_mulai','$val_jadwal->tgl_selesai','$val_jadwal->keterangan')";
						mysqli_query($connection, $sql1);
					}
			}

		    $sql = "INSERT INTO tb_lelang SET kode_lelang=?, kode_rup=?, nama_paket=?, kls_id=?, pagu=?, hps=?, tahun=?, status_lelang=?, paket_status=?, ukpbj=?, lls_kontrak_pembayaran=? 
		    	ON DUPLICATE KEY UPDATE
				kode_rup=values(kode_rup), nama_paket=values(nama_paket), kls_id=values(kls_id), pagu=values(pagu),
				hps=values(hps),tahun=values(tahun),status_lelang=values(status_lelang),paket_status=values(paket_status),ukpbj=values(ukpbj),lls_kontrak_pembayaran=values(lls_kontrak_pembayaran)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iissiisiisi", $val->kode_lelang, $val->kode_rup, $val->nama_paket, $val->kls_id, $val->pagu, $val->hps, $val->tahun,
					$val->status_lelang, $val->paket_status, $val->ukpbj, $val->lls_kontrak_pembayaran);

		    mysqli_stmt_execute($stmt);
		}

		$this->cari_pemenang();

		$this->update_status_aktif();

		redirect('lelang');

		// insert ke tb_lelang_new filter rup
		// mysqli_query($connection, "TRUNCATE TABLE tb_lelang_new");
		// mysqli_query($connection, "INSERT IGNORE INTO tb_lelang_new (SELECT * FROM tb_lelang WHERE (status_lelang = 0 OR status_lelang = 1) ORDER BY kode_lelang DESC)");
	}

	public function index_spse()
	{
		$data['inc'] = 'lelang_spse_table';
		$data['lelang'] = $this->lelang_m->get_lelang_spse();
		$this->load->view('admin/index', $data);
	}

	public function sync_lelang_spse()
	{
		$tahun = date('Y');
		// $this->db->empty_table('tb_lelang_spse_bck');

		// $str = "INSERT INTO tb_lelang_spse_bck
		// (SELECT * FROM tb_lelang_spse WHERE ang_tahun = $tahun)";
		// $this->db->query($str);

		// ambil link dinamis
		$json = $this->db->get_where('json',array('data'=>'lelang_spse'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$json = file_get_contents($filename);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_lelang_spse WHERE kode_lelang NOT IN ($id_list) AND ang_tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_lelang_spse
				SET kode_lelang=?, pnt_id=?, pkt_id=?, nama_paket=?, kls_id=?, pagu=?, hps=?, jenis_pengadaan=?, status_lelang=?, paket_status=?, ukpbj=?, pkt_lokasi=?,
				pkt_tgl_buat=?, stk_id=?, rup_stk_id=?, stk_nama=?, ang_tahun=?, sbd_id=?, mtd_pemilihan=?, lls_kontrak_pembayaran=?
		    ON DUPLICATE KEY UPDATE
				kode_lelang=values(kode_lelang),pnt_id=values(pnt_id),pkt_id=values(pkt_id),nama_paket=values(nama_paket),kls_id=values(kls_id),pagu=values(pagu),hps=values(hps),jenis_pengadaan=values(jenis_pengadaan),
				status_lelang=values(status_lelang),paket_status=values(paket_status),ukpbj=values(ukpbj),pkt_lokasi=values(pkt_lokasi),
				pkt_tgl_buat=values(pkt_tgl_buat),stk_id=values(stk_id),rup_stk_id=values(rup_stk_id),stk_nama=values(stk_nama),ang_tahun=values(ang_tahun),
				-- ang_tahun=IF(ang_tahun = values(ang_tahun),values(ang_tahun), ang_tahun),
				sbd_id=values(sbd_id), mtd_pemilihan=values(mtd_pemilihan), lls_kontrak_pembayaran=values(lls_kontrak_pembayaran)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiisssiisiissiisisii", $val->kode_lelang, $val->pnt_id, $val->pkt_id, $val->nama_paket, $val->kls_id, $val->pagu, $val->hps, $val->jenis_pengadaan,
				$val->status_lelang, $val->paket_status, $val->ukpbj, $val->pkt_lokasi, $val->pkt_tgl_buat, $val->stk_id, $val->rup_stk_id, $val->stk_nama,
				$val->ang_tahun, $val->sbd_id, $val->mtd_pemilihan, $val->lls_kontrak_pembayaran);

		    mysqli_stmt_execute($stmt);
		}

		$this->update_status_aktif_spse();
		
		$this->sync_panitia();

		$this->sync_lelang_spse_jadwal();

		$this->sync_ambil_pemenang();

		// $this->sync_lelang_spse2();

		$this->sync_sanggah();

		redirect('lelang/index_spse');

		// $this->ambil_selisih();
		// $this->db->truncate('tb_lelang_spse_bck');
		// mysqli_query($connection, "INSERT IGNORE INTO tb_lelang_spse_bck (SELECT * FROM tb_lelang_spse)");
	}

	public function ambil_selisih()
	{
		// $sql = "INSERT INTO tb_selisih_spse (tanggal, kode_lelang)
		// SELECT NOW(), ls.kode_lelang FROM tb_lelang_spse ls WHERE ls.kode_lelang NOT IN (SELECT lsb.kode_lelang FROM tb_lelang_spse_bck lsb)";
		// $this->db->query($sql);
	}

	public function sync_lelang_spse_jadwal()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/lelang_spse_jadwal?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->dtj_id; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_jadwal_spse WHERE dtj_id NOT IN ($id_list) AND LEFT(tgl_mulai,4) = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_jadwal_spse
				SET dtj_id=?, akt_id=?, kode_lelang=?, tahapan=?, tgl_mulai=?, tgl_selesai=?, keterangan=?
		    ON DUPLICATE KEY UPDATE
				dtj_id=values(dtj_id), akt_id=values(akt_id), kode_lelang=values(kode_lelang),tahapan=values(tahapan),tgl_mulai=values(tgl_mulai),tgl_selesai=values(tgl_selesai),keterangan=values(keterangan)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "iiissss", $val->dtj_id, $val->akt_id, $val->kode_lelang, $val->tahapan, $val->tgl_mulai, $val->tgl_selesai, $val->keterangan);
		    mysqli_stmt_execute($stmt);
		}
	}

	public function sync_ambil_pemenang()
	{
		$year = date('Y');

		$json = $this->db->get_where('json',array('data'=>'lelang_spse'))->row();
		$year = $json->tahun;

		$url = 'http://123.108.97.216/json/json/ambil_pemenang?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun='.$year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_pemenang WHERE kode_lelang NOT IN ($id_list) AND ang_tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_pemenang
				SET kode_lelang=?, nama_paket=?, pagu=?, hps=?, jenis_pengadaan=?, status_lelang=?, paket_status=?, ukpbj=?, jadwal=?, pkt_lokasi=?,
				rkn_id=?, rkn_npwp=?, rkn_nama=?, rkn_alamat=?, rkn_telepon=?, rkn_mobile_phone=?, rkn_pkp=?, rkn_email=?, psr_id=?, psr_harga=?, psr_harga_terkoreksi=?, nev_harga=?, nev_harga_terkoreksi=?, nev_harga_negosiasi=?, nev_urutan=?, eva_status=?,
				nev_lulus=?, pkt_tgl_buat=?, ang_tahun=?, sbd_id=?, is_pemenang=?, is_pemenang_verif=?
		    ON DUPLICATE KEY UPDATE
				kode_lelang=values(kode_lelang), nama_paket=values(nama_paket), pagu=values(pagu), hps=values(hps), jenis_pengadaan=values(jenis_pengadaan), status_lelang=values(status_lelang), paket_status=values(paket_status), ukpbj=values(ukpbj), jadwal=values(jadwal), pkt_lokasi=values(pkt_lokasi),
				rkn_id=values(rkn_id), rkn_npwp=values(rkn_npwp), rkn_nama=values(rkn_nama), rkn_alamat=values(rkn_alamat), rkn_telepon=values(rkn_telepon), rkn_mobile_phone=values(rkn_mobile_phone), rkn_pkp=values(rkn_pkp), rkn_email=values(rkn_email), psr_id=values(psr_id), psr_harga=values(psr_harga), psr_harga_terkoreksi=values(psr_harga_terkoreksi), nev_harga=values(nev_harga), nev_harga_terkoreksi=values(nev_harga_terkoreksi), nev_harga_negosiasi=values(nev_harga_negosiasi), nev_urutan=values(nev_urutan),
				eva_status=values(eva_status), nev_lulus=values(nev_lulus), pkt_tgl_buat=values(pkt_tgl_buat), ang_tahun=IF(ang_tahun = values(ang_tahun),values(ang_tahun), ang_tahun), sbd_id=values(sbd_id), is_pemenang=values(is_pemenang), is_pemenang_verif=values(is_pemenang_verif)";

		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "isiiiiisssisssssssiiiiiiiiisssii", $val->kode_lelang, $val->nama_paket, $val->pagu, $val->hps, $val->jenis_pengadaan, $val->status_lelang, $val->paket_status, $val->ukpbj, $val->jadwal, $val->pkt_lokasi,
				$val->rkn_id, $val->rkn_npwp, $val->rkn_nama, $val->rkn_alamat, $val->rkn_telepon, $val->rkn_mobile_phone, $val->rkn_pkp, $val->rkn_email, $val->psr_id, $val->psr_harga, $val->psr_harga_terkoreksi, $val->nev_harga, $val->nev_harga_terkoreksi, $val->nev_harga_negosiasi, $val->nev_urutan, $val->eva_status, $val->nev_lulus,
				$val->pkt_tgl_buat, $val->ang_tahun, $val->sbd_id, $val->is_pemenang, $val->is_pemenang_verif);

		    if (mysqli_stmt_execute($stmt)) {
			    // echo "Data inserted or updated.";
			} else {
				// $error_list = mysqli_stmt_error_list($stmt);
			    // foreach ($error_list as $error) {
			    //     echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
			    // }
			}
		}

		// set 5 pemenang lelang spse
		$str = "UPDATE tb_lelang_spse ls SET ls.menang = 5 WHERE ls.kode_lelang IN (SELECT p.kode_lelang FROM tb_pemenang p) AND ls.ang_tahun = $year";
		$this->db->query($str);

		// set 0
		$str = "UPDATE tb_lelang_spse ls SET ls.menang = 0 WHERE ls.kode_lelang NOT IN (SELECT p.kode_lelang FROM tb_pemenang p) AND ls.ang_tahun = $year";
		$this->db->query($str);
	}

	public function update_status_aktif_spse()
	{
		// mengupdate status aktif
		$str = "UPDATE tb_lelang_spse l
		SET l.status_aktif = 'aktif'
		WHERE (l.status_lelang = 1 AND l.menang = 5)
		OR (l.status_lelang = 1 AND l.menang = 0)";
		$this->db->query($str);

		// mengupdate status balikkan (non aktif)
		$str = "UPDATE tb_lelang_spse SET status_aktif = 'non aktif'
		WHERE status_lelang != 1 AND kode_lelang IN
		(SELECT kode_lelang FROM (SELECT l.* FROM tb_lelang_spse l WHERE (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0)
		OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) AND l.status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status non aktif
		$str = "UPDATE tb_lelang_spse l SET l.status_aktif = 'non aktif'
		WHERE l.status_aktif != 'aktif' AND l.kode_lelang IN
		(SELECT kode_lelang FROM (SELECT * FROM tb_lelang_spse WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status default
		$str = "UPDATE tb_lelang_spse l SET l.status_aktif = ''
		WHERE l.status_aktif != 'aktif' AND l.kode_lelang NOT IN
		(SELECT kode_lelang FROM (SELECT * FROM tb_lelang_spse WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);
	}

	public function sync_panitia()
	{
		$year = date('Y');
		$json = $this->db->get_where('json',array('data'=>'lelang_spse'))->row();
		$year = $json->tahun;
		
		$url = 'http://123.108.97.216/json/json/panitia?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->pnt_id; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_panitia WHERE pnt_id NOT IN ($id_list) AND pnt_tahun >= '$year'");

		foreach ($data as $val)
		{
				$sql = "INSERT INTO tb_panitia
				SET pnt_id=?, stk_id=?, pnt_nama=?, pnt_tahun=?, pnt_no_sk=?, audituser=?
				ON DUPLICATE KEY UPDATE
				pnt_id=values(pnt_id), stk_id=values(stk_id), pnt_nama=values(pnt_nama), pnt_tahun=values(pnt_tahun), pnt_no_sk=values(pnt_no_sk), audituser=values(audituser)";

				$stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iisiss", $val->pnt_id, $val->stk_id, $val->pnt_nama, $val->pnt_tahun, $val->pnt_no_sk, $val->audituser);
				mysqli_stmt_execute($stmt);
		}
	}

	public function sync_ppk()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/ppk?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2021';
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->ppk_id; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_ppk WHERE ppk_id NOT IN ($id_list) AND ang_tahun = '$year'");

		foreach ($data as $val)
		{
				$sql = "INSERT INTO tb_ppk
				SET ppk_id=?, peg_nip=?, peg_nama=?, lls_id=?, pkt_id=?, pkt_nama=?
				ON DUPLICATE KEY UPDATE
				ppk_id=values(ppk_id), peg_nip=values(peg_nip), peg_nama=values(peg_nama), lls_id=values(lls_id), pkt_id=values(pkt_id), pkt_nama=values(pkt_nama)";

				$stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iisiis", $val->ppk_id, $val->peg_nip, $val->peg_nama, $val->lls_id, $val->pkt_id, $val->pkt_nama);
				mysqli_stmt_execute($stmt);
		}
	}

	public function sync_sanggah()
	{
		$year = date('Y');

		$url = 'http://123.108.97.216/json/json/sanggah?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=' . $year;
		$json = file_get_contents($url);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->sgh_id; }, $data));
		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_sanggah WHERE sgh_id NOT IN ($id_list) AND left(sgh_tanggal,4) = '$year'");

		foreach ($data as $val)
		{
			$sql = "INSERT INTO tb_sanggah
			SET sgh_id=?, psr_id=?, thp_id=?, san_sgh_id=?, sgh_tanggal=?, rkn_id=?, lls_id=?
			ON DUPLICATE KEY UPDATE
			sgh_id=values(sgh_id), psr_id=values(psr_id), thp_id=values(thp_id), san_sgh_id=values(san_sgh_id), sgh_tanggal=values(sgh_tanggal), rkn_id=values(rkn_id), lls_id=values(lls_id)";

			$stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "sssssss", $val->sgh_id, $val->psr_id, $val->thp_id, $val->san_sgh_id, $val->sgh_tanggal, $val->rkn_id, $val->lls_id);

			mysqli_stmt_execute($stmt);
		}

		$sql = "UPDATE tb_lelang_spse ls SET ls.sanggah = 1 WHERE ls.kode_lelang IN (SELECT s.lls_id FROM tb_sanggah s)";
		$this->db->query($sql);
	}

	public function update_status_aktif()
	{
		// mengupdate status aktif
		$str = "UPDATE tb_lelang l
		LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup
		SET l.status_aktif = 'aktif'
		WHERE (l.status_lelang = 1 AND l.menang = 5 AND r.sumber_dana != 'APBN')
		OR (l.status_lelang = 1 AND l.menang = 0 AND r.sumber_dana != 'APBN')";
		$this->db->query($str);

		// mengupdate status balikkan (non aktif)
		$str = "UPDATE tb_lelang SET status_aktif = 'non aktif'
		WHERE status_lelang != 1 AND kode_rup IN
		(SELECT kode_rup FROM (SELECT l.* FROM tb_lelang l WHERE (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0)
		OR (l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5) AND l.status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status non aktif
		$str = "UPDATE tb_lelang l SET l.status_aktif = 'non aktif'
		WHERE l.status_aktif != 'aktif' AND l.kode_rup IN
		(SELECT kode_rup FROM (SELECT * FROM tb_lelang WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);

		// mengupdate status default
		$str = "UPDATE tb_lelang l SET l.status_aktif = ''
		WHERE l.status_aktif != 'aktif' AND l.kode_rup NOT IN
		(SELECT kode_rup FROM (SELECT * FROM tb_lelang WHERE status_aktif = 'aktif') as tb_temp)";
		$this->db->query($str);
	}

	public function jadwal()
	{
		$array = array();
		$data['inc'] = 'lelang_jadwal';
		$data['lelang'] = $this->lelang_m->get_jadwal();
		$this->load->view('admin/index', $data);
	}

	public function cari_pemenang()
	{
		$tahun1 = date('Y');
		$tahun2 = date('Y') + 1;

		$str = "SELECT l.kode_lelang, l.kode_rup, l.status_lelang, j.tgl_mulai FROM tb_lelang l
		LEFT JOIN tb_jadwal j ON l.kode_rup = j.kode_rup
		WHERE j.tahapan = 'TANDATANGAN_KONTRAK' AND (l.kode_lelang = j.kode_lelang AND l.kode_rup = j.kode_rup) AND l.status_aktif = 'aktif' AND (l.tahun = $tahun1 OR l.tahun = $tahun2)
		GROUP BY l.kode_lelang";
		$menang = $this->db->query($str)->result();

		foreach ($menang as $value) {

			$datetime1 = new DateTime ($value->tgl_mulai);
			$tgl_sekarang = new DateTime (date('Y-m-d H:i:s'));

			if( ($datetime1 <= $tgl_sekarang) && ($datetime1 !== NULL) ) {
				$key = array('kode_rup'=>$value->kode_rup,'status_lelang'=>1);
				$data['menang'] = 5;
				$this->db->update('tb_lelang',$data,$key);
			}elseif( ($datetime1 > $tgl_sekarang) && ($datetime1 !== NULL) ){
				$key = array('kode_rup'=>$value->kode_rup,'status_lelang'=>1);
				$data['menang'] = 0;
				$this->db->update('tb_lelang',$data,$key);
			}elseif($datetime1 == NULL){
				$key = array('kode_rup'=>$value->kode_rup,'status_lelang'=>1);
				$data['menang'] = 0;
				$this->db->update('tb_lelang',$data,$key);
			}
		}

	}

	public function sync_lelang2()
	{
		// $filename = 'https://inaproc.lkpp.go.id/isb/api/e3cd704d-9478-418a-ae1e-a4fe49d57115/json/23233435/PengumumanPenyediaDaerah1618/tipe/4:12/parameter/2022:D1';
		// $year = 2022;

		$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$test = file_get_contents($filename);
		$data = json_decode($test,true);

		foreach ($data as $val)
		{
			$val['tahun'] = $year;
			$val['jadwal'] = '';

			$insert_query= $this->db->insert_string('tmp_lelang', $val);
			$insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
			$this->db->query($insert_queryf);
		}

		$this->db->query("INSERT IGNORE INTO tb_lelang
			SELECT r3.* FROM tmp_lelang r3");

	}

	public function sync_lelang_spse2()
	{
		$json = $this->db->get_where('json',array('data'=>'lelang_spse'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$test = file_get_contents($filename);
		$data = json_decode($test,true);

		foreach ($data as $val)
		{
			$val['ang_tahun'] = $year;
			$val['jadwal'] = '';

			$insert_query= $this->db->insert_string('tmp_lelang_spse', $val);
			$insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
			$this->db->query($insert_queryf);
		}

	}

}
