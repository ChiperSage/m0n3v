<?php
class Pegawai_m extends CI_Model {

	public function __construct(){

	}

	public function get_pegawai_pokja()
	{
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		$this->db->select('*');
    $this->db->from('tb_pegawai_pokja');
		$this->db->where(array('pnt_tahun'=>$year));
		$this->db->group_by('peg_nip');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_pegawai_rekaphonor()
	{
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		$sql = "SELECT ppp.*, p.pokja_nip,

		(SELECT SUM((SELECT hh.honor FROM tb_honor hh WHERE hh.kgr_id = ls.jenis_pengadaan AND (hh.nilai1 <= ls.pagu AND hh.nilai2 > ls.pagu)))
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		WHERE pp.peg_nip = ppp.peg_nip AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY pp.peg_nip) as total_honor,

		(SELECT SUM(byr.jml_bayar) FROM tb_bayar byr WHERE byr.nip = ppp.peg_nip AND left(byr.tgl_bayar,4) = $year) as total_bayar

		FROM tb_pegawai_pokja ppp, tb_pokja p WHERE ppp.pnt_tahun = $year AND p.pokja_nip_honor = ppp.peg_nip GROUP BY ppp.peg_nip";
		// FROM tb_pegawai_pokja ppp, tb_pokja p, tb_potongan pt WHERE ppp.pnt_tahun = $year AND p.pokja_nip_honor = ppp.peg_nip AND ppp.peg_golongan = pt.kode GROUP BY ppp.peg_nip";
		return $this->db->query($sql)->result();
	}

	public function get_pegawai_rekaphonor2()
	{
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		$sql = "SELECT ppp.*, p.pokja_nip,

		(SELECT SUM((SELECT hh.honor FROM tb_honor hh WHERE hh.kgr_id = ls.jenis_pengadaan AND (hh.nilai1 <= ls.pagu AND hh.nilai2 > ls.pagu)))
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		WHERE pp.peg_nip = ppp.peg_nip AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY pp.peg_nip) as total_honor,

		(SELECT COUNT(ls.kode_lelang)
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		WHERE pp.peg_nip = ppp.peg_nip AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY pp.peg_nip) as total_paket,

		(SELECT COUNT(byr.kode_lelang) FROM tb_bayar byr WHERE byr.nip = ppp.peg_nip AND left(byr.tgl_bayar,4) = $year) as total_paket_bayar,

		(SELECT SUM(byr.jml_bayar) FROM tb_bayar byr WHERE byr.nip = ppp.peg_nip AND left(byr.tgl_bayar,4) = $year) as total_bayar

		FROM tb_pegawai_pokja ppp, tb_pokja p WHERE ppp.pnt_tahun = $year AND p.pokja_nip_honor = ppp.peg_nip GROUP BY ppp.peg_nip";

		return $this->db->query($sql)->result();
	}

	public function get_data_pokja($nip)
	{
		$filter = array('pokja_nip_honor'=>$nip);
		return $this->db->get_where('tb_pokja',$filter)->row();
	}

	public function get_data_bayar($nip)
	{
		$tahun = date('Y');
		$filter = array('nip'=>$nip,'left(tgl_bayar,4)'=>$tahun);
		return $this->db->get_where('tb_bayar',$filter)->result();
	}

	public function get_total_bayar($nip)
	{
		$tahun = date('Y');
		$sql = "SELECT SUM(jml_bayar) as total_bayar
		FROM tb_bayar WHERE nip = '$nip' AND left(tgl_bayar,4) = $tahun";
		return $this->db->query($sql)->result();
	}

	public function get_pejabat_ppk()
	{
		return $this->db->get_where('tb_pejabat_ppk',array('tahun'=>date('Y')))->row();
	}

	public function insert_bayar($nip)
	{
		$jml_bayar = str_replace('.','',$this->input->post('jml_bayar'));
		if($jml_bayar <= $this->input->post('sisa')){

			$data['nip'] = $nip;
			$data['tgl_bayar'] = date('Y-m-d');
			$data['kode_lelang'] = $this->input->post('checkbox');
			$data['jml_bayar'] = $jml_bayar;
			$this->db->insert('tb_bayar',$data);

		}else{
			$this->session->set_flashdata(array('msg'=>'<div class="text text-danger">Pembayaran anda lebih</div>'));
		}
	}

	public function get_listpaket($nip = '')
	{
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		// $hitung = $this->hitung_honor($nip, $year);

		$sql = "SELECT pp.*, ls.kode_lelang, ls.nama_paket, ls.pagu, ls.hps, ls.ang_tahun, k.kgr_nama,
		(SELECT hh.honor FROM tb_honor hh WHERE hh.kgr_id = ls.jenis_pengadaan AND (hh.nilai1 <= ls.pagu AND hh.nilai2 > ls.pagu) LIMIT 1) as honor,
		(SELECT b.status FROM tb_bayar b WHERE b.nip = '$nip' AND b.kode_lelang = ls.kode_lelang AND left(b.tgl_bayar,4) = $year LIMIT 1) as status_bayar
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		WHERE pp.peg_nip = '$nip' AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY ls.kode_lelang";

		return $this->db->query($sql)->result();
	}

	public function get_listpaket_total($nip = '')
	{
		$year = date('Y');
		if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
			$year = $_GET['tahun'];
		}

		$sql = "SELECT
		SUM((SELECT hh.honor FROM tb_honor hh WHERE hh.kgr_id = ls.jenis_pengadaan AND (hh.nilai1 <= ls.pagu AND hh.nilai2 > ls.pagu))) as honor
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		WHERE pp.peg_nip = '$nip' AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY pp.peg_nip";

		return $this->db->query($sql)->result();
	}

