<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settpd_m extends CI_Model{

    function __construct(){

    }

    public function get()
    {
    	$this->db->limit(1);
    	return $this->db->get('tb_settpd')->row();
    }

    public function get_all()
    {
    	return $this->db->get('tb_settpd')->result();
    }

    public function get_detail($id)
    {
    	$this->db->where(array('id'=>$id));
    	return $this->db->get('tb_settpd')->row();
    }

    public function update($id)
    {
        $key = array('id'=>$id);
        $data['set'] = $this->input->post('set');
        $data['keterangan'] = $this->input->post('keterangan');
        $this->db->update('tb_settpd',$data,$key);
    }

}
