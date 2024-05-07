<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sp_m extends CI_Model{

    function __construct(){

    }

    public function get()
  	{
      $tahun = date('Y');
      if(isset($_GET['tahun'])){
          $tahun = $_GET['tahun'];
      }

  		$this->db->select('a.*,
        (SELECT pokja_nama FROM tb_sp_anggota d
        LEFT JOIN tb_pokja e ON d.anggota_nip = e.pokja_nip
        WHERE d.anggota_sp = a.sp_id AND anggota_jabatan = "ketua" LIMIT 1) as ketua_pokja,
        (SELECT COUNT(b.id) FROM tb_sp_anggota b WHERE a.sp_id = b.anggota_sp) as tanggota,
        (SELECT COUNT(c.id) FROM tb_sp_paket c WHERE a.sp_id = c.paket_sp) as tpaket,
        (SELECT COUNT(b.id) FROM tb_sp_anggota b WHERE a.sp_id = b.anggota_sp AND b.anggota_keterangan != "-") as tketerangan');
      $this->db->from('tb_sp a');
      $this->db->join('tb_sp_paket c','a.sp_id = c.paket_sp','left');
      $this->db->join('tb_paket d','c.paket_id = d.paket_id','left');
      $this->db->where(array('a.tahun'=>$tahun));
      // $this->db->where($filter1)->or_where($filter2);
      $this->db->group_by('a.sp_nomor');
  		$query = $this->db->get();
  		return $query->result();
  	}

    public function get_karo($tanggal_sp)
    {
      $str = "SELECT * FROM users WHERE
      ('$tanggal_sp' >= tanggal_awal  AND '$tanggal_sp' <= tanggal_akhir) LIMIT 1";
      return $this->db->query($str)->row();
    }

  	public function get_detail($id)
  	{
      $key = array('sp_id'=>$id);
  		return $this->db->get_where('tb_sp', $key)->row();
  	}

  	public function create()
  	{
  		$data['sp_nomor'] = $this->input->post('nomor');
      $data['sp_tanggal'] = $this->input->post('tanggal');
      $data['sp_kelompok'] = $this->input->post('kelompok');
      $data['sp_log'] = $this->session->userdata('user_id');
      $data['tahun'] = $this->input->post('tahun');

  		$this->db->insert('tb_sp', $data);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

  	public function update($id)
  	{
  		$key = array('sp_id' => $id);
      $data['sp_nomor'] = $this->input->post('nomor');
      $data['sp_tanggal'] = $this->input->post('tanggal');
      $data['sp_kelompok'] = $this->input->post('kelompok');
      $data['tahun'] = $this->input->post('tahun');

  		$this->db->update('tb_sp', $data, $key);
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

    public function delete($id)
  	{
  		$filter = array('sp_id'=>$id);
  		$this->db->delete('tb_sp',$filter);
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
  		}
  	}

    // list anggota sp
    public function get_anggota($sp)
    {
      $this->db->select('*');
      $this->db->from('tb_sp_anggota a');
      $this->db->where(array('anggota_sp'=>$sp));
      $this->db->join('tb_sp b','a.anggota_sp = b.sp_id');
      $this->db->join('tb_pokja c','a.anggota_nip = c.pokja_nip');
      $this->db->group_by('a.id');
      $this->db->order_by('a.id ASC');
      $query = $this->db->get();
      return $query->result();
    }

    public function get_anggota_detail($id)
    {
      $this->db->select('*');
      $this->db->from('tb_sp_anggota');
      $this->db->where(array('id'=>$id));
      $query = $this->db->get();
      return $query->row();
    }

    public function update_anggota($sp, $id)
    {
      $key = array('id'=>$id,'anggota_sp'=>$sp);
      $data['anggota_sp'] = $sp;
      $data['sp_id'] = $sp;
      $data['anggota_nip'] = $this->input->post('nip');
      $data['anggota_jabatan'] = $this->input->post('jabatan');
      $data['anggota_keterangan'] = '-'; //($this->input->post('keterangan') == '') ? '-' : $this->input->post('keterangan');
      if($id == 0 && $this->anggota_exist($sp, $this->input->post('nip')) == 0){
          $this->db->insert('tb_sp_anggota', $data);
      }else{
          $this->db->update('tb_sp_anggota', $data, $key);
      }
      $result = $this->db->affected_rows();
      if($result >= 1)
      {
        $this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengubah data anggota</div>');
      }
    }

    public function delete_anggota($id)
    {
      $filter = array('id'=>$id);
  		$this->db->delete('tb_sp_anggota',$filter);
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menghapus data anggota</div>');
  		}
    }

    public function get_list_paket_pokja()
    {
      $tahun = date('Y') - 1;
      $tahun_next = date('Y') + 1;

      $this->db->select('a.*, b.kode_rup, b.nama_paket, c.sp_kelompok, d.anggota_nip, d.anggota_jabatan, e.pokja_nama,
      c.sp_status, (SELECT hapus FROM tb_hapus WHERE nip = d.anggota_nip AND kode_rup = a.paket_id) as hapus,
      (SELECT COUNT(id) FROM tb_sp_anggota WHERE anggota_sp = c.sp_id) as tpokja,
      (SELECT SUM(hapus) FROM tb_hapus WHERE kode_rup = a.paket_id) as tsetuju');

      $this->db->from('tb_sp_paket a');
      $this->db->join('tb_rup b','a.paket_id = b.kode_rup','left');
      $this->db->join('tb_sp c','a.paket_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      // $this->db->where(array('left(c.sp_tanggal,4)'=>$tahun));
      $this->db->where("LEFT(c.sp_tanggal,4) >= $tahun");
      $this->db->group_by('a.id');
      $this->db->order_by('a.id DESC');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function batal_paket_pokja()
    {
      // pakai kode rup
      $id = $this->input->post('id');

      $sp_paket = $this->db->get_where('tb_sp_paket',array('paket_id'=>$id))->row();
      $sp = $sp_paket->paket_sp;
      $paket = $sp_paket->paket_id;

      // tambah ke tabel batal
      $data['batal_sp'] = $sp;
      $data['batal_paket'] = $paket;
      $data['batal_nip'] = 'admin_karo';
      $data['batal_keterangan'] = $this->input->post('keterangan');
      $this->db->insert('tb_batal',$data);

      // tambah ke temp
      $keterangan2 = $this->input->post('keterangan');

      $str = "INSERT INTO tb_sp_paket_temp (sp_id,kode_rup,paket_keterangan,paket_tanggal,paket_status,paket_log,nt,paket_sp,paket_id,keterangan,keterangan2)
      SELECT sp.sp_id,sp.kode_rup,sp.paket_keterangan,sp.paket_tanggal,sp.paket_status,sp.paket_log,sp.nt,sp.paket_sp,sp.paket_id, 'sp batal' as keterangan, '$keterangan2' 
      FROM tb_sp_paket sp
      WHERE sp.kode_rup = '$id'";
      $this->db->query($str);

      $key1['kode_rup'] = $paket;
      $data1['catatan'] = 'batal sp';
      $this->db->update('tb_review',$data1,$key1);

      // delete from sp_paket
      $this->db->delete('tb_review',array('kode_rup'=>$id));
      $this->db->delete('tb_sp_paket',array('paket_id'=>$id));
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Paket telah dibatalkan</div>');
  		}
  	}

    public function get_paket($sp)
    {
      $this->db->select('a.*, coalesce(c.nama_paket,a.namapaket) as nama_paket, b.sp_kelompok, b.sp_status, c.kode_rup, c.jenis_pengadaan, d.singkatan, coalesce(e.hps,f.hps) as hps, g.nilai_hps');
      $this->db->from('tb_sp_paket a');
      $this->db->where(array('a.paket_sp'=>$sp));
      $this->db->join('tb_sp b','a.paket_sp = b.sp_id','left');
      $this->db->join('tb_rup c','a.paket_id = c.kode_rup','left');
      $this->db->join('tb_skpa d','c.id_satker = d.kode','left');
      // $this->db->join('tb_lelang_spse e','a.paket_id = e.kode_rup','left');
      $this->db->join('tb_lelang e','a.paket_id = e.kode_rup','left');
      $this->db->join('tb_non_tender f','a.paket_id = f.kode_rup','left');
      $this->db->join('tb_tpd g','a.paket_id = g.kode_rup','left');
      $this->db->group_by('a.id');
      $this->db->order_by('a.paket_tanggal ASC');
      $query = $this->db->get();
      return $query->result();
    }

    public function get_paket_detail($id)
    {
      $this->db->select('*');
      $this->db->from('tb_sp_paket');
      $this->db->where(array('id'=>$id));
      $query = $this->db->get();
      return $query->row();
    }

    public function update_paket($sp, $id)
    {
      $nt['kode_rup'] = $this->input->post('paket_id');
      $count = $this->db->get_where('tb_non_tender_complete', $nt)->num_rows();
      $status_nt = ($count > 0) ? 'ya' : '' ;

      $key = array('id'=>$id,'paket_sp'=>$sp);

      $data1['paket_sp'] = $sp;
      $data1['sp_id'] = $sp;
      $data1['paket_id'] = $this->input->post('paket_id');
      $data1['kode_rup'] = $this->input->post('paket_id');
      $data1['paket_keterangan'] = date('Y-m-d');
      $data1['paket_tanggal'] = date('Y-m-d');
      $data1['paket_status'] = 1;
      $data1['nt'] = $status_nt;
      $data1['paket_log'] = $this->session->userdata('user_id');
      $data1['namapaket'] = $this->get_nama_paket($this->input->post('paket_id'));

      if($id == 0 && $this->paket_exist($sp, $this->input->post('paket_id')) == 0){
          
          $this->db->insert('tb_sp_paket',$data1);
          $result = $this->db->affected_rows();

          // return status sp as new need confirm
          $key1 = array('sp_id'=>$sp);
          $data2['sp_status'] = 0;
          $this->db->update('tb_sp', $data2, $key1);
      }else{
          $this->db->update('tb_sp_paket', $data, $key);
          $result = $this->db->affected_rows();
      }

      //$tahun = date('Y');
      //$query_nt = "UPDATE tb_sp_paket SET nt = 'ya' WHERE kode_rup IN (SELECT kode_rup FROM tb_non_tender WHERE anggaran = $tahun)";
      //$this->db->query($query_nt);

      if($result >= 1)
      {
        $this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil mengubah data paket</div>');
      }
    }

    public function delete_paket($sp, $id)
    {
      $filter = array('id'=>$id);
  		$this->db->delete('tb_sp_paket',$filter);
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil menghapus data paket</div>');
  		}
    }

    // list pokja dan paket
  	public function get_pokja_list()
  	{
      $str = "SELECT a.pokja_nip, a.pokja_nama, count(d.paket_id) as tpaket
              FROM tb_pokja a
              LEFT JOIN tb_sp_anggota b ON a.pokja_nip = b.anggota_nip
              left join tb_sp c ON b.anggota_sp = c.sp_id
              left join tb_sp_paket d ON c.sp_id = d.paket_sp
              left JOIN tb_rup e on d.paket_id = e.kode_rup
              where a.pokja_status = 1
              group by a.pokja_nip";
      return $this->db->query($str)->result();
      // return $this->db->get_where('tb_pokja',array('pokja_status'=>1))->result();
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
      $tahun = date('Y');
      $str = "SELECT * FROM tb_rup
      WHERE (metode_pemilihan = 'Tender' OR metode_pemilihan = 'Tender Cepat' OR metode_pemilihan = 'Seleksi' OR (metode_pemilihan = 'Penunjukan Langsung' AND pagu_rup > 200000000))
      AND left(akhir_pekerjaan,4) = '$tahun' AND status_aktif = 'ya' AND status_umumkan = 'sudah'
      AND (sumber_dana = 'APBD' OR sumber_dana = 'APBN' OR sumber_dana = 'BLUD')
      AND kode_rup NOT IN (SELECT paket_id FROM tb_sp_paket)
      AND kode_rup NOT IN (SELECT batal_paket FROM tb_batal)";
      // $str = "SELECT * FROM tb_rup WHERE kode_rup";
      return $this->db->query($str)->result();
  	}

    public function get_paketbatal()
    {
      $this->db->select('a.*,b.nama_paket,c.sp_kelompok,b.jenis_pengadaan');
      $this->db->join('tb_rup b','a.batal_paket = b.kode_rup','left');
      $this->db->join('tb_sp c','a.batal_sp = c.sp_id','left');
      return $this->db->get('tb_batal a')->result();
    }

    // check data exist
    public function anggota_exist($sp, $nip)
    {
      $this->db->select('id');
      $this->db->from('tb_sp_anggota');
      $this->db->where(array('anggota_sp'=>$sp,'anggota_nip'=>$nip));
      $result = $this->db->get()->num_rows();
      if($result >= 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-danger">Anggota sudah dimasukkan.</div>');
  		}
      return $result;
    }

    public function get_paket_tanggal($sp)
    {
      $this->db->select('MAX(paket_tanggal) as paket_tanggal');
      $this->db->from('tb_sp_paket');
      $this->db->where(array('paket_sp'=>$sp));
      $result = $this->db->get();
      if($result->num_rows() == 1){
        return $result->row('paket_tanggal');
      }else{
        return date('d-m-Y');
      }
    }

    public function get_nama_paket($kode_rup)
    {
      return $this->db->get_where('tb_rup',array('kode_rup'=>$kode_rup))->row('nama_paket');
    }

    public function paket_exist($sp, $paket_id)
    {
      $this->db->select('id');
      $this->db->from('tb_sp_paket');
      $this->db->where(array('paket_sp'=>$sp,'paket_id'=>$paket_id));
      $result = $this->db->get()->num_rows();
      if($result >= 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-danger">Paket sudah ada.</div>');
  		}
      return $result;
    }

    public function count($filter)
    {
      return $this->db->get_where('tb_sp',$filter)->num_rows();
    }

    public function secure_cetak($id)
    {
      return $this->db->get_where('tb_sp',array('sp_id'=>$id))->row('sp_status');
    }
}
