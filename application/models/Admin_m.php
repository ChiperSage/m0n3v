<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_m extends CI_Model{

    function __construct(){

    }

    public function count($tb_name, $filter)
    {
        return $this->db->get_where($tb_name,$filter)->num_rows();
    }

    public function get_rekap()
    {
        $tahun = date('Y');

        $sql_tpd = "SELECT count(t.kode_rup) as total 
            FROM tb_tpd t
            WHERE t.tpd_status = 8 AND left(t.tanggal_terima_dok,4) = $tahun";

        $sql_tayang = "SELECT COUNT(DISTINCT l.kode_lelang) as total FROM tb_lelang_spse l
            WHERE l.ang_tahun = $tahun AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 0";

        $sql_menang = "SELECT COUNT(DISTINCT l.kode_lelang) as total FROM tb_lelang_spse l
            WHERE l.ang_tahun = $tahun AND l.status_lelang = 1 AND l.paket_status = 1 AND l.menang = 5";

        $sql_lhp = "SELECT COUNT(DISTINCT lh.kode_lelang) as total FROM tb_lhp lh, tb_lelang_spse ls
            WHERE lh.kode_lelang = ls.kode_lelang AND ls.ang_tahun = $tahun";

        $data['tpd'] = $this->db->query($sql_tpd)->row();
        $data['tayang'] = $this->db->query($sql_tayang)->row();
        $data['menang'] = $this->db->query($sql_menang)->row();
        $data['lhp'] = $this->db->query($sql_lhp)->row();
        return $data;
    }

    public function get_laporan()
    {
      $str = "SELECT * FROM tb_skpa";
      return $this->db->query($str)->result();
    }
}
