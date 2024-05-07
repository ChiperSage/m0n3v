<?php
class Skt_m extends CI_Model {

	public function __construct(){

	}

	public function input_mask($var)
	{
		$var1 = substr($var,0,2);
		$var2 = substr($var,2,3);
		$var3 = substr($var,5,3);
		$var4 = substr($var,8,1);
		$var5 = substr($var,9,3);
		$var6 = substr($var,12,3);

		return $var1.'.'.$var2.'.'.$var3.'.'.$var4.'-'.$var5.'.'.$var6;
	}

	public function cari_perusahaan()
	{
		$npwp = $this->input->post('cari_npwp');
		$filter = array('npwp'=>$npwp);
		$result = $this->db->get_where('tb_skt', $filter)->num_rows();
		if($result == 1){
			$sess_data = array('sess_npwp'=>$npwp);
		}else{
			$sess_data = array('sess_npwp'=>0);
		}
		$this->session->set_userdata($sess_data);
	}

	public function get()
	{
		$filter = array('tanggal_akhir <'=>date('Y-m-d H:i:s'));
		$this->db->delete('tb_skt',$filter);

		$this->db->select('a.*,b.nama_perusahaan,c.nama_paket');
		$this->db->from('tb_skt a');
		$this->db->join('tb_perusahaan b','a.npwp = b.npwp','left');
		$this->db->join('tb_rup c','a.kode_rup = c.kode_rup','left');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_skt', $key);
		return $query->row();
	}

	public function perusahaan_list()
	{
		return $this->db->get_where('tb_perusahaan',array())->result();
	}

	public function get_satker_list()
	{
		$this->db->select('id_satker,nama_satker');
		$this->db->from('tb_rup');
		$this->db->group_by('id_satker');
		$this->db->order_by('nama_satker ASC');
		return $this->db->get()->result();
	}

	public function create()
	{
		$tgl1 = $this->input->post('tanggal_skt');
		$tgl2 = date('Y-m-d', strtotime('+3 years', strtotime($tgl1))); //operasi penjumlahan tanggal sebanyak 6 hari

		if($this->is_limit(0) == false && $this->is_limit_npwp(0) == false)
		{
			$data['no_registrasi'] = $this->input->post('no_registrasi');
			$data['npwp'] = $this->input->post('npwp');
			$data['npwp_pribadi'] = $this->input_mask($this->input->post('npwp_pribadi'));
			$data['nama'] = $this->input->post('nama');
			$data['kode_rup'] = $this->input->post('kode_rup');
			$data['jenis'] = $this->input->post('jenis');
			$data['tanggal_mulai'] = $this->input->post('tanggal_mulai');
			$data['tanggal_akhir'] = $this->input->post('tanggal_akhir');
			$data['date_added'] = $this->input->post('tanggal_skt');
			$data['date_expired'] = $tgl2;
			$data['keterangan'] = $this->input->post('keterangan');
			$this->db->insert('tb_skt', $data);

			$data['nip'] = $this->get_cur_nip();
			$data['tanggal'] = date('Y-m-d H:i:s');
			$data['aksi'] = 'insert';
			$this->db->insert('temp_skt', $data);

			$result = $this->db->affected_rows();
			if($result = 1)
			{
				$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
			}
		}
	}

	public function update($id)
	{
		$tgl1 = $this->input->post('tanggal_skt');
		$tgl2 = date('Y-m-d', strtotime('+3 years', strtotime($tgl1))); //operasi penjumlahan tanggal sebanyak 6 hari

		// if($this->is_limit($id) == false){

		$key = array('id'=>$id);

		$data['no_registrasi'] = $this->input->post('no_registrasi');
		$data['npwp'] = $this->input->post('npwp');
		$data['npwp_pribadi'] = $this->input_mask($this->input->post('npwp_pribadi'));
		$data['nama'] = $this->input->post('nama');
		if( $this->input->post('kode_rup') != ''){
			$data['kode_rup'] = $this->input->post('kode_rup');
		}
		$data['jenis'] = $this->input->post('jenis');
		$data['tanggal_mulai'] = $this->input->post('tanggal_mulai');
		$data['tanggal_akhir'] = $this->input->post('tanggal_akhir');
		$data['date_added'] = $this->input->post('tanggal_skt');
		$data['date_expired'] = $tgl2;
		$data['keterangan'] = $this->input->post('keterangan');
		$this->db->update('tb_skt', $data, $key);

		$data['nip'] = $this->get_cur_nip();
		$data['tanggal'] = date('Y-m-d H:i:s');
		$data['aksi'] = 'update';
		$this->db->insert('temp_skt', $data);

		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}

