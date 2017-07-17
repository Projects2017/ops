$( document ).ready(function() {
  $("li.dropdown.user.user-menu").click(function(){
    $('li.dropdown.user.user-menu > ul.dropdown-menu.rss').toggle(); /*or $('.divclass').toggle() */
  });

  $(".username-login").focus(function() {
    $(".username").css({"left" : "-55px", "display": "block","float": "left"});
  });
  $(".username-login").blur(function() {
    $(".username").css({"left" : "0", "display": "none"});
  });
  $(".username-password").focus(function() {
    $(".pass-icon").css({"left" : "-55px", "display": "block","float": "left"});
  });
  $(".username-password").blur(function() {
    $(".pass-icon").css({"left" : "0", "display": "none"});
  });
});