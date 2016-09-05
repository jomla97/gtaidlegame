$(document).ready(function(){

  function filter_map_markers(mode){
    if(mode == "ALL"){
      $("#map-content").children(".map-marker").css("display", "block");
    }
    else if(mode == "OWNED"){
      $("#map-content").children(".map-marker").css("display", "none");
      $("#map-content").children(".owned").css("display", "block");
    }
    else if(mode == "SMALL"){
      $("#map-content").children(".map-marker").css("display", "none");
      $("#map-content").children(".small").css("display", "block");
    }
    else if(mode == "MEDIUM"){
      $("#map-content").children(".map-marker").css("display", "none");
      $("#map-content").children(".medium").css("display", "block");
    }
    else if(mode == "LARGE"){
      $("#map-content").children(".map-marker").css("display", "none");
      $("#map-content").children(".large").css("display", "block");
    }
  }

  //warehouse-toggle buttons
  $("#warehouse-toggle").children().click(function(){
    $("#warehouse-toggle").children().removeClass("toggle-selected").addClass("toggle");
    $(this).removeClass("toggle").addClass("toggle-selected");
    map_marker_filter_mode = $(this).text();
    //console.log("map_marker_filter_mode: " + map_marker_filter_mode);

    filter_map_markers(map_marker_filter_mode);
  });

  //page-toggle buttons
  $("#page-toggle").children().click(function(){
    $("#page-toggle").children().removeClass("toggle-selected").addClass("toggle");
    $(this).removeClass("toggle").addClass("toggle-selected");

    if($(this).text() == "SUMMARY PAGE"){
      $("#HUD").children().not("#page-toggle").css("display", "none");
      $("#map-content").css("display", "none");
      $("#map-wrapper").draggable("disable");
      $("#summary").css("display", "block");

      $.ajax({
          type: "POST",
          url: "templates/ajax/summary_page.php",
          dataType: "html",
          success: function(response){
              $("#summary").html(response);
          }

      });
    }
    else{
      $("#HUD").children().not(".dialog").not("script").not("#hamburger-menu").not("#hamburger-menu-toggle").css("display", "block");
      $("#map-content").css("display", "block");
      $("#map-wrapper").draggable("enable");
      $("#summary").css("display", "none").empty();
    }
  });

  //BUY dialog
  $(".buy-stock-button").click(function(){
    if($(this).hasClass("disabled")){

    }
    else{
      $(".dialog").css("display", "none");
      $("#buy-stock-dialog").toggle();
      $(".map-marker").removeClass("selected");
    }
  });

  $(".selection-list").children(".toggle").click(function(){
    $(this).siblings("input").val($(this).text()).trigger("change");
  });

  //SELL dialog
  $(".sell-stock-button").click(function(){
    if($(this).hasClass("disabled")){

    }
    else{
      $(".dialog").css("display", "none");
      $("#sell-stock-dialog").toggle();
      $(".map-marker").removeClass("selected");
    }
  });

  //UPGRADES dialog
  $(".upgrades-button").click(function(){
    if($(this).hasClass("disabled")){

    }
    else{
      $(".dialog").css("display", "none");
      $("#upgrades-dialog").toggle();
      $(".map-marker").removeClass("selected");
    }
  });

  //close dialog button
  $(".close-button").click(function(){
    $(this).parent().toggle();
  });

  $("#owned-warehouse-dialog").children(".close-button").click(function(){
    $(".map-marker").removeClass("selected");
  });
  $("#buy-warehouse-dialog").children(".close-button").click(function(){
    $(".map-marker").removeClass("selected");
  });

  //selection list toggle buttons
  $(".selection-list").children(".toggle").click(function(){
    $(this).removeClass("toggle").addClass("toggle-selected");
    $(this).siblings(".toggle-selected").removeClass("toggle-selected").addClass("toggle");
  });

  $("form").submit(function(e){
    if($(this).children(".submit-button").hasClass("disabled")){
      e.preventDefault();
    }
  });

  //ok button in alert box
  $(".alert").children(".ok-button").click(function(){
    $(".alert").remove();
    $(".alert-background").remove();
  });

  $("#hamburger-menu-toggle").click(function(){
    if($("#hamburger-menu").position().left === 0){
      $("#hamburger-menu").animate({
        left: -$("#hamburger-menu").width()
      });
    }
    else{
      $("#hamburger-menu").animate({
        left: 0
      });
    }
  });

  $("#hamburger-menu").children("ul").children("li").click(function(){
    $("#hamburger-menu").animate({
      left: -$("#hamburger-menu").width()
    });
  });

  $(".summary-button").click(function(){
    $("#HUD").children().css("display", "none");
    $("#map-content").css("display", "none");
    $("#map-wrapper").draggable("disable");
    $("#summary").css("display", "block");

    $.ajax({
        type: "POST",
        url: "templates/ajax/summary_page.php",
        dataType: "html",
        success: function(response){
            $("#summary").html(response);
        }

    });
  });

});
