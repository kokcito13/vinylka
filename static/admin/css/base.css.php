<?php
	header("Content-type: text/css");
	$path = dirname( __FILE__) . '/';
	header('Cache-Control: public');
	$cacheFileName = $path . '~base.css';
	$metaFileName = $path . '~meta.inf';
	header("Expires: " . date("D, d M Y H:i:s", 1534248469) . " GMT");//не скоро
	if (isset($_GET['del'])) {//modify
		unlink($cacheFileName);
		file_put_contents($metaFileName, time());
	}	
	if (is_file($cacheFileName)) {//load from cache
		header("Last-Modified: ".date("D, d M Y H:i:s", file_get_contents($metaFileName))." GMT");
		echo file_get_contents($cacheFileName);
	} else {//load all css files and compress it
		$handle = opendir($path);
		$css = '';
		while (($file = readdir($handle))!==false) {//colect css files
			if (preg_match('/^.*\.css$/', $file)) {
				$css .= file_get_contents($path.$file);
			}
		}
		$curl = curl_init();//compress with css YUI (need curl)
		$css = (preg_replace('/(\@?charset \"?utf-8\"?\;?)/i','', $css));
		$header = array();
		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";
		$referer = "http://oskidon.com.ua";
		$browsers = array(
			"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.3) Gecko/2008092510 Ubuntu/10.04 Firefox/3.6.13",
			"Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.30 (KHApplication_Model_Kernel_Catalog_Good_ParserTML, like Gecko) Chrome/12.0.742.77 Safari/534.30",
			"Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.6.13",
			"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)"
		);
		$browser = $browsers[array_rand($browsers)];
		curl_setopt($curl, CURLOPT_USERAGENT, $browser);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 7);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_URL, 'http://refresh-sf.com/yui/#output'); 
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
			'compresstext' => $css,
			'type' => 'css'
		));
		preg_match_all('/\<textarea rows\=\"20\" cols\=\"80\" class\=\"output\"\>(.*)\<\/textarea\>/i', curl_exec($curl), $data);
		file_put_contents($cacheFileName, $data[1][0]);
		file_put_contents($metaFileName, time());
		echo $data[1][0];
		curl_close($curl);
		closedir($handle);
	}