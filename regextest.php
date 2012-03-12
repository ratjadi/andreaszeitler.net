<?php

$var='
<p><a href="http://andreaszeitler.net/blog/wp-content/uploads/film_0101_038.jpg"><img class="alignleft" border="0" alt="film 0101 038" src="http://andreaszeitler.net/blog/wp-content/uploads/film_0101_038-small.jpg" width="225" height="151" /></a>adef</p>
';

$erg = preg_replace('/(<a)(([^>].)*)(\.[jpg|gif|png])/U', '<a class="azimg" rel="lightbox[1]" ${2}${4}', $var);
echo $erg;
?>