<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	if ( ! function_exists('tanggal_indo'))
	{
	    function tanggal_indo($tanggal)
	    {
				$arr1 = array('January','February','March','April','May','June','July','August','September','October','November','December');
				$arr2 = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');

				return str_replace($arr1,$arr2,$tanggal);

		        // switch ($bln)
		        // {
		        //     case "January":
		        //         return "Januari";
		        //         break;
		        //     case "February":
		        //         return "Februari";
		        //         break;
		        //     case "March":
		        //         return "Maret";
		        //         break;
		        //     case "April":
		        //         return "April";
		        //         break;
		        //     case "May":
		        //         return "Mei";
		        //         break;
		        //     case "June":
		        //         return "Juni";
		        //         break;
		        //     case "July":
		        //         return "Juli";
		        //         break;
		        //     case "August":
		        //         return "Agustus";
		        //         break;
		        //     case "September":
		        //         return "September";
		        //         break;
		        //     case "October":
		        //         return "Oktober";
		        //         break;
		        //     case "November":
		        //         return "November";
		        //         break;
		        //     case "December":
		        //         return "Desember";
		        //         break;
		        // }
	    }

		function hari_indo($hari)
	    {
				$arr1 = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
				$arr2 = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu',);

				return str_replace($arr1,$arr2,$hari);
	    }
	}
