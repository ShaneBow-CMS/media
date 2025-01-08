<?php defined('BASEPATH') OR exit('No direct script access allowed');

// require_once APPPATH . 'core/Ajax_controller.php';
class Asset { // extends Ajax_controller {

	function __construct() {
//		parent::__construct();
//		log_message('info', 'Asset ctor: '.current_url());
		log_message('debug', 'Asset ctor!');
		$method = $_SERVER['REQUEST_METHOD'];
		header('Access-Control-Allow-Origin: *');
		if ($method == "OPTIONS") {
			header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
			die();
			}
		}

	public function index() {
		$this->load->model(['mmedia','musers']);
		$data['title'] = 'Asset Table';
		try {
			$order_by = 'id';
			$where = [];
			$imgs = $this->mmedia->paginated_list('/media/index',
				$this->uri->segment(3), 20, 5, $order_by, $where);
			}
		catch (Exception $e) {$imgs = [];}
		$data['media'] = $imgs;
		$this->load->view('admin/cms-media-list', $data);
		}

	private function _respond($file) {
		$filespec = realpath(APPPATH.'../public_html/uploads/'.$file);
		log_message('debug', "_respond($file): $filespec");
/**************
	if (!file_exists($filespec)) {
		header( "HTTP/1.0 404 Not Found");
		header("Content-type: image/jpeg");
		header('Content-Length: ' . filesize("404_files.jpg"));
		header("Accept-Ranges: bytes");
		header("Last-Modified: Fri, 03 Mar 2004 06:32:31 GMT");
		readfile("404_files.jpg");
		}
**************/
		header('Content-Type: ' . mime_content_type($filespec));
	// header('Content-Type: image/png');

	//	header('Content-Disposition: filename=photo.'.$extension);
		header('Content-Disposition: filename='.$file);
	//	header('Content-Transfer-Encoding: binary');
	//	header('Expires: 0');	//	header('Content-Transfer-Encoding: binary');
	//	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Length: ' . filesize($filespec));
		// optional use passthru to save resources
		// passthru('cat '.$filespec);
		readfile($filespec);
		exit;
		}

	// return user avatar
	public function a($id='0',$token) {
		return $this->_respond("users/$id.jpg");
		}

	// return mp4
	public function v($id='0',$token) {
		return $this->_respond("vids/$id.mp4");
		}

	public function course($id='0',$token) {
		$name = 'users/dicky.png';
		$filespec = realpath(APPPATH.'../public_html/uploads/'.$name);
		log_message('debug', "/api/asset/course/$id/$token: $filespec"); // .current_url());
/******
		$method = $_SERVER['REQUEST_METHOD'];
		header('Access-Control-Allow-Origin: *');
		if ($method == "OPTIONS") {
			header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
			die();
			}
******/
/**************
	if (!file_exists($filespec)) {
		header( "HTTP/1.0 404 Not Found");
		header("Content-type: image/jpeg");
		header('Content-Length: ' . filesize("404_files.jpg"));
		header("Accept-Ranges: bytes");
		header("Last-Modified: Fri, 03 Mar 2004 06:32:31 GMT");
		readfile("404_files.jpg");
		}
**************/
	//	header('Content-Type: ' . mime_content_type($filespec));
	header('Content-Type: image/png');

	//	header('Content-Disposition: filename=photo.'.$extension);
		header('Content-Disposition: filename=$filename');
	//	header('Content-Transfer-Encoding: binary');
	//	header('Expires: 0');	//	header('Content-Transfer-Encoding: binary');
	//	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Length: ' . filesize($filespec));
		// optional use passthru to save resources
		// passthru('cat '.$filespec);
		readfile($filespec);
		exit;
		}

	public function details($id='0') {
		$this->load->model(['mmedia','musers']);
		$data['title'] = 'Asset Details';
		$data['icon'] = 'image2';
		try {
			$it = $this->mmedia->get($id);
			if (!$it) throw new Exception('not found');
			$owner_id = $it['owner_id'];
			$data['lead'] = $it['title'];
			$data['m'] = $it;
			$data['profile'] = $this->musers->get_profile($owner_id);
			$this->def_view('media-details', $data);
			}
		catch (Exception $e) { return show_404(current_url(), FALSE); }
		}

