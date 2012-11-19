<html>
<body>
<form action="/api.php" method="POST">
<!--<p>JSON: <textarea name="json" cols="50" rows="20">
{
	&quot;request&quot;:&quot;getrecentlocation&quot;,
	&quot;sender&quot;:&quot;test@location.h3ndrik.de&quot;,
	&quot;user&quot;:&quot;test2@location.h3ndrik.de&quot;,
	&quot;auth&quot;:&quot;00000000000000000000000000000000&quot;
}</textarea></p>-->
<p><textarea name="json" cols="50" rows="20"><?php

$json = '{ "request":"getlocationdata", "sender":"test@location.h3ndrik.de", "user":"test2@location.h3ndrik.de", "auth":"00000000000000000000000000000000"}';

echo base64_encode(gzdeflate($json));

?></textarea></p>
<p><input type="submit" /></p>
</form>
</body>
</html>
