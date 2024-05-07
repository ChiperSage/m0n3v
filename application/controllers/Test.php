<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

  public function index()
  {        
        $filename = 'https://isb.lkpp.go.id/isb-2/api/44077f5a-2b19-40b0-904c-4d229d4c0397/json/2542/SPSE-TenderSelesaiEkontrak/tipe/4:4/parameter/2023:106';
        $year = 2023;

        $test = file_get_contents($filename);
        $data = json_decode($test);

        $id_list = implode(",", array_map(function ($val) { return (int) $val->kode_lelang; }, $data));

        $connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

        mysqli_query($connection, "DELETE FROM tb_kon WHERE kd_tender NOT IN ($id_list) AND tahun_anggaran = '$year'");

        foreach ($data as $val)
        {
            $sql = "INSERT INTO tb_kon SET tahun_anggaran=?, kd_lpse=?, kd_tender=?, no_kontrak=?, tgl_kontrak=?, nilai_kontrak=?, kontrak_tipe_penyedia=?, wakil_sah_penyedia_kontrak=?, jabatan_wakil_penyedia_kontrak=?, no_spmk=?, tgl_spmk=?, waktu_penyelesaian_spmk=?, tgl_mulai_kerja_spmk=?, tgl_selesai_kerja_spmk=?, no_bast=?, tgl_bast=?, no_bap=?, tgl_bap=?, besar_pembayaran_bap=?, progres_fisik_bap=?, kd_penyedia=?, nama_penyedia=?, npwp_penyedia=?

            ON DUPLICATE KEY UPDATE
            
            tahun_anggaran=values(tahun_anggaran), kd_lpse=values(kd_lpse), kd_tender=values(kd_tender), no_kontrak=values(no_kontrak), tgl_kontrak=values(tgl_kontrak), nilai_kontrak=values(nilai_kontrak), kontrak_tipe_penyedia=values(kontrak_tipe_penyedia), wakil_sah_penyedia_kontrak=values(wakil_sah_penyedia_kontrak), jabatan_wakil_penyedia_kontrak=values(jabatan_wakil_penyedia_kontrak), no_spmk=values(no_spmk), tgl_spmk=values(tgl_spmk), waktu_penyelesaian_spmk=values(waktu_penyelesaian_spmk), tgl_mulai_kerja_spmk=values(tgl_mulai_kerja_spmk), tgl_selesai_kerja_spmk=values(tgl_selesai_kerja_spmk), no_bast=values(no_bast), tgl_bast=values(tgl_bast), no_bap=values(no_bap), tgl_bap=values(tgl_bap), besar_pembayaran_bap=values(besar_pembayaran_bap), progres_fisik_bap=values(progres_fisik_bap), kd_penyedia=values(kd_penyedia), nama_penyedia=values(nama_penyedia), npwp_penyedia=values(npwp_penyedia)";

            $stmt = mysqli_prepare($connection, $sql);

            $blank = '';

            mysqli_stmt_bind_param($stmt, "iiississssssssssssiiiss", $val->tahun_anggaran, $val->kd_lpse, $val->kd_tender, $blank, $val->tgl_kontrak, $val->nilai_kontrak, $val->kontrak_tipe_penyedia, $val->wakil_sah_penyedia_kontrak, $val->jabatan_wakil_penyedia_kontrak, $blank, $val->tgl_spmk, $val->waktu_penyelesaian_spmk, $val->tgl_mulai_kerja_spmk, $val->tgl_selesai_kerja_spmk, $blank, $val->tgl_bast, $val->no_bap, $val->tgl_bap, $val->besar_pembayaran_bap, $val->progres_fisik_bap, $val->kd_penyedia, $val->nama_penyedia, $val->npwp_penyedia);

            mysqli_stmt_execute($stmt);
        }
  }

  public function validasi_in_array()
  {

    $allfileuploaded = array();

    $allfileuploaded[] = '';
    $allfileuploaded[] = 'bcd';
    $allfileuploaded[] = 'bcd';
    $allfileuploaded[] = '123';

    if(!in_array('', $allfileuploaded))
    {
      $this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
    }else{
      $this->session->set_flashdata('msg','<div class="callout callout-success">Gagal</div>');
    }

    echo $this->session->flashdata('msg');

    // echo '<embed src="http://123.108.97.215/bpbj/test/qrcode" width="100%"height="100%"></embed>';
  }

  public function datetime()
  {
    $this->load->view('datetime');
  }

  public function qrcode()
  {
    // echo '<img src="'.$this->_generate_qrcode().'">';

    $this->load->library('ciqrcode');

    header("Content-Type: image/png");
    $params['data'] = 'This is a text to encode become QR Code';
    $params['level'] = 'H';
    // $params['savename'] = FCPATH.'qrcode.png';
    $this->ciqrcode->generate($params);
  }

  public function _generate_qrcode()
  {
    $this->load->library('ciqrcode');

    header("Content-Type: image/png");
    $params['data'] = 'This is a text to encode become QR Code';
    $params['savename'] = FCPATH.'files/tes.png';
    $this->ciqrcode->generate($params);
  }

}
