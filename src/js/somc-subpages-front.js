jQuery(document).ready(function() {
   jQuery("a#hide").click(function() {
      jQuery("li.somc-sub-page").toggleClass("hidden");
      jQuery("a#hide i.fa").toggleClass("fa-minus fa-plus");
   });
});