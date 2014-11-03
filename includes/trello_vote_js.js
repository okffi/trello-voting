jQuery(document).ready( function() {

   // init
   var cards = null;
   var load_data = jQuery.ajax({
      type: "post",
      dataType: "json",
      url: trellovotingAjax.ajaxurl,
      data: {action: "trello_load_data", trellourl: trellovotingAjax.trellourl},
      success: function(response) {
         if(response.status == true) {
            cards = response.cards;
         } else {
            alert(trellovotingAjax.fetcherr)
         }
      }
   })


   /**
    * Get random key from array
    * @param  array obj  array to use
    * @return string     random key
    */
   function randomKey(obj) {
       var ret;
       var c = 0;
       for (var key in obj)
           if (Math.random() < 1/++c)
              ret = key;
       return ret;
   }


   /**
    * Populate new cards for voting
    * @param  arrray obj   array containing all cards for one label
    */
   function voting(obj) {
      voting_data = [];

      // Get first random card
      voting_data[0] = obj[randomKey(obj)];
      
      // Get second random card but check that it isn't same as first
      for(var n = 0; n < 50; n++) {
         voting_data[1] = obj[randomKey(obj)]
         if(voting_data[0]['id'] != voting_data[1]['id']) {
            break
         }
      }

      // Show cards
      i = 0
      jQuery('.card').each(function(){
         jQuery(this).find('img').attr('src', voting_data[i]['image'])
         jQuery(this).find('.description').find('p').remove()
         jQuery(this).find('.description h2').text(voting_data[i]['name'])
         jQuery(this).find('.description h2').after(voting_data[i]['desc'])
         jQuery(this).find('.more-link a').attr('href', voting_data[i]['url'])
         jQuery(this).next('a#vote').attr('data-id', voting_data[i]['id'])
         jQuery(this).next('a#vote').attr('data-nonce', voting_data[i]['nonce'])
         i = i+1
      })
      
      // Remove overlay
      jQuery('#trellovoting #trellohover h2').text('')   
      jQuery('#trellovoting #trellohover').fadeOut(400);
   }


   /**
    * Send user's vote to backend
    * @param  string id    spesify card
    * @param  string nonce nonce for security check
    */
   function vote(id, nonce) {
      jQuery.ajax({
         type : "post",
         dataType : "json",
         url : trellovotingAjax.ajaxurl,
         data : {action: "trello_vote", id: id, nonce: nonce},
         success: function(response) {
            if(response.status == true) {
               setTimeout( function() {
                  voting(cards[randomKey(cards)])
               }, 500);
            } else {
               alert(trellovoting.votingerr)
            }
         }
      })
   }

   
   // Start making things happen
   load_data.done(function () {
      voting(cards[randomKey(cards)]);
   })

   jQuery('#trellovoting a#nxt').click(function(e) {
      e.preventDefault();

      jQuery('#trellovoting #trellohover').fadeIn(200);
      setTimeout( function() {
          voting(cards[randomKey(cards)])
       }, 500);
   })

   // Okay, user clicked voting link so send vote
   jQuery("#trellovoting a#vote").click(function(e) {
      e.preventDefault();

      jQuery('#trellovoting #trellohover').fadeIn(200);
      
      id = jQuery(this).attr("data-id")
      nonce = jQuery(this).attr("data-nonce")
      vote(id, nonce)
   })

})