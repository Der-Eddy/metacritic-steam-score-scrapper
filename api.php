<?php
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-type: application/json');
    
    $appid = 218620; // => PayDay2

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

    $steamLink = "http://store.steampowered.com/app/$appid/";

    $json_data = json_decode(get_content("currentPlayer.json", "http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v0001/?appid=$appid"), true);
    $metacritic_score = json_decode(get_content("storefront.json", "http://store.steampowered.com/api/appdetails/?appids=$appid"), true);


    $json_data["response"]["appid"] = $appid;
    $json_data["response"]["metacritic_link"] = $metacritic_score[$appid]["data"]["metacritic"]["url"];
    $json_data["response"]["metacritic_score"] = $metacritic_score[$appid]["data"]["metacritic"]["score"];
    $json_data["response"]["steam_link"] = $steamLink;
    $json_data["response"]["steam_reviews_all"] = $metacritic_score[$appid]["data"]["recommendations"]["total"];
    echo json_encode($json_data);
?>