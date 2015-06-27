<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
function sass_compile_less_mincss(){
	
	include( dirname( __FILE__ ) . '/compile_less_sass_class.php' );
	
	$less_file      = dirname( __FILE__ ) . '/assets/css/style.less';
	$css_file       = dirname( __FILE__ ) . '/assets/css/style.css';
	$css_min_file       = dirname( __FILE__ ) . '/assets/css/style.min.css';
	
	$compile = new Compile_Less_Sass;
	
	$compile->compileLessFile( $less_file, $css_file, $css_min_file );
}
sass_compile_less_mincss();
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>SASS PHP</title>
<link media="screen" href="./assets/css/style.min.css" type="text/css" rel="stylesheet">
</head>

<body>
<p element-id="3466" class="pvc_stats pvc_load_by_ajax_update" id="pvc_stats_3466">3,894&nbsp;total views, 1&nbsp;views today</p>
</body>
</html>
