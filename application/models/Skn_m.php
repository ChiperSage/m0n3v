<?php
class Skn_m extends CI_Model {

	public function __construct(){

	}

	public function get()
	{
		$this->cek_akhir_pekerjaan();
		// $filter = array();

		$this->db->select('a.*,b.nama_paket as jnama_paket, c.ekuitas, c.nama_perusahaan');
		$this->db->from('tb_skn a');
		$this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
		$this->db->join('tb_perusahaan c','a.npwp = c.npwp','left');
		// $this->db->where($filter);
		$this->db->group_by('a.id');
		$this->db->order_by('a.id ASC');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail($id)
	{
		$this->db->select('a.*,b.nama_perusahaan');
		$this->db->from('tb_skn a');
		$this->db->join('tb_perusahaan b','a.npwp = b.npwp','left');
		$this->db->where(array('a.id'=>$id));
		$query = $this->db->get();
		return $query->row();
	}

	public function detail_perusahaan()
	{
		if($this->session->userdata('company_logged') == true){
			$npwp = $this->session->userdata('company_npwp');
		}else{
			$npwp = '';
		}
		$query = $this->db->get_where('tb_perusahaan', array('npwp'=>$npwp));
		return $query->row();
	}

	public function get_perusahaan_list()
	{
		$this->db->select('*');
		$this->db->from('tb_perusahaan');
		$this->db->where(array('jenis_pengadaan'=>'konstruksi'));
		$this->db->or_where(array('jenis_pengadaan'=>'barang jasa'));
		$this->db->where(array('kualifikasi'=>'non kecil'));
		$this->db->order_by('nama_perusahaan ASC');
		return $this->db->get()->result();
	}

	public function get_satker_list()
	{
		$this->db->select('id_satker,nama_satker');
		$this->db->from('tb_rup');
		$this->db->group_by('id_satker');
		$this->db->order_by('nama_satker ASC');
		return $this->db->get()->result();
	}

	public function get_paket_list()
	{
		// $str = "SELECT * FROM tb_rup
		// WHERE (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		// AND left(akhir_pekerjaan,4) = '2019' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		// AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD')
		// AND kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
		// AND kode_rup NOT IN (SELECT batal_paket FROM tb_batal)";

		$str = "SELECT * FROM tb_rup
		WHERE kode_rup NOT IN (SELECT kode_rup FROM tb_skn)
		AND (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
		AND left(akhir_pekerjaan,4) = '2019' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
		AND (sumber_dana = 'APBD' OR sumber_dana = 'BLUD')";
		$query = $this->db->query($str);
		return $query->result();
	}

	public function insert()
	{
		if($this->check_limit() == true)
		{
				$data['npwp'] = $this->input->post('npwp');
				if($this->input->post('kode_rup') != ''){
					$data['kode_rup'] = $this->input->post('kode_rup');
				}
				$data['nama_paket'] = $this->input->post('nama_paket');
				$data['lokasi'] = $this->input->post('lokasi');
				$data['nilai_paket'] = $this->input->post('nilai_paket');
				$data['nilai_progres'] = $this->input->post('nilai_progres');
				$data['total'] = $this->input->post('nilai_paket') - $this->input->post('nilai_progres');
				$data['skn'] = $this->hitung_skn(0);
				$data['awal_pekerjaan'] = $this->input->post('awal_pekerjaan');
				$data['akhir_pekerjaan'] = $this->input->post('akhir_pekerjaan');
				$this->db->insert('tb_skn', $data);

				$data['nip'] = $this->get_cur_nip();
				$data['tanggal'] = date('Y-m-d H:i:s');
				$data['aksi'] = 'insert';
				$this->db->insert('temp_skn', $data);

				$result = $this->db->affected_rows();
				if($result = 1)
				{
					$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengupdate data</div>');
				}
		}
	}

	public function update($id)
	{
			$key = array('id'=>$id);
			if($this->input->post('npwp') !== null){
	    $data['npwp'] = $this->input->post('npwp');
			}
			if( $this->input->post('kode_rup') != ''){
				$data['kode_rup'] = $this->input->post('kode_rup');
			}
			$data['nama_paket'] = $this->input->post('nama_paket');
			$data['lokasi'] = $this->input->post('lokasi');
			$data['nilai_paket'] = $this->input->post('nilai_paket');
			$data['nilai_progres'] = $this->input->post('nilai_progres');
			$data['total'] = $this->input->post('nilai_paket') - $this->input->post('nilai_progres');
			$data['skn'] = $this->hitung_skn($id);
			$data['awal_pekerjaan'] = $this->input->post('awal_pekerjaan');
			$data['akhir_pekerjaan'] = $this->input->post('akhir_pekerjaan');
			$this->db->update('tb_skn', $data, $key);

			$data['nip'] = $this->get_cur_nip();
			$data['tanggal'] = date('Y-m-d H:i:s');
			$data['aksi'] = 'update';
			$this->db->insert('temp_skn', $data);

			$result = $this->db->affected_rows();
			if($result = 1)
			{
				$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengupdate data</div>');
			}
	}

	public function hitung_skn($id)
	{
		$npwp = $this->input->post('npwp');
		$perusahaan = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp))->row();

		if($id > 0){
			$skn = $this->db->get_where('tb_skn',array('id'=>$id))->row();
			$npwp = $skn->npwp;
			$perusahaan = $this->db->get_where('tb_perusahaan',array('npwp'=>$npwp))->row();
		}

		// hitung total
		if($id == 0){
			$str = "SELECT SUM(total) as vtotal FROM tb_skn WHERE npwp = '$npwp'";
		}else{
			$str = "SELECT SUM(total) as vtotal FROM tb_skn WHERE npwp = '$npwp' AND id != $id";
		}
		$temp = $this->db->query($str)->row();
		$total =  $temp->vtotal + ($this->input->post('nilai_paket') - $this->input->post('nilai_progres'));

		// cek perusahaan baru
		$cek_npwp = $this->input->post('npwp');
		$cek = $this->db->get_where('tb_skn',array('npwp'=>$cek_npwp))->num_rows();
		if($cek == 0){
			$skn = (($perusahaan->ekuitas * 0.6) * 7);
		}else{
			$skn = ((($perusahaan->ekuitas * 0.6) * 7) - $total);
		}

		return $skn;
	}

	function cek_akhir_pekerjaan()
	{
		$cur_date = date('Y-m-d');
		$data['total'] = 0;
		$this->db->update('tb_skn',$data,array('akhir_pekerjaan <'=>$cur_date));
	}

	public function check_limit()
	{
		$npwp = $this->input->post('npwp');
		$limit = $this->db->get_where('tb_skn',array('npwp'=>$npwp))->num_rows();
		if($limit < 6)
		{
			return true;
		}else{
			$this->session->set_flashdata('msg','<div class="callout callout-warning">Telah mencapai batas yg ditentukan.</div>');
			return false;
		}
	}

	public function delete($id)
	{
		$this->db->delete('tb_skn',array('id'=>$id));
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menghapus data</div>');
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
