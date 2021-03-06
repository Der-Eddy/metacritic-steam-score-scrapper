# metacritic-steam-score-scrapper
Scrapes from Metacritic and Steam the Reviews and Userscores

This repository was made for this project: [Road-From-Greedfest
](https://github.com/RoadFromGreedfest/Road-From-Greedfest)  
It's a fork of the API from my [crimefest-statistics
](https://github.com/Der-Eddy/crimefest-statistics) site


Usage
-------------
##### For JSON Output: `http://crimefest.eddydev.de/api/api.php`  
##### For XML Output: `http://crimefest.eddydev.de/api/api.php?type=xml`  
##### For JSONP Output: `http://crimefest.eddydev.de/api/api.php?callback=response`  
CORS Support is available

Sample Output
-------------

By visiting api.php you will get a json object which should look likes this:
```json
{

    "response": 

    {

        "player_count": 24459,
        "player_count_minimum": 9200,
        "appid": 218620,
        "metacritic": 

            {

                "link": "http://www.metacritic.com/game/pc/payday-2",
                "score": 79,
                "user_score": 4.6

            },
        "steam": 

            {
                "link": "http://store.steampowered.com/app/218620/",
                "reviews_all": 154385,
                "reviews_positive": 123374,
                "reviews_negative": 31011,
                "reviews_percentage": ​77,
                "reviews_rating": "Mostly Positive",
                "hub_users_following": ​3361228                
            }
    }

}
```

JSONP output for Cross-Site-Domain Calls is also possible.


ToDo
-------------
- [x] Metacritic Userscore
- [x] Steam Review Rating
- [x] Fix that the PHP script sometimes doesn't load anything from Metacritic and/or SteamStore
- [x] Adding Agecheck cookie for SteamStore


License
-------------
  
    The MIT License (MIT)
    
    Copyright (c) 2015
    
    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:
    
    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.
    
    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.