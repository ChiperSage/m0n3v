<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sanggah_m extends CI_Model{

    function __construct(){

    }

    public function get()
    {
    	$sql = "SELECT s.sgh_id, s.lls_id as kode_lelang, l.nama_paket, l.stk_nama, s.rkn_nama, s.sgh_tanggal, k.kgr_nama

        -- (SELECT COUNT(sb.lls_id) FROM sanggah_banding sb WHERE sb.lls_id = s.lls_id) as lls_id,

        -- (SELECT sb.sgh_waktu_mulai FROM sanggah_banding sb WHERE sb.lls_id = s.lls_id) as sgh_waktu_mulai

        FROM tb_sanggah s, tb_lelang_spse l, tb_kategori k
        WHERE s.lls_id = l.kode_lelang AND l.jenis_pengadaan = k.kgr_id
        ORDER BY s.lls_id DESC";
        return $this->db->query($sql)->result();
    }

    public function get2()
    {
        $tahun = date('Y');
        if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
          $tahun = $_GET['tahun'];
        }

        $sql = "SELECT s.sgh_id, s.lls_id as kode_lelang, l.nama_paket, l.stk_nama, s.rkn_nama, s.sgh_tanggal, k.kgr_nama

        -- (SELECT COUNT(sb.lls_id) FROM sanggah_banding sb WHERE sb.lls_id = s.lls_id) as lls_id,

        -- (SELECT sb.sgh_waktu_mulai FROM sanggah_banding sb WHERE sb.lls_id = s.lls_id) as sgh_waktu_mulai

        FROM tb_sanggah s

        LEFT JOIN tb_lelang_spse l ON s.lls_id = l.kode_lelang
        LEFT JOIN tb_kategori k ON l.jenis_pengadaan = k.kgr_id

        WHERE left(s.sgh_tanggal,4) = $tahun AND l.nama_paket IS NOT NULL

        ORDER BY s.sgh_tanggal DESC";
        return $this->db->query($sql)->result();
    }

}
