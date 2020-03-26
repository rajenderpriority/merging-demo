(function($) {
  $(document).ready(function() {


    //IP whitelisting validations

    var $errIPAddressSpan = '<span class="efx-ipaddress-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:5%;left: 0%;position:absolute;">Provide a valid IP Address</span>';
    var $errCIDRSpan = '<span class="efx-cidr-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;left: 62%;color:#e70022;bottom:5%;position:absolute;">Provide a valid CIDR</span>';
    var $errTargetDateSpan = '<span class="empty-targetdate-error" style="display:none;font-weight: 400;z-index: 2;white-space: nowrap;font-size: 1.4rem;color:#e70022;bottom:0%;left: 0%;position:absolute;">Targeted Go Live date is required</span>';
        
    var $form = $('.promotion-form');
    var $ipAddress = $form.find('#edit-ip-address');
    var $cidr = $form.find('#edit-cidr');
    var $ipVersion = $form.find('#edit-description');
    var $targetDate = $form.find('#edit-release-date');
    var targetDateValue = $targetDate.datepicker('getDate'); 
    var validIpAddress = true;
    var validCIDR = true;
    var validIpVersion = true;
    var validTargetDate = false;
    var ipwhitelist = 0;

    $ipAddress.after($errIPAddressSpan);
    $cidr.after($errCIDRSpan);
    $targetDate.after($errTargetDateSpan);    

    //Ip address validation || IP address must be of same IP version - matching Ipversion on dropdown
    var ipv4regex = /((^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[0-9]{1,2})){3}$))/;
    var ipv6regex = /((^([0-9a-fA-F]{4}|0)(\:([0-9a-fA-F]{4}|0)){7}$))/;
        
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();
      

    $ipAddress.on('keyup', function(e) {
      var $ip = $('#edit-ip-address').val();
      var response = checkIpAddress($ip);
      if(response == 'invalid'){
        $('.efx-ipaddress-error').show();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();
     
        validIpAddress = false;
      }
      else{
        $('.efx-ipaddress-error').hide();
        validIpAddress = true;
      }

    });

    $cidr.on('keyup', function(e) {
      var $cidrValue = $('#edit-cidr').val();
      if (!$cidrValue || $cidrValue == undefined || $cidrValue == 'undefined' || $cidrValue == '') {
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();
       
        validCIDR = true;
      } 
      else if(($cidrValue < 1)||($cidrValue > 128)||(!$.isNumeric($cidrValue))){
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').show();
        $('.empty-targetdate-error').hide();
 
        validCIDR = false;
      }
      else{
        $('.efx-cidr-error').hide();
        validCIDR = true;
      }
    });

    $('.add-ip-address').click(function(event){
      
      var $ipAddress = $('#edit-ip-address').val();
      var addressResponse = checkIpAddress($ipAddress);
      var ipVersionOption = $('#edit-ip-version').val();
      if(ipVersionOption == 1){
        var ipVersion = 'IPv6';
      }
      else if(ipVersionOption == 0){
        var ipVersion = 'IPv4';
      }

      var cidrValue = $('#edit-cidr').val();

      if(validIpAddress == false){
        $('.efx-ipaddress-error').show();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();
      }

      else if(validCIDR == false){
        console.log('u s n s t');
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').show();
        $('.empty-targetdate-error').hide();
      }

      else if(addressResponse != ipVersion){
        console.log(ipVersion);
        $('.efx-ipaddress-error').show();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();
      }

      else if((addressResponse=='IPv4') && (cidrValue > 32 || validCIDR == false)){
        
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').show();
        $('.empty-targetdate-error').hide();
      }

      else{
        $('.efx-ipaddress-error').hide();
        $('.efx-cidr-error').hide();
        $('.empty-targetdate-error').hide();

        if((addressResponse=='IPv4') && (!cidrValue || cidrValue == undefined || cidrValue == 'undefined' || cidrValue == '')){
          cidrValue = 32;
        }

        else if((addressResponse=='IPv6') && (!cidrValue || cidrValue == undefined || cidrValue == 'undefined' || cidrValue == '')){
          cidrValue = 128;
        }
        
        //Adding IPs also add remove button
        $('.ip-addresses-added').append('<div style="display:flex; width:632px;"><input data-drupal-selector="edit-ip-address-'+ipwhitelist+'" type="text" id="edit-ip-address'+ipwhitelist+'" name="ip_array['+ipwhitelist+'][ip]" value="'+$ipAddress+'" size="60" maxlength="128" class="form-text added-ipaddress"><input data-drupal-selector="edit-cidr-'+ipwhitelist+'" type="text" id="edit-cidr-'+ipwhitelist+'" name="ip_array['+ipwhitelist+'][cidr]" value="'+cidrValue+'" size="60" maxlength="128" class="form-text added-cidr"><input data-drupal-selector="edit-ipversion-'+ipwhitelist+'" type="text" id="edit-ipversion-'+ipwhitelist+'" name="ip_array['+ipwhitelist+'][ipversion]" value="'+ipVersion+'" size="60" maxlength="128" class="form-text added-ipversion"><i class="far fa-minus-circle remove-ip-address"></i></div>');
        
        //Removing values from main form
        $('#edit-ip-address').val('');
        $('#edit-cidr').val('');
        $('#edit-ip-version').val(0);
        ipwhitelist++;

      }
       
    });

//Remove IP values
$(document).on('click', '.ip-addresses-added div i.remove-ip-address', function(){
  $(this).parent().remove();
});

//Target date validation
$('#edit-promotion-submit').click(function(event){

if(!$('#edit-release-date').val() || $('#edit-release-date').val() == undefined || $('#edit-release-date').val() == 'undefined' || $('#edit-release-date').val() == ''){
    event.preventDefault();
  $('.empty-targetdate-error').show();
}
else{
  $('.empty-targetdate-error').hide();
  $('#edit-promotion-submit').click();
}
});


//Cancel Promotion
$('.cancel-promotion').click(function(event){

  $('.promotion-primary').css('display','block');
  $('.promotion-primary').css('position','absolute');
  $('.promotion-empty-form').css("display", "none");
  $('.promotion-empty-form').css("position", "relative");
  $('#edit-ip-address').val('');
  $('#edit-cidr').val('');
  $('#edit-description').val('');
  $('#edit-release-date').val('');
  $('.efx-ipaddress-error').hide();
  $('.efx-cidr-error').hide();
  $('.empty-targetdate-error').hide();
  $('.trigger-promotion').css("color", "#434343");
  $('.trigger-promotion').children("i").css("color", "#434343");
  $('.trigger-promotion').css("pointer-events", "all");
  $('.ip-addresses-added').empty();


});

//Datepicker not to allow past dates
$("#edit-release-date").datepicker({
    dateFormat: 'mm/dd/yy',
    minDate: 0,
 
});



    function checkIpAddress($ip) {

      if (!$ip || $ip == undefined || $ip == 'undefined' || $ip == '') {
        return 'empty';
      } 

      else {

        testipv4regex = ipv4regex.test(String($ip));
        testipv6regex = ipv6regex.test(String($ip));
        if(testipv4regex) {
          return 'IPv4';
        }
        else if(testipv6regex){
          return 'IPv6';
        }

        else{
          return 'invalid';
        }
      
      }

    }

  })

}(jQuery));

