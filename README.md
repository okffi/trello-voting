Trello voting plugin for WordPress
=========

Simple plugin for voting things in trello board.


Installation
--------------
1. Upload zip to plugins directory and activate plugin.

2. Add shortcode into that page where you want to show voting.
```
[trello_voting trello="JSON-DATA-URL" redirect="THANKS-PAGE"]
```
    * Replace JSON-DATA-URL with address to your Trello board JSON export
    * Replace THANKS-PAGE with address where results shortcode is

3. Add shortcode into that page where you want to show voting results
```
[trello_voting_results trello="JSON-DATA-URL"]
```
    * Replace JSON-DATA-URL with same address as in voting shortcode


Customizing layout
---

Customizing the visual output is quite simple. In your active theme directory make a new directory named trello-voting, copy voting.php and/or results.php from plugins/trello-voting/views/ to that directory and start modifying.

Note that you can't modify element id's or remove classes .description, .card and .more-link from elements. If you modify id's note that you need to change those also in trello_vote_js.js file.


License
----

GPL2
