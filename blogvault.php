<?php
/*
Plugin Name: Backup Plugin bv blogVault
Plugin URI: http://blogvault.net/
Description: Easiest way to backup your blog
Author: akshat
Author URI: http://blogvault.net/
Version: 1.08
 */

/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Global response array */
global $bvVersion;
global $blogvault;
global $bvDynamicEvents;
$bvVersion = '1.08';

if (is_admin())
	require_once dirname( __FILE__ ) . '/admin.php';


if ( !function_exists('bvActivateHandler') ) :
	function bvActivateHandler() {
		global $blogvault;
		##BVKEYSLOCATE##
		if ($blogvault->getOption('bvPublic')) {
			$blogvault->updateOption('bvLastSendTime', time());
			$blogvault->updateOption('bvLastRecvTime', 0);
			$blogvault->activate();
		} else {
			$blogvault->updateOption('bvActivateRedirect', true);
		}
	}
	register_activation_hook(__FILE__, 'bvActivateHandler');
endif;

if ( !function_exists('bvDeactivateHandler') ) :
	function bvDeactivateHandler() {
		global $blogvault;
		$body = array();
		$body['wpurl'] = urlencode(get_bloginfo("wpurl"));
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$resp = $clt->post($blogvault->getUrl("deactivate"), array(), $body);
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}
	register_deactivation_hook(__FILE__, 'bvDeactivateHandler');
endif;

if (!class_exists('BVHttpClient')) {
class BVHttpClient {
	var $user_agent = 'BVHttpClient';
	var $host;
	var $port;
	var $timeout = 20;
	var $errormsg = "";
	var $conn;
	var $mode;

	function BVHttpClient() {
		global $blogvault;
		$sno = "";
		if (array_key_exists('svrno', $_REQUEST)) {
			$sno = intval($_REQUEST['svrno']);
			if (array_key_exists('mode', $_REQUEST)) {
				$this->mode = $_REQUEST['mode'];
			} else {
				$this->mode = "req";
			}
			if ($this->mode === "resp") {
				$blogvault->addStatus("mode", "resp");
				return;
			}
		} else {
			$this->timeout = 5;
			$sno = $blogvault->getOption('bvServerId');
			if (empty($sno)) {
				$sno = "1";
			}
		}
		$this->host = "pluginapi".$sno.".blogvault.net";
		$this->port = 80;
		if (array_key_exists('ssl', $_REQUEST)) {
			$this->port = 443;
			$this->host = $_REQUEST['ssl']."://".$host;
		}
		if (!$this->conn = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout)) {
			$this->errormsg = "Cannot Open Connection to Host";
			$blogvault->addStatus("httperror", "Cannot Open Connection to Host");
			return;
		}
		socket_set_timeout($this->conn, $this->timeout);
	}

	function streamedPost($url, $headers = array()) {
		$headers['Transfer-Encoding'] = "chunked";
		$this->sendRequest("POST", $url, $headers);
	}

	function newChunk($data) {
		if ($this->mode === "resp") {
			echo("bvchunk:");
		}
		$this->write(sprintf("%x\r\n", strlen($data)));
		$this->write($data);
		$this->write("\r\n");
	}

	function closeChunk() {
		$this->newChunk("");
	}

	function uploadChunkedFile($url, $field, $filename)
	{
		$this->multipartChunkedPost($url, array("Content-Disposition" => "form-data; name=\"".$field."\"; filename=\"".$filename."\"", "Content-Type" => "application/octet-stream"));
	}

	function multipartChunkedPost($url, $mph = array(), $headers = array()) {
		$rnd = rand(100000, 999999);
		$this->boundary = "----".$rnd;
		$prologue = "--".$this->boundary."\r\n";
		foreach($mph as $key=>$val) {
			$prologue .= $key.":".$val."\r\n";
		}
		$prologue .= "\r\n";
		$epilogue = "\r\n\r\n--".$this->boundary."--\r\n";
		$headers['Content-Type'] = "multipart/form-data; boundary=".$this->boundary;
		$this->streamedPost($url, $headers);
		$this->newChunk($prologue);
	}

	function newChunkedPart($data) {
		if (strlen($data) > 0)
			$this->newChunk($data);
	}

	function closeChunkedPart() {
		$epilogue = "\r\n\r\n--".$this->boundary."--\r\n";
		$this->newChunk($epilogue);
		$this->closeChunk();
	}

	function write($data) {
		if ($this->mode === "resp") {
			echo($data);
		} else {
			fwrite($this->conn, $data);
		}
	}

	function get($url, $headers = array()) {
		return $this->request("GET", $url, $headers);
	}

	function post($url, $headers = array(), $body = "") {
		if(is_array($body)) {
			$b = "";
			foreach($body as $key=>$val) {
				$b .= $key."=".urlencode($val)."&";
			}
			$body = substr($b, 0, strlen($b) - 1);
		}
		if ($this->mode === "resp") {
			$this->newChunk("bvpost:".$body);
		}
		return $this->request("POST", $url, $headers, $body);
	}

	function request($method, $url, $headers = array(), $body = null) {
		$this->sendRequest($method, $url, $headers, $body);
		return $this->getResponse();
	}

	function sendRequest($method, $url, $headers = array(), $body = null) {
		if ($this->mode === "resp") {
			return;
		}
		$def_hdrs = array("Connection" => "keep-alive",
			"Host" => $this->host);
		$headers = array_merge($def_hdrs, $headers);
		$request = strtoupper($method)." ".$url." HTTP/1.1\r\n";
		if (null != $body) {
			$headers["Content-length"] = strlen($body);
		} else {
			$headers["Content-length"] = 0;
		}
		foreach($headers as $key=>$val) {
			$request .= $key.":".$val."\r\n";
		}
		$request .= "\r\n";
		if (null != $body) {
			$request .= $body;
		}
		$this->write($request);
		return $request;
	}

	function getResponse() {
		global $blogvault;
		$response = array();
		$response['headers'] = array();
		$state = 1;
		$conlen = 0;
		if ($this->mode === "resp") {
			return $response;
		}
		stream_set_timeout($this->conn, 300);
		while (!feof($this->conn)) {
			$line = fgets($this->conn, 4096);
			if (1 == $state) {
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					$response['errormsg'] = "Status code line invalid: ".htmlentities($line);
					return $response;
				}
				$response['http_version'] = $m[1];
				$response['status'] = $m[2];
				$response['status_string'] = $m[3];
				$state = 2;
				$blogvault->addStatus("respstatus", $response['status']);
				$blogvault->addStatus("respstatus_string", $response['status_string']);
			} else if (2 == $state) {
				# End of headers
				if (2 == strlen($line)) {
					if ($conlen > 0)
						$response['body'] = fread($this->conn, $conlen);
					return $response;
				}
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					// Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				$response['headers'][$key] = $val;
				if ($key == "content-length") {
					$conlen = intval($val);
				}
			}
		}
		return $response;
	}
}
} // Class exists


