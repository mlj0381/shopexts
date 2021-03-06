$.fn.bx_anim = function(action, effect, speed, h) {    
   return this.each(function() {   		
   		var sFunc = '';
   		var sEval;

   		if (speed == 0)
   			effect = 'default';
   			
  		switch (action) {
  			case 'show':
  				switch (effect) {
  					case 'slide': sFunc = 'slideDown'; break;
  					case 'fade': sFunc = 'fadeIn'; break;
  					default: sFunc = 'show';
  				}  				
  				break;
  			case 'hide':
  				switch (effect) {
  					case 'slide': sFunc = 'slideUp'; break;
  					case 'fade': sFunc = 'fadeOut'; break;
  					default: sFunc = 'hide';
  				}  				
  				break;  				
  			default:
  			case 'toggle':
  				switch (effect) {
  					case 'slide': sFunc = 'slideToggle'; break;
  					case 'fade': sFunc = ($(this).filter(':visible').length) ? 'fadeOut' : 'fadeIn'; break;
  					default: sFunc = 'toggle';
  				}  				  			  				
  		}
  		
  		if ((0 == speed || undefined == speed) && undefined == h) {
  			sEval = '$(this).' + sFunc + '();';
  		}
  		else if ((0 == speed || undefined == speed) && undefined != h) {
  			sEval = '$(this).' + sFunc + '(); $(this).each(h);';
  		}
  		else {
  			sEval = '$(this).' + sFunc + "('" + speed + "', h);";
  		}
  		eval(sEval);
  		
  		return this;
   });  
};
$.fn.bx_loading = function(sParentSelector) {
    var iWidth = 0, iHeight =0;
    var oParent = null, oOffset = null;    
    
    if(sParentSelector != undefined)
        oParent = $(sParentSelector);
    
    return this.each(function() {
        var oLoading = $(this);        
        var oLoadingIcon = oLoading.children('.sys-loading-icon');

        oParent = oParent != null ? oParent : oLoading.parent();        

        if(!oLoading.is(':visible')) {
            oOffset = oParent.offset();
            iWidth = oParent.outerWidth();
            iHeight = oParent.outerHeight();

            /*--- Set Main ---*/            
            oLoading.width(iWidth);
            oLoading.height(iHeight);

            /*--- Set Icon ---*/
            oLoadingIcon.css('left', (iWidth - parseInt(oLoadingIcon.css('width')))/2);
            oLoadingIcon.css('top', (iHeight - parseInt(oLoadingIcon.css('height')))/2);
            oLoading.show();
        }
        else
            oLoading.hide();
    });
};
$.fn.bx_message_box = function(sMessage, iTimer, onClose) {
    return this.each(function() {
        var oParent = $(this);
        
        if(oParent.children(':first').hasClass('MsgBox'))
            oParent.children(':first').replaceWith(sMessage);
        else 
            oParent.prepend(sMessage);

        if(iTimer == undefined || parseInt(iTimer) == 0)
            return;

        setTimeout(function(oParent, onClose) {
            oParent.children('div.MsgBox:first').bx_anim('hide', 'fade', 'slow', function(){
                $(this).remove();
                if(onClose != undefined)
                    onClose();
            });
        }, 1000 * parseInt(iTimer), oParent, onClose);
    });
};
function bxShowLoading(oParent, bShow) {
    var oLoading = oParent.children('.upload-loading');
    
    if(bShow) {
        oLoading.css('left', (oParent.width() - oLoading.width())/2);
        oLoading.css('top', (oParent.height() - oLoading.height())/2);
        oLoading.show();
    }
    else
        oLoading.hide();
}