		// }
	}

	public function delete($id)
	{
		$filter = array('id'=>$id);
		$this->db->delete('tb_skt',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function is_limit_npwp($id)
	{
		$npwp_pribadi = $this->input_mask($this->input->post('npwp_pribadi'));
		$jenis = $this->input->post('jenis');

		if($jenis == 'non lumsum'){

			$filter = array('npwp_pribadi'=>$npwp_pribadi);
			$result = $this->db->get_where('tb_skt',$filter)->num_rows();
			if($result >= 1){
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Telah melebihi batas (Non Lumsum)</div>');
				return true;
			}else{
				return false;
			}

		}elseif($jenis == 'lumsum'){

			$filter = array('npwp_pribadi'=>$npwp_pribadi,'jenis'=>$jenis);
			$result = $this->db->get_where('tb_skt',$filter)->num_rows();
			if($result >= 3){
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Telah melebihi batas (Lumsum)</div>');
				return true;
			}else{
				return false;
			}

		}elseif($jenis == 'perencanaan'){
			$date1 = $this->input->post('tanggal_mulai');
			$date2 = $this->input->post('tanggal_akhir');

			$filter = "SELECT id FROM tb_skt WHERE npwp_pribadi = '$npwp_pribadi' AND jenis = '$jenis' AND id != '$id'";
			$result1 = $this->db->query($filter)->num_rows();

			$filter = "SELECT id FROM tb_skt WHERE npwp_pribadi = '$npwp_pribadi' AND jenis = '$jenis' AND id != '$id' AND (tanggal_akhir > '$date1' OR tanggal_akhir > '$date2')";
			$result2 = $this->db->query($filter)->num_rows();

			if($result1 == 0){
				return false;
			}elseif($result1 != 0 && $result2 == 0){
				return false;
			}else{
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Data telah ada (Perencanaan).</div>');
				return true;
			}

		}

	}

	public function is_limit($id)
	{
		$no_registrasi = $this->input->post('no_registrasi');
		$jenis = $this->input->post('jenis');

		if($jenis == 'non lumsum'){

			$filter = array('no_registrasi'=>$no_registrasi);
			$result = $this->db->get_where('tb_skt',$filter)->num_rows();
			if($result >= 1){
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Telah melebihi batas (Non Lumsum)</div>');
				return true;
			}else{
				return false;
			}

		}elseif($jenis == 'lumsum'){

			$filter = array('no_registrasi'=>$no_registrasi,'jenis'=>$jenis);
			$result = $this->db->get_where('tb_skt',$filter)->num_rows();
			if($result >= 3){
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Telah melebihi batas (Lumsum)</div>');
				return true;
			}else{
				return false;
			}

		}elseif($jenis == 'perencanaan'){
			$date1 = $this->input->post('tanggal_mulai');
			$date2 = $this->input->post('tanggal_akhir');

			// $filter = array('no_registrasi'=>$no_registrasi,'jenis'=>$jenis);
			// $result1 = $this->db->get_where('tb_skt',$filter)->num_rows();

			// $filter = array('no_registrasi'=>$no_registrasi,'jenis'=>$jenis,'tanggal_akhir >'=>$date1,'tanggal_akhir >'=>$date2);
			// $result2 = $this->db->get_where('tb_skt',$filter)->num_rows();

			$filter = "SELECT id FROM tb_skt WHERE no_registrasi = '$no_registrasi' AND jenis = '$jenis' AND id != '$id'";
			$result1 = $this->db->query($filter)->num_rows();

			$filter = "SELECT id FROM tb_skt WHERE no_registrasi = '$no_registrasi' AND jenis = '$jenis' AND id != '$id' AND (tanggal_akhir > '$date1' OR tanggal_akhir > '$date2')";
			$result2 = $this->db->query($filter)->num_rows();

			if($result1 == 0){
				// $this->session->set_flashdata('msg','<div class="callout callout-warning">'.$result1.'</div>');
				return false;
			}elseif($result1 != 0 && $result2 == 0){
				// $this->session->set_flashdata('msg','<div class="callout callout-warning">'.$result1.' '.$result2.'</div>');
				return false;
			}else{
				$this->session->set_flashdata('msg','<div class="callout callout-warning">Data telah ada (Perencanaan).</div>');
				return true;
			}

		}

	}

	public function get_cur_nip()
	{
		$id = $this->session->userdata('user_id');
		$user = $this->db->get_where('users',array('id'=>$id))->row();
		if($user->nip != ''){
			return $user->nip;
		}else{
			return $user->id;
		}
	}
}
