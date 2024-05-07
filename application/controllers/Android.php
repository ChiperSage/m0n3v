<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Android extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('android_m');

		// $this->load->database();
		// $this->load->library(array('ion_auth','form_validation'));
		// $this->load->helper(array('url','language'));

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->lang->load('auth');

		// if(!$this->ion_auth->logged_in())
		// {
		// 	redirect('auth/login', 'refresh');
		// }
	}

  public function index()
	{
		// $this->data['inc'] = 'home_android';
		// $this->load->view('admin/index',$this->data);
	}

	public function _get_token()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://123.108.97.203/monevbpbj/public/api/post/auth_api?username=redha&password=bpbj1234",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "username=redha&password=bpbj1234#",
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/x-www-form-urlencoded",
		    "Postman-Token: 1b69a5a5-5bf6-4d05-83e1-71aa24fed66f",
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  // echo $response;
			$result = json_decode($response, true);
			return $result['success']['token'];
		}
	}

	public function send_rup1()
	{
		$file = $this->android_m->get_rup();
		$encode = json_encode($file);
		echo $encode;
	}

	public function send_rup()
	{
		$token = $this->_get_token();

		$file = $this->android_m->get_rup();
		$encode = json_encode($file);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://123.108.97.203/monevbpbj/public/api/post/data/paket_skpa_rup",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $encode,
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Accept-Encoding: gzip, deflate",
				"Authorization: Bearer " . $token,
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Content-Length: 13378",
				"Content-Type: application/x-www-form-urlencoded",
				"Host: 123.108.97.203",
				"Postman-Token: 0674b3da-b1ea-461a-b274-dcbbae77ca3a,02442f29-81bb-484f-b683-c9bf2a8dbd3d",
				"User-Agent: PostmanRuntime/7.16.3",
				"cache-control: no-cache"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$msg = "cURL Error #:" . $err;
		} else {
			$msg = $response;
		}

		$data['inc'] = 'home_msg';
		$data['msg'] = $msg;
		$data['btn'] = base_url('android');

		$this->load->view('admin/index',$data);

	}

	public function json_rup_tender_metode()
	{
		if(isset($_GET['X-API-KEY']) && $_GET['X-API-KEY'] == '6842cbf4ba070a2b5dbb1b45bd416664')
		{
			$file = $this->android_m->get_rup_tender_metode();
			$encode = json_encode($file);
			echo $encode;
		}
	}

	public function json_rup_non_tender_metode()
	{
		if(isset($_GET['X-API-KEY']) && $_GET['X-API-KEY'] == '6842cbf4ba070a2b5dbb1b45bd416664')
		{
			$file = $this->android_m->get_rup_non_tender_metode();
			$encode = json_encode($file);
			echo $encode;
		}
	}

	public function send_lelang1()
	{
		$file = $this->android_m->get_realisasi_lelang();
		$encode = json_encode($file);
		echo $encode;
	}

	public function send_lelang()
	{
		$token = $this->_get_token();

		$file = $this->android_m->get_realisasi_lelang();

		$encode = json_encode($file);

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://123.108.97.203/monevbpbj/public/api/post/data/paket_skpa",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encode,
		  CURLOPT_HTTPHEADER => array(
		    "Accept: application/json",
		    "Accept-Encoding: gzip, deflate",
		    "Authorization: Bearer " . $token,
		    "Cache-Control: no-cache",
		    "Connection: keep-alive",
		    "Content-Length: 16526",
		    "Content-Type: application/x-www-form-urlencoded",
		    "Host: 123.108.97.203",
		    "Postman-Token: 09159f6c-4fd2-4a7a-8a7e-7ecc25ef9866,05a019b9-6bad-4bbc-ac91-7e501c28b9e1",
		    "User-Agent: PostmanRuntime/7.16.3",
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$msg = "cURL Error #:" . $err;
		} else {
			$msg = $response;
		}

		$data['inc'] = 'home_msg';
		$data['msg'] = $msg;
		$data['btn'] = base_url('android');

		$this->load->view('admin/index',$data);

	}

	public function send_non_tender1()
	{
		$file = $this->android_m->get_realisasi_non_tender();
		$encode = json_encode($file);
		echo $encode;
	}

	public function send_non_tender()
	{
		$token = $this->_get_token();

		$file = $this->android_m->get_realisasi_non_tender();
		$encode = json_encode($file);

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://123.108.97.203/monevbpbj/public/api/post/data/paket_non_tender",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encode,
		  CURLOPT_HTTPHEADER => array(
		    "Accept: application/json",
		    "Accept-Encoding: gzip, deflate",
		    "Authorization: Bearer " . $token,
		    "Cache-Control: no-cache",
		    "Connection: keep-alive",
		    "Content-Length: 16526",
		    "Content-Type: application/x-www-form-urlencoded",
		    "Host: 123.108.97.203",
		    "Postman-Token: 09159f6c-4fd2-4a7a-8a7e-7ecc25ef9866,05a019b9-6bad-4bbc-ac91-7e501c28b9e1",
		    "User-Agent: PostmanRuntime/7.16.3",
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			$msg = "cURL Error #:" . $err;
		} else {
			$msg = $response;
		}

		$data['inc'] = 'home_msg';
		$data['msg'] = $msg;
		$data['btn'] = base_url('android');

		$this->load->view('admin/index',$data);

	}

	public function json_rup()
	{
		if(isset($_GET['X-API-KEY']) && $_GET['X-API-KEY'] == '6842cbf4ba070a2b5dbb1b45bd416664')
		{
			$file = $this->android_m->get_rup();
			$encode = json_encode($file);
			echo $encode;
		}
	}

	public function json_lelang()
	{
		if(isset($_GET['X-API-KEY']) && $_GET['X-API-KEY'] == '6842cbf4ba070a2b5dbb1b45bd416664')
		{
			$file = $this->android_m->get_realisasi_lelang();
			$encode = json_encode($file);
			echo $encode;
		}
	}

	public function json_non_tender()
	{
		if(isset($_GET['X-API-KEY']) && $_GET['X-API-KEY'] == '6842cbf4ba070a2b5dbb1b45bd416664')
		{
			$file = $this->android_m->get_realisasi_non_tender();
			$encode = json_encode($file);
			echo $encode;
		}
	}

}
