$(function() {
   var origtitle = $("#divisional_matt_header_span2").text();

   var updateMattHeader = function(manager) {
      if (manager == "Clifton Mast") {
          $("#divisional_matt_header_span2").text("- PillowTops Direct");
      } else {
          $("#divisional_matt_header_span2").text(origtitle);
      }
   };

   $("#manager").change(function(e) {
       updateMattHeader($(e.target).val());
   });
   updateMattHeader($("#manager").val()); 
});

