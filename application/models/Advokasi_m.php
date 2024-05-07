<?php
class Advokasi_m extends CI_Model {

  private $key;
  private $tb;

	public function __construct(){
    $tb = '';
    $key = '';
	}

	public function get($key)
	{
		$this->db->select('a.*,b.nama_paket,c.uraian');
    	$this->db->from('tb_advokasi a');
		$this->db->join('tb_lelang b','a.kode_rup = b.kode_rup','left');
		$this->db->join('tb_uraianadvokasi c','a.id_uraian = c.id','left');
		$this->db->where($key);
		$query = $this->db->get();
		return $query->result();
	}

  	public function get_paket($tahun)
	{		
    	$key = array('status_lelang'=>1,'menang'=>5,'tahun'=>$tahun);
		$this->db->select('*');
    	$this->db->from('tb_lelang');
		$this->db->where($key);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_uraian()
	{
		$this->db->select('*');
    	$this->db->from('tb_uraianadvokasi');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_detail_uraian($id)
	{
		$this->db->select('*');
    	$this->db->from('tb_uraianadvokasi');
    	$this->db->where(array('id'=>$id));
		$query = $this->db->get();
		return $query->row();
	}

	public function get_detail($key)
	{
		$query = $this->db->get_where('tb_advokasi', $key);
		return $query->row();
	}

	public function datalembagac()
	{
		$data['uraian'] = $this->input->post('uraian');
		$data['keterangan'] = $this->input->post('keterangan');

		$this->db->insert('tb_uraianadvokasi', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function datalembagau($id)
	{
		$data['uraian'] = $this->input->post('uraian');
		$data['keterangan'] = $this->input->post('keterangan');

		$this->db->update('tb_uraianadvokasi', $data, array('id'=>$id));
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function create()
	{
		$data['nomor_surat'] = $this->input->post('nomor');
		$data['tanggal'] = $this->input->post('tanggal');
		$data['tahun_paket'] = $this->input->post('tahun');
		$data['id_uraian'] = $this->input->post('id_uraian');
    	$data['alamat'] = $this->input->post('alamat');
		$data['kode_rup'] = $this->input->post('paket');
		$data['materi'] = $this->input->post('materi');
		$data['tahap'] = $this->input->post('tahap');
		$data['rekomendasi'] = $this->input->post('rekomendasi');
		$data['keterangan'] = $this->input->post('keterangan');

		$this->db->insert('tb_advokasi', $data);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function update($id)
	{
		$key = array('advokasi_id' => $id);
    	$data['nomor_surat'] = $this->input->post('nomor');
			$data['tanggal'] = $this->input->post('tanggal');
			$data['tahun_paket'] = $this->input->post('tahun');
			$data['id_uraian'] = $this->input->post('id_uraian');
    	$data['alamat'] = $this->input->post('alamat');
			$data['kode_rup'] = $this->input->post('paket');
			$data['materi'] = $this->input->post('materi');
			$data['tahap'] = $this->input->post('tahap');
			$data['rekomendasi'] = $this->input->post('rekomendasi');
			$data['keterangan'] = $this->input->post('keterangan');

		$this->db->update('tb_advokasi', $data, $key);
		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$filter = array('advokasi_id'=>$id);
		$this->db->delete('tb_advokasi',$filter);
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function get_database_vendor()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT pe.rkn_id, pe.rkn_nama, pe.rkn_alamat, pe.rkn_npwp, pe.rkn_telepon, pe.rkn_mobile_phone, pe.rkn_email, s.pgl_nilai, pg.pgr_nama, pg.pgr_jabatan, pg.pgr_alamat, pg.pgr_ktp,
			JSON_EXTRACT(p.data_sikap, '$.peralatan[*]') AS peralatan, 
			JSON_EXTRACT(p.data_sikap, '$.tenagaAhli[*]') AS tenaga_ahli 
			FROM tb_peralatan p, tb_sumber_daya s, tb_pemenang pe, tb_pengurus pg, tb_lelang_spse l
			WHERE pe.rkn_id = s.rkn_id AND pe.rkn_id = p.rkn_id AND pe.rkn_id = pg.rkn_id AND pg.pgr_jabatan LIKE '%direktur%' AND pe.kode_lelang = l.kode_lelang AND l.menang = 5 AND l.status_lelang = 1 AND l.paket_status = 1 AND l.ang_tahun = $tahun
			GROUP BY pe.rkn_id";

		return $this->db->query($str)->result();

	}

	public function get_data_pengurus()
	{
		$str = "SELECT * FROM tb_pengurus";
		return $this->db->query($str)->result();

	}
}
