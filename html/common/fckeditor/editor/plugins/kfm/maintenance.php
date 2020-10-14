<?php
require_once 'initialise.php';
?>
<!DOCTYPE html PUBLIC "-
<html>
<head>
<title>KFM-maintenance</title>
<link rel="stylesheet" href="themes/<?php echo $kfm_theme; ?>/prompt.css" />
<script type="text/javascript" src="j/jquery/jquery-1.2.2.pack.js"></script>
<script type="text/javascript" src="j/jquery/jquery.impromptu.js"></script>
<script type="text/javascript">
	var $j = jQuery.noConflict();
	$j(document).ready(function(){
		$j.prompt($j('#maintenance_complete_html').html(),{
			buttons:{'Show messages':false,'Go to the file manager':true},
			callback:function(v,m){
				if(v)window.location='index.php';
			}
		});
	});
</script>
<style type="text/css">
body{
	background-color:#eeeeee;
}
</style>
</head>
<body>
<div id="maintenance_messages">
<?php
$kfmdb->query('DELETE FROM '.$kfm_db_prefix.'_files WHERE directory=0');
?>
<p>Maintenance done. <a href="index.php">Return to the filemanager</a></p>
</div>
<div id="maintenance_complete_html" style="display:none;">
<h2>Maintenance complete</h2>
</div>
</body>
</html>
