$(document).ready(function(){
  var break_point = 1080;

  function mobile(){
    $("#HUD").children().not("#warehouse-space").not("#cash").not(".dialog").not("#active-shipment").hide();
    $("#hamburger-menu-toggle").show();
    $("#hamburger-menu").show();
  }

  function desktop(){
    $("#HUD").children().not(".dialog").not("script").show();
    $("#hamburger-menu-toggle").hide();
    $("#hamburger-menu").hide();
  }

  if($(window).width() <= break_point){
    mobile();
  }
  else{
    desktop();
  }
  $(window).resize(function(){
    if($(window).width() <= break_point){
      mobile();
    }
    else{
      desktop();
    }
  });
});
