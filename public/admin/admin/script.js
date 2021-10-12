function generatePassword() {
    var length = 8,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}
function UserPassword() {
  $("#user_password").val(generatePassword())
}


$(document).ready(function(){

  var site_url  = $('head base').attr('href');

  $("#serviceList").click(function(){
    $("#serviceListContent").html('<center><div class="modal-body"><img src="public/ajax-loading.gif" border="0" alt="loading"></div></center>');
    var href  = $(this).attr("data-href");
    var active= $(this).attr("data-active");
    $.post(site_url+href, {active:active }, function(data){
     $("#serviceListContent").html(data);
    });
  });

  $('#modalDiv').on('show.bs.modal', function(e) {
    $("#modalContent").html('<center><div class="modal-body"><img src="public/ajax-loading.gif" border="0" alt="loading"></div></center>');
    $.post(site_url+'admin/ajax_data', {action:$(e.relatedTarget).data('action'),id:$(e.relatedTarget).data('id') }, function(data){
      $("#modalTitle").html(data.title);
      $("#modalContent").html(data.content);
      $(".datetime").datepicker({
         format: "dd/mm/yyyy",
         language: "tr",
         startDate: new Date(),
       }).on('change', function(ev){
         $(".datetime").datepicker('hide');
       });
    },'json');
  });

  $('#modalDiv').on('hidden.bs.modal', function () {
    $("#modalTitle").html('');
    $("#modalContent").html('');
  });

  $('#subsDiv').on('show.bs.modal', function(e) {
    $.post(site_url+'admin/ajax_data', {action:$(e.relatedTarget).data('action'),id:$(e.relatedTarget).data('id') }, function(data){
      $("#subsTitle").html(data.title);
      $("#subsContent").html(data.content);
      $(".datetime").datepicker({
         format: "dd/mm/yyyy",
         language: "tr",
         startDate: new Date(),
       }).on('change', function(ev){
         $(".datetime").datepicker('hide');
       });
    },'json');
  });

  $('[id^="delete_rate_button-"]').click(function(){
    var id = $(this).attr("data-service");
    $("#rate-"+id).val("");
    $('#delete_rate_button-'+id).css("visibility","hidden");
  });

  $('[id^="delete_rate_button-"]').each(function() {
      var id    = $(this).attr("data-service");
      var price = $('#rate-'+id).val().length;
        if( price > 0 ){
          $("#delete_rate_button-"+id).css("visibility","visible");
        }
  });

  $('[id^="rate-"]').on('keyup', function(){
    var id    = $(this).attr("data-service");
    var price = $('#rate-'+id).val().length;
      if( price > 0 ){
        $("#delete_rate_button-"+id).css("visibility","visible");
      }else{
        $("#delete_rate_button-"+id).css("visibility","hidden");
      }
  });

  $('[id^="collapedAdd-"]').click(function(){
    var id = $(this).attr("data-category");
    if( $(this).attr("class") == "service-block__collapse-button" ){
      $(".Service"+id).hide();
      $(this).addClass(" collapsed");
    }else{
      $(".Service"+id).show();
      $(this).removeClass(" collapsed");
    }
  });

  $('#allServices').click(function(){
    if( $(this).attr("class") == "service-block__hide-all fa fa-compress" ){
      $('#allServices').removeClass("fa fa-compress");
      $('#allServices').addClass("fa fa-expand");
      $('[class^="Servicecategory-"]').each(function(){
        $(this).hide();
      });
      $('[id^="collapedAdd-"]').each(function(){
        $(this).addClass(" collapsed");
      });
    }else{
      $('#allServices').removeClass("fa fa-expand");
      $('#allServices').addClass("fa fa-compress");
      $('[class^="Servicecategory-"]').each(function(){
        $(this).show();
      });
      $('[id^="collapedAdd-"]').each(function(){
        $(this).removeClass(" collapsed");
      });
    }
  });

  $("#priceSearch").on('keyup',function(){
    var search = $(this).val();
    var filter = search.toUpperCase();
    var i = 0;
    $('[id^="servicepriceList-"]').each(function() {
      i++;
      var name = $(this).attr("data-name");
      var txtValue = name.textContent || name.innerText;
      if (name.toUpperCase().indexOf(filter) > -1) {
          $(this).show();
      } else {
          $(this).hide();
      }
    });
  });

  $("#priceService").on('keyup',function(){
    var search = $(this).val();
    var filter = search.toUpperCase();
    var i = 0;
    $('[data-id^="service-"]').each(function() {
      var name      = $(this).attr("data-service");
      var category  = $(this).attr("data-category");
      var txtValue  = name.textContent || name.innerText;
      if (name.toUpperCase().indexOf(filter) > -1) {
          $(this).show();
          $(this).attr("id","serviceshow"+category);
      } else {
          $(this).hide();
          $(this).attr("id","servicehide");
      }

    });
      $('[id^="Servicecategory-"]').each(function() {
        var id       = $(this).attr("data-id");
        var rowCount = $('#servicesTableList > tbody > tr#serviceshow'+id).length;
          if (rowCount == 0) {
            $("#"+id).hide();
          }else{
            $("#"+id).show();
          }
      });
  });
  $(".tiny-toggle").tinyToggle({
    onCheck: function() {
      var id     = $(this).attr("data-id");
      var action = $(this).attr("data-url")+"?type=on&id="+id;
        $.ajax({
        url:  action,
        type: 'GET',
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false
        }).done(function(result){
          if( result == 1 ){
            $('[data-toggle="'+id+'"]').removeClass("grey");
          }else{
            $.toast({
                heading: "Failed",
                text: "Failed",
                icon: "error",
                loader: true,
                loaderBg: "#9EC600"
            });
          }
        })
        .fail(function(){
          $.toast({
              heading: "Failed",
              text: "Failed",
              icon: "error",
              loader: true,
              loaderBg: "#9EC600"
          });
        });
    },
    onUncheck: function() {
      var id     = $(this).attr("data-id");
      var action = $(this).attr("data-url")+"?type=off&id="+id;
        $.ajax({
        url:  action,
        type: 'GET',
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false
        }).done(function(result){
          if( result == 1 ){
            $('[data-toggle="'+id+'"]').addClass("grey");
          }else{
            $.toast({
                heading: "Failed",
                text: "Failed",
                icon: "error",
                loader: true,
                loaderBg: "#9EC600"
            });
          }
        })
        .fail(function(){
          $.toast({
              heading: "Failed",
              text: "Failed",
              icon: "error",
              loader: true,
              loaderBg: "#9EC600"
          });
        });
    },
  });

  $("#provider").change(function(){
    var provider = $(this).val();
    getProviderServices(provider,site_url);
  });

  getProvider();
  $("#serviceMode").change(function(){
    getProvider();
  });

  getSalePrice();
  $("#saleprice_cal").change(function(){
    getSalePrice();
  });

  getSubscription();
  $("#subscription_package").change(function(){
    getSubscription();
  });

  $('#confirmChange').on('show.bs.modal', function(e) {
      $(this).find('#confirmYes').attr('href', $(e.relatedTarget).data('href'));
  });
  $('#confirmYes').click(function(){
      if( $(this).attr("href") == null ){
        $("#changebulkForm").submit();
        return false;
      }
  });
  $('.bulkorder').click(function (){
     var status = $(this).attr("data-type");
      $("#bulkStatus").val(status);
      $("#confirmYes").removeAttr('href');
      $("#confirmChange").modal('show');
  });

  $("#checkAll").click(function () {
   if ( $(this).prop('checked') == true ) {
     $('.selectOrder').not(this).prop('checked', true);
   }else{
     $('.selectOrder').not(this).prop('checked', false);
   }
   var count = $('.selectOrder').filter(':checked').length;
   $('.countOrders').html(count);
   if( count > 0 ){
     $('.checkAll-th').addClass("show-action-menu");
   }else{
     $('.checkAll-th').removeClass("show-action-menu");
   }
 });
 $(".selectOrder").click(function () {
    var count = $('.selectOrder').filter(':checked').length;
    if( count > 0 ){
      $('.checkAll-th').addClass("show-action-menu");
    }else{
      $('.checkAll-th').removeClass("show-action-menu");
    }
    $('.countOrders').html(count);
 });


});

function getProviderServices(provider,site_url){
  if( provider == 0 ){
    $("#provider_service").hide();
  }else{
    $.post(site_url+'admin/ajax_data',{action:'providers_list',provider:provider}).done(function( data ) {
      $("#provider_service").show();
      $("#provider_service").html(data);
    }).fail(function(){
      alert("Hata oluştu!");
    });
  }
}

function getProvider(){
  var mode = $("#serviceMode").val();
    if( mode == 1 ){
      $("#autoMode").hide();
    }else{
      $("#autoMode").show();
    }
}

function getSalePrice(){
  var type = $("#saleprice_cal").val();
    if( type == "normal" ){
      $("#saleprice").hide();
      $("#servicePrice").show();
    }else{
      $("#saleprice").show();
      $("#servicePrice").hide();
    }
}

function getSubscription(){
  var type = $("#subscription_package").val();
    if( type == "11" || type == "12" ){
      $("#unlimited").show();
      $("#limited").hide();
    }else{
      $("#unlimited").hide();
      $("#limited").show();
    }
}
