jQuery(document).ready(function() {
   jQuery('#somc-subpages').submit(function() { 
      jQuery(this).ajaxSubmit({
         success: function(resp) {
			jQuery('#somc-subpages').prepend('<p id="successfully">Your settings was successfully updated</p>');
			setTimeout(function(){
			  jQuery('#successfully').empty();
			}, 2000);
         }
      }); 
      return false; 
   });
   jQuery("a#hide").click(function() {
   jQuery("li.somc-sub-page").toggleClass("hidden");
   jQuery("a#hide i.fa").toggleClass("fa-minus fa-plus");
});
});