<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auto extends CI_Controller {

	function __construct(){
		parent::__construct();
	}

	public function lepas_antrian()
	{
		$date = date('Y-m-d');
		$sql = "UPDATE tb_tpd SET tpd_status = 6 WHERE tpd_status = 7 AND tanggal_antrian = $date";
		$this->db->query($sql);
	}

	public function syncall()
	{
		$json = $this->db->get_where('json',array('data'=>'rup'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

		$connection = mysqli_connect("localhost","monev","#bpbj123!","monev");

		mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun='$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_rup SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?,
							kode_rup=?, nama_satker=?, nama_paket=?, program=?, kode_string_program=?, kegiatan=?, kode_string_kegiatan=?, volume=?, pagu_rup=?, mak=?,
							lokasi=?, detail_lokasi=?, sumber_dana=?, metode_pemilihan=?, jenis_pengadaan=?, pagu_perjenis_pengadaan=?, awal_pengadaan=?,
							akhir_pengadaan=?, awal_pekerjaan=?, akhir_pekerjaan=?, tanggal_kebutuhan=?, spesifikasi=?, id_swakelola=?, nama_kpa=?, penyedia_didalam_swakelola=?,
							tkdn=?, pradipa=?, status_aktif=?, status_umumkan=?, id_client=?, tahun=?
		            ON DUPLICATE KEY UPDATE
								tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi),
									nama_satker=values(nama_satker),nama_paket=values(nama_paket),program=values(program),kode_string_program=values(kode_string_program),kegiatan=values(kegiatan),kode_string_kegiatan=values(kode_string_kegiatan),volume=values(volume),pagu_rup=values(pagu_rup),
									mak=values(mak),lokasi=values(lokasi),detail_lokasi=values(detail_lokasi),sumber_dana=values(sumber_dana),metode_pemilihan=values(metode_pemilihan),jenis_pengadaan=values(jenis_pengadaan),pagu_perjenis_pengadaan=values(pagu_perjenis_pengadaan),awal_pengadaan=values(awal_pengadaan),
									akhir_pengadaan=values(akhir_pengadaan),awal_pekerjaan=values(awal_pekerjaan),akhir_pekerjaan=values(akhir_pekerjaan),tanggal_kebutuhan=values(tanggal_kebutuhan),spesifikasi=values(spesifikasi),id_swakelola=values(id_swakelola),nama_kpa=values(nama_kpa),penyedia_didalam_swakelola=values(penyedia_didalam_swakelola),
									tkdn=values(tkdn),pradipa=values(pradipa),status_aktif=values(status_aktif),status_umumkan=values(status_umumkan),id_client=values(id_client),tahun=values(tahun)";
		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "isisssisssssssissssssisssssssssssssss", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi,
					$val->kode_rup, $val->nama_satker, $val->nama_paket, $val->program, $val->kode_string_program, $val->kegiatan, $val->kode_string_kegiatan, $val->volume, $val->pagu_rup,
					$val->mak, $val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->metode_pemilihan, $val->jenis_pengadaan, $val->pagu_perjenis_pengadaan, $val->awal_pengadaan,
					$val->akhir_pengadaan, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->tanggal_kebutuhan, $val->spesifikasi, $val->id_swakelola, $val->nama_kpa, $val->penyedia_didalam_swakelola,
					$val->tkdn, $val->pradipa, $val->status_aktif, $val->status_umumkan, $val->id_client, $year);
		        mysqli_stmt_execute($stmt);
		}

		unsleep(30000000);

		$tahun = date('Y');
		$this->db->empty_table('tb_lelang_bck');

		$str = "INSERT INTO tb_lelang_bck
		(SELECT * FROM tb_lelang WHERE tahun = $tahun)";
		$this->db->query($str);

		// ambil link dinamis
		$json = $this->db->get_where('json',array('data'=>'lelang'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$test = file_get_contents($filename);
		$data = json_decode($test);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));

		// $connection = mysqli_connect("localhost","root","","monev");
		$connection = mysqli_connect("localhost","monev","#bpbj123!","monev");

		mysqli_query($connection, "DELETE FROM tb_lelang WHERE kode_lelang NOT IN ($id_list) AND tahun = '$year'");
		//mysqli_query($connection, "TRUNCATE TABLE tb_jadwal");

		foreach ($data as $val)
		{
				$jadwal = (empty($val->jadwal)) ? '' : json_encode($val->jadwal) ;

				if(count($val->jadwal) != 0){

					$data_jadwal = $val->jadwal;
					foreach ($data_jadwal as $val_jadwal)
					{
						$sql1 = "INSERT INTO tb_jadwal (kode_lelang,kode_rup,tahapan,tgl_mulai,tgl_selesai,keterangan)
						VALUES ('$val->kode_lelang','$val->kode_rup','$val_jadwal->tahapan','$val_jadwal->tgl_mulai','$val_jadwal->tgl_selesai','$val_jadwal->keterangan')";
						mysqli_query($connection, $sql1);
					}
				}

		    $sql = "INSERT INTO tb_lelang SET kode_lelang=?, kode_rup=?, nama_paket=?, pagu=?, hps=?, tahun=?, status_lelang=?, paket_status=?, ukpbj=?, lls_kontrak_pembayaran=?
		    ON DUPLICATE KEY UPDATE
				kode_rup=values(kode_rup), nama_paket=values(nama_paket), pagu=values(pagu),
				hps=values(hps),tahun=values(tahun),status_lelang=values(status_lelang),paket_status=values(paket_status),ukpbj=values(ukpbj),lls_kontrak_pembayaran=values(lls_kontrak_pembayaran)";
		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iisiisiisi", $val->kode_lelang, $val->kode_rup, $val->nama_paket, $val->pagu, $val->hps, $val->tahun,
					$val->status_lelang, $val->paket_status, $val->ukpbj, $val->lls_kontrak_pembayaran);
		    mysqli_stmt_execute($stmt);


		}

		$this->cari_pemenang();

		// insert ke tb_lelang_new filter rup
		mysqli_query($connection, "TRUNCATE TABLE tb_lelang_new");
		mysqli_query($connection, "INSERT IGNORE INTO tb_lelang_new (SELECT * FROM tb_lelang WHERE (status_lelang = 0 OR status_lelang = 1) ORDER BY kode_lelang DESC)");

		$this->update_status_aktif();

		unsleep(30000000);

		$tahun = date('Y');

		$this->db->empty_table('tb_lelang_spse_bck');

		$str = "INSERT INTO tb_lelang_spse_bck
		(SELECT * FROM tb_lelang_spse WHERE ang_tahun = $tahun)";
		$this->db->query($str);

		// ambil link dinamis
		$json = $this->db->get_where('json',array('data'=>'lelang_spse'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		// $url 	= 'http://123.108.97.215/json/json/lelang_spse?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun='.$year;
		$json = file_get_contents($filename);
		$data = json_decode($json);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));
		$connection = mysqli_connect("localhost","monev","#bpbj123!","monev");

		mysqli_query($connection, "DELETE FROM tb_lelang_spse WHERE kode_lelang NOT IN ($id_list) AND ang_tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_lelang_spse
				SET kode_lelang=?, pnt_id=?, pkt_id=?, nama_paket=?, pagu=?, hps=?, jenis_pengadaan=?, status_lelang=?, paket_status=?, ukpbj=?, pkt_lokasi=?,
				pkt_tgl_buat=?, stk_id=?, rup_stk_id=?, stk_nama=?, ang_tahun=?, sbd_id=?, mtd_pemilihan=?, lls_kontrak_pembayaran=?
		    ON DUPLICATE KEY UPDATE
				kode_lelang=values(kode_lelang),pnt_id=values(pnt_id),pkt_id=values(pkt_id),nama_paket=values(nama_paket),pagu=values(pagu),hps=values(hps),jenis_pengadaan=values(jenis_pengadaan),
				status_lelang=values(status_lelang),paket_status=values(paket_status),ukpbj=values(ukpbj),pkt_lokasi=values(pkt_lokasi),
				pkt_tgl_buat=values(pkt_tgl_buat),stk_id=values(stk_id),rup_stk_id=values(rup_stk_id),stk_nama=values(stk_nama),ang_tahun=IF(ang_tahun = values(ang_tahun),values(ang_tahun), ang_tahun),
				sbd_id=values(sbd_id), mtd_pemilihan=values(mtd_pemilihan), lls_kontrak_pembayaran=values(lls_kontrak_pembayaran)";

		    $stmt = mysqli_prepare($connection, $sql);

				mysqli_stmt_bind_param($stmt, "iiissiisiissiisisii", $val->kode_lelang, $val->pnt_id, $val->pkt_id, $val->nama_paket, $val->pagu, $val->hps, $val->jenis_pengadaan,
				$val->status_lelang, $val->paket_status, $val->ukpbj, $val->pkt_lokasi, $val->pkt_tgl_buat, $val->stk_id, $val->rup_stk_id, $val->stk_nama,
				$val->ang_tahun, $val->sbd_id, $val->mtd_pemilihan, $val->lls_kontrak_pembayaran);
		    mysqli_stmt_execute($stmt);
		}

		$this->sync_lelang_spse_jadwal();
		$this->update_status_aktif_spse();
		$this->sync_ambil_pemenang();
		$this->sync_panitia();
	}
}
