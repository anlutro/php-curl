<?php
foreach ($_FILES as $file) {
	echo $file['name']."\t".$file['size']."\n";
}