if (!class_exists('BlogVault')) {
class BlogVault {

	private static $instance = NULL; 
	public $status;

	static public function getInstance() {
		if (self::$instance === NULL)
			self::$instance = new BlogVault();
		return self::$instance;
	}

	function BlogVault() {
		$this->status = array("blogvault" => "response");
	}

	function addStatus($key, $value) {
		$this->status[$key] = $value;
	}
	
	function addArrayToStatus($key, $value) {
		if (!isset($this->status[$key])) {
			$this->status[$key] = array();
		}
		$this->status[$key][] = $value;
	}

	function terminate() {
		die("bvbvbvbvbv".serialize($this->status)."bvbvbvbvbv");
		exit;
	}

	function getUrl($method) {
		global $bvVersion;
		$baseurl = "/bvapi/";
		$time = time();
		if ($time < $this->getOption('bvLastSendTime')) {
			$time = $this->getOption('bvLastSendTime') + 1;
		}
		$this->updateOption('bvLastSendTime', $time);
		$public = urlencode($this->getOption('bvPublic'));
		$secret = urlencode($this->getOption('bvSecretKey'));
		$time = urlencode($time);
		$version = urlencode($bvVersion);
		$sig = md5($public.$secret.$time.$version);
		return $baseurl.$method."?sig=".$sig."&bvTime=".$time."&bvPublic=".$public."&bvVersion=".$version;
	}

	function scanFiles($initdir = "./", $offset = 0, $limit = 0, $bsize = 512) {
		$i = 0;
		$j = 0;
		$dirs = array();
		$dirs[] = $initdir;
		$j++;
		$bfc = 0;
		$bfa = array();
		$current = 0;
		$recurse = true;
		if (array_key_exists('recurse', $_REQUEST) && $_REQUEST["recurse"] == "false") {
			$recurse = false;
		}
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("listfiles")."&recurse=".$_REQUEST["recurse"]."&offset=".$offset."&initdir=".urlencode($initdir), "fileslist", "allfiles");
		while ($i < $j) {
			$dir = $dirs[$i];
			$d = @opendir(ABSPATH.$dir);
			if ($d) {
				while (($file = readdir($d)) !== false) {
					if ($file == '.' || $file == '..') { continue; }
						$relfile = $dir.$file;
					$absfile = ABSPATH.$relfile;
					if (is_dir($absfile)) {
						if (is_link($absfile)) { continue; }
							$dirs[] = $relfile."/";
						$j++;
					}
					$stats = @stat($absfile);
					$fdata = array();
					if (!$stats)
						continue;
					$current++;
					if ($offset >= $current)
						continue;
					if (($limit != 0) && (($current - $offset) > $limit)) {
						$i = $j;
						break;
					}
					foreach(preg_grep('#size|uid|gid|mode|mtime#i', array_keys($stats)) as $key ) {
						$fdata[$key] = $stats[$key];
					}

					$fdata["filename"] = $relfile;
					if (($fdata["mode"] & 0xF000) == 0xA000) {
						$fdata["link"] = @readlink($absfile);
					}
					$bfa[] = $fdata;
					$bfc++;
					if ($bfc == 512) {
						$str = serialize($bfa);
						$clt->newChunkedPart(strlen($str).":".$str);
						$bfc = 0;
						$bfa = array();
					}
				}
				closedir($d);
			}
			$i++;
			if ($recurse == false)
				break;
		}
		if ($bfc != 0) {
			$str = serialize($bfa);
			$clt->newChunkedPart(strlen($str).":".$str);
		}
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	function getValidFiles($files)
	{
		$outfiles = array();
		foreach($files as $file) {
			if (!file_exists($file) || !is_readable($file) ||
				(!is_file($file) && !is_link($file))) {
					$this->addArrayToStatus("missingfiles", $file);
					continue;
				}
			$outfiles[] = $file;
		}
		return $outfiles;
	}

	function fileStat($file) {
		$stats = @stat(ABSPATH.$file);
		$fdata = array();
		foreach(preg_grep('#size|uid|gid|mode|mtime#i', array_keys($stats)) as $key ) {
			$fdata[$key] = $stats[$key];
		}

		$fdata["filename"] = $file;
		return $fdata;
	}

	function fileMd5($files, $offset = 0, $limit = 0, $bsize = 102400) {
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("filesmd5")."&offset=".$offset, "filemd5", "list");
		$files = $this->getValidFiles($files);
		foreach($files as $file) {
			$fdata = array();
			$fdata = $this->fileStat($file);
			$_limit = $limit;
			$_bsize = $bsize;
			if (!file_exists(ABSPATH.$file)) {
				$this->addArrayToStatus("missingfiles", $file);
				continue;
			}
			if ($offset == 0 && $_limit == 0) {
				$md5 = md5_file(ABSPATH.$file);
			} else {
				if ($_limit == 0)
					$_limit = $fdata["size"];
				if ($offset + $_limit < $fdata["size"])
					$_limit = $fdata["size"] - $offset;
				$handle = fopen(ABSPATH.$file, "rb");
				$ctx = hash_init('md5');
				fseek($handle, $offset, SEEK_SET);
				$dlen = 1;
				while (($_limit > 0) && ($dlen > 0)) {
					if ($_bsize > $_limit)
						$_bsize = $_limit;
					$d = fread($handle, $_bsize);
					$dlen = strlen($d);
					hash_update($ctx, $d);
					$_limit -= $dlen;
				}
				fclose($handle);
				$md5 = hash_final($ctx);
			}
			$fdata["md5"] = $md5;
			$sfdata = serialize($fdata);
			$clt->newChunkedPart(strlen($sfdata).":".$sfdata);
		}
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}

		return true;
	}

	function uploadFiles($files, $offset = 0, $limit = 0, $bsize = 102400) {
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("filedump")."&offset=".$offset, "filedump", "data");

		foreach($files as $file) {
			if (!file_exists(ABSPATH.$file)) {
				$this->addArrayToStatus("missingfiles", $file);
				continue;
			}
			$handle = fopen(ABSPATH.$file, "rb");
			if (($handle != null) && is_resource($handle)) {
				$fdata = $this->fileStat($file);
				$sfdata = serialize($fdata);
				$_limit = $limit;
				$_bsize = $bsize;
				if ($_limit == 0)
					$_limit = $fdata["size"];
				if ($offset + $_limit > $fdata["size"])
					$_limit = $fdata["size"] - $offset;
				$clt->newChunkedPart(strlen($sfdata).":".$sfdata.$_limit.":");
				fseek($handle, $offset, SEEK_SET);
				$dlen = 1;
				while (($_limit > 0) && ($dlen > 0)) {
					if ($_bsize > $_limit)
						$_bsize = $_limit;
					$d = fread($handle, $_bsize);
					$dlen = strlen($d);
					$clt->newChunkedPart($d);
					$_limit -= $dlen;
				}
				fclose($handle);
			} else {
				$this->addArrayToStatus("unreadablefiles", $file);
			}
		}
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	/* This informs the server about the activation */
	function activate() {
		global $wpdb;
		global $bvVersion;
		global $blogvault;
		$body = array();
		$body['wpurl'] = urlencode(get_bloginfo("wpurl"));
		$body['abspath'] = urlencode(ABSPATH);
		if (defined('DB_CHARSET'))
			$body['dbcharset'] = urlencode(DB_CHARSET);
		if ($wpdb->base_prefix) {
			$body['dbprefix'] = urlencode($wpdb->base_prefix);
		} else {
			$body['dbprefix'] = urlencode($wpdb->prefix);
		}
		$body['bvversion'] = urlencode($bvVersion);
		$body['serverip'] = urlencode($_SERVER['SERVER_ADDR']);
		$body['dynsync'] = urlencode($blogvault->getOption('bvDynSyncActive'));
		$body['woodyn'] = urlencode($blogvault->getOption('bvWooDynSync'));
		if (extension_loaded('openssl')) {
			$body['openssl'] = "1";
		}
		if (function_exists('is_ssl') && is_ssl()) {
			$body['https'] = "1";
		}
		$all_tables = $this->getAllTables();
		$i = 0;
		foreach ($all_tables as $table) {
			$body["all_tables[$i]"] = urlencode($table);
			$i++;
		}

		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$resp = $clt->post($this->getUrl("activate"), array(), $body);
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	function listTables() {
		global $wpdb;

		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("listtables"), "tableslist", "status");
		$data["listtables"] = $wpdb->get_results( "SHOW TABLE STATUS", ARRAY_A);
		$data["tables"] = $wpdb->get_results( "SHOW TABLES", ARRAY_N);
		$str = serialize($data);
		$clt->newChunkedPart(strlen($str).":".$str);
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	function tableKeys($table) {
		global $wpdb, $blogvault;
		$info = $wpdb->get_results("SHOW KEYS FROM $table;", ARRAY_A);
		$blogvault->addStatus("table_keys", $info);
		return true;
	}

	function describeTable($table) {
		global $wpdb, $blogvault;
		$info = $wpdb->get_results("DESCRIBE $table;", ARRAY_A);
		$blogvault->addStatus("table_description", $info);
		return true;
	}

	function checkTable($table, $type) {
		global $wpdb, $blogvault;
		$info = $wpdb->get_results("CHECK TABLE $table $type;", ARRAY_A);
		$blogvault->addStatus("status", $info);
		return true;
	}

	function tableInfo($tbl, $offset = 0, $limit = 0, $bsize = 512, $filter = "") {
		global $wpdb;

		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("tableinfo")."&offset=".$offset, "tablename", $tbl);
		$str = "SHOW CREATE TABLE " . $tbl . ";";
		$create = $wpdb->get_var($str, 1);
		$rows_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$tbl);
		$data = array();
		$data["create"] = $create;
		$data["count"] = intval($rows_count);
		$data["encoding"] = mysql_client_encoding();
		$str = serialize($data);
		$clt->newChunkedPart(strlen($str).":".$str);

		if ($limit == 0) {
			$limit = $rows_count;
		}
		$srows = 1;
		while (($limit > 0) && ($srows > 0)) {
			if ($bsize > $limit)
				$bsize = $limit;
			$rows = $wpdb->get_results("SELECT * FROM $tbl $filter LIMIT $bsize OFFSET $offset", ARRAY_A);
			$srows = sizeof($rows);
			$data = array();
			$data["table"] = $tbl;
			$data["offset"] = $offset;
			$data["size"] = $srows;
			$data["md5"] = md5(serialize($rows));
			$str = serialize($data);
			$clt->newChunkedPart(strlen($str).":".$str);
			$offset += $srows;
			$limit -= $srows;
		}
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	function uploadRows($tbl, $offset = 0, $limit = 0, $bsize = 512, $filter = "") {
		global $wpdb;
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		$clt->uploadChunkedFile($this->getUrl("uploadrows")."&offset=".$offset, "tablename", $tbl);

		if ($limit == 0) {
			$limit = $wpdb->get_var("SELECT COUNT(*) FROM ".$tbl);
		}
		$srows = 1;
		while (($limit > 0) && ($srows > 0)) {
			if ($bsize > $limit)
				$bsize = $limit;
			$rows = $wpdb->get_results("SELECT * FROM $tbl $filter LIMIT $bsize OFFSET $offset", ARRAY_A);
			$srows = sizeof($rows);
			$data = array();
			$data["offset"] = $offset;
			$data["size"] = $srows;
			$data["rows"] = $rows;
			$data["md5"] = md5(serialize($rows));
			$str = serialize($data);
			$clt->newChunkedPart(strlen($str).":".$str);
			$offset += $srows;
			$limit -= $srows;
		}
		$clt->closeChunkedPart();
		$resp = $clt->getResponse();
		if (array_key_exists('status', $resp) && ($resp['status'] != '200')) {
			return false;
		}
		return true;
	}

	function updateKeys($publickey, $secretkey) {
		$this->updateOption('bvPublic', $publickey);
		$this->updateOption('bvSecretKey', $secretkey);
	}

	function updateOption($key, $value) {
		if ($this->isMultisite()) {
			update_blog_option(1, $key, $value);
		} else {
			update_option($key, $value);
		}
	}

	function getOption($key) {
		if ($this->isMultisite()) {
			return get_blog_option(1, $key);
		} else {
			return get_option($key);
		}
	}

	function getAllTables() {
		global $wpdb;
		$all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
		$all_tables = array_map(create_function('$a', 'return $a[0];'), $all_tables);
		return $all_tables;
	}


	/* Control Channel */
	function authenticateControlRequest() {
		$secret = urlencode($this->getOption('bvSecretKey'));
		$method = $_REQUEST['bvMethod'];
		$sig = $_REQUEST['sig'];
		$time = intval($_REQUEST['bvTime']);
		$version = $_REQUEST['bvVersion'];
		if ($time < intval($this->getOption('bvLastRecvTime')) - 300) {
			return false;
		}
		if (md5($method.$secret.$time.$version) != $sig) {
			return false;
		}
		$this->updateOption('bvLastRecvTime', $time);
		return true;
	}

	function isMultisite() {
		if (function_exists('is_multisite'))
			return is_multisite();
		return false;
	}

	function isMainSite() {
		if (!function_exists('is_main_site' ) || !$this->isMultisite())
			return true;
		return is_main_site();
	}

	function uploadPath() {
		$dir = wp_upload_dir();

		return $dir['basedir'];
	}

}
$blogvault = BlogVault::getInstance();
} // class exists