	public function list_by_owner($owner_id='u7') {
		$owner_id = substr($owner_id,1);
		$this->load->model(['mmedia','musers']);
		$data['title'] = 'Asset';
		$data['icon'] = 'images3';
		try {
			$order_by = 'debut DESC';
			$where = ['owner_id' => $owner_id];
			$imgs = $this->mmedia->paginated_list('/media/list_by_owner/u'.$owner_id,
				$this->uri->segment(4), 11, 5, $order_by, $where);
			$data['profile'] = $this->musers->get_profile($owner_id);
			$data['lead'] = 'Contributed by '.$data['profile']['nickname'];
			}
		catch (Exception $e) {$imgs = [];}
		$data['media'] = $imgs;
		$this->def_view('user-media', $data);
		}

	/**
	* ajax call to fetch paged images
	* DEPRECATED - use fetch2
	*/
	public function fetch() {
		$this->load->helper('ajax');
		if (!$this->_usr)
			respond(EXIT_ERR_LOGIN, "Log in required", '/user/login');

		$this->load->model('mmedia');
		try {
			$order_by = 'debut DESC';
			$where = [];
			$dat['imgs'] = $this->mmedia->paginated_list('/media/fetch', $this->uri->segment(3),
			                                             25, 5, $order_by, $where);
			$dat['page'] = $this->pagination->create_links();
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $dat);
		}

	/**
	* ajax call to fetch paged images
	* works with PagedList.js
	*/
	public function fetch2() {
		$this->load->helper('ajax');
		if (!$this->_usr)
			respond(EXIT_ERR_LOGIN, "Log in required", '/user/login');

		$per_page = intval($_POST['per_page']);
		$where = [];
		if (isset($_POST['like'])) {
			$term = $_POST['like'];
			if ($term[0] == '#')
				$where = ['id' => substr($term, 1)];
			else
				$where = ['title LIKE' => "'%$term%'"];
			}
		$this->load->model('mmedia');
		try {
			$order_by = 'debut DESC';
			$dat['items'] = $this->mmedia->paginated_list('/media/fetch2', $this->uri->segment(3),
			                                             $per_page, 5, $order_by, $where);
			$dat['page'] = $this->pagination->create_links();
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $dat);
		}

	/**
	* ajax call to upload an image
	*/
	public function upload_single() {
		// log_message('debug', '_FILES'.dump($_FILES));
		unset($_POST['null']); // ?? the submit button
		$this->load->helper('ajax');
		$usr = $this->session->userdata('usr');
		if (!$this->_usr)
			dieLoginRequired('/user/login',403);
		$this->load->library('form_validation');
		if ($this->form_validation->run('media-upload') == FALSE)
			dieInvalidFields(406);

		$this->load->model('mmedia');
		$meta = $_POST;
		$meta['owner_id'] = $usr['id'];
		$updating = array_key_exists('id', $meta);

		try {
			$dat = $this->mmedia->do_upload($meta);
			respond(0, $updating?'updated' : 'ok',$dat);
			}
		catch (Exception $e) {db_error($e->getMessage(), 400);}
		}

	/**
	* ajax call to upload an image
	* used in cms.js line 327
	*/
	public function upload_special() {
		// log_message('debug', '_FILES'.dump($_FILES));
		unset($_POST['null']); // ?? the submit button
		$this->load->helper('ajax');
		$usr = $this->session->userdata('usr');
		if (!$this->_usr)
			dieLoginRequired('/user/login',403);
		/***
jsonDie(1,'upload_special not implemented');
		$this->load->library('form_validation');
		if ($this->form_validation->run('media-upload') == FALSE)
			dieInvalidFields(406);
		***/

		$this->load->model('mmedia');
		$id = $_POST['id'];
		$path = $_POST['path'];

		try {
			$fname = $this->mmedia->upload_to($id, 'jpg', $path);
			respond(0, "$path/$fname",$id);
			}
		catch (Exception $e) {db_error($e->getMessage(), 400);}
		}

	public function download_brochure() {
		$this->load->helper('download');
		$data = file_get_contents("assets/files/AlasNoMe-Brochure.pdf");
		force_download('AlasNoMe-Brochure.pdf', $data);
		}

	/**
	* admin method to create & charge the media db tables
	*/
	public function seed() {
		$this->require_role(ROLE_ADMIN);
		$this->load->model('mseeder');
		$this->mseeder->reseed_tables('Create Asset Tables', [
			'media','media2objs'
			]);
		}

	}
