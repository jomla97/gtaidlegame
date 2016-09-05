$(document).ready(function(){
  Notification.requestPermission();

  var map = {mouseover: false, zoom_speed: 500, start_height: $("#map-wrapper").height(), start_width: $("#map-wrapper").width(), max_width: $("#map-wrapper").width()*3, max_height: ($("#map-wrapper").width()*3)*1.308}; //zoom speed: height = width * 1.308
  var w = {}; //window
  var mouse = {};
  var map_marker_filter_mode = $("#warehouse-toggle").children(".toggle-selected").text();

  $("#map-wrapper").draggable();

  //mouse coordinates (for debugging)
  $("#map-wrapper").mousemove(function(e){
    mouse.map_x = e.pageX - $(this).offset().left;
    mouse.map_y = e.pageY - $(this).offset().top;

    $("#mouse_x").text(mouse.map_x);
    $("#mouse_y").text(mouse.map_y);
  });

  function update_map(){
    map.x = $("#map-wrapper").position().left;
    map.y = $("#map-wrapper").position().top;
    map.width = parseInt($("#map-wrapper").css("width"));
    map.height = parseInt($("#map-wrapper").css("height"));

    $("#map-wrapper").css("left", map.x + "px");
    $("#map-wrapper").css("top", map.y + "px");
  }

  function update_HUD(){
    $("#map_x").text(map.x);
    $("#map_y").text(map.y);

    $("#map_height").text(map.height);
    $("#map_width").text(map.width);
  }

  function update(){
    w.height = $(window).height();
    w.width = $(window).width();

    update_map();
    update_HUD();
  }
  setInterval(update, 32); //updates at aprox. 30/sec (32ms)

  $("#map-wrapper").scroll(function(){
    var height = $(this).height();
    var width = $(this).width();
    $(this).css("height", height + 50);
    $(this).css("width", width + 50);
  });

  $(".map-marker").click(function(){
    $(".map-marker").removeClass("selected");
    $(this).addClass("selected");
  });

  //buy warehouse dialog
  function buy_warehouse_info(id){
    var cash = parseInt($("#cash").text().replace("$", "").replace(/,/g, ""));

    id = id.replace("warehouse", "");
    var data_to_send = {warehouse_id: id, cash: cash};
    $.ajax({    //create an ajax request to load_page.php
        type: "GET",
        url: "templates/ajax/buy_warehouse.php",
        dataType: "html",   //expect html to be returned
        data: data_to_send,
        success: function(response){
            $("#buy-warehouse-dialog").html(response);

            $(".dialog").css("display", "none");
            $("#buy-warehouse-dialog").toggle();
        }

    });
  }

  $(".map-marker.for-sale").click(function(){
    var warehouse_id = $(this).attr("id");

    buy_warehouse_info(warehouse_id);
  });

  //owned warehouse dialog
  function owned_warehouse_info(id){
    id = id.replace("warehouse", "");
    var data_to_send = {warehouse_id: id};
    $.ajax({    //create an ajax request to load_page.php
        type: "GET",
        url: "templates/ajax/owned_warehouse.php",
        dataType: "html",   //expect html to be returned
        data: data_to_send,
        success: function(response){
            $("#owned-warehouse-dialog").html(response);

            $(".dialog").css("display", "none");
            $("#owned-warehouse-dialog").toggle();
        }

    });
  }

  $(".map-marker.owned").click(function(){
    var warehouse_id = $(this).attr("id");

    owned_warehouse_info(warehouse_id);
  });

  function close_button(){
    $(this).parent().toggle();
    $(".map-marker").removeClass("selected");
  }

  //buy stock dialog
  function buy_stock_dialog(id){
    id = id.replace("warehouse", "");
    var data_to_send = {warehouse_id: id};
    $.ajax({    //create an ajax request to load_page.php
        type: "GET",
        url: "templates/ajax/owned_warehouse.php",
        dataType: "html",   //expect html to be returned
        data: data_to_send,
        success: function(response){
            $("#owned-warehouse-dialog").html(response);

            $(".dialog").css("display", "none");
            $("#owned-warehouse-dialog").toggle();
        }

    });
  }

  $(".map-marker.owned").click(function(){
    var warehouse_id = $(this).attr("id");

    buy_stock_dialog(warehouse_id);
  });

  //buy cargo crates ajax info
  var crate_type_name = "";
  var quantity = "";
  $(".selection-list").children("input.hidden").change(function(){
    if($(this).attr("name") == "type"){
      crate_type_name = $(this).val();
      //console.log("selected type");
    }
    else if($(this).attr("name") == "quantity"){
      quantity = $(this).val();
      //console.log("selected quantity");
    }

    if((typeof(crate_type_name) !== "undefined" && crate_type_name !== null) && (typeof(quantity) !== "undefined" && quantity !== null)){
    //console.log("if statement went through");
      var data_to_send = {
        crate_type_name: crate_type_name,
        quantity: quantity
      };
      $.ajax({
          type: "GET",
          url: "templates/ajax/buy_cargo_crates.php",
          dataType: "json",
          data: data_to_send,
          success: function(response){
            $("#delivery-risk").text(response['risk'] + "%");
            $("#delivery-profit").text(response['profit'] + "%");
            $("#delivery-price").text("$" + response['price']);

            console.log("user cash: " + parseInt($("#cash").text().replace("$", "").replace(/,/g, "")));
            console.log("price: " + parseInt(response['price'].replace(/$/g, "").replace(/,/g, "")));

            if(parseInt(response['price'].replace(/$/g, "").replace(/,/g, "")) > parseInt($("#cash").text().replace("$", "").replace(/,/g, ""))){
              if($("#delivery-price").hasClass("red-text")){

              }
              else{
                $("#delivery-price").addClass("red-text");
              }

              if($("#buy-stock-dialog").children("form").children(".submit-button").hasClass("disabled")){

              }
              else{
                $("#buy-stock-dialog").children("form").children(".submit-button").addClass("disabled")
              }
            }
            else{
              if($("#delivery-price").hasClass("red-text")){
                $("#delivery-price").removeClass("red-text");
              }
              if($("#buy-stock-dialog").children("form").children(".submit-button").hasClass("disabled")){
                $("#buy-stock-dialog").children("form").children(".submit-button").removeClass("disabled")
              }
            }

            //console.log("price: " + response['price']);
            //console.log("risk: " + response['risk']);
            //console.log("profit: " + response['profit']);

            //console.log("response: " + response);
          },
          error: function(jqXHR, textStatus, errorThrown){
            console.log("jqXHR: " + jqXHR);
            console.log("textStatus: " + textStatus);
            console.log("errorThrown: " + errorThrown);
          }
      });
    }
  });

  $("#loading").hide();
});