if (!class_exists('BVDynamicBackup')) {
class BVDynamicBackup {
	function BVDynamicBackup() {
		$this->add_actions_and_listeners();
		$this->reset_events();
	}

	function &init() {
		static $instance = false;
		if (!$instance) {
			$instance = new BVDynamicBackup();
		}
		return $instance;
	}

	function reset_events() {
		global $bvDynamicEvents;
		$bvDynamicEvents = array();
	}

	function send_updates() {
		global $bvDynamicEvents, $blogvault;
		if (count($bvDynamicEvents) == 0) {
			return true;
		}
		$clt = new BVHttpClient();
		if (strlen($clt->errormsg) > 0) {
			return false;
		}
		if ($blogvault->isMultisite()) {
			$site_id = get_current_blog_id();
		} else {
			$site_id = 1;
		}
		$timestamp = gmmktime();
		// Should we do a GET to bypass hosts which might block POSTS
		$resp = $clt->post($blogvault->getUrl("dynamic_updates"), array(), array('events' => serialize($bvDynamicEvents),
			'site_id' => $site_id, 'timestamp' => $timestamp, 'wpurl' => urlencode(network_site_url())));
		if ($resp['status'] != '200') {
			return false;
		}
		$this->reset_events();
		return true;
	}

	function add_event($event_type, $message) {
		global $bvDynamicEvents, $wp_current_filter;
		$message['event_type'] = $event_type;
		$message['event_tag'] = end($wp_current_filter);
		if (!in_array($message, $bvDynamicEvents))
			$bvDynamicEvents[] = $message;
	}

	function add_db_event($table, $message) {
		global $bvDynamicEvents;
		$_msg = array();
		$_msg['table'] = $table;
		$_msg['data'] = $message;
		$this->add_event('db', $_msg);
	}

	function post_action_handler($post_id) {
		if (current_filter() == 'delete_post')
			$msg_type = 'delete';
		else 
			$msg_type = 'edit';
		$this->add_db_event('posts', array('ID' => $post_id, 'msg_type' => $msg_type));
	}

	function postmeta_insert_handler($meta_id, $post_id, $meta_key, $meta_value='') {
		$this->add_db_event('postmeta', array('meta_id' => $meta_id));
	}

	function postmeta_modification_handler( $meta_id, $object_id, $meta_key, $meta_value ) {
		if (!is_array($meta_id))
			return $this->add_db_event('postmeta', array('meta_id' => $meta_id));
		foreach ($meta_id as $id) {
			$this->add_db_event('postmeta', array('meta_id' => $id));
		}
	}

	function postmeta_action_handler( $meta_id ) {
		if ( !is_array($meta_id) )
			return $this->add_db_event('postmeta', array('meta_id' => $meta_id));
		foreach ( $meta_id as $id )
			$this->add_db_event('postmeta', array('meta_id' => $id));
	}

	function comment_action_handler($comment_id) {
		if (!is_array($comment_id)) {
			$this->add_db_event('comments', array('comment_ID' => $comment_id));
		} else {
			foreach ($comment_id as $id) {
				$this->add_db_event('comments', array('comment_ID' => $id));
			}
		}
	}

	function commentmeta_insert_handler($meta_id, $comment_id = null) {
		$this->add_db_event('commentmeta', array('meta_id' => $meta_id));
	}

	function commentmeta_modification_handler($meta_id, $object_id, $meta_key, $meta_value) {
		if (!is_array($meta_id))
			return $this->add_db_event('commentmeta', array('meta_id' => $meta_id));
		foreach ($meta_id as $id) {
			$this->add_db_event('commentmeta', array('meta_id' => $id));
		}
	}

	function userid_action_handler($user_or_id) {
		if (is_object($user_or_id))
			$userid = intval( $user_or_id->ID );
		else
			$userid = intval( $user_or_id );
		if ( !$userid )
			return;
		$this->add_db_event('users', array('ID' => $userid));
	}

	function usermeta_action_handler($umeta_id, $user_id, $meta_key, $meta_value='') {
		$this->add_db_event('usermeta', array('umeta_id' => $umeta_id));
	}

	function link_action_handler($link_id) {
		$this->add_db_event('links', array('link_id' => $link_id));
	}

	function term_handler($term_id, $tt_id = null) {
		$this->add_db_event('terms', array('term_id' => $term_id));
		if ($tt_id)
			$this->term_taxonomy_handler($tt_id);
	}

	function term_taxonomy_handler($tt_id) {
		$this->add_db_event('term_taxonomy', array('term_taxonomy_id' => $tt_id));
	}

	function term_taxonomies_handler($tt_ids) {
		foreach((array)$tt_ids as $tt_id) {
			$this->term_taxonomy_handler($tt_id);
		}
	}

	function term_relationship_handler($object_id, $term_id) {
		$this->add_db_event('term_relationships', array('term_taxonomy_id' => $term_id, 'object_id' => $object_id));
	}

	function term_relationships_handler($object_id, $term_ids) {
		foreach ((array)$term_ids as $term_id) {
			$this->term_relationship_handler($object_id, $term_id);
		}
	}

	function set_object_terms_handler( $object_id, $terms, $tt_ids ) {
		$this->term_relationships_handler( $object_id, $tt_ids );
	}

	function get_option_name_ignore($return_defaults = false) {
		$defaults = array(
			'cron',
			'wpsupercache_gc_time',
			'rewrite_rules',
			'akismet_spam_count',
			'/_transient_/'
		);
		$ignore_names = array(
			'bvLastRecvTime',
			'bvLastSendTime',
			'bvPublic',
			'bvSecretKey'
		);
		/* XNOTE: Have a configurable array of options so that in future we can add new options that we discover
		 */
		/* XNOTE: Ideally handle the ignore list at the server itself
		 */
		return array_unique(array_merge($defaults, $ignore_names));
	}

	function option_handler($option_name) {
		$should_ping = true;
		$ignore_names = $this->get_option_name_ignore();
		foreach((array)$ignore_names as $val) {
			if ($val{0} == '/') {
				if (preg_match($val, $option_name))
					$should_ping = false;
			} else {
				if ($val == $option_name)
					$should_ping = false;
			}
			if (!$should_ping)
				break;
		}
		if ($should_ping)
			$this->add_db_event('options', array('option_name' => $option_name));
		 /*Step 2 -- If WordPress is about to kick off some "cron" action, we need to
			 flush blogvault, because the "remote" cron threads done via  http 
			 fetch will be happening completely inside the window of this thread.
			 That thread will be expecting touched and accounted for tables.
		  */
		if ($option_name == '_transient_doing_cron')
			$this->send_updates();
		return $option_name;	
	}

	function theme_action_handler($theme) {
		global $blogvault;
		$this->add_event('themes', array('theme' => $blogvault->getOption('stylesheet')));
	}

	function plugin_action_handler($plugin='') {
		$this->add_event('plugins', array('name' => $plugin));
	}

	function upload_handler($file) {
		$this->add_event('uploads', array('file' => $file['file']));
		return $file;	
	}

	function wpmu_new_blog_create_handler($site_id) {
		$this->add_db_event('blogs', array('site_id' => $site_id));
	}

	function sitemeta_handler($option) {
		global $wpdb;
		$this->add_db_event('sitemeta', array('site_id' => $wpdb->siteid, 'meta_key' => $option));
	}

	/* WOOCOMMERCE SUPPORT FUNCTIONS BEGINS FROM HERE*/
	function delete_term_handler($term_id) {
		return $this->add_db_event('woocommerce_termmeta', array('woocommerce_term_id' => $term_id, 'msg_type' => 'delete'));
	}

	function woocommerce_settings_start_handler() {
		if (!empty($_POST)) {
			if ($_GET['tab'] == 'tax') {
				$this->add_event('sync_table', array('name' => 'woocommerce_tax_rate_locations'));
				$this->add_event('sync_table', array('name' => 'woocommerce_tax_rates'));
			}
		}
	}

	function woocommerce_resume_order_handler($order_id) {
		$this->add_db_event('woocommerce_order_items', array('order_id' => $order_id, 'msg_type' => 'delete'));
		$this->add_event('sync_table', array('name' => 'woocommerce_order_itemmeta'));
	}

	function woocommerce_new_order_item_handler($item_id, $item, $order_id) {
		$this->add_db_event('woocommerce_order_items', array('order_item_id' => $item_id));
	}

	function woocommerce_delete_order_item_handler($item_id) {
		$this->add_db_event('woocommerce_order_itemmeta', array('order_item_id' => $item_id, 'msg_type' => 'delete'));
		$this->add_db_event('woocommerce_order_items', array('order_item_id' => $item_id, 'msg_type' => 'delete'));
	}

	function woocommerce_downloadable_product_permissions_handler($order_id = null) {
		$this->add_db_event('woocommerce_downloadable_product_permissions', array('order_id' => $order_id));
	}

	function woocommerce_download_product_handler($email, $order_key, $product_id, $user_id, $download_id, $order_id) {
		$this->add_db_event('woocommerce_downloadable_product_permissions', array(
				'user_email' => $email,
				'download_id' => $download_id,
				'product_id' => $product_id,
				'order_key' => $order_key));
	}

	function woocommerce_order_itemmeta_insert_handler($meta_id, $order_item_id = null) {
		$this->add_db_event('woocommerce_order_itemmeta', array('meta_id' => $meta_id));
	}

	function woocommerce_order_itemmeta_modification_handler($meta_id, $object_id, $meta_key, $meta_value) {
		if (!is_array($meta_id))
			return $this->add_db_event('woocommerce_order_itemmeta', array('meta_id' => $meta_id));
		foreach ($meta_id as $id) {
			$this->add_db_event('woocommerce_order_itemmeta', array('meta_id' => $id));
		}
	}

	function woocommerce_termmeta_insert_handler($meta_id, $order_item_id = null) {
		$this->add_db_event('woocommerce_termmeta', array('meta_id' => $meta_id));
	}

	function woocommerce_termmeta_modification_handler($meta_id, $object_id, $meta_key, $meta_value) {
		if (!is_array($meta_id))
			return $this->add_db_event('woocommerce_termmeta', array('meta_id' => $meta_id));
		foreach ($meta_id as $id) {
			$this->add_db_event('woocommerce_termmeta', array('meta_id' => $id));
		}
	}

	function woocommerce_attribute_added_handler($attribute_id, $attribute) {
		$this->add_db_event('woocommerce_attribute_taxonomies', array('attribute_id' => $attribute_id));
	}

	function woocommerce_attribute_updated_handler($attribute_id, $attribute, $old_attribute_name) {
		$this->add_db_event('woocommerce_attribute_taxonomies', array('attribute_id' => $attribute_id));
		# $woocommerce->attribute_taxonomy_name( $attribute_name )
		$this->add_db_event('term_taxonomy', array('taxonomy' => 'pa_' . $attribute['attribute_name']));
		# sanitize_title( $attribute_name )
		$this->add_db_event('woocommerce_termmeta', array('meta_key' => 'order_pa_' . $attribute['attribute_name']));
		$this->add_db_event('postmeta', array('meta_key' => '_product_attributes'));
		# sanitize_title( $attribute_name )
		$this->add_db_event('postmeta', array('meta_key' => 'attribute_pa_' . $attribute['attribute_name']));
	}

	function woocommerce_attribute_deleted_handler($attribute_id, $attribute_name, $taxonomy) {
		return $this->add_db_event('woocommerce_attribute_taxonomies', array('attribute_id' => $attribute_id, 'msg_type' => 'delete'));
	}

	function woocommerce_grant_access_to_download_handler() {
		$order_id   = intval($_POST['order_id']);
		$product_id = intval($_POST['product_id']);
		$this->add_db_event('woocommerce_downloadable_product_permissions', array('order_id' => $order_id, 'product_id' => $product_id));
	}

	function woocommerce_revoke_access_to_download_handler() {
		$order_id   = intval($_POST['order_id']);
		$product_id = intval($_POST['product_id']);
		$download_id = $_POST['download_id'];
		$this->add_db_event('woocommerce_downloadable_product_permissions', array('order_id' => $order_id, 'product_id' => $product_id,
				'download_id' => $download_id, 'msg_type' => 'delete'));
	}

	function woocommerce_remove_order_item_meta_handler() {
		$meta_id = absint($_POST['meta_id']);
		$this->add_db_event('woocommerce_order_itemmeta', array('meta_id' => $meta_id, 'msg_type' => 'delete'));
	}

	function woocommerce_calc_line_taxes_handler() {
		$order_id = absint($_POST['order_id']);
		$this->add_event('sync_table', array('name' => 'woocommerce_order_itemmeta'));
		$this->add_db_event('woocommerce_order_items', array('order_id' => $order_id, 'order_item_type' => 'tax', 'msg_type' => 'delete'));
	}

	function woocommerce_product_ordering_handler() {
		$this->add_event('sync_table', array('name' => 'posts'));
	}

	function woocommerce_process_shop_coupon_meta_handler($post_id, $post) {
		$this->add_db_event('posts', array('ID' => $post_id));
	}

	function woocommerce_process_shop_order_meta_handler($post_id, $post) {
		$this->add_db_event('posts', array('ID' => $post_id));
		if (isset($_POST['order_taxes_id'])) {
			foreach($_POST['order_taxes_id'] as $item_id) {
				$this->add_db_event('woocommerce_order_items', array('order_item_id' => $item_id));
			}
		}
		if (isset($_POST['order_item_id'])) {
			foreach($_POST['order_item_id'] as $item_id) {
				$this->add_db_event('woocommerce_order_items', array('order_item_id' => $item_id));
			}
		}
		if (isset($_POST['meta_key'])) {
			foreach($_POST['meta_key'] as $id => $meta_key) {
				$this->add_db_event('woocommerce_order_itemmeta', array('meta_id' => $id));
			}
		}
		if (isset($_POST['download_id']) && isset($_POST['product_id'])) {
			$download_ids = $_POST['download_id'];
			$product_ids = $_POST['product_id'];
			$product_ids_count = sizeof($product_ids);
			for ( $i = 0; $i < $product_ids_count; $i ++ ) {
				$this->add_db_event('woocommerce_downloadable_product_permissions', array(
					'order_id'    => $post_id,
					'product_id'  => absint($product_ids[$i]),
					'download_id' => $download_ids[$i]
				));
			}
		}
	}

	function woocommerce_update_product_variation_handler($variation_id) {
		$this->add_db_event('posts', array('ID' => $variation_id));
	}

	function woocommerce_save_product_variation_handler($variation_id, $i) {
		if ($variation_id) {
			$this->add_db_event('postmeta', array('post_id' => $variation_id, 'msg_type' => 'delete'));
		}
	}

	function woocommerce_duplicate_product_handler($id, $post) {
		#$this->add_db_event('posts', array('ID' => $id));
		#$this->add_db_event('postmeta', array('post_id' => $id));
		$this->add_event('sync_table', array('name' => 'posts'));
		$this->add_event('sync_table', array('name' => 'postmeta'));
	}

	function woocommerce_delete_order_items_handler($postid) {
		$this->add_db_event('woocommerce_order_itemmeta', array('order_item_id' => $postid, 'msg_type' => 'delete'));
		$this->add_db_event('woocommerce_order_items', array('order_item_id' => $postid, 'msg_type' => 'delete'));
	}

	function import_handler() {
		$this->add_event('sync_table', array('all' => 'true'));
	}

	function retrieve_password_key_handler($user_login, $key) {
		$this->add_db_event('users', array('user_login' => $user_login));
	}

	/* ADDING ACTION AND LISTENERS FOR CAPTURING EVENTS. */
	function add_actions_and_listeners() {
		global $blogvault;
		/* CAPTURING EVENTS FOR WP_COMMENTS TABLE */
		add_action('delete_comment', array($this, 'comment_action_handler'));
		add_action('wp_set_comment_status', array($this, 'comment_action_handler'));
		add_action('trashed_comment', array($this, 'comment_action_handler'));
		add_action('untrashed_comment', array($this, 'comment_action_handler'));
		add_action('wp_insert_comment', array($this, 'comment_action_handler'));
		add_action('comment_post', array($this, 'comment_action_handler'));
		add_action('edit_comment', array($this, 'comment_action_handler'));

		/* CAPTURING EVENTS FOR WP_COMMENTMETA TABLE */
		add_action('added_comment_meta', array($this, 'commentmeta_insert_handler' ), 10, 2 );
		add_action('updated_comment_meta', array($this, 'commentmeta_modification_handler' ), 10, 4 );
		add_action('deleted_comment_meta', array($this, 'commentmeta_modification_handler' ), 10, 4 );

		add_action('user_register', array($this, 'userid_action_handler'));
		add_action('password_reset', array($this, 'userid_action_handler'));
		add_action('profile_update', array($this, 'userid_action_handler'));
		add_action('deleted_user', array($this, 'userid_action_handler'));

		/* CAPTURING EVENTS FOR WP_POSTS TABLE */
		add_action('delete_post', array($this, 'post_action_handler'));
		add_action('trash_post', array($this, 'post_action_handler'));
		add_action('untrash_post', array($this, 'post_action_handler'));
		add_action('edit_post', array($this, 'post_action_handler'));
		add_action('save_post', array($this, 'post_action_handler'));
		add_action('wp_insert_post', array($this, 'post_action_handler'));
		add_action('edit_attachment', array($this, 'post_action_handler'));
		add_action('add_attachment', array($this, 'post_action_handler'));
		add_action('delete_attachment', array($this, 'post_action_handler'));
		add_action('private_to_published', array($this, 'post_action_handler'));
		add_action('wp_restore_post_revision', array($this, 'post_action_handler'));

		/* CAPTURING EVENTS FOR WP_POSTMETA TABLE */
		// Why events for both delete and deleted
		add_action('added_post_meta', array($this, 'postmeta_insert_handler'), 10, 4);
		add_action('update_post_meta', array($this, 'postmeta_modification_handler'), 10, 4);
		add_action('updated_post_meta', array($this, 'postmeta_modification_handler'), 10, 4);
		add_action('delete_post_meta', array($this, 'postmeta_modification_handler'), 10, 4);
		add_action('deleted_post_meta', array($this, 'postmeta_modification_handler'), 10, 4);
		add_action('added_postmeta', array($this, 'postmeta_action_handler'));
		add_action('update_postmeta', array($this, 'postmeta_action_handler'));
		add_action('delete_postmeta', array($this, 'postmeta_action_handler'));

		/* CAPTURING EVENTS FOR WP_LINKS TABLE */
		add_action('edit_link', array($this, 'link_action_handler'));
		add_action('add_link', array($this, 'link_action_handler'));
		add_action('delete_link', array($this, 'link_action_handler'));

		/* CAPTURING EVENTS FOR WP_TERM AND WP_TERM_TAXONOMY TABLE */
		add_action('created_term', array($this, 'term_handler'), 2);
		add_action('edited_terms', array($this, 'term_handler'), 2);
		add_action('delete_term', array($this, 'term_handler'), 2);
		add_action('edit_term_taxonomy', array($this, 'term_taxonomy_handler'));
		add_action('delete_term_taxonomy', array($this, 'term_taxonomy_handler'));
		add_action('edit_term_taxonomies', array($this, 'term_taxonomies_handler'));
		add_action('add_term_relationship', array($this, 'term_relationship_handler'), 10, 2);
		add_action('delete_term_relationships', array($this, 'term_relationships_handler'), 10, 2);
		add_action('set_object_terms', array($this, 'set_object_terms_handler'), 10, 3);

		add_action('switch_theme', array($this, 'theme_action_handler'));
		add_action('activate_plugin', array($this, 'plugin_action_handler'));
		add_action('deactivate_plugin', array($this, 'plugin_action_handler'));

		/* CAPTURING EVENTS FOR WP_OPTIONS */
		add_action('deleted_option', array($this, 'option_handler'), 1);
		add_action('updated_option', array($this, 'option_handler'), 1);
		add_action('added_option', array($this, 'option_handler'), 1);

		/* CAPTURING EVENTS FOR FILES UPLOAD */
		add_action('wp_handle_upload', array($this, 'upload_handler'));

		if ($blogvault->isMultisite()) {
			add_action('wpmu_new_blog', array($this, 'wpmu_new_blog_create_handler'), 10, 1);
			add_action('refresh_blog_details', array($this, 'wpmu_new_blog_create_handler'), 10, 1);
			/* XNOTE: Handle registration_log_handler from within the server */
			/* These are applicable only in case of WPMU */
			add_action('delete_site_option',array($this, 'sitemeta_handler'), 10, 1);
			add_action('add_site_option', array($this, 'sitemeta_handler'), 10, 1);
			add_action('update_site_option', array($this, 'sitemeta_handler'), 10, 1);
		}

		$is_woo_dyn = $blogvault->getOption('bvWooDynSync');
		if ($is_woo_dyn == 'yes') {
			add_action('delete_term', array($this, 'delete_term_handler'), 2);
			add_action('woocommerce_settings_start', array($this, 'woocommerce_settings_start_handler'), 10, 1);

			add_action('woocommerce_resume_order', array($this, 'woocommerce_resume_order_handler'), 10, 1);
			add_action('woocommerce_new_order_item', 	array($this, 'woocommerce_new_order_item_handler'), 10, 3);
			add_action('woocommerce_delete_order_item', array($this, 'woocommerce_delete_order_item_handler'), 10, 1);

			add_action('woocommerce_order_status_processing',array($this, 'woocommerce_downloadable_product_permissions_handler'), 10, 1);
			add_action('woocommerce_order_status_completed', array($this, 'woocommerce_downloadable_product_permissions_handler'), 10, 1);
			add_action('woocommerce_download_product', array($this, 'woocommerce_download_product_handler'), 10, 6);

			add_action('added_order_item_meta', array($this, 'woocommerce_order_itemmeta_insert_handler' ), 10, 2 );
			add_action('updated_order_item_meta', array($this, 'woocommerce_order_itemmeta_modification_handler'), 10, 4 );
			add_action('deleted_order_item_meta', array($this, 'woocommerce_order_itemmeta_modification_handler'), 10, 4 );

			add_action('added_woocommerce_term_meta', array($this, 'woocommerce_termmeta_insert_handler' ), 10, 2 );
			add_action('updated_woocommerce_term_meta', array($this, 'woocommerce_termmeta_modification_handler'), 10, 4 );
			add_action('deleted_woocommerce_term_meta', array($this, 'woocommerce_termmeta_modification_handler'), 10, 4 );

			add_action('woocommerce_attribute_added', array($this, 'woocommerce_attribute_added_handler' ), 10, 2 );
			add_action('woocommerce_attribute_updated', array($this, 'woocommerce_attribute_updated_handler'), 10, 4 );
			add_action('woocommerce_attribute_deleted', array($this, 'woocommerce_attribute_deleted_handler'), 10, 4 );

			add_action('wp_ajax_woocommerce_grant_access_to_download', array($this, 'woocommerce_grant_access_to_download_handler'), 10, 6);
			add_action('wp_ajax_woocommerce_revoke_access_to_download', array($this, 'woocommerce_revoke_access_to_download_handler'), 10, 6);
			add_action('wp_ajax_woocommerce_remove_order_item_meta', array($this, 'woocommerce_remove_order_item_meta_handler'), 10, 4 );
			add_action('wp_ajax_woocommerce_calc_line_taxes', array($this, 'woocommerce_calc_line_taxes_handler'), 10, 4 );
			add_action('wp_ajax_woocommerce_product_ordering', array($this, 'woocommerce_product_ordering_handler'), 10, 4 );

			add_action('woocommerce_process_shop_coupon_meta', array($this, 'woocommerce_process_shop_coupon_meta_handler'), 10, 4 );
			add_action('woocommerce_process_shop_order_meta', array($this, 'woocommerce_process_shop_order_meta_handler'), 10, 4 );
			add_action('woocommerce_update_product_variation', array($this, 'woocommerce_update_product_variation_handler'), 10, 4 );
			add_action('woocommerce_save_product_variation', array($this, 'woocommerce_save_product_variation_handler'), 10, 4 );
			add_action('woocommerce_duplicate_product', array($this, 'woocommerce_duplicate_product_handler'), 10, 4 );
			add_action('before_delete_post', array($this, 'woocommerce_delete_order_items_handler'), 10, 4 );
			add_action('import_start', array($this, 'import_handler'), 10, 4 );
			add_action('import_end', array($this, 'import_handler'), 10, 4 );

			add_action('retrieve_password_key', array($this, 'retrieve_password_key_handler'), 10, 4 );
		}

		$this->add_bv_required_filters();
	}

	function add_bv_required_filters() {
		/* REPORT BACK TO BLOGVAULT FOR UPDATES */
		add_action('shutdown', array($this, 'send_updates'));
	}
}
} // class exists


