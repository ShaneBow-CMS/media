<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Media extends MY_Controller {

	function __construct() {
		parent::__construct();
		}

	public function index() {
		$this->load->model(['mmedia','musers']);
		$data['title'] = 'Media Table';
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

	public function details($id='0') {
		$this->load->model(['mmedia','musers']);
		$data['title'] = 'Media Details';
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
		$data['title'] = 'Media';
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
		$this->mseeder->reseed_tables('Create Media Tables', [
			'media','media2objs'
			]);
		}

	}
