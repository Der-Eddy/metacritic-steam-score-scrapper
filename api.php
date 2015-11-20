<?php
    header('Cache-Control: no-cache, must-revalidate');
    header('content-type: application/json; charset=utf-8');
    header("access-control-allow-origin: *");
    
    $appid = 218620; // => PayDay2

    //This is for caching, since Steam will ban my server if they got too many requests from it
    function get_content($file,$url,$seconds = 5,$fn = '',$fn_args = '',$agecheck=false) {
        
        $current_time = time(); $expire_time = $seconds; $file_time = filemtime($file);
        
        if(file_exists($file) && ($current_time - $expire_time < $file_time)) {
            
            return file_get_contents($file);
        }
        else {
            if ($agecheck == false){
                $content = file_get_contents($url);
            } else {
                $data = array(  'snr' => '1_agecheck_agecheck__age-gate',
                                'ageDay' => 1,
                                'ageMonth' => 'January',
                                'ageYear' => '1955'
                );

                $options = array(
                    'http' => array(
                        'header'  =>    "Content-type: application/x-www-form-urlencoded\r\n" .
                                        "Cookie: http%3A%2F%2Fstore.steampowered.com%2Fapp%2F218620%2F; birthtime=-473356799; lastagecheckage=1-January-1955;",
                        'method'  => 'POST',
                        'referer'   => "Referer: http://store.steampowered.com/agecheck/app/$appid/",
                        'content' => http_build_query($data),
                    ),
                );
                $context  = stream_context_create($options);
                $content = file_get_contents($url, false, $context);
            }
            
            if (strlen($content) < 1) {
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

    // function defination to convert array to xml / http://stackoverflow.com/a/5965940
    function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }

    $steamLink = "http://store.steampowered.com/app/$appid/";

    $json_data = json_decode(get_content("currentPlayer.json", "http://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v0001/?appid=$appid"), true);
    unset($json_data["response"]["result"]);
    $metacritic_score = json_decode(get_content("storefront.json", "http://store.steampowered.com/api/appdetails/?appids=$appid"), true);

    $metacritic = get_content("metacritic_dump.html", $metacritic_score[$appid]["data"]["metacritic"]["url"], 120);
    preg_match("/<div class=\"metascore_w user large game .*?\">(.*?)<\\/div>/", $metacritic, $metacritic_userscore);

    $steamhub = get_content("steamhub_dump.html", "https://steamcommunity.com/games/$appid/", 120);
    preg_match("/members\">(.*?)\\s/", $steamhub, $steamhub_users);
    $steamhub_users = (int)str_replace(',', '', $steamhub_users[1]);

    $steamreview = get_content("steamstore_dump.html", $steamLink, 60, "", "", true); // => Agecheck
    preg_match("/<span class=\"user_reviews_count\">\\((.*?)\\)<\\/span>/", $steamreview, $steamreview_positive);
    preg_match("/<span class=\"nonresponsive_hidden responsive_reviewdesc\">\\W+(\\d+)%/", $steamreview, $steamreview_percentage);
    $steamreview_positive = (int)str_replace(',', '', $steamreview_positive[1]);
    $steamreview_percentage = (int)$steamreview_percentage[1];
    if ($steamreview_positive == 0){
        $steamreview_negative = 0;
    } else {
        $steamreview_negative = $metacritic_score[$appid]["data"]["recommendations"]["total"] - $steamreview_positive;
    }

    switch (true){
        case ($steamreview_percentage > 94):
        $steamreview_rating = "Overhwelmingly Positive";
        break;
        case ($steamreview_percentage > 79):
        $steamreview_rating = "Very Positive";
        break;
        case ($steamreview_percentage > 69):
        $steamreview_rating = "Mostly Positive";
        break;
        case ($steamreview_percentage > 39):
        $steamreview_rating = "Mixed";
        break;
        case ($steamreview_percentage > 19):
        $steamreview_rating = "Mostly Negative";
        break;
        default:
        $steamreview_rating = "Overwhelmingly Negative";
        break;

    }

    $min_player = json_decode(file_get_contents("minPlayer.json"), true);
    if ($min_player["response"]["player_count"] > $json_data["response"]["player_count"] && $json_data["response"]["player_count"] > 0){
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
    $json_data["response"]["steam"]["reviews_negative"] = $steamreview_negative;
    $json_data["response"]["steam"]["reviews_percentage"] = $steamreview_percentage;
    $json_data["response"]["steam"]["reviews_rating"] = $steamreview_rating;
    $json_data["response"]["steam"]["hub_users_following"] = $steamhub_users;

    $json = json_encode($json_data);

    # XML if ?type=xml
    if($_GET['type'] == 'xml'){
        header("Content-type: text/xml; charset=utf-8");
        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        array_to_xml($json_data,$xml_data);
        exit($xml_data->asXML());
    }

    # JSON if no callback
    if( ! isset($_GET['callback']))
        exit($json);

    # JSONP if valid callback
    if(is_valid_callback($_GET['callback']))
        exit("{$_GET['callback']}($json)");

    # Otherwise, bad request
    header('status: 400 Bad Request', true, 400);
?>