<?php
$root_dir = '/tmp';
$handle = opendir($root_dir);
$pattern = "widgetdata";
$files = array();
$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
$search = isset($_GET['search']) ? strtolower($_GET['search']) : false;
$filter = "";

function color($value) {
	if ($value < 100) {
		return "090";
	} else if ($value < 250) {
		return "f90";
	} else {
		return "b00";
	}
}

if ($handle) {
	while (false !== ($file = readdir($handle))) {
		if (preg_match("/$pattern/",$file)) {
			$path = $root_dir . "/" . $file;
			$files[filemtime($path)] = str_replace(".$pattern","",$file);
		}
	}
}
krsort($files);
?>
<script type="text/javascript">
	var active_runs = [];
	function toggle_run_link(hash) {

		exists = false;
		for (i=0; i<active_runs.length; i++) {
			if (active_runs[i] == hash) {
				active_runs.splice(i,1);
				exists = true;
			}
		}
		if (!exists) {
			active_runs.push(hash);
		}

		document.getElementById("aggregated").href='http://<?=gethostname()?>/xhprof/?run='+active_runs.join(',')+'&sort=excl_wt&source=<?=$pattern?>';
	}

	function select_all() {
		active_runs=[];
		var array = document.getElementsByTagName("input");

		for(var ii = 0; ii < array.length; ii++)
		{
			if(array[ii].type == "checkbox")
			{
				if(array[ii].className == 'aggregated')
				{
					array[ii].checked = true;
					toggle_run_link(array[ii].id)
				}
			}
		}
	}

	function unselect_all() {

		var array = document.getElementsByTagName("input");

		for(var ii = 0; ii < array.length; ii++)
		{
			if(array[ii].type == "checkbox")
			{
				if(array[ii].className == 'aggregated')
				{
					array[ii].checked = false;
				}
			}
		}
	}
</script>
<?php
echo "<a href='' id='aggregated'>aggregated Profile of selected runs</a>";

echo "<form>";

echo "<table cellspacing='15'>";
echo "<tr>";
echo "<td><a href='javascript:select_all()'>all</a></a></td>";
echo "<td>Details</td>";
echo "<td>Date</td>";
echo "<td>Match</td>";
echo "<td>Walltime</td>";
echo "</tr>";

$i = 0;
foreach ($files as $file) {
	$i++;

	if ($i > $limit) {
		break;
	}

	$path = $root_dir . "/" . $file . "." . $pattern;
	$xhprof_details=unserialize(file_get_contents($path));
	$walltime = ($xhprof_details["main()"]["wt"] / 1000) . " ms";
	if ($search) {
		$filter = "";
		$keys = array_keys($xhprof_details);
		$keys = implode("",$keys);
		$keys = strtolower($keys);
		if (strpos($keys, $search) !== false) {
			$filter = "found";
		}
	}
	$date = date("Y-m-d H:i",filemtime($path));

	echo "<tr>";
	echo "<td><input type='checkbox' class='aggregated' onchange='toggle_run_link(\"".$file."\")' id='$file'></input></td>";
	echo "<td><a href='http://".gethostname()."/xhprof/?run=".$file."&sort=excl_wt&source=$pattern'>".$file."</a></td>";
	echo "<td>".$date."</td>";
	echo "<td>".$filter."</td>";
	echo "<td style='background-color:#".color($walltime)."'>".$walltime."</td>";
	echo "</tr>";
}
echo "</table>";

echo "</form>";
?>
<a href="?limit=<?=$limit+10?>">show more runs</a>


<script type="text/javascript">
	unselect_all();
</script>