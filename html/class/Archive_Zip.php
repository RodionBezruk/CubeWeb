<?php
  define( 'ARCHIVE_ZIP_READ_BLOCK_SIZE', 2048 );
  define( 'ARCHIVE_ZIP_SEPARATOR', ',' );
  define( 'ARCHIVE_ZIP_TEMPORARY_DIR', '' );
  define( 'ARCHIVE_ZIP_ERR_NO_ERROR', 0 );
  define( 'ARCHIVE_ZIP_ERR_WRITE_OPEN_FAIL', -1 );
  define( 'ARCHIVE_ZIP_ERR_READ_OPEN_FAIL', -2 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_PARAMETER', -3 );
  define( 'ARCHIVE_ZIP_ERR_MISSING_FILE', -4 );
  define( 'ARCHIVE_ZIP_ERR_FILENAME_TOO_LONG', -5 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_ZIP', -6 );
  define( 'ARCHIVE_ZIP_ERR_BAD_EXTRACTED_FILE', -7 );
  define( 'ARCHIVE_ZIP_ERR_DIR_CREATE_FAIL', -8 );
  define( 'ARCHIVE_ZIP_ERR_BAD_EXTENSION', -9 );
  define( 'ARCHIVE_ZIP_ERR_BAD_FORMAT', -10 );
  define( 'ARCHIVE_ZIP_ERR_DELETE_FILE_FAIL', -11 );
  define( 'ARCHIVE_ZIP_ERR_RENAME_FILE_FAIL', -12 );
  define( 'ARCHIVE_ZIP_ERR_BAD_CHECKSUM', -13 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP', -14 );
  define( 'ARCHIVE_ZIP_ERR_MISSING_OPTION_VALUE', -15 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_PARAM_VALUE', -16 );
  define( 'ARCHIVE_ZIP_WARN_NO_WARNING', 0 );
  define( 'ARCHIVE_ZIP_WARN_FILE_EXIST', 1 );
  define( 'ARCHIVE_ZIP_PARAM_PATH', 'path' );
  define( 'ARCHIVE_ZIP_PARAM_ADD_PATH', 'add_path' );
  define( 'ARCHIVE_ZIP_PARAM_REMOVE_PATH', 'remove_path' );
  define( 'ARCHIVE_ZIP_PARAM_REMOVE_ALL_PATH', 'remove_all_path' );
  define( 'ARCHIVE_ZIP_PARAM_SET_CHMOD', 'set_chmod' );
  define( 'ARCHIVE_ZIP_PARAM_EXTRACT_AS_STRING', 'extract_as_string' );
  define( 'ARCHIVE_ZIP_PARAM_NO_COMPRESSION', 'no_compression' );
  define( 'ARCHIVE_ZIP_PARAM_BY_NAME', 'by_name' );
  define( 'ARCHIVE_ZIP_PARAM_BY_INDEX', 'by_index' );
  define( 'ARCHIVE_ZIP_PARAM_BY_EREG', 'by_ereg' );
  define( 'ARCHIVE_ZIP_PARAM_BY_PREG', 'by_preg' );
  define( 'ARCHIVE_ZIP_PARAM_PRE_EXTRACT', 'callback_pre_extract' );
  define( 'ARCHIVE_ZIP_PARAM_POST_EXTRACT', 'callback_post_extract' );
  define( 'ARCHIVE_ZIP_PARAM_PRE_ADD', 'callback_pre_add' );
  define( 'ARCHIVE_ZIP_PARAM_POST_ADD', 'callback_post_add' );
class Archive_Zip
{
    var $_zipname='';
    var $_zip_fd=0;
    var $_error_code=1;
    var $_error_string='';
    function Archive_Zip($p_zipname)
    {
      if (!extension_loaded('zlib')) {
          die("The extension 'zlib' couldn't be found.\n".
              "Please make sure your version of PHP was built ".
              "with 'zlib' support.\n");
          return false;
      }
      $this->_zipname = $p_zipname;
      $this->_zip_fd = 0;
      return;
    }
    function create($p_filelist, $p_params=0)
    {
        $this->_errorReset();
        if ($p_params === 0) {
    	    $p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array('no_compression' => false,
	                                       'add_path' => "",
	                                       'remove_path' => "",
	                                       'remove_all_path' => false)) != 1) {
		    return 0;
	    }
        $p_result_list = array();
        if (is_array($p_filelist)) {
            $v_result = $this->_create($p_filelist, $p_result_list, $p_params);
        }
        else if (is_string($p_filelist)) {
            $v_list = explode(ARCHIVE_ZIP_SEPARATOR, $p_filelist);
            $v_result = $this->_create($v_list, $p_result_list, $p_params);
        }
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
	                         'Invalid variable type p_filelist');
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }
        if ($v_result != 1) {
            return 0;
        }
        return $p_result_list;
    }
    function add($p_filelist, $p_params=0)
    {
        $this->_errorReset();
        if ($p_params === 0) {
        	$p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array ('no_compression' => false,
	                                        'add_path' => '',
	                                        'remove_path' => '',
	                                        'remove_all_path' => false,
						    	     		'callback_pre_add' => '',
							    		    'callback_post_add' => '')) != 1) {
		    return 0;
	    }
        $p_result_list = array();
        if (is_array($p_filelist)) {
            $v_result = $this->_add($p_filelist, $p_result_list, $p_params);
        }
        else if (is_string($p_filelist)) {
            $v_list = explode(ARCHIVE_ZIP_SEPARATOR, $p_filelist);
            $v_result = $this->_add($v_list, $p_result_list, $p_params);
        }
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
	                         "add() : Invalid variable type p_filelist");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }
        if ($v_result != 1) {
            return 0;
        }
        return $p_result_list;
    }
    function listContent()
    {
        $this->_errorReset();
        if (!$this->_checkFormat()) {
            return(0);
        }
        $v_list = array();
        if ($this->_list($v_list) != 1) {
            unset($v_list);
            return(0);
        }
        return $v_list;
    }
    function extract($p_params=0)
    {
        $this->_errorReset();
        if (!$this->_checkFormat()) {
            return(0);
        }
        if ($p_params === 0) {
        	$p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array ('extract_as_string' => false,
	                                        'add_path' => '',
	                                        'remove_path' => '',
	                                        'remove_all_path' => false,
					    		     		'callback_pre_extract' => '',
						    			    'callback_post_extract' => '',
							    		    'set_chmod' => 0,
								    	    'by_name' => '',
									        'by_index' => '',
									        'by_ereg' => '',
									        'by_preg' => '') ) != 1) {
	    	return 0;
	    }
        $v_list = array();
        if ($this->_extractByRule($v_list, $p_params) != 1) {
            unset($v_list);
            return(0);
        }
        return $v_list;
    }
    function delete($p_params)
    {
        $this->_errorReset();
        if (!$this->_checkFormat()) {
            return(0);
        }
        if ($this->_check_parameters($p_params,
	                                 array ('by_name' => '',
									        'by_index' => '',
									        'by_ereg' => '',
									        'by_preg' => '') ) != 1) {
	    	return 0;
    	}
        if (   ($p_params['by_name'] == '')
            && ($p_params['by_index'] == '')
            && ($p_params['by_ereg'] == '')
            && ($p_params['by_preg'] == '')) {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 'At least one filtering rule must'
							 .' be set as parameter');
            return 0;
        }
        $v_list = array();
        if ($this->_deleteByRule($v_list, $p_params) != 1) {
            unset($v_list);
            return(0);
        }
        return $v_list;
    }
    function properties()
    {
        $this->_errorReset();
        if (!$this->_checkFormat()) {
            return(0);
        }
        $v_prop = array();
        $v_prop['comment'] = '';
        $v_prop['nb'] = 0;
        $v_prop['status'] = 'not_exist';
        if (@is_file($this->_zipname)) {
            if (($this->_zip_fd = @fopen($this->_zipname, 'rb')) == 0) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
				                 'Unable to open archive \''.$this->_zipname
								 .'\' in binary read mode');
                return 0;
            }
            $v_central_dir = array();
            if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1) {
                return 0;
            }
            $this->_closeFd();
            $v_prop['comment'] = $v_central_dir['comment'];
            $v_prop['nb'] = $v_central_dir['entries'];
            $v_prop['status'] = 'ok';
        }
        return $v_prop;
    }
    function duplicate($p_archive)
    {
        $this->_errorReset();
        if (   (is_object($p_archive))
		    && (strtolower(get_class($p_archive)) == 'archive_zip')) {
            $v_result = $this->_duplicate($p_archive->_zipname);
        }
        else if (is_string($p_archive)) {
            if (!is_file($p_archive)) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
				                 "No file with filename '".$p_archive."'");
                $v_result = ARCHIVE_ZIP_ERR_MISSING_FILE;
            }
            else {
                $v_result = $this->_duplicate($p_archive);
            }
        }
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 "Invalid variable type p_archive_to_add");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }
        return $v_result;
    }
    function merge($p_archive_to_add)
    {
        $v_result = 1;
        $this->_errorReset();
        if (!$this->_checkFormat()) {
            return(0);
        }
        if (   (is_object($p_archive_to_add))
		    && (strtolower(get_class($p_archive_to_add)) == 'archive_zip')) {
            $v_result = $this->_merge($p_archive_to_add);
        }
        else if (is_string($p_archive_to_add)) {
            $v_object_archive = new Archive_Zip($p_archive_to_add);
            $v_result = $this->_merge($v_object_archive);
        }
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 "Invalid variable type p_archive_to_add");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }
        return $v_result;
    }
    function errorCode()
    {
        return($this->_error_code);
    }
    function errorName($p_with_code=false)
    {
        $v_const_list = get_defined_constants();
        for (reset($v_const_list);
		     list($v_key, $v_value) = each($v_const_list);) {
     	    if (substr($v_key, 0, strlen('ARCHIVE_ZIP_ERR_'))
			    =='ARCHIVE_ZIP_ERR_') {
    		    $v_error_list[$v_key] = $v_value;
    	    }
        }
        $v_key=array_search($this->_error_code, $v_error_list, true);
  	    if ($v_key!=false) {
            $v_value = $v_key;
  	    }
  	    else {
            $v_value = 'NoName';
  	    }
        if ($p_with_code) {
            return($v_value.' ('.$this->_error_code.')');
        }
        else {
          return($v_value);
        }
    }
    function errorInfo($p_full=false)
    {
        if ($p_full) {
            return($this->errorName(true)." : ".$this->_error_string);
        }
        else {
            return($this->_error_string." [code ".$this->_error_code."]");
        }
    }
  function _checkFormat($p_level=0)
  {
    $v_result = true;
    $this->_errorReset();
    if (!is_file($this->_zipname)) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
	                   "Missing archive file '".$this->_zipname."'");
      return(false);
    }
    if (!is_readable($this->_zipname)) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   "Unable to read archive '".$this->_zipname."'");
      return(false);
    }
    return $v_result;
  }
  function _create($p_list, &$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();
	$p_add_dir = $p_params['add_path'];
	$p_remove_dir = $p_params['remove_path'];
	$p_remove_all_dir = $p_params['remove_all_path'];
    if (($v_result = $this->_openFd('wb')) != 1)
    {
      return $v_result;
    }
    $v_result = $this->_addList($p_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params);
    $this->_closeFd();
    return $v_result;
  }
  function _add($p_list, &$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();
	$p_add_dir = $p_params['add_path'];
	$p_remove_dir = $p_params['remove_path'];
	$p_remove_all_dir = $p_params['remove_all_path'];
    if ((!is_file($this->_zipname)) || (filesize($this->_zipname) == 0)) {
      $v_result = $this->_create($p_list, $p_result_list, $p_params);
      return $v_result;
    }
    if (($v_result=$this->_openFd('rb')) != 1) {
      return $v_result;
    }
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      $this->_closeFd();
      return $v_result;
    }
    @rewind($this->_zip_fd);
    $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-').'.tmp';
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0)
    {
      $this->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open temporary file \''
					   .$v_zip_temp_name.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;
    $v_header_list = array();
    if (($v_result = $this->_addFileList($p_list, $v_header_list,
	                                     $p_add_dir, $p_remove_dir,
										 $p_remove_all_dir, $p_params)) != 1)
    {
      fclose($v_zip_temp_fd);
      $this->_closeFd();
      @unlink($v_zip_temp_name);
      return $v_result;
    }
    $v_offset = @ftell($this->_zip_fd);
    $v_size = $v_central_dir['size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->_zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    for ($i=0, $v_count=0; $i<sizeof($v_header_list); $i++)
    {
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result=$this->_writeCentralFileHeader($v_header_list[$i]))!=1) {
          fclose($v_zip_temp_fd);
          $this->_closeFd();
          @unlink($v_zip_temp_name);
          return $v_result;
        }
        $v_count++;
      }
      $this->_convertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }
    $v_comment = '';
    $v_size = @ftell($this->_zip_fd)-$v_offset;
    if (($v_result = $this->_writeCentralHeader($v_count
	                                              +$v_central_dir['entries'],
	                                            $v_size, $v_offset,
												$v_comment)) != 1) {
      unset($v_header_list);
      return $v_result;
    }
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;
    $this->_closeFd();
    @fclose($v_zip_temp_fd);
    @unlink($this->_zipname);
    $this->_tool_Rename($v_zip_temp_name, $this->_zipname);
    return $v_result;
  }
  function _openFd($p_mode)
  {
    $v_result=1;
    if ($this->_zip_fd != 0)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Zip file \''.$this->_zipname.'\' already open');
      return Archive_Zip::errorCode();
    }
    if (($this->_zip_fd = @fopen($this->_zipname, $p_mode)) == 0)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open archive \''.$this->_zipname
					   .'\' in '.$p_mode.' mode');
      return Archive_Zip::errorCode();
    }
    return $v_result;
  }
  function _closeFd()
  {
    $v_result=1;
    if ($this->_zip_fd != 0)
      @fclose($this->_zip_fd);
    $this->_zip_fd = 0;
    return $v_result;
  }
  function _addList($p_list, &$p_result_list,
                    $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_params)
  {
    $v_result=1;
    $v_header_list = array();
    if (($v_result = $this->_addFileList($p_list, $v_header_list,
	                                     $p_add_dir, $p_remove_dir,
										 $p_remove_all_dir, $p_params)) != 1) {
      return $v_result;
    }
    $v_offset = @ftell($this->_zip_fd);
    for ($i=0,$v_count=0; $i<sizeof($v_header_list); $i++)
    {
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result = $this->_writeCentralFileHeader($v_header_list[$i])) != 1) {
          return $v_result;
        }
        $v_count++;
      }
      $this->_convertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }
    $v_comment = '';
    $v_size = @ftell($this->_zip_fd)-$v_offset;
    if (($v_result = $this->_writeCentralHeader($v_count, $v_size, $v_offset,
	                                            $v_comment)) != 1)
    {
      unset($v_header_list);
      return $v_result;
    }
    return $v_result;
  }
  function _addFileList($p_list, &$p_result_list,
                        $p_add_dir, $p_remove_dir, $p_remove_all_dir,
						&$p_params)
  {
    $v_result=1;
    $v_header = array();
    $v_nb = sizeof($p_result_list);
    for ($j=0; ($j<count($p_list)) && ($v_result==1); $j++)
    {
      $p_filename = $this->_tool_TranslateWinPath($p_list[$j], false);
      if ($p_filename == "")
      {
        continue;
      }
      if (!file_exists($p_filename))
      {
        $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
		                 "File '$p_filename' does not exists");
        return Archive_Zip::errorCode();
      }
      if ((is_file($p_filename)) || ((is_dir($p_filename)) && !$p_remove_all_dir)) {
        if (($v_result = $this->_addFile($p_filename, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params)) != 1)
        {
          return $v_result;
        }
        $p_result_list[$v_nb++] = $v_header;
      }
      if (is_dir($p_filename))
      {
        if ($p_filename != ".")
          $v_path = $p_filename."/";
        else
          $v_path = "";
        $p_hdir = opendir($p_filename);
        $p_hitem = readdir($p_hdir); 
        $p_hitem = readdir($p_hdir); 
        while ($p_hitem = readdir($p_hdir))
        {
          if (is_file($v_path.$p_hitem))
          {
            if (($v_result = $this->_addFile($v_path.$p_hitem, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params)) != 1)
            {
              return $v_result;
            }
            $p_result_list[$v_nb++] = $v_header;
          }
          else
          {
            $p_temp_list[0] = $v_path.$p_hitem;
            $v_result = $this->_addFileList($p_temp_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params);
            $v_nb = sizeof($p_result_list);
          }
        }
        unset($p_temp_list);
        unset($p_hdir);
        unset($p_hitem);
      }
    }
    return $v_result;
  }
  function _addFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_params)
  {
    $v_result=1;
    if ($p_filename == "")
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER, "Invalid file list parameter (invalid or empty list)");
      return Archive_Zip::errorCode();
    }
    $v_stored_filename = $p_filename;
    if ($p_remove_all_dir) {
      $v_stored_filename = basename($p_filename);
    }
    else if ($p_remove_dir != "")
    {
      if (substr($p_remove_dir, -1) != '/')
        $p_remove_dir .= "/";
      if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./"))
      {
        if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./"))
          $p_remove_dir = "./".$p_remove_dir;
        if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./"))
          $p_remove_dir = substr($p_remove_dir, 2);
      }
      $v_compare = $this->_tool_PathInclusion($p_remove_dir, $p_filename);
      if ($v_compare > 0)
      {
        if ($v_compare == 2) {
          $v_stored_filename = "";
        }
        else {
          $v_stored_filename = substr($p_filename, strlen($p_remove_dir));
        }
      }
    }
    if ($p_add_dir != "")
    {
      if (substr($p_add_dir, -1) == "/")
        $v_stored_filename = $p_add_dir.$v_stored_filename;
      else
        $v_stored_filename = $p_add_dir."/".$v_stored_filename;
    }
    $v_stored_filename = $this->_tool_PathReduction($v_stored_filename);
    clearstatcache();
    $p_header['version'] = 20;
    $p_header['version_extracted'] = 10;
    $p_header['flag'] = 0;
    $p_header['compression'] = 0;
    $p_header['mtime'] = filemtime($p_filename);
    $p_header['crc'] = 0;
    $p_header['compressed_size'] = 0;
    $p_header['size'] = filesize($p_filename);
    $p_header['filename_len'] = strlen($p_filename);
    $p_header['extra_len'] = 0;
    $p_header['comment_len'] = 0;
    $p_header['disk'] = 0;
    $p_header['internal'] = 0;
    $p_header['external'] = (is_file($p_filename)?0xFE49FFE0:0x41FF0010);
    $p_header['offset'] = 0;
    $p_header['filename'] = $p_filename;
    $p_header['stored_filename'] = $v_stored_filename;
    $p_header['extra'] = '';
    $p_header['comment'] = '';
    $p_header['status'] = 'ok';
    $p_header['index'] = -1;
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_PRE_ADD]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_PRE_ADD] != '')) {
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_header, $v_local_header);
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_PRE_ADD].'(ARCHIVE_ZIP_PARAM_PRE_ADD, $v_local_header);');
      if ($v_result == 0) {
        $p_header['status'] = "skipped";
        $v_result = 1;
      }
      if ($p_header['stored_filename'] != $v_local_header['stored_filename']) {
        $p_header['stored_filename'] = $this->_tool_PathReduction($v_local_header['stored_filename']);
      }
    }
    if ($p_header['stored_filename'] == "") {
      $p_header['status'] = "filtered";
    }
    if (strlen($p_header['stored_filename']) > 0xFF) {
      $p_header['status'] = 'filename_too_long';
    }
    if ($p_header['status'] == 'ok') {
      if (is_file($p_filename))
      {
        if (($v_file = @fopen($p_filename, "rb")) == 0) {
          $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
          return Archive_Zip::errorCode();
        }
        if ($p_params['no_compression']) {
          $v_content_compressed = @fread($v_file, $p_header['size']);
          $p_header['crc'] = crc32($v_content_compressed);
        }
        else {
          $v_content = @fread($v_file, $p_header['size']);
          $p_header['crc'] = crc32($v_content);
          $v_content_compressed = gzdeflate($v_content);
        }
        $p_header['compressed_size'] = strlen($v_content_compressed);
        $p_header['compression'] = 8;
        if (($v_result = $this->_writeFileHeader($p_header)) != 1) {
          @fclose($v_file);
          return $v_result;
        }
        $v_binary_data = pack('a'.$p_header['compressed_size'], $v_content_compressed);
        @fwrite($this->_zip_fd, $v_binary_data, $p_header['compressed_size']);
        @fclose($v_file);
      }
      else
      {
        $p_header['filename'] .= '/';
        $p_header['filename_len']++;
        $p_header['size'] = 0;
        $p_header['external'] = 0x41FF0010;   
        if (($v_result = $this->_writeFileHeader($p_header)) != 1)
        {
          return $v_result;
        }
      }
    }
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_POST_ADD]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_POST_ADD] != '')) {
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_header, $v_local_header);
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_POST_ADD].'(ARCHIVE_ZIP_PARAM_POST_ADD, $v_local_header);');
      if ($v_result == 0) {
        $v_result = 1;
      }
    }
    return $v_result;
  }
  function _writeFileHeader(&$p_header)
  {
    $v_result=1;
    $p_header['offset'] = ftell($this->_zip_fd);
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
    $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50, $p_header['version'], $p_header['flag'],
                          $p_header['compression'], $v_mtime, $v_mdate,
                          $p_header['crc'], $p_header['compressed_size'], $p_header['size'],
                          strlen($p_header['stored_filename']), $p_header['extra_len']);
    fputs($this->_zip_fd, $v_binary_data, 30);
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->_zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['extra'], $p_header['extra_len']);
    }
    return $v_result;
  }
  function _writeCentralFileHeader(&$p_header)
  {
    $v_result=1;
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
    $v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50, $p_header['version'], $p_header['version_extracted'],
                          $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'],
                          $p_header['compressed_size'], $p_header['size'],
                          strlen($p_header['stored_filename']), $p_header['extra_len'], $p_header['comment_len'],
                          $p_header['disk'], $p_header['internal'], $p_header['external'], $p_header['offset']);
    fputs($this->_zip_fd, $v_binary_data, 46);
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->_zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['extra'], $p_header['extra_len']);
    }
    if ($p_header['comment_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['comment'], $p_header['comment_len']);
    }
    return $v_result;
  }
  function _writeCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment)
  {
    $v_result=1;
    $v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries, $p_nb_entries, $p_size, $p_offset, strlen($p_comment));
    fputs($this->_zip_fd, $v_binary_data, 22);
    if (strlen($p_comment) != 0)
    {
      fputs($this->_zip_fd, $p_comment, strlen($p_comment));
    }
    return $v_result;
  }
  function _list(&$p_list)
  {
    $v_result=1;
    if (($this->_zip_fd = @fopen($this->_zipname, 'rb')) == 0)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->_zipname.'\' in binary read mode');
      return Archive_Zip::errorCode();
    }
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      return $v_result;
    }
    @rewind($this->_zip_fd);
    if (@fseek($this->_zip_fd, $v_central_dir['offset']))
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
      return Archive_Zip::errorCode();
    }
    for ($i=0; $i<$v_central_dir['entries']; $i++)
    {
      if (($v_result = $this->_readCentralFileHeader($v_header)) != 1)
      {
        return $v_result;
      }
      $v_header['index'] = $i;
      $this->_convertHeader2FileInfo($v_header, $p_list[$i]);
      unset($v_header);
    }
    $this->_closeFd();
    return $v_result;
  }
  function _convertHeader2FileInfo($p_header, &$p_info)
  {
    $v_result=1;
    $p_info['filename'] = $p_header['filename'];
    $p_info['stored_filename'] = $p_header['stored_filename'];
    $p_info['size'] = $p_header['size'];
    $p_info['compressed_size'] = $p_header['compressed_size'];
    $p_info['mtime'] = $p_header['mtime'];
    $p_info['comment'] = $p_header['comment'];
    $p_info['folder'] = (($p_header['external']&0x00000010)==0x00000010);
    $p_info['index'] = $p_header['index'];
    $p_info['status'] = $p_header['status'];
    return $v_result;
  }
  function _extractByRule(&$p_file_list, &$p_params)
  {
    $v_result=1;
	$p_path = $p_params['add_path'];
	$p_remove_path = $p_params['remove_path'];
	$p_remove_all_path = $p_params['remove_all_path'];
    if (($p_path == "")
	    || ((substr($p_path, 0, 1) != "/")
	    && (substr($p_path, 0, 3) != "../") && (substr($p_path,1,2)!=":/")))
      $p_path = "./".$p_path;
    if (($p_path != "./") && ($p_path != "/")) {
      while (substr($p_path, -1) == "/") {
        $p_path = substr($p_path, 0, strlen($p_path)-1);
      }
    }
    if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/')) {
      $p_remove_path .= '/';
    }
    $p_remove_path_size = strlen($p_remove_path);
    if (($v_result = $this->_openFd('rb')) != 1)
    {
      return $v_result;
    }
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      $this->_closeFd();
      return $v_result;
    }
    $v_pos_entry = $v_central_dir['offset'];
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++) {
      @rewind($this->_zip_fd);
      if (@fseek($this->_zip_fd, $v_pos_entry)) {
        $this->_closeFd();
        $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
		                 'Invalid archive size');
        return Archive_Zip::errorCode();
      }
      $v_header = array();
      if (($v_result = $this->_readCentralFileHeader($v_header)) != 1) {
        $this->_closeFd();
        return $v_result;
      }
      $v_header['index'] = $i;
      $v_pos_entry = ftell($this->_zip_fd);
      $v_extract = false;
      if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
          && ($p_params[ARCHIVE_ZIP_PARAM_BY_NAME] != 0)) {
          for ($j=0;
		          ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
			   && (!$v_extract);
			   $j++) {
              if (substr($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j], -1) == "/") {
                  if (   (strlen($v_header['stored_filename']) > strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]))
                      && (substr($v_header['stored_filename'], 0, strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_extract = true;
                  }
              }
              elseif ($v_header['stored_filename'] == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]) {
                  $v_extract = true;
              }
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_EREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_EREG] != "")) {
          if (ereg($p_params[ARCHIVE_ZIP_PARAM_BY_EREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_PREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_PREG] != "")) {
          if (preg_match($p_params[ARCHIVE_ZIP_PARAM_BY_PREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX] != 0)) {
          for ($j=$j_start; ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX])) && (!$v_extract); $j++) {
              if (($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']) && ($i<=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end'])) {
                  $v_extract = true;
              }
              if ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }
              if ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }
      else {
          $v_extract = true;
      }
      if ($v_extract)
      {
        @rewind($this->_zip_fd);
        if (@fseek($this->_zip_fd, $v_header['offset']))
        {
          $this->_closeFd();
          $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
          return Archive_Zip::errorCode();
        }
        if ($p_params[ARCHIVE_ZIP_PARAM_EXTRACT_AS_STRING]) {
          if (($v_result = $this->_extractFileAsString($v_header, $v_string)) != 1)
          {
            $this->_closeFd();
            return $v_result;
          }
          if (($v_result = $this->_convertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted])) != 1)
          {
            $this->_closeFd();
            return $v_result;
          }
          $p_file_list[$v_nb_extracted]['content'] = $v_string;
          $v_nb_extracted++;
        }
        else {
          if (($v_result = $this->_extractFile($v_header, $p_path, $p_remove_path, $p_remove_all_path, $p_params)) != 1)
          {
            $this->_closeFd();
            return $v_result;
          }
          if (($v_result = $this->_convertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1)
          {
            $this->_closeFd();
            return $v_result;
          }
        }
      }
    }
    $this->_closeFd();
    return $v_result;
  }
  function _extractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_params)
  {
    $v_result=1;
    if (($v_result = $this->_readFileHeader($v_header)) != 1)
    {
      return $v_result;
    }
    if ($p_remove_all_path == true) {
        $p_entry['filename'] = basename($p_entry['filename']);
    }
    else if ($p_remove_path != "")
    {
      if ($this->_tool_PathInclusion($p_remove_path, $p_entry['filename']) == 2)
      {
        $p_entry['status'] = "filtered";
        return $v_result;
      }
      $p_remove_path_size = strlen($p_remove_path);
      if (substr($p_entry['filename'], 0, $p_remove_path_size) == $p_remove_path)
      {
        $p_entry['filename'] = substr($p_entry['filename'], $p_remove_path_size);
      }
    }
    if ($p_path != '')
    {
      $p_entry['filename'] = $p_path."/".$p_entry['filename'];
    }
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT] != '')) {
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_entry, $v_local_header);
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT].'(ARCHIVE_ZIP_PARAM_PRE_EXTRACT, $v_local_header);');
      if ($v_result == 0) {
        $p_entry['status'] = "skipped";
        $v_result = 1;
      }
      $p_entry['filename'] = $v_local_header['filename'];
    }
    if ($p_entry['status'] == 'ok') {
    if (file_exists($p_entry['filename']))
    {
      if (is_dir($p_entry['filename']))
      {
        $p_entry['status'] = "already_a_directory";
      }
      else if (!is_writeable($p_entry['filename']))
      {
        $p_entry['status'] = "write_protected";
      }
      else if (filemtime($p_entry['filename']) > $p_entry['mtime'])
      {
        $p_entry['status'] = "newer_exist";
      }
    }
    else {
      if ((($p_entry['external']&0x00000010)==0x00000010) || (substr($p_entry['filename'], -1) == '/'))
        $v_dir_to_check = $p_entry['filename'];
      else if (!strstr($p_entry['filename'], "/"))
        $v_dir_to_check = "";
      else
        $v_dir_to_check = dirname($p_entry['filename']);
      if (($v_result = $this->_dirCheck($v_dir_to_check, (($p_entry['external']&0x00000010)==0x00000010))) != 1) {
        $p_entry['status'] = "path_creation_fail";
        $v_result = 1;
      }
    }
    }
    if ($p_entry['status'] == 'ok') {
      if (!(($p_entry['external']&0x00000010)==0x00000010))
      {
        if ($p_entry['compressed_size'] == $p_entry['size'])
        {
          if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0)
          {
            $p_entry['status'] = "write_error";
            return $v_result;
          }
          $v_size = $p_entry['compressed_size'];
          while ($v_size != 0)
          {
            $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
            $v_buffer = fread($this->_zip_fd, $v_read_size);
            $v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($v_dest_file, $v_binary_data, $v_read_size);
            $v_size -= $v_read_size;
          }
          fclose($v_dest_file);
          touch($p_entry['filename'], $p_entry['mtime']);
        }
        else
        {
          if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
            $p_entry['status'] = "write_error";
            return $v_result;
          }
          $v_buffer = @fread($this->_zip_fd, $p_entry['compressed_size']);
          $v_file_content = gzinflate($v_buffer);
          unset($v_buffer);
          @fwrite($v_dest_file, $v_file_content, $p_entry['size']);
          unset($v_file_content);
          @fclose($v_dest_file);
          touch($p_entry['filename'], $p_entry['mtime']);
        }
        if (   (isset($p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD]))
		    && ($p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD] != 0)) {
          chmod($p_entry['filename'], $p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD]);
        }
      }
    }
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT] != '')) {
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_entry, $v_local_header);
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT].'(ARCHIVE_ZIP_PARAM_POST_EXTRACT, $v_local_header);');
    }
    return $v_result;
  }
  function _extractFileAsString(&$p_entry, &$p_string)
  {
    $v_result=1;
    $v_header = array();
    if (($v_result = $this->_readFileHeader($v_header)) != 1)
    {
      return $v_result;
    }
    if (!(($p_entry['external']&0x00000010)==0x00000010))
    {
      if ($p_entry['compressed_size'] == $p_entry['size'])
      {
        $p_string = fread($this->_zip_fd, $p_entry['compressed_size']);
      }
      else
      {
        $v_data = fread($this->_zip_fd, $p_entry['compressed_size']);
        $p_string = gzinflate($v_data);
      }
    }
    else {
    }
    return $v_result;
  }
  function _readFileHeader(&$p_header)
  {
    $v_result=1;
    $v_binary_data = @fread($this->_zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);
    if ($v_data['id'] != 0x04034b50)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, 'Invalid archive structure');
      return Archive_Zip::errorCode();
    }
    $v_binary_data = fread($this->_zip_fd, 26);
    if (strlen($v_binary_data) != 26)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));
      return Archive_Zip::errorCode();
    }
    $v_data = unpack('vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $v_binary_data);
    $p_header['filename'] = fread($this->_zip_fd, $v_data['filename_len']);
    if ($v_data['extra_len'] != 0) {
      $p_header['extra'] = fread($this->_zip_fd, $v_data['extra_len']);
    }
    else {
      $p_header['extra'] = '';
    }
    $p_header['compression'] = $v_data['compression'];
    $p_header['size'] = $v_data['size'];
    $p_header['compressed_size'] = $v_data['compressed_size'];
    $p_header['crc'] = $v_data['crc'];
    $p_header['flag'] = $v_data['flag'];
    $p_header['mdate'] = $v_data['mdate'];
    $p_header['mtime'] = $v_data['mtime'];
    if ($p_header['mdate'] && $p_header['mtime'])
    {
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;
      $p_header['mtime'] = mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);
    }
    else
    {
      $p_header['mtime'] = time();
    }
    $p_header['stored_filename'] = $p_header['filename'];
    $p_header['status'] = "ok";
    return $v_result;
  }
  function _readCentralFileHeader(&$p_header)
  {
    $v_result=1;
    $v_binary_data = @fread($this->_zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);
    if ($v_data['id'] != 0x02014b50)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, 'Invalid archive structure');
      return Archive_Zip::errorCode();
    }
    $v_binary_data = fread($this->_zip_fd, 42);
    if (strlen($v_binary_data) != 42)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));
      return Archive_Zip::errorCode();
    }
    $p_header = unpack('vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $v_binary_data);
    if ($p_header['filename_len'] != 0)
      $p_header['filename'] = fread($this->_zip_fd, $p_header['filename_len']);
    else
      $p_header['filename'] = '';
    if ($p_header['extra_len'] != 0)
      $p_header['extra'] = fread($this->_zip_fd, $p_header['extra_len']);
    else
      $p_header['extra'] = '';
    if ($p_header['comment_len'] != 0)
      $p_header['comment'] = fread($this->_zip_fd, $p_header['comment_len']);
    else
      $p_header['comment'] = '';
    if ($p_header['mdate'] && $p_header['mtime'])
    {
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;
      $p_header['mtime'] = mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);
    }
    else
    {
      $p_header['mtime'] = time();
    }
    $p_header['stored_filename'] = $p_header['filename'];
    $p_header['status'] = 'ok';
    if (substr($p_header['filename'], -1) == '/')
    {
      $p_header['external'] = 0x41FF0010;
    }
    return $v_result;
  }
  function _readEndCentralDir(&$p_central_dir)
  {
    $v_result=1;
    $v_size = filesize($this->_zipname);
    @fseek($this->_zip_fd, $v_size);
    if (@ftell($this->_zip_fd) != $v_size) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   'Unable to go to the end of the archive \''
					   .$this->_zipname.'\'');
      return Archive_Zip::errorCode();
    }
    $v_found = 0;
    if ($v_size > 26) {
      @fseek($this->_zip_fd, $v_size-22);
      if (($v_pos = @ftell($this->_zip_fd)) != ($v_size-22)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 'Unable to seek back to the middle of the archive \''
						 .$this->_zipname.'\'');
        return Archive_Zip::errorCode();
      }
      $v_binary_data = @fread($this->_zip_fd, 4);
      $v_data = unpack('Vid', $v_binary_data);
      if ($v_data['id'] == 0x06054b50) {
        $v_found = 1;
      }
      $v_pos = ftell($this->_zip_fd);
    }
    if (!$v_found) {
      $v_maximum_size = 65557; 
      if ($v_maximum_size > $v_size)
        $v_maximum_size = $v_size;
      @fseek($this->_zip_fd, $v_size-$v_maximum_size);
      if (@ftell($this->_zip_fd) != ($v_size-$v_maximum_size)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 'Unable to seek back to the middle of the archive \''
						 .$this->_zipname.'\'');
        return Archive_Zip::errorCode();
      }
      $v_pos = ftell($this->_zip_fd);
      $v_bytes = 0x00000000;
      while ($v_pos < $v_size) {
        $v_byte = @fread($this->_zip_fd, 1);
        $v_bytes = ($v_bytes << 8) | Ord($v_byte);
        if ($v_bytes == 0x504b0506) {
          $v_pos++;
          break;
        }
        $v_pos++;
      }
      if ($v_pos == $v_size) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 "Unable to find End of Central Dir Record signature");
        return Archive_Zip::errorCode();
      }
    }
    $v_binary_data = fread($this->_zip_fd, 18);
    if (strlen($v_binary_data) != 18) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   "Invalid End of Central Dir Record size : "
					   .strlen($v_binary_data));
      return Archive_Zip::errorCode();
    }
    $v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);
    if (($v_pos + $v_data['comment_size'] + 18) != $v_size) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   "Fail to find the right signature");
      return Archive_Zip::errorCode();
    }
    if ($v_data['comment_size'] != 0)
      $p_central_dir['comment'] = fread($this->_zip_fd, $v_data['comment_size']);
    else
      $p_central_dir['comment'] = '';
    $p_central_dir['entries'] = $v_data['entries'];
    $p_central_dir['disk_entries'] = $v_data['disk_entries'];
    $p_central_dir['offset'] = $v_data['offset'];
    $p_central_dir['size'] = $v_data['size'];
    $p_central_dir['disk'] = $v_data['disk'];
    $p_central_dir['disk_start'] = $v_data['disk_start'];
    return $v_result;
  }
  function _deleteByRule(&$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();
    if (($v_result=$this->_openFd('rb')) != 1)
    {
      return $v_result;
    }
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      $this->_closeFd();
      return $v_result;
    }
    @rewind($this->_zip_fd);
    $v_pos_entry = $v_central_dir['offset'];
    @rewind($this->_zip_fd);
    if (@fseek($this->_zip_fd, $v_pos_entry)) {
      $this->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
	                   'Invalid archive size');
      return Archive_Zip::errorCode();
    }
    $v_header_list = array();
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++) {
      $v_header_list[$v_nb_extracted] = array();
      $v_result
	    = $this->_readCentralFileHeader($v_header_list[$v_nb_extracted]);
      if ($v_result != 1) {
        $this->_closeFd();
        return $v_result;
      }
      $v_header_list[$v_nb_extracted]['index'] = $i;
      $v_found = false;
      if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
          && ($p_params[ARCHIVE_ZIP_PARAM_BY_NAME] != 0)) {
          for ($j=0;
		       ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
			     && (!$v_found);
			   $j++) {
              if (substr($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j], -1) == "/") {
                  if (   (strlen($v_header_list[$v_nb_extracted]['stored_filename']) > strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]))
                      && (substr($v_header_list[$v_nb_extracted]['stored_filename'], 0, strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_found = true;
                  }
                  elseif (   (($v_header_list[$v_nb_extracted]['external']&0x00000010)==0x00000010) 
                          && ($v_header_list[$v_nb_extracted]['stored_filename'].'/' == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_found = true;
                  }
              }
              elseif ($v_header_list[$v_nb_extracted]['stored_filename']
			          == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]) {
                  $v_found = true;
              }
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_EREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_EREG] != "")) {
          if (ereg($p_params[ARCHIVE_ZIP_PARAM_BY_EREG],
		           $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_PREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_PREG] != "")) {
          if (preg_match($p_params[ARCHIVE_ZIP_PARAM_BY_PREG],
		                 $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX] != 0)) {
          for ($j=$j_start;
		       ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
			     && (!$v_found);
			   $j++) {
              if (   ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start'])
			      && ($i<=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end'])) {
                  $v_found = true;
              }
              if ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }
              if ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }
      if ($v_found) {
        unset($v_header_list[$v_nb_extracted]);
      }
      else {
        $v_nb_extracted++;
      }
    }
    if ($v_nb_extracted > 0) {
        $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-')
		                   .'.tmp';
        $v_temp_zip = new Archive_Zip($v_zip_temp_name);
        if (($v_result = $v_temp_zip->_openFd('wb')) != 1) {
            $this->_closeFd();
            return $v_result;
        }
        for ($i=0; $i<sizeof($v_header_list); $i++) {
            @rewind($this->_zip_fd);
            if (@fseek($this->_zip_fd,  $v_header_list[$i]['offset'])) {
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);
                $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
				                 'Invalid archive size');
                return Archive_Zip::errorCode();
            }
            if (($v_result = $this->_readFileHeader($v_header_list[$i])) != 1) {
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);
                return $v_result;
            }
            $v_result = $v_temp_zip->_writeFileHeader($v_header_list[$i]);
            if ($v_result != 1) {
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);
                return $v_result;
            }
            $v_result = $this->_tool_CopyBlock($this->_zip_fd,
			                                   $v_temp_zip->_zip_fd,
								       $v_header_list[$i]['compressed_size']);
            if ($v_result != 1) {
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);
                return $v_result;
            }
        }
        $v_offset = @ftell($v_temp_zip->_zip_fd);
        for ($i=0; $i<sizeof($v_header_list); $i++) {
            $v_result=$v_temp_zip->_writeCentralFileHeader($v_header_list[$i]);
            if ($v_result != 1) {
                $v_temp_zip->_closeFd();
                $this->_closeFd();
                @unlink($v_zip_temp_name);
                return $v_result;
            }
            $v_temp_zip->_convertHeader2FileInfo($v_header_list[$i],
			                                     $p_result_list[$i]);
        }
        $v_comment = '';
        $v_size = @ftell($v_temp_zip->_zip_fd)-$v_offset;
        $v_result = $v_temp_zip->_writeCentralHeader(sizeof($v_header_list),
		                                             $v_size, $v_offset,
													 $v_comment);
        if ($v_result != 1) {
            unset($v_header_list);
            $v_temp_zip->_closeFd();
            $this->_closeFd();
            @unlink($v_zip_temp_name);
            return $v_result;
        }
        $v_temp_zip->_closeFd();
        $this->_closeFd();
        @unlink($this->_zipname);
        $this->_tool_Rename($v_zip_temp_name, $this->_zipname);
        unset($v_temp_zip);
    }
    return $v_result;
  }
  function _dirCheck($p_dir, $p_is_dir=false)
  {
    $v_result = 1;
    if (($p_is_dir) && (substr($p_dir, -1)=='/')) {
      $p_dir = substr($p_dir, 0, strlen($p_dir)-1);
    }
    if ((is_dir($p_dir)) || ($p_dir == "")) {
      return 1;
    }
    $p_parent_dir = dirname($p_dir);
    if ($p_parent_dir != $p_dir) {
      if ($p_parent_dir != "") {
        if (($v_result = $this->_dirCheck($p_parent_dir)) != 1) {
          return $v_result;
        }
      }
    }
    if (!@mkdir($p_dir, 0777)) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_DIR_CREATE_FAIL,
	                   "Unable to create directory '$p_dir'");
      return Archive_Zip::errorCode();
    }
    return $v_result;
  }
  function _merge(&$p_archive_to_add)
  {
    $v_result=1;
    if (!is_file($p_archive_to_add->_zipname)) {
      return 1;
    }
    if (!is_file($this->_zipname)) {
      $v_result = $this->_duplicate($p_archive_to_add->_zipname);
      return $v_result;
    }
    if (($v_result=$this->_openFd('rb')) != 1) {
      return $v_result;
    }
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1) {
      $this->_closeFd();
      return $v_result;
    }
    @rewind($this->_zip_fd);
    if (($v_result=$p_archive_to_add->_openFd('rb')) != 1) {
      $this->_closeFd();
      return $v_result;
    }
    $v_central_dir_to_add = array();
    $v_result = $p_archive_to_add->_readEndCentralDir($v_central_dir_to_add);
    if ($v_result != 1) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      return $v_result;
    }
    @rewind($p_archive_to_add->_zip_fd);
    $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-').'.tmp';
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open temporary file \''
					   .$v_zip_temp_name.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $v_size = $v_central_dir_to_add['offset'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($p_archive_to_add->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $v_offset = @ftell($v_zip_temp_fd);
    $v_size = $v_central_dir['size'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $v_size = $v_central_dir_to_add['size'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($p_archive_to_add->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $v_comment = '';
    $v_size = @ftell($v_zip_temp_fd)-$v_offset;
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;
    if (($v_result = $this->_writeCentralHeader($v_central_dir['entries']
	                                          +$v_central_dir_to_add['entries'],
												$v_size, $v_offset,
												$v_comment)) != 1) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      @fclose($v_zip_temp_fd);
      $this->_zip_fd = null;
      unset($v_header_list);
      return $v_result;
    }
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;
    $this->_closeFd();
    $p_archive_to_add->_closeFd();
    @fclose($v_zip_temp_fd);
    @unlink($this->_zipname);
    $this->_tool_Rename($v_zip_temp_name, $this->_zipname);
    return $v_result;
  }
  function _duplicate($p_archive_filename)
  {
    $v_result=1;
    if (!is_file($p_archive_filename)) {
      $v_result = 1;
      return $v_result;
    }
    if (($v_result=$this->_openFd('wb')) != 1) {
      return $v_result;
    }
    if (($v_zip_temp_fd = @fopen($p_archive_filename, 'rb')) == 0) {
      $this->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open archive file \''
					   .$p_archive_filename.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }
    $v_size = filesize($p_archive_filename);
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->_zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    $this->_closeFd();
    @fclose($v_zip_temp_fd);
    return $v_result;
  }
  function _check_parameters(&$p_params, $p_default)
  {
    if (!is_array($p_params)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
		                 'Unsupported parameter, waiting for an array');
        return Archive_Zip::errorCode();
    }
    for (reset($p_params); list($v_key, $v_value) = each($p_params); ) {
    	if (!isset($p_default[$v_key])) {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 'Unsupported parameter with key \''.$v_key.'\'');
            return Archive_Zip::errorCode();
    	}
    }
    for (reset($p_default); list($v_key, $v_value) = each($p_default); ) {
    	if (!isset($p_params[$v_key])) {
    		$p_params[$v_key] = $p_default[$v_key];
    	}
    }
    $v_callback_list = array ('callback_pre_add','callback_post_add',
	                          'callback_pre_extract','callback_post_extract');
    for ($i=0; $i<sizeof($v_callback_list); $i++) {
    	$v_key=$v_callback_list[$i];
        if (   (isset($p_params[$v_key])) && ($p_params[$v_key] != '')) {
            if (!function_exists($p_params[$v_key])) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAM_VALUE,
				                 "Callback '".$p_params[$v_key]
								 ."()' is not an existing function for "
								 ."parameter '".$v_key."'");
                return Archive_Zip::errorCode();
            }
	    }
    }
    return(1);
  }
  function _errorLog($p_error_code=0, $p_error_string='')
  {
      $this->_error_code = $p_error_code;
      $this->_error_string = $p_error_string;
  }
  function _errorReset()
  {
      $this->_error_code = 1;
      $this->_error_string = '';
  }
  function _tool_PathReduction($p_dir)
  {
    $v_result = "";
    if ($p_dir != "")
    {
      $v_list = explode("/", $p_dir);
      for ($i=sizeof($v_list)-1; $i>=0; $i--)
      {
        if ($v_list[$i] == ".")
        {
        }
        else if ($v_list[$i] == "..")
        {
          $i--;
        }
        else if (($v_list[$i] == "") && ($i!=(sizeof($v_list)-1)) && ($i!=0))
        {
        }
        else
        {
          $v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?"/".$v_result:"");
        }
      }
    }
    return $v_result;
  }
  function _tool_PathInclusion($p_dir, $p_path)
  {
    $v_result = 1;
    $v_list_dir = explode("/", $p_dir);
    $v_list_dir_size = sizeof($v_list_dir);
    $v_list_path = explode("/", $p_path);
    $v_list_path_size = sizeof($v_list_path);
    $i = 0;
    $j = 0;
    while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {
      if ($v_list_dir[$i] == '') {
        $i++;
        continue;
      }
      if ($v_list_path[$j] == '') {
        $j++;
        continue;
      }
      if (   ($v_list_dir[$i] != $v_list_path[$j])
	      && ($v_list_dir[$i] != '')
		  && ( $v_list_path[$j] != ''))  {
        $v_result = 0;
      }
      $i++;
      $j++;
    }
    if ($v_result) {
      while (($j < $v_list_path_size) && ($v_list_path[$j] == '')) $j++;
      while (($i < $v_list_dir_size) && ($v_list_dir[$i] == '')) $i++;
      if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) {
        $v_result = 2;
      }
      else if ($i < $v_list_dir_size) {
        $v_result = 0;
      }
    }
    return $v_result;
  }
  function _tool_CopyBlock($p_src, $p_dest, $p_size, $p_mode=0)
  {
    $v_result = 1;
    if ($p_mode==0)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==1)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==2)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==3)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    return $v_result;
  }
  function _tool_Rename($p_src, $p_dest)
  {
    $v_result = 1;
    if (!@rename($p_src, $p_dest)) {
      if (!@copy($p_src, $p_dest)) {
        $v_result = 0;
      }
      else if (!@unlink($p_src)) {
        $v_result = 0;
      }
    }
    return $v_result;
  }
  function _tool_TranslateWinPath($p_path, $p_remove_disk_letter=true)
  {
    if (stristr(php_uname(), 'windows')) {
      if (   ($p_remove_disk_letter)
	      && (($v_position = strpos($p_path, ':')) != false)) {
          $p_path = substr($p_path, $v_position+1);
      }
      if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
          $p_path = strtr($p_path, '\\', '/');
      }
    }
    return $p_path;
  }
  }
?>
