<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Qmaker extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

  public function index()
  {
    //echo '<embed src="http://123.108.97.215/bpbj/test/qrcode" width="100%"height="100%"></embed>';

    $id = $this->session->userdata('user_id');
    $paraf = $this->db->get_where('users',array('id'=>$id))->row('paraf');
  }

  public function show($text)
  {
    $this->load->library('ciqrcode');

    header("Content-Type: image/png");
    $params['data'] = $text;
    $params['level'] = 'H';

    $this->ciqrcode->generate($params);
  }

  public function show_sp($text)
  {
    $this->load->library('ciqrcode');

    header("Content-Type: image/png");
    $params['level'] = 'H';
    $params['data'] = $text;

    $this->ciqrcode->generate($params);
  }

  public function _generate_qrcode()
  {
    $this->load->library('ciqrcode');

    header("Content-Type: image/png");
    $params['data'] = 'This is a text to encode become QR Code';
    $params['savename'] = FCPATH.'files/tes.png';
    $this->ciqrcode->generate($params);
  }

  public function genqrcode()
  {
    use Endroid\QrCode\QrCode;

    $qrCode = new QrCode();
    $qrCode
        ->setText('Life is too short to be generating QR codes')
        ->setSize(300)
        ->setPadding(10)
        ->setErrorCorrection('high')
        ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
        ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
        ->setLabel('Scan the code')
        ->setLabelFontSize(16)
        ->setImageType(QrCode::IMAGE_TYPE_PNG)
    ;

    // now we can directly output the qrcode
    header('Content-Type: '.$qrCode->getContentType());
    $qrCode->render();

    // or create a response object
    $response = new Response($qrCode->get(), 200, array('Content-Type' => $qrCode->getContentType()));
  }

}
