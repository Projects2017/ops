jQuery( ".search" ).click(function() {
    jQuery(".searchbox").slideToggle(function() {
		jQuery(".pagecontent").click(function() {
		  jQuery(".searchbox").fadeOut();
		});
		
		jQuery(".searchinput").focus();
    });
	
	jQuery(".collapse.in").removeClass('in');
});


 function sortaz(id,target) {
		$(id).sort(function (a, b) {
			if ( ($(a).attr("data-value").toLowerCase() > $(b).attr("data-value").toLowerCase()) )  { 
				return 1;
			} else if ( ($(a).attr("data-value").toLowerCase() == $(b).attr("data-value").toLowerCase()) ){
				return 0;
			} else {
				return -1;
			}
		}).each(function () {
			var elem = $(this);
			elem.remove();
			$(elem).appendTo(target);
		});
 }
 
  function sortza(id,target) {
		$(id).sort(function (b, a) {
			if ( ($(a).attr("data-value").toLowerCase() > $(b).attr("data-value").toLowerCase()) )  { 
				return 1;
			} else if ( ($(a).attr("data-value").toLowerCase() == $(b).attr("data-value").toLowerCase()) ){
				return 0;
			} else {
				return -1;
			}
		}).each(function () {
			var elem = $(this);
			elem.remove();
			$(elem).appendTo(target);
		});
 }


jQuery( document ).ready(function() {
	
		jQuery('#tab1 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab1 .boxes .box","#tab1 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab1 .boxes .box","#tab1 .boxes");
			}
		});	
		
		jQuery('#tab2 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab2 .boxes .box","#tab2 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab2 .boxes .box","#tab2 .boxes");
			}
		});	
		
		jQuery('#tab3 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab3 .boxes .box","#tab3 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab3 .boxes .box","#tab3 .boxes");
			}
		});	
		
		jQuery('#tab4 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab4 .boxes .box","#tab4 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab4 .boxes .box","#tab4 .boxes");
			}
		});	
		
		jQuery('#tab5 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab5 .boxes .box","#tab5 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab5 .boxes .box","#tab5 .boxes");
			}
		});	
		
		jQuery('#tab6 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab6 .boxes .box","#tab6 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab6 .boxes .box","#tab6 .boxes");
			}
		});	
		
		jQuery('#tab1 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab7 .boxes .box","#tab7 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab7 .boxes .box","#tab7 .boxes");
			}
		});	
		
		jQuery('#tab1 .actions .dropdown-menu ul li a').click(function() {
			if (jQuery(this).find('.text').html() == "A-Z" ){
			  sortaz("#tab8 .boxes .box","#tab8 .boxes");
			}
			if (jQuery(this).find('.text').html() == "Z-A" ){
			  sortza("#tab8 .boxes .box","#tab8 .boxes");
			}
		});	

		sortaz("#tab1 .boxes .box","#tab1 .boxes");
		sortaz("#tab2 .boxes .box","#tab2 .boxes");
		sortaz("#tab3 .boxes .box","#tab3 .boxes");
		sortaz("#tab4 .boxes .box","#tab4 .boxes");
		sortaz("#tab5 .boxes .box","#tab5 .boxes");
		sortaz("#tab6 .boxes .box","#tab6 .boxes");
		sortaz("#tab7 .boxes .box","#tab7 .boxes");
		sortaz("#tab8 .boxes .box","#tab8 .boxes");
		
		if ( jQuery(".topbar").length ) {
			jQuery('.topbar .widgetbox').matchHeight({
				byRow: false,
				property: 'height',
				target: null,
				remove: false
			});
		}
		
		if ( jQuery(".resources").length ) {
			jQuery('.resources .used .widgetbox2 .titl').matchHeight({
				byRow: false,
				property: 'height',
				target: null,
				remove: false
			});
		}
		
	
	$(".responsive-toggler").click(function(){
		$("#topMenu").toggle();
	});

	
});





jQuery(document).ready(function() {    
	if (jQuery().datepicker) {
			jQuery('.date-picker').datepicker({
				 minViewMode: 0,
				 maxViewMode: 0
			});
		}
});





