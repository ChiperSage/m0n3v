<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

    public function cron_rup()
    {

        // ambil data json
        $json = $this->db->get_where('json',array('data'=>'rup'))->row();
        $filename = $json->url;
        $year = $json->tahun;

        $jsondata = file_get_contents($filename);
        $data = json_decode($jsondata);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; }, $data));

        $connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

        mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND tahun = '$year'");

        foreach ($data as $val)
        {
            $sql = "INSERT INTO tb_rup SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, kldi=?, kode_rup=?, nama_satker=?, nama_paket=?, program=?, kode_string_program=?, kegiatan=?, pagu_rup=?, mak=?, sumber_dana=?, metode_pemilihan=?, jenis_pengadaan=?, pagu_perjenis_pengadaan=?, awal_pengadaan=?, awal_pekerjaan=?, nama_kpa=?, penyedia_didalam_swakelola=?, tkdn=?, pradipa=?, status_aktif=?, status_umumkan=?, nama_ppk=?, nip_ppk=?, nip_kpa=?, deskripsi=?, umkm=?, tahun=?
            ON DUPLICATE KEY UPDATE tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), kldi=values(kldi), nama_satker=values(nama_satker), nama_paket=values(nama_paket), program=values(program), kode_string_program=values(kode_string_program), kegiatan=values(kegiatan), pagu_rup=values(pagu_rup), mak=values(mak), sumber_dana=values(sumber_dana), metode_pemilihan=values(metode_pemilihan), jenis_pengadaan=values(jenis_pengadaan), pagu_perjenis_pengadaan=values(pagu_perjenis_pengadaan), awal_pengadaan=values(awal_pengadaan), awal_pekerjaan=values(awal_pekerjaan), nama_kpa=values(nama_kpa), penyedia_didalam_swakelola=values(penyedia_didalam_swakelola), tkdn=values(tkdn), pradipa=values(pradipa), status_aktif=values(status_aktif), status_umumkan=values(status_umumkan), nama_ppk=values(nama_ppk), nip_ppk=values(nip_ppk), nip_kpa=values(nip_kpa), deskripsi=values(deskripsi), umkm=values(umkm), tahun=values(tahun)";
                                    
            $stmt = mysqli_prepare($connection, $sql);

            $jenis_pengadaan = explode(";", $val->jenis_pengadaan);
            $pagu_perjenis_pengadaan = explode(";", $val->pagu_perjenis_pengadaan);

            mysqli_stmt_bind_param($stmt, "ssiisssssssissssisssssssssssssi", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->kldi, $val->kode_rup, $val->nama_satker, $val->nama_paket, $val->program, $val->kode_string_program, $val->kegiatan, $val->pagu_rup, $val->mak, $val->sumber_dana, $val->metode_pemilihan, $jenis_pengadaan[0], $pagu_perjenis_pengadaan[0], $val->awal_pengadaan, $val->awal_pekerjaan, $val->nama_kpa, $val->penyedia_didalam_swakelola, $val->tkdn, $val->pradipa, $val->status_aktif, $val->status_umumkan, $val->nama_ppk, $val->nip_ppk, $val->nip_kpa, $val->deskripsi, $val->umkm, $year);

            if (mysqli_stmt_execute($stmt)) {
                // echo "Data inserted or updated.";
            } else {
                // $error_list = mysqli_stmt_error_list($stmt);
                // foreach ($error_list as $error) {
                //     echo "Error inserting data: " . $error['error'] . " on column " . $error['sqlstate'] . "\n";
                // }
            }
        }

    }

    public function cron_lelang()
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
        
    }

    public function rup()
    {
        $filename = 'https://inaproc.lkpp.go.id/isb/api/e3cd704d-9478-418a-ae1e-a4fe49d57115/json/23233435/PengumumanPenyediaDaerah1618/tipe/4:12/parameter/2021:D1';
        $year = 2021;

        $test = file_get_contents($filename);
        $data = json_decode($test,true);

        $idlist = array();
        foreach ($data as $val)
        {
            $idlist[] = $val['kode_rup'];
            
            $val['tahun'] = $year;

            $insert_query= $this->db->insert_string('tmp_rup', $val);
            $insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
            $this->db->query($insert_queryf);     
        }

        $this->db->query("DELETE FROM tmp_rup WHERE kode_rup NOT IN ($idlist) AND tahun = $year");
       
        $this->db->query("INSERT INTO tb_rup
         (SELECT r3.* FROM tmp_rup r3 WHERE r3.tahun = $year) ON DUPLICATE KEY UPDATE 
         nama_paket=values(nama_paket), jenis_pengadaan=values(jenis_pengadaan), status_aktif=values(status_aktif), status_umumkan=values(status_umumkan), metode_pemilihan=values(metode_pemilihan)");
    }

    public function lelang()
    {
        $filename = 'http://123.108.97.215/json/json/lelang?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2022';
        $year = 2022;

        $test = file_get_contents($filename);
        $data = json_decode($test,true);

        $idlist = array();
        foreach ($data as $val)
        {
            $idlist[] = $val['kode_lelang'];
            
            $val['tahun'] = $year;

            $insert_query= $this->db->insert_string('tmp_lelang', $val);
            $insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
            $this->db->query($insert_queryf);     
        }
        $this->db->query("DELETE FROM tmp_lelang WHERE kode_lelang NOT IN ($idlist) AND tahun = $year");
       
        $this->db->query("INSERT INTO tb_lelang
        (SELECT r3.* FROM tmp_lelang r3 WHERE r3.tahun = $year) ON DUPLICATE KEY UPDATE 
        nama_paket=values(nama_paket),paket_status=values(paket_status),status_lelang=values(status_lelang)");
    }

    public function lelangspse()
    {
        $filename = 'http://123.108.97.215/json/json/lelang_spse?x-api-key=6842cbf4ba070a2b5dbb1b45bd416664&tahun=2022';
        $year = 2022;

        $test = file_get_contents($filename);
        $data = json_decode($test,true);

        $idlist = array();
        foreach ($data as $val)
        {
            $idlist[] = $val['kode_lelang'];
            
            $val['ang_tahun'] = $year;

            $insert_query= $this->db->insert_string('tmp_lelang_spse', $val);
            $insert_queryf = str_replace("INSERT INTO","INSERT IGNORE INTO",$insert_query);
            $this->db->query($insert_queryf);     
        }
        $this->db->query("DELETE FROM tmp_lelang_spse WHERE kode_lelang NOT IN ($idlist) AND ang_tahun = $year");
       
        $this->db->query("INSERT INTO tb_lelang_spse
        (SELECT r3.* FROM tmp_lelang_spse r3 WHERE r3.ang_tahun = $year) ON DUPLICATE KEY UPDATE
        nama_paket=values(nama_paket),paket_status=values(paket_status),status_lelang=values(status_lelang)");
    }

	public function index()
	{
      $h = date('H');

      if($h == '01'){

        // sinkron RUP
        $json = $this->db->get_where('json',array('data'=>'rup'))->row();

    		$filename = $json->url;
    		$year = $json->tahun;

    		$test = file_get_contents($filename);
    		$data = json_decode($test);

    		$id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

    		$connection = mysqli_connect("localhost","monev","laserj3tbpbjplatinum","monev");

    		mysqli_query($connection, "DELETE FROM tb_rup WHERE kode_rup NOT IN ($id_list) AND left(awal_pekerjaan,4)='$year'");

    		foreach ($data as $val)
    		{
    		    $sql = "INSERT INTO tb_rup SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?,
    							kode_rup=?, nama_satker=?, nama_paket=?, program=?, kode_string_program=?, kegiatan=?, kode_string_kegiatan=?, volume=?, pagu_rup=?, mak=?,
    							lokasi=?, detail_lokasi=?, sumber_dana=?, metode_pemilihan=?, jenis_pengadaan=?, pagu_perjenis_pengadaan=?, awal_pengadaan=?,
    							akhir_pengadaan=?, awal_pekerjaan=?, akhir_pekerjaan=?, tanggal_kebutuhan=?, spesifikasi=?, id_swakelola=?, nama_kpa=?, penyedia_didalam_swakelola=?,
    							tkdn=?, pradipa=?, status_aktif=?, status_umumkan=?, id_client=?
    		            ON DUPLICATE KEY UPDATE
    								tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi),
    									nama_satker=values(nama_satker),nama_paket=values(nama_paket),program=values(program),kode_string_program=values(kode_string_program),kegiatan=values(kegiatan),kode_string_kegiatan=values(kode_string_kegiatan),volume=values(volume),pagu_rup=values(pagu_rup),
    									mak=values(mak),lokasi=values(lokasi),detail_lokasi=values(detail_lokasi),sumber_dana=values(sumber_dana),metode_pemilihan=values(metode_pemilihan),jenis_pengadaan=values(jenis_pengadaan),pagu_perjenis_pengadaan=values(pagu_perjenis_pengadaan),awal_pengadaan=values(awal_pengadaan),
    									akhir_pengadaan=values(akhir_pengadaan),awal_pekerjaan=values(awal_pekerjaan),akhir_pekerjaan=values(akhir_pekerjaan),tanggal_kebutuhan=values(tanggal_kebutuhan),spesifikasi=values(spesifikasi),id_swakelola=values(id_swakelola),nama_kpa=values(nama_kpa),penyedia_didalam_swakelola=values(penyedia_didalam_swakelola),
    									tkdn=values(tkdn),pradipa=values(pradipa),status_aktif=values(status_aktif),status_umumkan=values(status_umumkan),id_client=values(id_client)";
    		    $stmt = mysqli_prepare($connection, $sql);

    				mysqli_stmt_bind_param($stmt, "isisssisssssssissssssissssssssssssss", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi,
    					$val->kode_rup, $val->nama_satker, $val->nama_paket, $val->program, $val->kode_string_program, $val->kegiatan, $val->kode_string_kegiatan, $val->volume, $val->pagu_rup,
    					$val->mak, $val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->metode_pemilihan, $val->jenis_pengadaan, $val->pagu_perjenis_pengadaan, $val->awal_pengadaan,
    					$val->akhir_pengadaan, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->tanggal_kebutuhan, $val->spesifikasi, $val->id_swakelola, $val->nama_kpa, $val->penyedia_didalam_swakelola,
    					$val->tkdn, $val->pradipa, $val->status_aktif, $val->status_umumkan, $val->id_client);
    		        mysqli_stmt_execute($stmt);
    		}

        // sinkron RUP swakelola

        $filename = 'https://inaproc.lkpp.go.id/isb/api/491f8a1b-0df9-486c-89ef-bb1b295d1c4e/json/23106260/PengumumanSwakelolaKL1618/tipe/4:12/parameter/2020:D1';
        $year = date('Y');

        $test = file_get_contents($filename);
        $data = json_decode($test);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kode_rup; },$data));

        $connection = mysqli_connect("localhost","monev","laserj3tbpbjplatinum","monev");

        mysqli_query($connection, "DELETE FROM tb_rup_swakelola WHERE kode_rup NOT IN ($id_list) AND left(awal_pekerjaan,4)='$year'");

        foreach ($data as $val)
        {
            $sql = "INSERT INTO tb_rup_swakelola SET tanggal_terakhir_di_update=?, kode_kldi=?, id_satker=?, kode_satker_asli=?, jenis=?, kldi=?, kode_rup=?, nama_satker=?,
                    nama_paket=?, program=?, kegiatan=?, output=?, suboutput=?, komponen=?, pagu_rup=?, mak=?,
                    lokasi=?, detail_lokasi=?, sumber_dana=?, awal_pekerjaan=?, akhir_pekerjaan=?, nama_kpa=?, tipe_swakelola=?, status_aktif=?,
                    status_umumkan=?, id_program=?, id_kegiatan=?, id_output=?, id_suboutput=?, id_komponen=?, nama_ppk=?, nomor_renja=?
                  ON DUPLICATE KEY UPDATE
                    tanggal_terakhir_di_update=values(tanggal_terakhir_di_update), kode_kldi=values(kode_kldi), id_satker=values(id_satker), kode_satker_asli=values(kode_satker_asli), jenis=values(jenis), kldi=values(kldi), kode_rup=values(kode_rup), nama_satker=values(nama_satker),
                    nama_paket=values(nama_paket), program=values(program), kegiatan=values(kegiatan), output=values(output), suboutput=values(suboutput), komponen=values(komponen), pagu_rup=values(pagu_rup), mak=values(mak),
                    lokasi=values(lokasi), detail_lokasi=values(detail_lokasi), sumber_dana=values(sumber_dana), awal_pekerjaan=values(awal_pekerjaan), akhir_pekerjaan=values(akhir_pekerjaan), nama_kpa=values(nama_paket), tipe_swakelola=values(tipe_swakelola), status_aktif=values(status_aktif),
                    status_umumkan=values(status_umumkan), id_program=values(id_program), id_kegiatan=values(id_kegiatan), id_output=values(id_output), id_suboutput=values(id_suboutput), id_komponen=values(id_komponen), nama_ppk=values(nama_ppk), nomor_renja=values(nomor_renja)";
            $stmt = mysqli_prepare($connection, $sql);

            mysqli_stmt_bind_param($stmt, "isisssissiiiiiisssssssissiiiiisi", $val->tanggal_terakhir_di_update, $val->kode_kldi, $val->id_satker, $val->kode_satker_asli, $val->jenis, $val->kldi, $val->kode_rup, $val->nama_satker,
              $val->nama_paket, $val->program, $val->kegiatan, $val->output, $val->suboutput, $val->komponen, $val->pagu_rup, $val->mak,
              $val->lokasi, $val->detail_lokasi, $val->sumber_dana, $val->awal_pekerjaan, $val->akhir_pekerjaan, $val->nama_kpa, $val->tipe_swakelola, $val->status_aktif,
              $val->status_umumkan, $val->id_program, $val->id_kegiatan, $val->id_output, $val->suboutput, $val->id_komponen, $val->nama_ppk, $val->nomor_renja);
            mysqli_stmt_execute($stmt);
        }

      }

      if($h == '02'){

      }

      if($h == '03'){

      }
	}

}
