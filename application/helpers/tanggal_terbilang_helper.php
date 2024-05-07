<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	if ( ! function_exists('tanggal_terbilang'))
	{
      // var = date('l-d-m-Y')
      // tanggal sekarang terbilang

	    function tanggal_terbilang($ldmy)
	    {
        if($ldmy == 'l'){
          $var = date('l');
          $hari1 = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    			$hari2 = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
    			return str_replace($hari1, $hari2, $var);
        }

        if($ldmy == 'd'){
          $var = date('d');
          $tanggal1 = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23',
          '24','25','26','27','28','29','30','31');
    			$tanggal2 = array('Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas','Dua Belas',
          'Tiga Belas','Empat Belas','Lima Belas','Enam Belas','Tujuh Belas','Delapan Belas','Sembilan Belas','Dua Puluh',
          'Dua Puluh Satu','Dua Puluh Dua','Dua Puluh Tiga','Dua Puluh Empat','Dua Puluh Lima','Dua Puluh Enam','Dua Puluh Tujuh',
          'Dua Puluh Delapan','Dua Puluh Sembilan','Tiga Puluh','Tiga Puluh Satu');
    			return str_replace($tanggal1,$tanggal2,$var);
        }

        if($ldmy == 'm'){
          $var = date('m');
          $bulan1 = array('01','02','03','04','05','06','07','08','09','10','11','12');
  				$bulan2 = array('Januari','Februari','Maret','April','Juni','Juli','Agustus','September','Oktober','November','Desember');
          return str_replace($bulan1,$bulan2,$var);
        }

        if($ldmy == 'Y'){
          $var = date('Y');
          $tahun1 = array('2020','2021','2023');
    			$tahun2 = array('Dua Ribu Dua Puluh','Dua Ribu Dua Puluh Satu','Dua Ribu Dua Puluh Dua');
          return str_replace($tahun1, $tahun2, $var);
        }

	    }
	}
