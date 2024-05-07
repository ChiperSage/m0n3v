<?php
class Lhp_m extends CI_Model {

	public function __construct(){

	}

	public function get_cari_paket_lhp($var)
	{
		$kode_lelang = $this->input->post('pencarian');

		if($var != 0){
			$kode_lelang = $var;
		}

		$this->is_kode_lelang_exist($kode_lelang);

		$str = "SELECT ls.kode_lelang, ls.nama_paket, ls.pagu, ls.hps, k.kgr_nama, m.mtd_nama, sa.nama as nama_satker, p.pnt_nama
				FROM tb_lelang_spse ls, tb_kategori k, tb_metode m, tb_panitia p, tb_skpa sa
				WHERE ls.kode_lelang = '$kode_lelang' AND ls.jenis_pengadaan = k.kgr_id AND ls.mtd_pemilihan = m.mtd_id
				AND ls.rup_stk_id = sa.kode AND ls.pnt_id = p.pnt_id";

		if($this->db->query($str)->num_rows() == 0){
				$this->session->set_flashdata('msg','<div class="callout callout-danger">Kode Lelang Tersebut Tidak Ada.</div>');
		}

		return $this->db->query($str)->row();
	}

	public function get_data_lhp()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun'])){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT lh.*, ls.*, pn.*, kg.kgr_nama, sa.nama as nama_satker
				FROM tb_lhp lh, tb_lelang_spse ls, tb_panitia pn, tb_kategori kg, tb_skpa sa
				WHERE left(lh.tgl_terima,4) = $tahun AND lh.kode_lelang = ls.kode_lelang AND pn.pnt_id = ls.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND sa.kode = ls.rup_stk_id";
		$query = $this->db->query($str);
		return $query->result();
	}

	public function get_data_blm_lhp()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$str = "SELECT pm.kode_lelang, ls.nama_paket, ls.stk_nama, m.mtd_nama, pn.pnt_nama, kg.kgr_nama, ls.pagu, ls.hps, '' as tahapan1, '' as tahapan2, ls.sanggah,
		IF(m.mtd_id = 9, (SELECT js.tgl_selesai FROM tb_jadwal_spse js WHERE js.tahapan = 'PEMASUKAN_PENAWARAN' AND js.kode_lelang = pm.kode_lelang ORDER BY js.tgl_selesai DESC LIMIT 1), '') as akhir_masa_sanggah1,
		IF(m.mtd_id != 9, (SELECT js.tgl_selesai FROM tb_jadwal_spse js WHERE (js.tahapan = 'SANGGAH' OR js.tahapan = 'PENETAPAN_DAN_PENGUMUMAN_PEMENANG_AKHIR') AND js.kode_lelang = pm.kode_lelang ORDER BY js.tgl_selesai DESC LIMIT 1), '') as akhir_masa_sanggah2
		-- (SELECT count(sb.lls_id) FROM sanggah_banding sb WHERE pm.kode_lelang = sb.lls_id) as sanggah_banding
		FROM tb_pemenang pm, tb_lelang_spse ls, tb_metode m, tb_panitia pn, tb_kategori kg
		WHERE pm.kode_lelang = ls.kode_lelang AND ls.mtd_pemilihan = m.mtd_id AND ls.pnt_id = pn.pnt_id AND pm.ang_tahun = $tahun AND ls.jenis_pengadaan = kg.kgr_id
		AND pm.kode_lelang NOT IN (SELECT l.kode_lelang FROM tb_lhp l WHERE left(l.tgl_terima,4) = $tahun)";

		$query = $this->db->query($str);
		return $query->result();
	}

	public function list_paket_blm_krm()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$skpa = '';
		if(isset($_GET['skpa']) && $_GET['skpa'] != ''){
			$skpa = $_GET['skpa'];
		}

		$str = "SELECT lh.kode_lelang, ls.nama_paket, ls.pagu, ls.hps, pn.*, kg.kgr_nama, sa.nama as nama_satker, m.mtd_nama
			FROM tb_lhp lh, tb_lelang_spse ls, tb_panitia pn, tb_kategori kg, tb_skpa sa, tb_metode m
			WHERE ls.ang_tahun = $tahun AND lh.kode_lelang = ls.kode_lelang AND pn.pnt_id = ls.pnt_id AND ls.jenis_pengadaan = kg.kgr_id AND sa.kode = ls.rup_stk_id 
			AND ls.mtd_pemilihan = m.mtd_id AND ls.rup_stk_id = '$skpa' AND (lh.keterangan_serah = 'belum kirim' OR lh.keterangan_serah = 'belum ambil')";

		$query = $this->db->query($str);
		return $query->result();
	}

	public function list_paket_pilihan()
	{
		$tahun = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$tahun = $_GET['tahun'];
		}

		$skpa = '';
		if(isset($_GET['skpa']) && $_GET['skpa'] != ''){
			$skpa = $_GET['skpa'];
		}

		$string = $_GET['kode_lelang'];
		$array = array_map('intval', explode('_', $string));
		$ids = implode(",",$array);

		$str = "SELECT lh.kode_lelang, ls.nama_paket, ls.pagu, r.nama_kpa
			FROM tb_lhp lh
			
			LEFT JOIN tb_lelang_spse ls ON lh.kode_lelang = ls.kode_lelang
			LEFT JOIN tb_skpa sa ON sa.kode = ls.rup_stk_id
			LEFT JOIN tb_lelang l ON ls.kode_lelang = ls.kode_lelang
			LEFT JOIN tb_rup r ON l.kode_rup = r.kode_rup

			WHERE ls.ang_tahun = $tahun AND ls.rup_stk_id = '$skpa' AND lh.keterangan_serah = 'belum kirim' AND ls.kode_lelang IN ($ids)
			GROUP BY lh.kode_lelang";

		$query = $this->db->query($str);
		return $query->result();
	}

	public function insert($data)
	{
		$kode = $this->input->post('kode_lelang');
		if($this->is_kode_lelang_exist($kode) != true){

			$this->db->insert('tb_lhp',$data);

			$result = $this->db->affected_rows();
			if($result = 1){
				$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
			}

		}
	}

	public function update($kode_lelang, $data)
	{
		$this->db->update('tb_lhp',$data,array('kode_lelang'=>$kode_lelang));

		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-success">Berhasil</div>');
		}
	}

	public function delete($id)
	{
		$this->db->where('kode_lelang',$id);
		$this->db->delete('tb_lhp');

		$result = $this->db->affected_rows();
		if($result = 1)
		{
			$this->session->set_flashdata('msg','<div class="callout callout-danger">Berhasil Hapus Data</div>');
		}
	}

	public function is_kode_lelang_exist($kode_lelang)
	{
		$str = "SELECT kode_lelang FROM tb_lhp WHERE kode_lelang = $kode_lelang";
		$result = $this->db->query($str)->num_rows();
		if($result == 1){
			$this->session->set_flashdata('msg','<div class="callout callout-warning">Kode Lelang Tersebut Sudah Ada</div>');
			return true;
		}
	}

	public function get_skpa()
	{
		$this->db->select('kode, nama');
		$this->db->from('tb_skpa');
		$this->db->where('nama NOT LIKE "%biro%"');
		$this->db->order_by('nama ASC');
		return $this->db->get()->result();
	}

}
