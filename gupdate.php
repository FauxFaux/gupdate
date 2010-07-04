<?
function hextuple($x) {
	return str_pad(dechex($x), '0', 2, STR_PAD_LEFT);
}

$iphex = join('', array_map('hextuple', split('\\.', $_SERVER['REMOTE_ADDR'])));

$apps['{8A69D345-D564-463C-AFF1-A69D9E530F96}'] = 'Chrome';
$apps['{74AF07D8-FB8F-4d51-8AC7-927721D56EBB}'] = 'Earth';
$gver = $_GET{'ver'};
$gapp = $_GET{'app'};
$gos = $_GET{'os'};
$gsp = $_GET{'sp'};
$dev = $_GET{'dev'};
$beta = $_GET{'beta'};

if (!$gver) {
	if (preg_match(',Chrome/(\d+\.\d+\.\d+\.\d+),', $_SERVER['HTTP_USER_AGENT'], $args))
		$gver = $args[1];
}

$valv = preg_match('/^\d+\.\d+\.\d+\.\d+$/', $gver);
$valapp = isset($apps[$gapp]);
$valos = is_numeric($gos);
$valsp = preg_match('/^[ \w\d]*$/', $gsp);
$val = $valv && $valapp && $valsp;

$inv='style="border: 1px solid red"';
?>
<form method="get">
<ul>
<li><input <?=$gver&&!$valv?$inv:''?>type="text" name="ver" value="<?=htmlentities($gver)?>"/></li>
<li><select <?=$gapp&&!$valapp?$inv:''?> type="text" name="app">
<?	foreach ($apps as $id => $name)
		echo "<option value='$id'>$name</option>\n";
?></select></li>
<li><select <?=$gos&&!$valos?$inv:''?> name="os">
	<option value="6.0">Vista</option>
	<option value="5.1">XP</option>
</select></li>
<li><select <?=$gsp &&!$valsp?$inv:''?> type="text" name="sp"></li>
	<option>Service Pack 2</option>
	<option>Service Pack 1</option>
</select>
<li><input type="checkbox" name="beta" <?=$beta?'checked="checked"':''?>/> Chrome 2.0-beta channel.</li>
<li><input type="checkbox" name="dev" <?=$dev?'checked="checked"':''?>/> Chrome 2.0-dev channel.</li>
<li><input type="submit"/></li>
</ul>
</form>
<?
if (!$val)
	exit;
$con='<?xml version="1.0" encoding="UTF-8"?><o:gupdate xmlns:o="http://www.google.com/update2/request" protocol="2.0" version="1.2.183.7" ismachine="0" machineid="{B08ED3F4-42C4-49be-A5E3-0000' . $iphex . '" userid="{F3627360-8932-49b0-9547-23D24D0D1739}" requestid="{5D7BF504-D356-4155-B30D-' . substr(uniqid(), -12) . '}"><o:os platform="win" version="' . $gos . '" sp="' . $gsp . '"/><o:app appid="' . $gapp . '" version="' . $gver . '" lang="en" brand="GGLS" client="" installsource="scheduler"><o:updatecheck' .  ($dev ? ' tag="2.0-dev"' : '') . ($beta ? ' tag="1.1-beta"' : '') . '/><o:ping active="1"/></o:app></o:gupdate>';

/*
$sock=fsockopen("tools.google.com", 80);
fputs($sock, 'POST http://tools.google.com/service/update2 HTTP/1.1
Content-Length: ' . strlen($con) . "\r\n\r\n" . $con
);
fflush($sock);
$s = fread($sock, 2000);
fclose($sock);
$arr = split("\n", $s);
while ($arr && trim(array_shift($arr)) != '');
$s = join('', $arr);
*/
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://tools.google.com/service/update2");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $con);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$s = curl_exec ($ch);
curl_close ($ch);

$xml = simplexml_load_string($s);

$st = $xml->app->updatecheck['status'];

if ($st == "ok") {
	$ver=$xml->app->updatecheck['Version'];
	$cb= $xml->app->updatecheck['codebase'];
	$app=$xml->app['appid'];
	echo "<p>$ver of " . $apps[(string)$app] . " is available at <a href='$cb'>$cb</a></p>";
} else {
	echo "<p>Status: $st</p>";
}

echo htmlentities($s);

?>

<p><a href="http://git.goeswhere.com/?p=gupdate.git">source</a></p>


