<?php

	function delRecursively ($dirName) {
//		echo $dirName;
		$dir = opendir($dirName);
	
		
		while ($file = readdir($dir)) {
			$gowno = $dirName.'/'.$file;
			if (is_dir($gowno) && $file != '.' && $file != '..' && $file != '.svn')
			{
			//	echo $gowno;
				$kupa = 'rm -Rf '.$gowno.'/.svn'."\n";
				echo $kupa;
				exec($kupa);
				delRecursively($gowno);
			}
		}
	}

	delRecursively('.');