if ((array_key_exists('apipage', $_REQUEST)) && stristr($_REQUEST['apipage'], 'blogvault')) {
	global $blogvault;
	global $wp_version, $wp_db_version;
	global $wpdb, $bvVersion;
	if (array_key_exists('obend', $_REQUEST) && function_exists('ob_end_clean'))
		@ob_end_clean();
	if ((array_key_exists('mode', $_REQUEST)) && ($_REQUEST['mode'] === "resp")) {
		if (array_key_exists('op_reset', $_REQUEST)) {
			output_reset_rewrite_vars();
		}
		header("Content-type: application/binary");
		header('Content-Transfer-Encoding: binary');
	}
	$blogvault->addStatus("signature", "Blogvault API");
	if (!$blogvault->authenticateControlRequest()) {
		$blogvault->addStatus("statusmsg", 'failed authentication');
		$blogvault->addStatus("public", substr($blogvault->getOption('bvPublic'), 0, 6));
		$blogvault->terminate();
	}
	$method = urldecode($_REQUEST['bvMethod']);
	$blogvault->addStatus("callback", $method);
	if (!(array_key_exists('stripquotes', $_REQUEST)) && (get_magic_quotes_gpc() || function_exists('wp_magic_quotes'))) {
		$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
	}
	if (array_key_exists('b64', $_REQUEST)) {
		if (array_key_exists('files', $_REQUEST)) {
			$_REQUEST['files'] = array_map('base64_decode', $_REQUEST['files']);
		}
		if (array_key_exists('initdir', $_REQUEST)) {
			$_REQUEST['initdir'] = base64_decode($_REQUEST['initdir']);
		}
		if (array_key_exists('filter', $_REQUEST)) {
			$_REQUEST['filter'] = base64_decode($_REQUEST['filter']);
		}
	}
	if (array_key_exists('memset', $_REQUEST)) {
		$val = intval(urldecode($_REQUEST['memset']));
		ini_set('memory_limit', $val.'M');
	}
	switch ($method) {
	case "sendmanyfiles":
		$files = $_REQUEST['files'];
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$blogvault->addStatus("status", $blogvault->uploadFiles($files, $offset, $limit, $bsize));
		break;
	case "sendfilesmd5":
		$files = $_REQUEST['files'];
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$blogvault->addStatus("status", $blogvault->fileMd5($files, $offset, $limit, $bsize));
		break;
	case "listtables":
		$blogvault->addStatus("status", $blogvault->listTables());
		break;
	case "tableinfo":
		$table = urldecode($_REQUEST['table']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$filter = urldecode($_REQUEST['filter']);
		$blogvault->addStatus("status", $blogvault->tableInfo($table, $offset, $limit, $bsize, $filter));
		break;
	case "uploadrows":
		$table = urldecode($_REQUEST['table']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$filter = urldecode($_REQUEST['filter']);
		$blogvault->addStatus("status", $blogvault->uploadRows($table, $offset, $limit, $bsize, $filter));
		break;
	case "sendactivate":
		$blogvault->addStatus("status", $blogvault->activate());
		break;
	case "scanfilesdefault":
		$blogvault->addStatus("status", $blogvault->scanFiles());
		break;
	case "scanfiles":
		$initdir = urldecode($_REQUEST['initdir']);
		$offset = intval(urldecode($_REQUEST['offset']));
		$limit = intval(urldecode($_REQUEST['limit']));
		$bsize = intval(urldecode($_REQUEST['bsize']));
		$blogvault->addStatus("status", $blogvault->scanFiles($initdir, $offset, $limit, $bsize));
		break;
	case "setdynsync":
		$blogvault->updateOption('bvDynSyncActive', $_REQUEST['dynsync']);
		break;
	case "setwoodyn":
		$blogvault->updateOption('bvWooDynSync', $_REQUEST['woodyn']);
		break;
	case "setserverid":
		$blogvault->updateOption('bvServerId', $_REQUEST['serverid']);
		break;
	case "updatekeys":
		$blogvault->addStatus("status", $blogvault->updateKeys($_REQUEST['public'], $_REQUEST['secret']));
		break;
	case "phpinfo":
		phpinfo();
		die();
		break;
	case "getposts":
		require_once (ABSPATH."wp-includes/pluggable.php");
		$post_type = urldecode($_REQUEST['post_type']);
		$args = array('numberposts' => 5, 'post_type' => $post_type);
		$posts = get_posts($args);
		$keys = array('post_title', 'guid', 'ID', 'post_date');
		foreach($posts as $post) {
			$pdata = array();
			$post_array = get_object_vars($post);
			foreach($keys as $key) {
				$pdata[$key] = $post_array[$key];
			}
			$blogvault->addArrayToStatus("posts", $pdata);
		}
		break;
	case "getstats":
		if (!function_exists('wp_count_posts'))
			require_once (ABSPATH."wp-includes/post.php");
		require_once (ABSPATH."wp-includes/pluggable.php");
		$blogvault->addStatus("posts", get_object_vars(wp_count_posts()));
		$blogvault->addStatus("pages", get_object_vars(wp_count_posts("page")));
		$blogvault->addStatus("comments", get_object_vars(wp_count_comments()));
		break;
	case "getinfo":
		if (array_key_exists('wp', $_REQUEST)) {
			$wp_info = array(
				'current_theme' => (string)(function_exists('wp_get_theme') ? wp_get_theme() : get_current_theme()),
				'dbprefix' => $wpdb->base_prefix ? $wpdb->base_prefix : $wpdb->prefix,
				'wpmu' => $blogvault->isMultisite(),
				'mainsite' => $blogvault->isMainSite(),
				'name' => get_bloginfo('name'),
				'site_url' => get_bloginfo('wpurl'),
				'home_url' => get_bloginfo('url'),
				'charset' => get_bloginfo('charset'),
				'wpversion' => $wp_version,
				'dbversion' => $wp_db_version,
				'abspath' => ABSPATH,
				'uploadpath' => $blogvault->uploadPath(),
				'contentdir' => defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : null,
				'plugindir' => defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : null,
				'dbcharset' => defined('DB_CHARSET') ? DB_CHARSET : null,
				'disallow_file_edit' => defined('DISALLOW_FILE_EDIT'),
				'disallow_file_mods' => defined('DISALLOW_FILE_MODS'),
				'bvversion' => $bvVersion
			);
			$blogvault->addStatus("wp", $wp_info);
		}
		if (array_key_exists('plugins', $_REQUEST)) {
			if (!function_exists('get_plugins'))
				require_once (ABSPATH."wp-admin/includes/plugin.php");
			$plugins = get_plugins();
			foreach($plugins as $plugin_file => $plugin_data) {
				$pdata = array(
					'file' => $plugin_file,
					'title' => $plugin_data['Title'],
					'version' => $plugin_data['Version'],
					'active' => is_plugin_active($plugin_file)
				);
				$blogvault->addArrayToStatus("plugins", $pdata);
			}
		}
		if (array_key_exists('themes', $_REQUEST)) {
			$themes = function_exists('wp_get_themes') ? wp_get_themes() : get_themes();
			foreach($themes as $theme) {
				if (is_object($theme)) {
					$pdata = array(
						'name' => $theme->Name,
						'title' => $theme->Title,
						'stylesheet' => $theme->get_stylesheet(),
						'template' => $theme->Template,
						'version' => $theme->Version
					);
				} else {
					$pdata = array(
						'name' => $theme["Name"],
						'title' => $theme["Title"],
						'stylesheet' => $theme["Stylesheet"],
						'template' => $theme["Template"],
						'version' => $theme["Version"]
					);
				}
				$blogvault->addArrayToStatus("themes", $pdata);
			}
		}
		if (array_key_exists('users', $_REQUEST)) {
			$users = array();
			if (function_exists('get_users')) {
				$users = get_users('search=admin');
			} else if (function_exists('get_users_of_blog')) {
				$users = get_users_of_blog();
			}
			foreach($users as $user) {
				if (stristr($user->user_login, 'admin')) {
					$pdata = array(
						'login' => $user->user_login,
						'ID' => $user->ID
					);
					$blogvault->addArrayToStatus("users", $pdata);
				}
			}
		}
		if (array_key_exists('system', $_REQUEST)) {
			$sys_info = array(
				'serverip' => $_SERVER['SERVER_ADDR'],
				'host' => $_SERVER['HTTP_HOST'],
				'uname' => @php_uname("a"),
				'phpversion' => phpversion(),
				'uid' => getmyuid(),
				'gid' => getmygid(),
				'user' => get_current_user()
			);
			if (function_exists('posix_getuid')) {
				$sys_info['webuid'] = posix_getuid();
				$sys_info['webgid'] = posix_getgid();
			}
			$blogvault->addStatus("sys", $sys_info);
		}
		break;
	case "describetable":
		$table = urldecode($_REQUEST['table']);
		$blogvault->describeTable($table);
		break;
	case "checktable":
		$table = urldecode($_REQUEST['table']);
		$type = urldecode($_REQUEST['type']);
		$blogvault->checkTable($table, $type);
		break;
	case "tablekeys":
		$table = urldecode($_REQUEST['table']);
		$blogvault->tableKeys($table);
		break;
	default:
		$blogvault->addStatus("statusmsg", "Bad Command");
		$blogvault->addStatus("status", false);
		break;
	}

	$blogvault->terminate();
}

$isdynsyncactive = $blogvault->getOption('bvDynSyncActive');
if ($isdynsyncactive == 'yes') {
	BVDynamicBackup::init();
}
?>