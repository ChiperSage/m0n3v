<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('kinerja_tender'))
{
    function kinerja_tender($nilai)
    {
        if ($nilai == " " || $nilai == null){
            return "Belum di Nilai";
        }elseif($nilai >= 0.00 && $nilai < 1.00) {
            return "Buruk";
        } elseif ($nilai >= 1.00 && $nilai < 2.00) {
            return "Cukup";
        } elseif ($nilai >= 2.00 && $nilai < 3.00) {
            return "Baik";
        } elseif ($nilai >= 3.00) {
            return "Sangat baik";
      
        }
    }
}