(function($) {
  $(document).ready(function() {
//Validations on update client app
    var $errAppNameSpan = '<span class="efx-appname-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:24px;left: 0%;position:absolute;">Symbols are not allowed when naming your application</span>';
    var $errEmptyAppNameSpan = '<span class="empty-efx-appname" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;left: 0%;color:#e70022;bottom:24px;position:absolute;">Please name your application</span>';
    var $maxAppNameSpan = '<span class="efx-max-chars-appname-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:24px;left: 0%;position:absolute;">25 Character Limit</span>';
        
    var $form = $('.client-app-dashboard-form');
    var $applicationNameField = $form.find('#edit-displaynameinput');
    var $descriptionField = $form.find('#edit-description');
    var validApplicationName = true;
    var validDescription = true;
    var re = /^[A-Za-z0-9 ]+$/;
    
    $form.append($errAppNameSpan).append($errEmptyAppNameSpan).append($maxAppNameSpan);
    
    $applicationNameField.on('blur', function(e) {
      checkApplicationName();
    });

    $applicationNameField.on('keyup', function(e) {
      validApplicationName = false;
      var inputApplicationName = $('#edit-displaynameinput').val();
      var lowercasevalue = String(inputApplicationName).toLowerCase();
      var value = lowercasevalue.trim();
      $('.empty-efx-appname').hide();
      $('.efx-appname-error').hide();
      $('.efx-max-chars-appname-error').hide();

      
      if (!value || value == undefined || value == 'undefined' || value == '') {
        
        validApplicationName = false;
      } 

      else {



        testApplicationName = re.test(String(value).toLowerCase());
        if (!testApplicationName) {
          validApplicationName = false;
        }

        
        else if(value.length > 25){
          validApplicationName = false;
        }
        else {
          validApplicationName = true;
        }
      }

    });


    
    function checkApplicationName() {
      validApplicationName = false;
      var inputApplicationName = $('#edit-displaynameinput').val();
      var lowercasevalue = String(inputApplicationName).toLowerCase();
      var value = lowercasevalue.trim();

      if (!value || value == undefined || value == 'undefined' || value == '') {

       
        validApplicationName = false;
        $('.empty-efx-appname').show();
        $('.efx-appname-error').hide();
        $('.efx-max-chars-appname-error').hide();

      } 

      else {

        testApplicationName = re.test(String(value).toLowerCase());
        if (!testApplicationName) {
          validApplicationName = false;
          $('.empty-efx-appname').hide();
          $('.efx-appname-error').show();
          $('.efx-max-chars-appname-error').hide();

        }

      else if(value.length > 25) {
        validApplicationName = false;
        $('.empty-efx-appname').hide();
        $('.efx-appname-error').hide();
        $('.efx-max-chars-appname-error').show();


      }
      
        else {
          validApplicationName = true;
          $('.empty-efx-appname').hide();
          $('.efx-appname-error').hide();
          $('.efx-max-chars-appname-error').hide();

        }
      }

    }
//product dropdown to show or hide product details
$('.product-dropdown').click(function(){
  if($(this).hasClass('fa-chevron-down')){
  $(this).removeClass('fa-chevron-down');
  $(this).addClass('fa-chevron-up');
  $(this).next().css("display", "inline-block");
  }
  else{
      $(this).removeClass('fa-chevron-up');
      $(this).addClass('fa-chevron-down');
        $(this).next().css("display", "none");
  }
});

//Copy scope to clipboard
$('.copy-scope').click(function(){

  var $textCopy = $(this).prev().text();
  // Create a "hidden" input
  var tempelement = document.createElement("input");

  // Assign it the value of the specified element
  tempelement.setAttribute("value", $textCopy);

  // Append it to the body
  document.body.appendChild(tempelement);

  // Highlight its content
  tempelement.select();

  // Copy the highlighted text
  document.execCommand("copy");

  // Remove it from the body
  document.body.removeChild(tempelement);

  $(this).children().css("display","block");
  setTimeout(function(){ $('.copiedScope-tooltip').css("display","none"); },5000);
});

//character count on description field
$('#edit-descriptioninput').keyup(function(){
var charsno = $(this).val().length;
$('.character-count').html(charsno + "/120");
});

//mask the app credentials
$('#edit-access-token').attr('type', 'password');
$('#edit-consumerkey').attr('type', 'password');
$('#edit-consumersecret').attr('type', 'password');

//Copy app credentials to clipboard
$('.copy-credentials').click(function(){
  var $copyTextSelection = $(this).prev().prev();
  var $textCopy = $copyTextSelection.children();
  var originalState = $textCopy.attr('type');
  
  $textCopy.attr('type', 'text');
  $textCopy.focus();
  $textCopy.select();
  document.execCommand("copy");
  $(this).children().css("display","block");
  setTimeout(function(){ $('.copied-tooltip').css("display","none"); },5000);
  $textCopy.attr('type', originalState);
});



$('body').on('click', '.client-app-dashboard-form', function(event){
  // if((!$(event.target).hasClass('show-credentials'))&&(!$(event.target).hasClass('copy-credentials'))){
  //   $('#edit-access-token').attr('type', 'password');
  //   $('#edit-consumerkey').attr('type', 'password');
  //   $('#edit-consumersecret').attr('type', 'password');
  // }
  if(!$(event.target).hasClass('copy-credentials')){
      $('.copied-tooltip').hide();
  }
});

//Display app credentials
$('.show-credentials').click(function(){
  var originalState = $(this).prev().children().attr('type');
  if(originalState == 'password'){
    $(this).prev().children().attr('type', 'text');
  }
  else{
    $(this).prev().children().attr('type', 'password');
  }
});

//Show Promotion Interim page
$('.enabled-tier').click(function(event){

  if($(this).hasClass('inactive-subscription')){

  $('.subscription-created').css('display','block'); 
  $('.promotion-primary').css('display','none');
  $('.promotion-primary').css('position','relative');
  $('.promotion-empty-form').css("display", "none");
  $('.promotion-empty-form').css("position", "relative");
  $('.trigger-promotion').css("color", "#434343");
  $('.trigger-promotion').children("i").css("color", "#434343");
  $('.trigger-promotion').css("pointer-events", "all");
 

  $('.client-app-dashboard-tiers li').each(function(){
    if($(this).hasClass('inactive-subscription')){
      $(this).addClass('active-subscription'); 
    }
  });
  $(this).parent().children().removeClass('inactive-subscription');
  $(this).parent().children().removeClass('promotion-tab');

  }
  else{

  $('.subscription-created').css('display','none'); 
  $('.promotion-primary').css('display','block');
  $('.promotion-primary').css('position','absolute');
  $('.promotion-primary').css('left', '0px');
  $('.client-app-dashboard-tiers li').each(function(){
    if($(this).hasClass('active-subscription')){
      $(this).addClass('inactive-subscription'); 
    }
  });
  $(this).parent().children().removeClass('active-subscription');
  $(this).addClass('promotion-tab');

  }
 
});


//Show Promotion form
$('.trigger-promotion').click(function(){
  $(this).css("color", "rgba(0, 0, 0, 0.3)");
  $(this).css("pointer-events", "none");
  $(this).children("i").css("color", "rgba(0, 0, 0, 0.3)");

  $('.promotion-empty-form').css("display", "block");
  $('.promotion-empty-form').css("position", "absolute");
  $('.promotion-empty-form').css("top", "24%");
  $('.promotion-primary').css("display", "none");
  $('.promotion-primary').css("position", "relative");
  $('.subscription-created').css('display','none');

  $('.client-app-dashboard-tiers li').each(function(){
    if($(this).hasClass('enabled-tier')){
      if((!$(this).hasClass('active-subscription'))&&(!$(this).hasClass('inactive-subscription'))){
          $(this).addClass('promotion-tab');
      }
    }

    $('.client-app-dashboard-tiers li').each(function(){
      if($(this).hasClass('active-subscription')){
        $(this).addClass('inactive-subscription');
        $(this).removeClass('active-subscription'); 
      }

    });
  });

});

//Show Promotion form from Interim page
$('.promotion-button').click(function(){
  $('.trigger-promotion').css("color", "rgba(0, 0, 0, 0.3)");
  $('.trigger-promotion').css("pointer-events", "none");
  $('.trigger-promotion').children("i").css("color", "rgba(0, 0, 0, 0.3)");

  $('.promotion-empty-form').css("display", "block");
  $('.promotion-empty-form').css("position", "absolute");
  $('.promotion-empty-form').css("top", "24%");
  $('.promotion-primary').css("display", "none");
  $('.promotion-primary').css("position", "relative");
  $('.subscription-created').css('display','none');

  $('.client-app-dashboard-tiers li').each(function(){
    if($(this).hasClass('enabled-tier')){
      if((!$(this).hasClass('active-subscription'))&&(!$(this).hasClass('inactive-subscription'))){
          $(this).addClass('promotion-tab');
      }
    }

    $('.client-app-dashboard-tiers li').each(function(){
      if($(this).hasClass('active-subscription')){
        $(this).addClass('inactive-subscription');
        $(this).removeClass('active-subscription'); 
      }

    });
  });

});


$('.promote-button').click(function(){
  $('.promotion-empty-form').css("display", "block");
  $('.promotion-empty-form').css("position", "absolute");
  $('.promotion-empty-form').css("top", "24%");
  $('.promotion-primary').css("display", "none");
  $('.promotion-primary').css("position", "relative");

  
});






    //Update Client App name 
    $('body').on('click', '.updateClientAppButton', function(event){
   
        $('.descriptionInputDiv-hidden').addClass('descriptionInputDiv-visible');
        $('.descriptionInputDiv-visible').removeClass('descriptionInputDiv-hidden');
        $('.displayNameInputDiv-hidden').addClass('displayNameInputDiv-visible');
        $('.displayNameInputDiv-visible').removeClass('displayNameInputDiv-hidden');

        $('.clientApp-displayName-visible').addClass('clientApp-displayName-hidden');
        $('.clientApp-displayName-hidden').removeClass('clientApp-displayName-visible');
        $('.clientApp-description-visible').addClass('clientApp-description-hidden');
        $('.clientApp-description-hidden').removeClass('clientApp-description-visible');
        event.stopImmediatePropagation();

    });
    

     var updateClientAppInputfields = ['edit-displaynameinput', 'edit-descriptioninput'];


      $('body').on('click', '.client-app-dashboard-form', function(event){
      var displayName = $('#edit-displaynameinput').val();
      var description = $('#edit-descriptioninput').val();
         // if (($('.displayNameInputDiv-visible')[0]) && (!$('.displayNameInputDiv-hidden')[0])){ 

          if(($.inArray(event.target.id, updateClientAppInputfields) == -1) && (validApplicationName == true)){
          $('#edit-updateclientappsubmit').click();

          $('.descriptionInputDiv-visible').addClass('descriptionInputDiv-hidden');
          $('.descriptionInputDiv-hidden').removeClass('descriptionInputDiv-visible');
          $('.displayNameInputDiv-visible').addClass('displayNameInputDiv-hidden');
          $('.displayNameInputDiv-hidden').removeClass('displayNameInputDiv-visible');

          $('.clientApp-displayName-hidden').html(displayName + '<i class="fas fa-edit updateClientAppButton"></i>');
          
          if(description.length == 0){
            $('.clientApp-description-hidden').html("<i class='fal fa-info-circle'></i>Include information about your application; ex. who it's for, what are the use cases");
          }

          else{
            $('.clientApp-description-hidden').html(description);            
          }



          $('.clientApp-displayName-hidden').addClass('clientApp-displayName-visible');
          $('.clientApp-displayName-visible').removeClass('clientApp-displayName-hidden');
          $('.clientApp-description-hidden').addClass('clientApp-description-visible');
          $('.clientApp-description-visible').removeClass('clientApp-description-hidden');

        
      }
      });  


    })

}(jQuery));

