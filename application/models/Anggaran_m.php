<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Anggaran_m extends CI_Model{

    function __construct(){

    }

    public function get_data()
    {
        $tahun = date('Y');
        if(isset($_GET['tahun'])){
            $tahun = $_GET['tahun'];
        }
        return $this->db->get_where('tb_struktur_anggaran', array('tahun_anggaran'=>$tahun))->result();
    }

}