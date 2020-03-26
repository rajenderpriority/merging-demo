(function($) {
  $(document).ready(function() {

    var $errAppNameSpan = '<span class="efx-appname-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:9px;left: 2%;position:absolute;">Symbols are not allowed when naming your application</span>';
    var $errEmptyAppNameSpan = '<span class="empty-efx-appname" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;left: 2%;color:#e70022;bottom:9px;position:absolute;">Please name your application</span>';
    var $maxAppNameSpan = '<span class="efx-max-chars-appname-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:9px;left: 2%;position:absolute;">25 Character Limit</span>';
    var $duplicateAppNameSpan = '<span class="efx-duplicate-appname-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:9px;left: 2%;position:absolute;">Looks like you used this name before. Please provide a unique name.</span>';
    
    var $form = $('.app-management-dashboard-form');
    var $applicationNameField = $form.find('#edit-applicationname');
    var $descriptionField = $form.find('#edit-description');
    var validApplicationName = false;
    var validDescription = true;
    var $btn = $form.find('input[type=submit]');
    var re = /^[A-Za-z0-9 ]+$/;

    var adiv = document.getElementsByClassName("dashboard-app-name");
    var clientAppNames = [];

    for(i = 0; i < adiv.length; ++i) {
   
      var clientAppName = adiv[i].innerText.toLowerCase();
      clientAppNames.push(clientAppName);
    }
     
    $form.append($errAppNameSpan).append($errEmptyAppNameSpan).append($maxAppNameSpan).append($duplicateAppNameSpan);
    
    $btn.prop('disabled', true);

    $applicationNameField.on('blur', function(e) {
      checkApplicationName();
    });

    $applicationNameField.on('keyup', function(e) {
      validApplicationName = false;
      var inputApplicationName = $('#edit-applicationname').val();
      var lowercasevalue = String(inputApplicationName).toLowerCase();
      var value = lowercasevalue.trim();
      $('.empty-efx-appname').hide();
      $('.efx-appname-error').hide();
      $('.efx-max-chars-appname-error').hide();
      $('.efx-duplicate-appname-error').hide();
      
      if (!value || value == undefined || value == 'undefined' || value == '') {
        
        validApplicationName = false;
      } 

      else {



        testApplicationName = re.test(String(value).toLowerCase());
        if (!testApplicationName) {
          validApplicationName = false;
        }

        else if($.inArray(value, clientAppNames) > -1){
          validApplicationName = false;
        } 
        
        else if(value.length > 25){
          validApplicationName = false;
        }
        else {
          validApplicationName = true;
        }
      }
      handleButton();
    });

    function handleButton() {
      if (validApplicationName && validDescription) {
        $btn.prop('disabled', false);
      } else {
        $btn.prop('disabled', true);
      }
    }
    
    function checkApplicationName() {
      validApplicationName = false;
      var inputApplicationName = $('#edit-applicationname').val();
      var lowercasevalue = String(inputApplicationName).toLowerCase();
      var value = lowercasevalue.trim();

      if (!value || value == undefined || value == 'undefined' || value == '') {

        console.log(inputApplicationName);
                console.log(lowercasevalue);
                        console.log(value);
       
        validApplicationName = false;
        $('.empty-efx-appname').show();
        $('.efx-appname-error').hide();
        $('.efx-max-chars-appname-error').hide();
        $('.efx-duplicate-appname-error').hide();
      } 

      else {

        testApplicationName = re.test(String(value).toLowerCase());
        if (!testApplicationName) {
          validApplicationName = false;
          $('.empty-efx-appname').hide();
          $('.efx-appname-error').show();
          $('.efx-max-chars-appname-error').hide();
          $('.efx-duplicate-appname-error').hide();
        }

        else if($.inArray(value, clientAppNames) > -1){
          validApplicationName = false;
        $('.empty-efx-appname').hide();
        $('.efx-appname-error').hide();
        $('.efx-max-chars-appname-error').hide();
        $('.efx-duplicate-appname-error').show();
        }  
      else if(value.length > 25) {
        validApplicationName = false;
        $('.empty-efx-appname').hide();
        $('.efx-appname-error').hide();
        $('.efx-max-chars-appname-error').show();
        $('.efx-duplicate-appname-error').hide();

      }
      
        else {
          validApplicationName = true;
          $('.empty-efx-appname').hide();
          $('.efx-appname-error').hide();
          $('.efx-max-chars-appname-error').hide();
          $('.efx-duplicate-appname-error').hide();
        }
      }
      handleButton();
    }

  $('#edit-description').keyup(function(){
    var charsno = $(this).val().length;
    $('.character-count').html(charsno + "/120");
  });

  })
}(jQuery));