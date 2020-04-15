<?php
class Mmedia extends MY_Model {

	var $upload_path;
	var $upload_path_url;

	static $media_types = [
		1 => 'jpg',
		2 => 'png',
		3 => 'gif'
		];

	public function __construct() {
		parent::__construct();

		$this->upload_path = realpath(APPPATH.'../public_html/uploads');
		$this->upload_path_url = base_url().'public_html/uploads/';
		}

	function type_id($ext) {
		foreach(self::$media_types as $id => $value)
			if ($ext == $value) return $id;
		throw new Exception("Unsupported file ext: $ext");
		}

	function upload_to($id, $ext = 'jpg', $path=null) {
		if (!$path) $path = '/uploads';

		$config = [
		//	'max_size' => 2000,
			'allowed_types' => 'jpg|jpeg|gif|png',
			'overwrite' => TRUE,
			'file_name' => "$id.$ext",
			'upload_path' => realpath(APPPATH.'../public_html'.$path)
			];

		$this->load->library('upload', $config);
		$this->upload->do_upload();
		$image_data = $this->upload->data();
		return $config['file_name'];
		}

	function update_meta($meta) {
		$id = $meta['id'];
		if (!$this->_update($this->table_name, $id, $meta, FALSE)) $this->_throwError();
		}

	function do_upload($meta=[]) {
		if (count($_FILES)) {
			$ext = strtolower(pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION));
			if ($ext == 'jpeg') $ext = 'jpg';
			$meta['type_id'] = $this->type_id($ext);
			}
		if (!array_key_exists('id', $meta)) {
			$meta['debut'] = time();
			$success = $this->db->insert($this->table_name, $meta);
			if (!$success) $this->_throwError();
			$meta['id'] = $this->db->insert_id();
			}
		else $this->update_meta($meta);
		if (!count($_FILES)) return $meta; // 'update meta only' request

		$config = [
		//	'max_size' => 2000,
			'allowed_types' => 'jpg|jpeg|gif|png',
			'overwrite' => TRUE,
			'file_name' => "{$meta['id']}.$ext",
			'upload_path' => $this->upload_path
			];

		$this->load->library('upload', $config);
		$this->upload->do_upload();
		$image_data = $this->upload->data();
		$meta['ext'] = $ext;
		return $meta;

	/*****
		$config = array(
			'source_image' => $image_data['full_path'],
			'new_image' => $this->upload_path . '/thumbs',
			'maintain_ration' => true,
			'width' => 150,
			'height' => 100
			);

		$this->load->library('image_lib', $config);
		$this->image_lib->resize();
	*****/
		}

	public function add_exts(&$it) {
		foreach($it as &$m)
			$m['file'] = $m['id'].'.'.Mmedia::$media_types[$m['type_id']];
		return $it;
		}

	public function paginated_list($base_url, $first, $per_page = 10, $num_links = 5, $order_by = '', $where=[]) {
		$it = parent::paginated_list($base_url, $first, $per_page, $num_links, $order_by, $where);
		return $this->add_exts($it);
		}

	/**
	* get all media linked to specified object
	* sorted by sequence number
	*/
	public function get_object_media($otype, $oid) {
		$it = $this->db->select('m.*,m2o.seq')
			->where(['m2o.oid' => $oid, 'm2o.otype' => $otype])
			->join('media m', 'm2o.mid = m.id')
			->order_by('m2o.seq')
			->get('media2objs m2o')
			->result_array();

		return $this->add_exts($it);
		}

	/**
	* remove all records in 'media2objs' for the given object
	*/
	public function delete_object2media($otype,$oid) {
		$this->db->where(['otype'=>$otype, 'oid' => $oid])
				->delete('media2objs');
		}

	/**
	* insert records into 'media2objs' for the given object:
	* One per media id in the csv.
	*/
	public function insert_object2media($otype,$oid,$mid_csv) {
		if (strlen($mid_csv)) {
			$mids = explode(',', $mid_csv);
			$values = "";
			$seq = 1;
			foreach ($mids as $mid) {
				$values .= ",($otype,$oid,$seq,$mid)";
				$seq++;
				}
			$values = substr($values,1);

			$sql = "INSERT INTO `media2objs` (`otype`,`oid`,`seq`,`mid`) VALUES ".$values.';';
			return $this->db->query($sql);
			}
		else return 0;
		}

	/**
	* deletes then insert records into 'media2objs' for
	* the given object: One per media id in the csv.
	*/
	public function update_object_media($otype,$oid,$mid_csv) {
		$this->delete_object2media($otype,$oid);
		return $this->insert_object2media($otype,$oid,$mid_csv);
		}
	}