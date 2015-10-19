# metacritic-steam-score-scrapper
Scrapes from Metacritic and Steam the Reviews and Userscores

This repository was made for this project: [Road-From-Greedfest
](https://github.com/RoadFromGreedfest/Road-From-Greedfest)

By visiting api.php you will get a json object which should look likes this:
```json
{
    "response": 
    {
        "player_count": 27392,
        "result": 1,
        "appid": 218620,
        "metacritic_link": "http://www.metacritic.com/game/pc/payday-2",
        "metacritic_score": 79,
        "steam_link": "http://store.steampowered.com/app/218620/",
        "steam_reviews_all": 152450
    }
}
```