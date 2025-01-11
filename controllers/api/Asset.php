<?php defined('BASEPATH') OR exit('No direct script access allowed');

// require_once APPPATH . 'core/Ajax_controller.php';
class Asset { // extends Ajax_controller {
	

	function __construct() {
//		parent::__construct();
		log_message('debug', 'Asset ctor!');
		$method = $_SERVER['REQUEST_METHOD'];
		$this->_log_headers($method);
		header('Access-Control-Allow-Origin: *');
		if ($method == "OPTIONS") {
			header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
			die();
			}
		}

/***
$_SERVER:
    [REDIRECT_STATUS] => 200
    [HTTP_HOST] => thaidrills.local
    [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:134.0) Gecko/20100101 Firefox/134.0
    [HTTP_ACCEPT] => image/avif,image/webp,image/png,image/svg+xml,image/ *;q=0.8,* / *;q=0.5
    [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.5
    [HTTP_ACCEPT_ENCODING] => gzip, deflate
    [HTTP_CONNECTION] => keep-alive
    [HTTP_REFERER] => http://shanebow.local/
    [HTTP_PRIORITY] => u=5, i
    [HTTP_PRAGMA] => no-cache
    [HTTP_CACHE_CONTROL] => no-cache
    [PATH] => C:\Program Files (x86)\Common Files\Oracle\Java\java8path;C:\Program Files (x86)\Common Files\Oracle\Java\javapath;C:\ProgramData\Oracle\Java\javapath;C:\Program Files\NVIDIA\CUDNN\v9.4\bin;D:\bin\python\python3.11\Scripts\;D:\bin\python\python3.11\;d:\bin\gradle\gradle-8.10.2\bin;C:\Program Files\Java\jdk-17.0.12+7\bin;C:\WINDOWS\system32;C:\WINDOWS;C:\WINDOWS\System32\Wbem;C:\WINDOWS\System32\WindowsPowerShell\v1.0\;C:\WINDOWS\System32\OpenSSH\;C:\ProgramData\ComposerSetup\bin;C:\ProgramData\chocolatey\bin;C:\Program Files (x86)\NVIDIA Corporation\PhysX\Common;C:\Program Files\NVIDIA Corporation\NVIDIA NvDLISR;C:\Program Files\nodejs\;D:\bin\apache-maven-3.9.9\bin\;C:\Program Files\Git\cmd;C:\Program Files (x86)\Bitvise SSH Client;C:\WINDOWS\system32\config\systemprofile\AppData\Local\Microsoft\WindowsApps
    [SystemRoot] => C:\WINDOWS
    [COMSPEC] => C:\WINDOWS\system32\cmd.exe
    [PATHEXT] => .COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH;.MSC
    [WINDIR] => C:\WINDOWS
    [SERVER_SIGNATURE] => <address>Apache/2.4.46 (Win64) PHP/7.4.9 Server at thaidrills.local Port 80</address>

    [SERVER_SOFTWARE] => Apache/2.4.46 (Win64) PHP/7.4.9
    [SERVER_NAME] => thaidrills.local
    [SERVER_ADDR] => 127.0.0.1
    [SERVER_PORT] => 80
    [REMOTE_ADDR] => 127.0.0.1
    [DOCUMENT_ROOT] => D:/www/thaidrills/public_html
    [REQUEST_SCHEME] => http
    [CONTEXT_PREFIX] => 
    [CONTEXT_DOCUMENT_ROOT] => D:/www/thaidrills/public_html
    [SERVER_ADMIN] => wampserver@wampserver.invalid
    [SCRIPT_FILENAME] => D:/www/thaidrills/public_html/index.php
    [REMOTE_PORT] => 55981
    [REDIRECT_URL] => /api/asset/a/11/cream
    [REDIRECT_QUERY_STRING] => /api/asset/a/11/cream
    [GATEWAY_INTERFACE] => CGI/1.1
    [SERVER_PROTOCOL] => HTTP/1.1
    [REQUEST_METHOD] => GET
    [QUERY_STRING] => 
    [REQUEST_URI] => /api/asset/a/11/cream
    [SCRIPT_NAME] => /index.php
    [PHP_SELF] => /index.php
    [REQUEST_TIME_FLOAT] => 1736623949.3749
    [REQUEST_TIME] => 1736623949
***/
	private function _log_headers($method) {
		$fspec = realpath(APPPATH.'../public_html/assets/files').'/asset-log.csv';

		$now = time();
		$target = explode('/', $_SERVER['REQUEST_URI'],4)[3];
log_message('debug', "Asset log target: $target fspec: '$fspec'");
		$pre = $now.','.$method.','.$target;
		$out = fopen($fspec, 'a');
		foreach (getallheaders() as $name => $value) {
			fwrite($out, "$pre,$name,\"$value\"\n");
			}
		fclose($out);
		}

	private function _jsonOut($dat) {
		$method = $_SERVER['REQUEST_METHOD'];
		header('Access-Control-Allow-Origin: *');
		$it = json_encode($dat);
		header('Content-Type: application/json');
		exit($it);
		}

	private function _json_respond($code, $msg, $dat='') {
		$this->_jsonOut(['err'=>$code,'msg'=>$msg,'dat'=>$dat]);
		}

	public function get_log() {
		$fspec = realpath(APPPATH.'../public_html/assets/files').'/asset-log.csv';
		$dat = file_get_contents($fspec);
		$this->_json_respond(0, 'ok', explode("\n", $dat));
		}

	public function truncate_log() {
		$fspec = realpath(APPPATH.'../public_html/assets/files').'/asset-log.csv';
		file_put_contents($fspec, '');
		$this->_json_respond(0, 'log truncated', '');
		}

	private function _head($file) {
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
