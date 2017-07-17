$(document).ready(function(){
    var cms_page = $('#cms_page_id');
    var resource_link = $("#resource_link");
    checkdropdown = function() {
      if (cms_page.val() == 'file')
      {
        resource_link.show();
      }
      else
      {
        resource_link.hide();
      }
    };
    cms_page.on('change', checkdropdown);
    checkdropdown();
});
