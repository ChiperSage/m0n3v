<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Epurchasing extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Epurchasing_m');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		$groups = array('monev','kabagppse','karo','kasubbag_monev');
		if(!$this->ion_auth->logged_in() || !$this->ion_auth->in_group($groups))
		{
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$data['inc'] = 'epurchasing_tb';
		$data['epurchasing'] = $this->Epurchasing_m->get_data();
		
		if(isset($_GET['action']) && $_GET['action'] == 'excel'){
			$this->load->view('admin/epurchasing_ex', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function update_penyedia()
	{
	
		// $this->db->select('kd_penyedia');
		// $this->db->from('tb_paketepurchasing');
		// $this->db->group_by('kd_penyedia');
		// $ekatalog = $this->db->get()->result();
		
		$sql = "SELECT kd_penyedia FROM tb_paketepurchasing WHERE kd_penyedia NOT IN (SELECT a.kd_penyedia FROM tb_penyedia_ekatalog a) GROUP BY kd_penyedia";
		$ekatalog = $this->db->query($sql)->result();

		foreach($ekatalog as $val)
		{
			$jsondata = file_get_contents('https://isb.lkpp.go.id/isb/api/9d32e37f-3314-4b1a-a9a0-05a4b22e4cef/json/736991721/Ecat-PenyediaDetail/tipe/4/parameter/'.$val->kd_penyedia);
			$data = json_decode($jsondata);

			$data1['kd_penyedia'] = $data[0]->kd_penyedia;
			$data1['nama_penyedia'] = $data[0]->nama_penyedia;
			$data1['penyedia_umkm'] = $data[0]->penyedia_umkm;
			$data1['alamat_penyedia'] = $data[0]->alamat_penyedia;
			$data1['email_penyedia'] = $data[0]->email_penyedia;
			$data1['no_telp_penyedia'] = $data[0]->no_telp_penyedia;
			$data1['npwp_penyedia'] = $data[0]->npwp_penyedia;
			$data1['kbli2020_penyedia'] = $data[0]->kbli2020_penyedia;

			// $this->db->insert('tb_penyedia_ekatalog',$data1);

			$insert_query = $this->db->insert('tb_penyedia_ekatalog', $data1);  // QUERY RUNS ONCE
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
			$this->db->query($insert_query); // QUERY RUNS A SECOND TIME
		}		
	}

	public function rekap()
	{
		$data['inc'] = 'epurchasing_rekap_tb';
		$data['epurchasing'] = $this->Epurchasing_m->get_realisasi();

		$data['pagu_skpa'] = $this->Epurchasing_m->total_pagu();

		if(isset($_GET['action']) && $_GET['action'] == 'excel'){
			$this->load->view('admin/epurchasing_rekap_ex', $data);
		}else{
			$this->load->view('admin/index', $data);
		}
	}

	public function create()
	{
		// ambil data json
		$json = $this->db->get_where('json',array('data'=>'paket_epurchasing'))->row();
		$filename = $json->url;
		$year = $json->tahun;

		$jsondata = file_get_contents($filename);
		$data = json_decode($jsondata);

		$id_list = implode(",", array_map(function ($val) { return (int) $val->kd_rup; }, $data));

		$connection = mysqli_connect("localhost","root","p>.@?A3!eur]>T*RE@YA","monev");

		mysqli_query($connection, "DELETE FROM tb_paketepurchasing WHERE tahun_anggaran = '$year'");

		// mysqli_query($connection, "DELETE FROM tb_paketepurchasing WHERE kd_rup NOT IN ($id_list) AND tahun = '$year'");

		foreach ($data as $val)
		{
		    $sql = "INSERT INTO tb_paketepurchasing SET tahun_anggaran=?, kd_klpd=?, satker_id=?, nama_satker=?, alamat_satker=?, npwp_satker=?, kd_paket=?, no_paket=?, nama_paket=?, kd_rup=?,
		    nama_sumber_dana=?, kode_anggaran=?, kd_komoditas=?, kd_produk=?, kd_penyedia=?, kd_penyedia_distributor=?, jml_jenis_produk=?, total=?, kuantitas=?, harga_satuan=?, ongkos_kirim=?, total_harga=?, kd_user_pokja=?, no_telp_user_pokja=?, email_user_pokja=?, kd_user_ppk=?, ppk_nip=?, jabatan_ppk=?, tanggal_buat_paket=?, tanggal_edit_paket=?, deskripsi=?, status_paket=?, paket_status_str=?, catatan_produk=?

		    ON DUPLICATE KEY UPDATE tahun_anggaran=values(tahun_anggaran), kd_klpd=values(kd_klpd), satker_id=values(satker_id), nama_satker=values(nama_satker), alamat_satker=values(alamat_satker), npwp_satker=values(npwp_satker), kd_paket=values(kd_paket), no_paket=values(no_paket), nama_paket=values(nama_paket), kd_rup=values(kd_rup), nama_sumber_dana=values(nama_sumber_dana), kode_anggaran=values(kode_anggaran), kd_komoditas=values(kd_komoditas), kd_produk=values(kd_produk), kd_penyedia=values(kd_penyedia), kd_penyedia_distributor=values(kd_penyedia_distributor), jml_jenis_produk=values(jml_jenis_produk), total=values(total), kuantitas=values(kuantitas), harga_satuan=values(harga_satuan), ongkos_kirim=values(ongkos_kirim), total_harga=values(total_harga), kd_user_pokja=values(kd_user_pokja), no_telp_user_pokja=values(no_telp_user_pokja), email_user_pokja=values(email_user_pokja), kd_user_ppk=values(kd_user_ppk), ppk_nip=values(ppk_nip), jabatan_ppk=values(jabatan_ppk), tanggal_buat_paket=values(tanggal_buat_paket), tanggal_edit_paket=values(tanggal_buat_paket), deskripsi=values(deskripsi), status_paket=values(status_paket), paket_status_str=values(paket_status_str), catatan_produk=values(catatan_produk)";
									
		    $stmt = mysqli_prepare($connection, $sql);

			mysqli_stmt_bind_param($stmt, "isssssisssssiiiiiiiiiiississssssss", $val->tahun_anggaran, $val->kd_klpd, $val->satker_id, $val->nama_satker, $val->alamat_satker, $val->npwp_satker, $val->kd_paket, $val->no_paket, $val->nama_paket, $val->kd_rup, $val->nama_sumber_dana, $val->kode_anggaran, $val->kd_komoditas, $val->kd_produk, $val->kd_penyedia, $val->kd_penyedia_distributor, $val->jml_jenis_produk, $val->total, $val->kuantitas, $val->harga_satuan, $val->ongkos_kirim, $val->total_harga, $val->kd_user_pokja, $val->no_telp_user_pokja, $val->email_user_pokja, $val->kd_user_ppk, $val->ppk_nip, $val->jabatan_ppk, $val->tanggal_buat_paket, $val->tanggal_edit_paket, $val->deskripsi, $val->status_paket, $val->paket_status_str, $val->catatan_produk);

		    mysqli_stmt_execute($stmt);
		}

		$this->update_penyedia();
		
		redirect('epurchasing');
	}

	public function sync_komoditas()
	{
		$this->db->select('kd_komoditas');
		$this->db->from('tb_paketepurchasing');
		$this->db->group_by('kd_komoditas');
		$result = $this->db->get()->result();
		foreach($result as $val)
		{
			$kd_komoditas = $val->kd_komoditas;

			$jsondata = file_get_contents('https://isb.lkpp.go.id/isb/api/011bf999-8ca7-4068-96eb-9e39fd120ca4/json/736991612/Ecat-KomoditasDetail/tipe/4/parameter/'.$kd_komoditas);

			$data = json_decode($jsondata);
			print_r($data);
			
			// $this->db->where('kd_komoditas', $data[0]->kd_komoditas);
		    // $query = $this->db->get('tb_komoditas');

		    // if($query->num_rows() == 0) {
		    //     $insert_data = array(
		    //         'kd_komoditas' => $data[0]->kd_komoditas,
		    //         'nama_komoditas' => $data[0]->nama_komoditas,
		    //         'jenis_katalog' => $data[0]->jenis_katalog,
		    //         'nama_instansi_katalog' => $data[0]->nama_instansi_katalog
		    //     );
		    //     $this->db->insert('tb_komoditas', $insert_data);
		    // }
		}
	}

	public function sync_produkdetail()
	{
		$sql = "SELECT kd_produk FROM tb_paketepurchasing WHERE tahun_anggaran >= 2021 GROUP BY kd_produk";
		$list_kode_produk = $this->db->query($sql)->result();

		foreach($list_kode_produk as $val)
		{
		
			$kd_produk = $val->kd_produk;

			// ambil data json
			$jsondata = file_get_contents('https://isb.lkpp.go.id/isb-2/api/757f8cc6-bcf0-45d7-9697-fe026c690c27/json/2539/Ecat-ProdukDetail/tipe/4/parameter/'.$kd_produk);
			$data = json_decode($jsondata);

			// $kd_produk = $data[0]->kd_produk;
			// $no_kontrak = $data[0]->no_kontrak;
			// $nama_penyedia = $data[0]->nama_penyedia;
			// $no_produk = $data[0]->no_produk;
			// $no_produk_penyedia = $data[0]->no_produk_penyedia;
			// $nama_manufaktur = $data[0]->nama_manufaktur;
			// $nama_produk = $data[0]->nama_produk;
			// $nama_kategori_terkecil = $data[0]->nama_kategori_terkecil;
			// $nama_komoditas = $data[0]->nama_komoditas;
			// $jumlah_stok = $data[0]->jumlah_stok;
			// $setuju_tolak_tanggal = $data[0]->setuju_tolak_tanggal;
			// $berlaku_sampai = $data[0]->berlaku_sampai;
			// $unit_pengukuran = $data[0]->unit_pengukuran;
			// $jenis_produk = $data[0]->jenis_produk;
			// $kbki_id = $data[0]->kbki_id;
			// $kd_produk_kategori = $data[0]->kd_produk_kategori;
			// $active = $data[0]->active;
			// $created_date = $data[0]->created_date;
			// $modified_date = $data[0]->modified_date;
			// $status = $data[0]->status;
			// $status_tayang = $data[0]->status_tayang;
			// $apakah_dapat_dibeli = $data[0]->apakah_dapat_dibeli;

			$data = array(
			    'kd_produk' => $data[0]->kd_produk,
			    'no_kontrak' => $data[0]->no_kontrak,
			    'nama_penyedia' => $data[0]->nama_penyedia,
			    'no_produk' => $data[0]->no_produk,
			    'no_produk_penyedia' => $data[0]->no_produk_penyedia,
			    'nama_manufaktur' => $data[0]->nama_manufaktur,
			    'nama_produk' => $data[0]->nama_produk,
			    'nama_kategori_terkecil' => $data[0]->nama_kategori_terkecil,
			    'nama_komoditas' => $data[0]->nama_komoditas,
			    'jumlah_stok' => $data[0]->jumlah_stok,
			    'setuju_tolak_tanggal' => $data[0]->setuju_tolak_tanggal,
			    'berlaku_sampai' => $data[0]->berlaku_sampai,
			    'unit_pengukuran' => $data[0]->unit_pengukuran,
			    'jenis_produk' => $data[0]->jenis_produk,
			    'kbki_id' => $data[0]->kbki_id,
			    'kd_produk_kategori' => $data[0]->kd_produk_kategori,
			    'active' => $data[0]->active,
			    'created_date' => $data[0]->created_date,
			    'modified_date' => $data[0]->modified_date,
			    'status' => $data[0]->status,
			    'status_tayang' => $data[0]->status_tayang,
			    'apakah_dapat_dibeli' => $data[0]->apakah_dapat_dibeli
			);

			// check if kd_produk already exists in table
			$this->db->where('kd_produk', $data[0]->kd_produk);
			$query = $this->db->get('tb_produkdetail');

			// if kd_produk exists, update the data
			if ($query->num_rows() > 0) {
			    $this->db->where('kd_produk', $data[0]->kd_produk);
			    $this->db->update('tb_produkdetail', $data);
			}
			// if kd_produk does not exist, insert the data
			else {
			    $this->db->insert('tb_produkdetail', $data);
			}


		}


	}

}
