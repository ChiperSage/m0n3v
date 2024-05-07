<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mpdf extends CI_Controller {

	public function index()
	{
		require_once './vendor/autoload.php';

		$mpdf = new \Mpdf\Mpdf(['tempDir' => 'mpdf/tmp']);
		$mpdf->WriteHTML('...');
		$mpdf->Output();
	}
}
