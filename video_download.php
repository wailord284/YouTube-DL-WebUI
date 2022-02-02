<?php
//Enable gzip compression
ob_start("ob_gzhandler");
//Set locale. Might help with weird errors and video encoding
setlocale(LC_ALL,'en_US.UTF-8');
//Create path variables and audio extension
//Do not add a trailing / to any folder patths
$video_path = "/home/alex/videos";
$script_path = "/var/www/wailord284.club/public/dl";
$audio_extension = "m4a";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // collect value of input field
  $videolinkRAW = $_POST['videoLink'];
  $videotype = $_POST['videoOutput'];
  $userip = $_SERVER['REMOTE_ADDR'];
  $browser = include 'browser_info.php';
  $operatingsystem = include 'os_info.php';

  //If the video link is empty, a javascript alert popup box appears letting the user know no URL was entered
  if (empty($videolinkRAW)) {
	echo '<script type="text/JavaScript">
     	alert("Please enter a URL and try again.\nPress OK to go back.");
	window.location.href = "/dl/";
     	</script>';

  } else {
    //Replace all character that could escape
    $videolink_replaced = str_replace(array('\\','$',';','*','"','<','>','|'),'',$videolinkRAW);
    //Sanitize the URL - https://www.php.net/manual/en/filter.filters.sanitize.php
    $videolink = filter_var($videolink_replaced, FILTER_SANITIZE_URL);
    //Generate random string with allowed characters
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    $random_subfolder = substr(str_shuffle($permitted_chars), 0, 20);
    //Create random subfolder with misc characters for each video
    mkdir("$video_path/$random_subfolder");
    //Run the bash script that downloads the video, pass the video URL + subfolder and also set locale
    shell_exec("LC_ALL=en_US.UTF-8 $script_path/ytdl.sh -t $videotype -f $random_subfolder -v $videolink -a $userip -o $operatingsystem -b $browser");

    //scan the random subfolder for all files. Set $download_video to the result
    foreach(new DirectoryIterator("$video_path/$random_subfolder/") as $file) {
	if ($file->isDot()) continue;
	$download_video = $file->getFilename();
	//Replace all character that could escape
	$download_video_replaced = str_replace(array('\\','$','&',';','/',':','*','?','"','<','>','|'),'',$download_video);
	//Get file extension to change the Content-Type
	$video_extension = pathinfo("$video_path/$random_subfolder/$download_video", PATHINFO_EXTENSION);

	//If file extension = m4a change Content-Type
	if ($video_extension == $audio_extension) {
		$file_type = "audio/m4a";
	} else  {
		$file_type = "video/mp4";
	}

	//Set path to upload the downloaded video
        $attachment_location = "$video_path/$random_subfolder/$download_video";
	//Clear cache
	clearstatcache();
	//If the file exists, upload to user. else redirect
	if (file_exists($attachment_location)) {
		//Setup download information
		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		header("Cache-Control: public"); // needed for internet explorer
		header("Content-Type: $file_type");
		header('Content-Description: Video Transfer');
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length:".filesize("$attachment_location"));
		header("Content-Disposition: attachment; filename=$download_video_replaced");
		flush(); //clear system output buffer
		readfile("$attachment_location");
		die();
	//if video file does not exist redirect
	} else {
		header("Location: https://wailord284.club/dl/", TRUE, 400);
		die('No file');
	} //end if file exists
    } //end else

  }
}

?>
