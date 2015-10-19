<?php
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    
    $appid = 218620; // => PayDay2
    $metaLink = "http://www.metacritic.com/game/pc/payday-2";

    //This is for caching, since Steam will ban my server if they got too many requests from it
    function get_content($file,$url,$seconds = 5,$fn = '',$fn_args = '') {
        
        $current_time = time(); $expire_time = $seconds; $file_time = filemtime($file);
        
        if(file_exists($file) && ($current_time - $expire_time < $file_time)) {
            
            return file_get_contents($file);
        }
        else {
            $content = file_get_contents($url);
            if($fn) { $content = $fn($content,$fn_args); }
            file_put_contents($file,$content);
            return $content;
        }
    }

    $json_data = json_decode(get_content("currentPlayer.json", "http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v0001/?appid=$appid"), true);
    $json_data["response"]["appid"] = $appid;
    $json_data["response"]["metacritic_link"] = $metaLink;
    echo json_encode($json_data);
?>