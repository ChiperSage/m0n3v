<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monev_spse_m extends CI_Model{

    function __construct(){

    }

    public function laporan_bps_spse_2()
    {
      // tahun dinamis
      $year = date('Y');
      if(isset($_GET['tahun'])){
        $year = $_GET['tahun'];
      }

      // set default
      $tgl1 = $year.'-01-01';
      $a_date = $year."-03-01";
      $date = new DateTime($a_date);
      $date->modify('last day of this month');
      $tgl2 = $date->format('Y-m-d');

      // pilihan tahun dan triwulan
      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        if($triwulan == 1 && $tahun != 0){

          $tgl1 = $tahun.'-01-01';
          $a_date = $tahun."-03-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 2 && $tahun != 0){

          $tgl1 = $tahun.'-04-01';
          $a_date = $tahun."-06-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 3 && $tahun != 0){

          $tgl1 = $tahun.'-07-01';
          $a_date = $tahun."-09-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 4 && $tahun != 0){

          $tgl1 = $tahun.'-10-01';
          $a_date = $tahun."-12-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }

      }

      $str = "SELECT js.tgl_mulai,

      -- barang

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 0) as b_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 0) as b_total_pagu,

      -- konsultansi

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 1) as ks_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 1) as ks_total_pagu,

      -- konstruksi

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 2) as kt_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 2) as kt_total_pagu,

      -- jasa

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 3) as j_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 3) as j_total_pagu,

      -- total

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7)) as total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss
      INNER JOIN tb_jadwal_spse jss ON lss.kode_lelang = jss.kode_lelang
      WHERE ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00')) AND lss.ang_tahun = $year
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7)) as total_pagu

      FROM tb_lelang_spse ls
      -- FROM tb_lelang_spse ls, tb_jadwal_spse js
      INNER JOIN tb_jadwal_spse js ON ls.kode_lelang = js.kode_lelang
      WHERE (left(js.tgl_mulai,4) = $year OR ls.ang_tahun = $year)
      AND (js.tgl_mulai BETWEEN '$tgl1' AND '$tgl2')

      GROUP BY left(js.tgl_mulai,7)";

      return $this->db->query($str)->result();
    }

    public function laporan_bps_spse_2_bck()
    {
      // tahun dinamis
      $year = date('Y');
      if(isset($_GET['tahun'])){
        $year = $_GET['tahun'];
      }

      // set default
      $tgl1 = $year.'-01-01';
      $a_date = $year."-03-01";
      $date = new DateTime($a_date);
      $date->modify('last day of this month');
      $tgl2 = $date->format('Y-m-d');

      // pilihan tahun dan triwulan
      if(isset($_GET['triwulan']) && isset($_GET['tahun'])){

        $tahun = $_GET['tahun'];
        $triwulan = $_GET['triwulan'];

        if($triwulan == 1 && $tahun != 0){

          $tgl1 = $tahun.'-01-01';
          $a_date = $tahun."-03-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 2 && $tahun != 0){

          $tgl1 = $tahun.'-04-01';
          $a_date = $tahun."-06-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 3 && $tahun != 0){

          $tgl1 = $tahun.'-07-01';
          $a_date = $tahun."-09-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }elseif($triwulan == 4 && $tahun != 0){

          $tgl1 = $tahun.'-10-01';
          $a_date = $tahun."-12-01";
          $date = new DateTime($a_date);
          $date->modify('last day of this month');
          $tgl2 = $date->format('Y-m-d');

        }

      }

      $str = "SELECT js.tgl_mulai,

      -- barang

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 0) as b_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 0) as b_total_pagu,

      -- konsultansi

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 1) as ks_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 1) as ks_total_pagu,

      -- konstruksi

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 2) as kt_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 2) as kt_total_pagu,

      -- jasa

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 3) as j_total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7) AND jenis_pengadaan = 3) as j_total_pagu,

      -- total

      (SELECT COUNT(lss.kode_lelang) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7)) as total_paket,

      (SELECT SUM(lss.pagu) FROM tb_lelang_spse lss, tb_jadwal_spse jss
      WHERE lss.kode_lelang = lss.kode_lelang AND lss.kode_lelang = jss.kode_lelang
      AND ((lss.status_lelang = 1) OR (lss.status_lelang = 1 AND lss.ukpbj = '1106.00'))
      AND jss.tahapan = 'TANDATANGAN_KONTRAK' AND left(jss.tgl_mulai,7) = left(js.tgl_mulai,7)) as total_pagu

      FROM tb_lelang_spse ls, tb_jadwal_spse js

      WHERE ls.kode_lelang = js.kode_lelang AND left(js.tgl_mulai,4) = $year
      AND (js.tgl_mulai BETWEEN '$tgl1' AND '$tgl2')

      GROUP BY left(js.tgl_mulai,7)";

      return $this->db->query($str)->result();
    }

}
