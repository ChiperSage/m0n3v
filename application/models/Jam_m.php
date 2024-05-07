<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Jam_m extends CI_Model{

    function __construct(){

    }

    public function get()
    {
    	$this->db->limit(1);
    	return $this->db->get('tb_jam_antrian')->row();
    }

    public function get_all()
    {
    	return $this->db->get('tb_jam_antrian')->result();
    }

    public function get_detail($id)
    {
    	$this->db->where(array('id_jam'=>$id));
    	return $this->db->get('tb_jam_antrian')->row();
    }

    public function update($id)
    {
        $key = array('id_jam'=>$id);
        $data['jam_awal'] = $this->input->post('jam_awal');
        $data['jam_akhir'] = $this->input->post('jam_akhir');
        $data['keterangan'] = $this->input->post('keterangan');
        $this->db->update('tb_jam_antrian',$data,$key);
    }

}
