<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pokja_paket_m extends CI_Model{

    function __construct(){

    }

    public function reviu_bersama_insert($upload_result)
    {
      $data['kode_rup'] = $this->input->post('kode_rup');
      $data['hari'] = $this->input->post('hari');
      $data['tanggal'] = $this->input->post('tanggal');
      $data['waktu'] = $this->input->post('waktu');
      $data['tempat'] = $this->input->post('tempat');
      $data['namapa'] = $this->input->post('namapa');
      $data['namapptk'] = $this->input->post('namapptk');
      $data['tinjutreviubersama'] = $this->input->post('tinjutreviubersama');
      $data['peserta'] = $this->input->post('peserta');
      $data['undreviu'] = $this->input->post('undreviu');
      $data['reviuke'] = $this->input->post('reviuke');
      $data['komitmenserah'] = $this->input->post('komitmenserah');

      $data['foto1'] = $upload_result[0];
      $data['foto2'] = $upload_result[1];
      $data['foto3'] = $upload_result[2];
      $data['foto4'] = $upload_result[3];
      
      $this->db->insert('tb_reviubersama',$data);
      return $this->db->insert_id();
    }

    public function reviu_bersama_get($id)
    {
      $this->db->select('a.*,r.*,l.*,s.sp_kelompok, (SELECT jk.nama FROM tb_jenis_kontrak jk WHERE jk.idkontrak = l.lls_kontrak_pembayaran) as jenis_kontrak');
      $this->db->join('tb_rup r','a.kode_rup = r.kode_rup','left');
      $this->db->join('tb_lelang l','a.kode_rup = l.kode_rup','left');
      $this->db->join('tb_sp_paket sp','a.kode_rup = sp.kode_rup','left');
      $this->db->join('tb_sp s','s.sp_id = sp.sp_id','left'); 
      
      $this->db->where(array('a.id'=>$id));
      return $this->db->get('tb_reviubersama a')->row();
    }

    public function get_pokja_list($id)
    {
      $str = "SELECT a.kode_rup, d.pokja_nama 
      FROM tb_reviubersama a, tb_sp_paket b, tb_sp_anggota c, tb_pokja d
      WHERE a.kode_rup = b.kode_rup AND b.sp_id = c.sp_id AND c.anggota_nip = d.pokja_nip AND a.id = $id";
      return $this->db->query($str)->result();
    }

    public function get_paraf_karo()
    {
      $date = date('Y-m-d');
      // $str = "SELECT * FROM users WHERE tanggal_awal != '0000-00-00' AND tanggal_akhir = '0000-00-00' AND tanggal_awal <= '$date' ORDER BY id DESC LIMIT 1";
      $str = "SELECT * FROM users WHERE tanggal_awal != null AND tanggal_akhir = null AND tanggal_awal <= '$date' ORDER BY id DESC LIMIT 1";
      return $this->db->query($str)->row();
    }

    // module review baru
    public function review_dokumen($kode_rup)
    {
      $detail = $this->get_detail_review($kode_rup);

      // update tb_review

      $key1 = array('kode_rup' => $kode_rup);
      $data1['id_sp'] = $detail->sp_id;
      $data1['tgl_review'] = date('Y-m-d H:i:s');
      $data1['status'] = 0;
      $this->db->update('tb_review', $data1, $key1);

    }

    public function get_detail_review($kode_rup)
    {
      $tahun = date('Y');

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT rv.*, s.sp_id, s.sp_kelompok, r.nama_paket, t.nama_skpa, l.hps, t.nilai_hps FROM tb_review rv
          left join tb_sp_paket pk ON rv.kode_rup = pk.paket_id
          left join tb_sp s ON s.sp_id = pk.paket_sp
          left join tb_sp_anggota sa ON s.sp_id = sa.anggota_sp
          left join tb_rup r ON rv.kode_rup = r.kode_rup
          left join tb_lelang l ON rv.kode_rup = l.kode_rup
          left join tb_tpd t ON rv.kode_rup = t.kode_rup
          where rv.kode_rup = $kode_rup
          group by rv.kode_rup";

      return $this->db->query($str)->row();
    }

    public function get_detail_review_bck($kode_rup)
    {
      $tahun = date('Y');

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT rv.*, s.sp_id, s.sp_kelompok, r.nama_paket, l.hps, t.nilai_hps FROM tb_review rv
          left join tb_sp_paket pk ON rv.kode_rup = pk.paket_id
          left join tb_sp s ON s.sp_id = pk.paket_sp
          left join tb_sp_anggota sa ON s.sp_id = sa.anggota_sp
          left join tb_rup r ON rv.kode_rup = r.kode_rup
          left join tb_lelang l ON rv.kode_rup = l.kode_rup
          left join tb_tpd t ON rv.kode_rup = t.kode_rup
          where rv.kode_rup = $kode_rup
          group by rv.kode_rup";

      return $this->db->query($str)->row();
    }

    public function update_review_pokja($kode_rup)
    {

      if(isset($_POST['review']) && $_POST['review'] == 'skpa'){
        $review = 1;
        $review_ket = 'reviu skpa';
      }elseif(isset($_POST['review']) && $_POST['review'] == 'selesai'){
        $review = 2;
        $review_ket = 'reviu final';
        $data1['tgl_selesai'] = date('Y-m-d');
      }

      $tanggal = $this->input->post('tanggal');

      $key1 = array('kode_rup'=>$kode_rup);
      $data1['id_sp'] = $this->input->post('id_sp');
      $data1['tgl_review'] = $tanggal;
      $data1['status'] = $review;
      $data1['qrcode'] = '';

      $this->db->update('tb_review', $data1, $key1);

      // insert tb_review_paket
      
      $hari = date('l',strtotime($tanggal)); 
      $y = date('Y',strtotime($tanggal)); 
      $m = date('F',strtotime($tanggal)); 
      $d = date('j',strtotime($tanggal)); 

      $data2['id_pokja'] = $this->input->post('id_sp');
      $data2['nama_pokja'] = $this->input->post('sp_kelompok');
      $data2['kode_rup'] = $this->input->post('kode_rup');
      $data2['nama_paket'] = $this->input->post('nama_paket');
      $data2['tgl_review'] = $tanggal;
      $data2['status'] = $review_ket;
      $data2['keterangan'] = $this->input->post('keterangan');

      $data2['nama_kpa'] = $this->input->post('nama_kpa');
      $data2['hps'] = str_replace(',','',$this->input->post('hps') );
      $data2['hps_terbilang'] = $this->input->post('hps_terbilang');
      $data2['jangka_waktu_pelaksanaan'] = $this->input->post('jangka_waktu_pelaksanaan');
      $data2['jenis_kontrak'] = $this->input->post('jenis_kontrak');

      $data2['hari'] = hari_indo($hari);
      $data2['tanggal'] = number_to_words($d);
      $data2['bulan'] = tanggal_indo($m);
      $data2['tahun'] = number_to_words($y);

      $data2['review_spesifikasi_teknis'] = $this->input->post('review_spesifikasi_teknis');
      $data2['review_hps'] = $this->input->post('review_hps');
      $data2['review_rancangan_kontrak'] = $this->input->post('review_rancangan_kontrak');
      $data2['review_dokumen_anggaran'] = $this->input->post('review_dokumen_anggaran');
      $data2['review_id_rup'] = $this->input->post('review_id_rup');
      $data2['review_waktu_penggunaan'] = $this->input->post('review_waktu_penggunaan');
      $data2['review_analisis_pasar'] = $this->input->post('review_analisis_pasar');
      $data2['review_uraian_pekerjaan'] = $this->input->post('review_uraian_pekerjaan');
      $data2['review_komitmen_reviu'] = $this->input->post('review_komitmen_reviu');
      $this->db->insert('tb_review_paket',$data2);

      if($this->db->affected_rows() == 1){
        $this->session->set_flashdata(array('msg'=>'<div class="callout callout-success">Reviu berhasil</div>'));
      }

    }

    public function get_last_review($kode_rup)
    {
      $tahun = date('Y');

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT rp.*, sp.sp_kelompok, r.nama_satker
          FROM tb_review_paket rp
          LEFT JOIN tb_review rv ON rp.kode_rup = rv.kode_rup
          LEFT JOIN tb_sp_paket pk ON rp.kode_rup = pk.paket_id
          LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
          LEFT JOIN tb_rup r ON rp.kode_rup = r.kode_rup
          WHERE rp.kode_rup = '$kode_rup'
          ORDER BY rp.id DESC LIMIT 1";

      return $this->db->query($str)->row();
    }

    public function get_last_history_review($id)
    {
      $sql = "SELECT rv.status as review_status, rv.nomor as vnomor, rv.qrcode, r.nama_paket, r.nama_satker, rp.*, sp.sp_kelompok, r.nama_kpa
      FROM tb_rup r, tb_review rv, tb_review_paket rp
      LEFT JOIN tb_sp_paket pk ON rp.kode_rup = pk.paket_id
      LEFT JOIN tb_sp sp ON pk.paket_sp = sp.sp_id
      WHERE r.kode_rup = rv.kode_rup AND rv.kode_rup = rp.kode_rup AND rp.id = '$id'";
      return $this->db->query($sql)->row();
    }

    // end module

    public function get_paket()
    {
      $tahun = date('Y');

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.*, b.kode_rup, b.nama_paket, c.sp_kelompok, d.anggota_nip, d.anggota_jabatan, e.pokja_nama,
      c.sp_status, (SELECT hapus FROM tb_hapus WHERE nip = d.anggota_nip AND kode_rup = a.paket_id) as hapus,
      (SELECT COUNT(id) FROM tb_sp_anggota WHERE anggota_sp = c.sp_id) as tpokja,
      (SELECT SUM(hapus) FROM tb_hapus WHERE kode_rup = a.paket_id) as tsetuju');
      $this->db->from('tb_sp_paket a');
      $this->db->join('tb_rup b','a.paket_id = b.kode_rup','left');
      $this->db->join('tb_sp c','a.paket_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where(array('a.paket_status'=>2,'d.anggota_nip'=>$nip));
      $this->db->order_by('a.paket_tanggal DESC');
      $this->db->group_by('a.id');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function get_paket_review2()
    {
        $tahun = date('Y');
        if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
          $tahun = $_GET['tahun'];
        }

        $id = $this->session->userdata('user_id');
        $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

        $sql = "SELECT 

          a.id_sp, a.kode_rup, b.nama_paket, b.nama_satker, b.nama_kpa, c.sp_kelompok, a.tgl_serah_dokumen, a.tgl_selesai, a.status, f.tgl_review, f.rv_status, f.ket_review, f.histori_reviu, h.id_reviubersama, GROUP_CONCAT(rp.id) as id, 

          IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen

          FROM tb_review a 

          LEFT JOIN (SELECT kode_rup, nama_paket, nama_satker, nama_kpa FROM tb_rup) as b ON a.kode_rup = b.kode_rup 
          LEFT JOIN tb_sp c ON a.id_sp = c.sp_id 
          LEFT JOIN tb_sp_anggota d ON c.sp_id = d.anggota_sp 
          LEFT JOIN tb_pokja e ON d.anggota_nip = e.pokja_nip 
          LEFT JOIN (SELECT kode_rup, id FROM tb_review_paket GROUP BY id ORDER BY id DESC) as rp ON a.kode_rup = rp.kode_rup

          LEFT JOIN (SELECT kode_rup, tgl_review, status as rv_status, keterangan as ket_review, id as histori_reviu FROM tb_review_paket ORDER BY tgl_review DESC) AS f 
          ON a.kode_rup = f.kode_rup

          LEFT JOIN (SELECT kode_rup, id as id_reviubersama FROM tb_reviubersama ORDER BY id DESC) AS h 
          ON a.kode_rup = h.kode_rup

          WHERE d.anggota_jabatan = 'anggota' AND a.catatan != 'batal' AND (LEFT(c.sp_tanggal,4) = $tahun OR c.tahun = $tahun) AND d.anggota_nip = '$nip'

          GROUP BY a.kode_rup
          ORDER BY a.tgl_review DESC";

        return $this->db->query($sql)->result();
    }

    public function get_paket_review()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, c.sp_kelompok, a.tgl_serah_dokumen,
      a.tgl_selesai, a.status, b.nama_kpa, IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen,
      
      (SELECT rp.tgl_review FROM tb_review_paket rp WHERE rp.kode_rup = a.kode_rup ORDER BY rp.tgl_review DESC LIMIT 1) as tgl_review,
      (SELECT rv.status FROM tb_review_paket rv WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as rv_status,
      (SELECT keterangan FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket_review,
      (SELECT h.id FROM tb_review_paket h WHERE h.kode_rup = a.kode_rup LIMIT 1) as histori_reviu,
      (SELECT rb.id FROM tb_reviubersama rb WHERE rb.kode_rup = a.kode_rup ORDER BY rb.id DESC LIMIT 1) as id_reviubersama');

      $this->db->from('tb_review a');
      $this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
      $this->db->join('tb_sp c','a.id_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where(array('d.anggota_jabatan'=>'anggota','d.anggota_nip'=>$nip,'left(c.sp_tanggal,4)'=>$tahun,'a.catatan !='=>'batal sp'));
      $this->db->order_by('a.tgl_review DESC');
      $this->db->group_by('a.kode_rup');
  		$query = $this->db->get();
  		return $query->result();
    }

    public function get_ba_review_histori()
    {
      $tahun = date('Y');
      if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
        $tahun = $_GET['tahun'];
      }

      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $this->db->select('a.id_sp, b.kode_rup, b.nama_paket, b.nama_satker, c.sp_kelompok, a.tgl_serah_dokumen,
      (SELECT rp.tgl_review FROM tb_review_paket rp WHERE rp.kode_rup = a.kode_rup ORDER BY rp.tgl_review DESC LIMIT 1) as tgl_review,
      (SELECT rv.status FROM tb_review_paket rv WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as rv_status,
      (SELECT keterangan FROM tb_review_paket WHERE kode_rup = a.kode_rup ORDER BY tgl_review DESC LIMIT 1) as ket_review,
      a.tgl_review, a.tgl_selesai, a.status, b.nama_kpa, IFNULL(datediff(NOW(),a.tgl_serah_dokumen),0) as lama_dokumen,
      (SELECT h.id FROM tb_review_paket h WHERE h.kode_rup = a.kode_rup LIMIT 1) as histori_reviu,
      (SELECT rb.id FROM tb_reviubersama rb WHERE rb.kode_rup = a.kode_rup ORDER BY rb.id DESC LIMIT 1) as id_reviubersama,
      (SELECT GROUP_CONCAT(hi.id) FROM tb_review_paket hi WHERE hi.kode_rup = a.kode_rup AND hi.id_pokja = c.sp_id) as histori_id');

      $this->db->from('tb_review a');
      $this->db->join('tb_rup b','a.kode_rup = b.kode_rup','left');
      $this->db->join('tb_sp c','a.id_sp = c.sp_id','left');
      $this->db->join('tb_sp_anggota d','c.sp_id = d.anggota_sp','left');
      $this->db->join('tb_pokja e','d.anggota_nip = e.pokja_nip','left');

      $this->db->where(array('d.anggota_jabatan'=>'anggota','d.anggota_nip'=>$nip,'left(c.sp_tanggal,4)'=>$tahun,'a.catatan !='=>'batal sp'));
      $this->db->order_by('a.tgl_review DESC');
      $this->db->group_by('a.kode_rup');
      $query = $this->db->get();
      return $query->result();
    }

    public function get_paket_review_histori()
    {
        $tahun = date('Y');
        if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
          $tahun = $_GET['tahun'];
        }

        $kode_rup = array();

        $sql = "SELECT rv.kode_rup FROM tb_review rv 
        LEFT JOIN tb_rup r ON rv.kode_rup = r.kode_rup 
        WHERE r.tahun = $tahun";
        $result = $this->db->query($sql)->result();

        foreach ($result as $val)
        {
          $sql1 = "SELECT id FROM tb_review_paket WHERE kode_rup = $val->kode_rup LIMIT 10";
          $result1 = $this->db->query($sql1)->result_array();

          $kode_rup[$val->kode_rup] = $result1;
        }

        return $kode_rup;
    }

    public function get_history()
    {
      $id = $this->session->userdata('user_id');
      $nip = $this->db->get_where('users',array('id'=>$id))->row('nip');

      $str = "SELECT * FROM (SELECT v.kode_rup as kode_rup, r.nama_paket as nama_paket, r.nama_satker as nama_satker, r.nama_kpa as nama_kpa, s.sp_kelompok as kelompok, '' as tgl_review, '' as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE sa.anggota_nip = '$nip' AND v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      GROUP BY v.kode_rup

      UNION

      SELECT h.kode_rup as kode_rup, '' as nama_paket, '' as nama_satker, '' as nama_kpa, '' as kelompok, h.tgl_review as tgl_review, h.keterangan as keterangan
      FROM tb_review v, tb_review_paket h, tb_rup r,
      tb_sp s, tb_sp_paket pk, tb_sp_anggota sa, tb_pokja pj
      WHERE sa.anggota_nip = '$nip' AND v.kode_rup = h.kode_rup AND v.kode_rup = r.kode_rup AND v.kode_rup = pk.paket_id AND pk.paket_sp = s.sp_id AND s.sp_id = sa.anggota_sp
      ) as tb_join ORDER BY kode_rup DESC";

      return $this->db->query($str)->result();
    }

    public function update_review($kode_rup, $sp, $status)
    {
      $count = $this->db->get_where('tb_review',array('kode_rup'=>$kode_rup))->num_rows();
      if($count == 1 && $status == 1){
        $data['tgl_review'] = date('Y-m-d');
        $data['status'] = $status;
        $this->db->update('tb_review', $data, array('kode_rup'=>$kode_rup));
      }elseif($count == 1 && $status == 2){
        $data['tgl_selesai'] = date('Y-m-d');
        $data['status'] = $status;
        $this->db->update('tb_review', $data, array('kode_rup'=>$kode_rup));
      }
    }

  	public function batal()
    {
      // pakai kode rup
      $id = $this->input->post('id');

      // lock sp
      // if($this->_islocked($id) != true){

      $sp_paket = $this->db->get_where('tb_sp_paket',array('paket_id'=>$id))->row();
      $sp = $sp_paket->paket_sp;
      $paket = $sp_paket->paket_id;

      // tambah ke tabel batal
      $data['batal_sp'] = $sp;
      $data['tgl_batal'] = date('Y-m-d');
      $data['batal_paket'] = $paket;
      $data['batal_keterangan'] = $this->input->post('keterangan');
      $this->db->insert('tb_batal',$data);

      // delete from sp_paket
      $this->db->delete('tb_review',array('kode_rup'=>$id));
      $this->db->delete('tb_sp_paket',array('paket_id'=>$id));
  		$result = $this->db->affected_rows();
  		if($result = 1)
  		{
  			$this->session->set_flashdata('msg','<div class="callout callout-success">Paket telah dibatalkan</div>');
  		}
  	}

    public function _islocked($kode_rup)
    {
      $this->db->select('b.sp_status');
      $this->db->from('tb_sp_paket a');
      $this->db->join('tb_sp b','a.paket_sp = b.sp_id','left');
      $this->db->where(array('a.paket_id'=>$kode_rup));
      $this->db->group_by('a.paket_id');
      $result = $this->db->get()->row();
      if($result->sp_status == 2){
        return true;
      }else{
        return false;
      }
    }
}