	public function get_total_paket($nip = '')
	{
		$tahun = date('Y');
		$sql = "SELECT COUNT(kode_lelang) as total
		FROM tb_bayar WHERE nip = '$nip' AND left(tgl_bayar,4) = $tahun";
		return $this->db->query($sql)->result();

		// $year = date('Y');
		// if(isset($_GET['tahun']) && $_GET['tahun'] != ''){
		// 	$year = $_GET['tahun'];
		// }
		// $sql = "SELECT COUNT(ls.kode_lelang) as total FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh, tb_kategori k
		// WHERE pp.peg_nip = '$nip' AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year AND k.kgr_id = ls.jenis_pengadaan
		// AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' GROUP BY pp.peg_nip";
		// return $this->db->query($sql)->result();
	}


	public function hitung_honor($nip,$year)
	{
		$sql = "SELECT pp.*, ls.kode_lelang, ls.jenis_pengadaan, ls.pagu, ls.hps, ls.ang_tahun,
		h.kgr_id, h.nilai1, h.nilai2
		FROM tb_pegawai_pokja pp, tb_lelang_spse ls, tb_lhp lh
		WHERE pp.peg_nip = '$nip' AND pp.pnt_id = ls.pnt_id AND ls.ang_tahun = $year
		AND ls.kode_lelang = lh.kode_lelang AND lh.keterangan_terima = 'sudah' IN

		(SELECT honor FROM tb_honor WHERE kgr_id = ls.jenis_pengadaan AND (ls.pagu >= h.nilai1 AND ls.pagu <= h.nilai2) )";

		$result = $this->db->query($sql)->result();

		return $result;

		// foreach ($result as $val) {
		//
		// 	$kgr_id = $val->kgr_id;
		// 	$nilai1 = $val->nilai1;
		// 	$nilai2 = $val->nilai2;
		// 	$jenis = $val->jenis_pengadaan;
		// 	$pagu = $val->pagu;
		// 	$honor = $val->honor;
		//
		// 	IF (($kgr_id == $jenis) AND ($pagu >= $nilai1 AND $pagu <= $nilai2))
		// 	{
		// 	$total_honor = $honor;
		// 	//total_bayar = total_bayar + total_honor;
		// 	}
		// 	else
		// 	{
		// 		$total_honor = 0;
		// 	}
		// 	return $total_honor;
		// }

		// return $total_honor;

	}

	public function get_pegawai_honor()
	{
		$sql = "SELECT h.*, k.kgr_nama FROM tb_honor h, tb_kategori k WHERE h.kgr_id = k.kgr_id";
		return $this->db->query($sql)->result();
	}

	public function get_pegawai_honor_detail($id)
	{
		return $this->db->get_where('tb_honor', array('id'=>$id))->row();
	}

	public function honor_insert()
	{
		$data['kgr_id'] = $this->input->post('jenis_pengadaan');
		$data['nilai1'] = $this->input->post('nilai1');
		$data['nilai2'] = $this->input->post('nilai2');
		$data['honor'] = $this->input->post('honor');
		$this->db->insert('tb_honor',$data);
	}

	public function honor_update($id)
	{
		$filter = array('id'=>$id);
		$data['kgr_id'] = $this->input->post('jenis_pengadaan');
		$data['nilai1'] = $this->input->post('nilai1');
		$data['nilai2'] = $this->input->post('nilai2');
		$data['honor'] = $this->input->post('honor');
		$this->db->update('tb_honor', $data, $filter);
	}

	public function honor_delete($id){
		$filter = array('id'=>$id);
		$this->db->delete('tb_honor', $filter);
		redirect('pegawai/honor');
	}

}
