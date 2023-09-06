

    jQuery(function() { 

       
        var billing_phone = document.querySelector("form.checkout.woocommerce-checkout [name=billing_phone]") 
        var billing_phone_full = document.querySelector("form.checkout.woocommerce-checkout [name=billing_phone_full]") 

     
        jQuery(billing_phone).numeric();
        if(billing_phone != null){

            
            var default_country_code = jQuery('form.checkout.woocommerce-checkout  [name=default_country_code]').val()
            var iti = window.intlTelInput(billing_phone, {
                initialCountry: default_country_code,
                separateDialCode:true,
                preferredCountries:["mx","us","gb","ca","ru"],
                formatOnDisplay:false,
                autoPlaceholder:"off",
                nationalMode:false,


            });
    

    
            iti.promise.then(function() {
                
                var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                jQuery('form.checkout.woocommerce-checkout .intl_tel_input_billing_phone_full input').val(number.replace(/ /g,''))
                jQuery('form.checkout.woocommerce-checkout #billing_phone_code_field input').val(iti.getSelectedCountryData().iso2 )
                jQuery('form.checkout.woocommerce-checkout #billing_country_phone_code').val(iti.getSelectedCountryData().dialCode)
                
                var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val()
                jQuery('form.checkout.woocommerce-checkout .intl_tel_input_billing_phone input').val(number)
                
                billing_phone.addEventListener("countrychange", function() {
                    var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                    jQuery('form.checkout.woocommerce-checkout .intl_tel_input_billing_phone_full input').val(number.replace(/ /g,''))
                    jQuery('form.checkout.woocommerce-checkout #billing_phone_code_field input').val(iti.getSelectedCountryData().iso2)
                    jQuery('form.checkout.woocommerce-checkout #billing_country_phone_code').val(iti.getSelectedCountryData().dialCode)

                });
    
                jQuery(billing_phone).on('keyup',function(){
                    var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                    jQuery('form.checkout.woocommerce-checkout .intl_tel_input_billing_phone_full input').val(number.replace(/ /g,''))
                })

    
            });

            if(vl_ajax.is_user_logged_in == '1'){
                var _this = jQuery(billing_phone)
                var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                jQuery(billing_phone).closest('.form-row').find('.wa-validation-result').remove();
                jQuery(billing_phone).closest('.form-row').append('<p class="wa-validation-result"><strong>Validando número de WhatsApp...</strong></p>');
                jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',true)
                jQuery.ajax({
                    url : vl_ajax.ajaxurl,
                    dataType : "json",
                    type: 'post',
                    data: {
                        action : 'vl_validate_wa',
                        wa_number: number
                    },
                    success: function(resultado){
                        _this.closest('.form-row').find('.wa-validation-result').remove();
                        if(resultado.response){
                           
                            if(resultado.whatsapp_validation == true){
                                _this.closest('.form-row').append('<p class="wa-validation-result" style="color:#0f834d"><strong>Número de WhatsApp válido.</strong></p>');
                                _this.closest('.form-row').addClass('woocommerce-validated')
                            }
                            
                            jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',false)
                        }else{
                           
                            if(typeof resultado.error != 'undefined'){
                                //_this.closest('.form-row').append('<p class="wa-validation-result" style="color:#ff9800"><strong>'+resultado.error+'</strong></p>');
                                //_this.closest('.form-row').addClass('woocommerce-invalid')
                                jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',false)
                            }else{
                                _this.closest('.form-row').append('<p class="wa-validation-result" style="color:#e2401c"><strong>Número de WhatsApp inválido.</strong></p>');
                                _this.closest('.form-row').addClass('woocommerce-invalid')
                            }
                           
                            
                        }
                        
                        
                    }
                    
                });
            }
            
            jQuery(billing_phone).on('blur',function(){
                var _this = jQuery(this)
                var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                jQuery(this).closest('.form-row').find('.wa-validation-result').remove();
                jQuery(this).closest('.form-row').append('<p class="wa-validation-result"><strong>Validando número de WhatsApp...</strong></p>');
                jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',true)
                _this.closest('.form-row').removeClass('woocommerce-validated')
                 _this.closest('.form-row').removeClass('woocommerce-invalid')
                jQuery.ajax({
                    url : vl_ajax.ajaxurl,
                    dataType : "json",
                    type: 'post',
                    data: {
                        action : 'vl_validate_wa',
                        wa_number: number
                    },
                    success: function(resultado){
                        _this.closest('.form-row').find('.wa-validation-result').remove();
                        if(resultado.response){
                           
                            if(resultado.whatsapp_validation == true){
                                _this.closest('.form-row').append('<p class="wa-validation-result" style="color:#0f834d"><strong>Número de WhatsApp válido.</strong></p>');
                                _this.closest('.form-row').addClass('woocommerce-validated')
                            }
                            
                            jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',false)
                        }else{
                           
                            if(typeof resultado.error != 'undefined'){
                                //_this.closest('.form-row').append('<p class="wa-validation-result" style="color:#ff9800"><strong>'+resultado.error+'</strong></p>');
                                //_this.closest('.form-row').addClass('woocommerce-invalid')
                                jQuery('form.checkout.woocommerce-checkout').find('button[type=submit]').attr('disabled',false)
                            }else{
                                _this.closest('.form-row').append('<p class="wa-validation-result" style="color:#e2401c"><strong>Número de WhatsApp inválido.</strong></p>');
                                _this.closest('.form-row').addClass('woocommerce-invalid')
                            }
                           
                            
                        }
                        
                        
                    }
                    
                });
                
            })

            if(jQuery(billing_phone).val() != ""){
                var number = "+"+iti.getSelectedCountryData().dialCode+jQuery(billing_phone).val().trim()
                jQuery('form.checkout.woocommerce-checkout .intl_tel_input_billing_phone_full input').val(number.replace(/ /g,''))
            }

            jQuery(document).ajaxComplete(function() {
                if(jQuery('.woocommerce-error [data-id=billing_phone]').length > 0){
                    jQuery('#billing_phone_field').removeClass("woocommerce-validated").addClass("woocommerce-invalid")
                }
            });
           
            
           

        }

        
    
    })

