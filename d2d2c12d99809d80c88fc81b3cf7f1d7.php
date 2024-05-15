<form method="get"> 
	<div>
		<label for="link">URL : </label>
		<input type="text" id="link" name="link" />
		<input type="submit" name="button1" class="button" value="GO" /> 
	</div>
</form> 
 <?php
	// Indique aux robots de ne pas indexer la page et de ne pas suivre les liens
	header('X-Robots-Tag: noindex, nofollow', true);
	// Check for time identifier in the youtube video link and conver it into seconds
	function ConvertTimeToSeconds($data)
	{
		$time = null;
		$hours = null;
		$minutes = null;
		$seconds = null;
		$pattern_time_split = "([0-9]{1-2}+[^hms])";
		
		// Regex to check for youtube video link with time identifier
		$youtube_time = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*(t=((\d+h)?(\d+m)?(\d+s)?))~i';
		
		// Check for time identifier in the youtube video link, extract it and convert it to seconds
		if (preg_match($youtube_time, $data, $matches)) {
			// Check for hours
			if (isset($matches[4])) {
				$hours = $matches[4];
				$hours = preg_split($pattern_time_split, $hours);
				$hours = substr($hours[0], 0, -1);
			}
			// Check for minutes
			if (isset($matches[5])) {
				$minutes = $matches[5];
				$minutes = preg_split($pattern_time_split, $minutes);
				$minutes = substr($minutes[0], 0, -1);
			}
			// Check for seconds
			if (isset($matches[6])) {
				$seconds = $matches[6];
				$seconds = preg_split($pattern_time_split, $seconds);
				$seconds = substr($seconds[0], 0, -1);
			}
			// Convert time to seconds
			$time = ((intval($hours,10)*3600) + (intval($minutes,10)*60) + intval($seconds,10));
		}
		
		return $time;
	}
	 // Automatically parse youtube video/playlist links and generate the respective embed code
	function AutoParseYoutubeLink($data)
	{
		// Check if youtube link is a playlist
		if (strpos($data, 'list=') !== false) {
			//echo "<div>playlist</div>";
			// Check if playlist have an index
			if (strpos($data, 'index=') == true) {
				// Generate the embed code
				//echo "<div>index</div>";
				$data = preg_replace('~(?:.*)(?:[?&]list=([^&]+)).*?([?&]index=\d+)(?:.*)~i', 'https://www.youtube.com/embed/?listType=playlist&list=$1$2', $data);
				}
			else {
				//echo "<div>no index</div>";
				// Generate the embed code
				$data = preg_replace('~(?:.*)(?:[?&]list=([^&]+))(?:.*)~i', 'https://www.youtube.com/embed/?listType=playlist&list=$1', $data);
			}
			return $data;
		}
		
		// Check if youtube link is not a playlist but a video [with time identifier]
		if (strpos($data, 'list=') === false && strpos($data, 't=') !== false) {
			$time_in_secs = null;
			
			// Get the time in seconds from the time function
			$time_in_secs = ConvertTimeToSeconds($data);
			
			// Generate the embed code
			$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube.com/embed/$1?start=' . $time_in_secs, $data);
			
			return $data;
		}
		
		// If the above conditions were false then the youtube link is probably just a plain video link. So generate the embed code already.
		$data = preg_replace('~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i', 'https://www.youtube.com/embed/$1', $data);
		
		return $data;
	}
	
	//there are two types of youtube normal url
	//the code below simulatneously takes care of the two.
	//many codes that attempt to do this fail because they don't realize this ancient truth
	function isValidYouTubeURL($url) {
		$pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=|playlist\?list=|embed\/|v\/|.+\?v=)?([^#\&\?]*).*/';
		return preg_match($pattern, $url) === 1;
	}
	
	$url = isset($_GET['link']) ? $_GET['link'] : "https://www.youtube.com/watch?v=rlarCLhzfoU";
		// Validation et filtrage de l'URL
	if (filter_var($url, FILTER_VALIDATE_URL)) {
		// L'URL est valide, on la filtre pour s'assurer qu'elle est sécurisée
		$validated_url = filter_var($url, FILTER_SANITIZE_URL);
	} else {
		// L'URL n'est pas valide
		$validated_url = '';
	}
	
	if (isValidYouTubeURL($validated_url)) {
		$fullEmbedUrl = AutoParseYoutubeLink($validated_url);
		// Échapper l'URL avant de l'afficher dans le HTML
		$fullEmbedUrl = htmlspecialchars($fullEmbedUrl, ENT_QUOTES, 'UTF-8');
		//echo $fullEmbedUrl;
	}
	else {
		echo "L'URL est pas bonne :(";
		$url = "https://www.youtube.com/watch?v=OMshIwFI_JY";
		$fullEmbedUrl = AutoParseYoutubeLink($url);
	}
?>
<!-- the embed code -->
<div>          
	<iframe id="ytplayer" type="text/html" width="560" height="400"
		src="<?php echo $fullEmbedUrl ?>"
		frameborder="0" allowfullscreen></iframe> 
</div>
