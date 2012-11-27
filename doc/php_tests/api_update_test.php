<html>
<body>
<form action="/api.php" method="POST">
<!--<p>JSON: <textarea name="json" cols="50" rows="20">
{
	&quot;request&quot;:&quot;setlocation&quot;,
	&quot;sender&quot;:&quot;test&quot;,
	&quot;data&quot;:&quot;&quot;&quot;
}</textarea></p>-->
<p><textarea name="json" cols="50" rows="20"><?php

$json = '{ "request":"setlocation", "sender":"test", "data":[{"time":"123", "latitude":"2", "longitude":"3", "altitude":"4", "accuracy":"5", "speed":"6", "bearing":"7", "provider":"8"}, {"time":"987", "latitude":"8", "longitude":"7", "altitude":"6", "accuracy":"5", "speed":"4", "bearing":"3", "provider":"2"}] }';

echo base64_encode(gzdeflate($json));

?></textarea></p>
<p><input type="submit" /></p>
</form>
</body>
</html>
