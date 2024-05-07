<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_pdf extends CI_Controller{

	public function __construct()
	{
		parent::__construct();
	}

  function index()
  {
		require('fpdf.php');

		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(40,10,'Hello World!');
		$pdf->Output('http://localhost/monev/');
  }

}
