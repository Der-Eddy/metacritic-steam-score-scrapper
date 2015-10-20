<?php
    header('Cache-Control: no-cache, must-revalidate');
    header('content-type: application/json; charset=utf-8');
    header("access-control-allow-origin: *");
    
    $appid = 218620; // => PayDay2

    //This is for caching, since Steam will ban my server if they got too many requests from it
    function get_content($file,$url,$seconds = 5,$fn = '',$fn_args = '') {
        
        $current_time = time(); $expire_time = $seconds; $file_time = filemtime($file);
        
        if(file_exists($file) && ($current_time - $expire_time < $file_time)) {
            
            return file_get_contents($file);
        }
        else {
            $content = file_get_contents($url);
            if (strlen($content) > 1) {
                return file_get_contents($file);
            }
            if($fn) { $content = $fn($content,$fn_args); }
            file_put_contents($file,$content);
            return $content;
        }
    }

    //http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/    
    function is_valid_callback($subject)
    {
        $identifier_syntax
          = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

        $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
          'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
          'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
          'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
          'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
          'private', 'public', 'yield', 'interface', 'package', 'protected',
          'static', 'null', 'true', 'false');

        return preg_match($identifier_syntax, $subject)
            && ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
    }

    $steamLink = "http://store.steampowered.com/app/$appid/";

    $json_data = json_decode(get_content("currentPlayer.json", "http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v0001/?appid=$appid"), true);
    unset($json_data["response"]["result"]);
    $metacritic_score = json_decode(get_content("storefront.json", "http://store.steampowered.com/api/appdetails/?appids=$appid"), true);

    $metacritic = get_content("metacritic_dump.html", $metacritic_score[$appid]["data"]["metacritic"]["url"], 120);
    preg_match("/<div class=\"metascore_w user large game .*?\">(.*?)<\\/div>/", $metacritic, $metacritic_userscore);

    $steamreview = get_content("steamstore_dump.html", $steamLink, 30);
    preg_match("/<span class=\"user_reviews_count\">\\((.*?)\\)<\\/span>/", $steamreview, $steamreview_positive);
    $steamreview_positive = (int)str_replace(',', '', $steamreview_positive[1]);

    $min_player = json_decode(file_get_contents("minPlayer.json"), true);
    if ($min_player["response"]["player_count"] > $json_data["response"]["player_count"]){
        file_put_contents("minPlayer.json", json_encode($json_data));
    }
    $json_data["response"]["player_count_minimum"] = $min_player["response"]["player_count"];

    $json_data["response"]["appid"] = $appid;
    $json_data["response"]["metacritic"]["link"] = $metacritic_score[$appid]["data"]["metacritic"]["url"];
    $json_data["response"]["metacritic"]["score"] = $metacritic_score[$appid]["data"]["metacritic"]["score"];
    $json_data["response"]["metacritic"]["user_score"] = (float)$metacritic_userscore[1];
    $json_data["response"]["steam"]["link"] = $steamLink;
    $json_data["response"]["steam"]["reviews_all"] = $metacritic_score[$appid]["data"]["recommendations"]["total"];
    $json_data["response"]["steam"]["reviews_positive"] = $steamreview_positive;
    $json_data["response"]["steam"]["reviews_negative"] = $metacritic_score[$appid]["data"]["recommendations"]["total"] - $steamreview_positive;

    $json = json_encode($json_data);

    # JSON if no callback
    if( ! isset($_GET['callback']))
        exit($json);

    # JSONP if valid callback
    if(is_valid_callback($_GET['callback']))
        exit("{$_GET['callback']}($json)");

    # Otherwise, bad request
    header('status: 400 Bad Request', true, 400);
?>