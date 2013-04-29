/**
* HTML5 placeholder polyfill
* @requires jQuery - tested with 1.6.2 but might as well work with older versions
* 
* code: https://github.com/ginader/HTML5-placeholder-polyfill
* please report issues at: https://github.com/ginader/HTML5-placeholder-polyfill/issues
*
* Copyright (c) 2012 Dirk Ginader (ginader.de)
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource.org/licenses/mit-license.php
* http://www.gnu.org/licenses/gpl.html
*
* Version: 2.0.6
* 
*/

(function($) {
    var debug = false,
        animId;
    function showPlaceholderIfEmpty(input,options) {
        if( input.val() === '' ){
            input.data('placeholder').removeClass(options.hideClass);
        }else{
            input.data('placeholder').addClass(options.hideClass);
        }
    }
    function hidePlaceholder(input,options){
        input.data('placeholder').addClass(options.hideClass);
    }
    function positionPlaceholder(placeholder,input){
        var ta  = input.is('textarea');

        // Determine if we need to shift the header down more.
        var offset = input.offset();
        if (input.css('padding') && input.css('padding') !== '0px') {
          var padding = input.css('padding').split(' ');
          offset.top += Number(padding[0].replace('px', ''));
          offset.left += Number(padding[padding.length - 1].replace('px', ''));
        }
        else {
          if (input.css('padding-top') && input.css('padding-top') !== '0px') {
            offset.top += Number(input.css('padding-top').replace('px', ''));
          }
          if (input.css('padding-left') && input.css('padding-left') !== '0px') {
            offset.left += Number(input.css('padding-left').replace('px', ''));
          }
        }

        placeholder.css({
            width : input.innerWidth()-(ta ? 20 : 4),
            height : input.innerHeight()-6,
            lineHeight : input.css('line-height'),
            whiteSpace : ta ? 'normal' : 'nowrap',
            overflow : 'hidden'
        }).offset(offset);
    }
    function startFilledCheckChange(input,options){
        var val = input.val();
        (function checkloop(){
            animId = requestAnimationFrame(checkloop);
            if(input.val() !== val){
                hidePlaceholder(input,options);
                stopCheckChange();
                startEmptiedCheckChange(input,options);
            }
        }());
    }
    function startEmptiedCheckChange(input,options){
        (function checkloop(){
            animId = requestAnimationFrame(checkloop);
            showPlaceholderIfEmpty(input,options);
        }());
    }
    function stopCheckChange(){
        cancelAnimationFrame(animId);
    }
    function log(msg){
        if(debug && window.console && window.console.log){
            window.console.log(msg);
        }
    }

    $.fn.placeHolder = function(config) {
        log('init placeHolder');
        var o = this;
        var l = $(this).length;
        this.options = $.extend({
            className: 'placeholder', // css class that is used to style the placeholder
            visibleToScreenreaders : true, // expose the placeholder text to screenreaders or not
            visibleToScreenreadersHideClass : 'placeholder-hide-except-screenreader', // css class is used to visually hide the placeholder
            visibleToNoneHideClass : 'placeholder-hide', // css class used to hide the placeholder for all
            hideOnFocus : false, // either hide the placeholder on focus or on type
            removeLabelClass : 'visuallyhidden', // remove this class from a label (to fix hidden labels)
            hiddenOverrideClass : 'visuallyhidden-with-placeholder', // replace the label above with this class
            forceHiddenOverride : true, // allow the replace of the removeLabelClass with hiddenOverrideClass or not
            forceApply : false, // apply the polyfill even for browser with native support
            autoInit : true // init automatically or not
        }, config);
        this.options.hideClass = this.options.visibleToScreenreaders ? this.options.visibleToScreenreadersHideClass : this.options.visibleToNoneHideClass;
        return $(this).each(function(index) {
            var input = $(this),
                text = input.attr('placeholder'),
                id = input.attr('id'),
                label,placeholder,titleNeeded,polyfilled;
            if(text === "" || text === undefined) {
              text = input[0].attributes["placeholder"].value;
            }
            label = input.closest('label');
            input.removeAttr('placeholder');
            if(!label.length && !id){
                log('the input element with the placeholder needs an id!');
                return;
            }
            label = label.length ? label : $('label[for="'+id+'"]').first();
            if(!label.length){
                log('the input element with the placeholder needs a label!');
                return;
            }
            polyfilled = $(label).find('.placeholder');
            if(polyfilled.length) {
                //log('the input element already has a polyfilled placeholder!');
                positionPlaceholder(polyfilled,input);
                polyfilled.text(text);
                return input;
            }
            
            if(label.hasClass(o.options.removeLabelClass)){
                label.removeClass(o.options.removeLabelClass)
                     .addClass(o.options.hiddenOverrideClass);
            }

            placeholder = $('<span>').addClass(o.options.className).text(text).appendTo(label);

            titleNeeded = (placeholder.width() > input.width());
            if(titleNeeded){
                placeholder.attr('title',text);
            }
            positionPlaceholder(placeholder,input);
            input.data('placeholder',placeholder);
            placeholder.data('input',placeholder);
            placeholder.click(function(){
                $(this).data('input').focus();
            });
            input.focusin(function() {
                if(!o.options.hideOnFocus && window.requestAnimationFrame){
                    startFilledCheckChange(input,o.options);
                }else{
                    hidePlaceholder(input,o.options);
                }
            });
            input.focusout(function(){
                showPlaceholderIfEmpty($(this),o.options);
                if(!o.options.hideOnFocus && window.cancelAnimationFrame){
                    stopCheckChange();
                }
            });
            showPlaceholderIfEmpty(input,o.options);

            // reformat on window resize and optional reformat on font resize - requires: http://www.tomdeater.com/jquery/onfontresize/
            $(document).bind("fontresize resize", function(){
                positionPlaceholder(placeholder,input);
            });

            // optional reformat when a textarea is being resized - requires http://benalman.com/projects/jquery-resize-plugin/
            if($.event.special.resize){
                $("textarea").bind("resize", function(e){
                    positionPlaceholder(placeholder,input);
                });
            }else{
                // we simply disable the resizeablilty of textareas when we can't react on them resizing
                $("textarea").css('resize','none');
            }

            if(index >= l-1){
                $.attrHooks.placeholder = {
                    get: function(elem) {
                        if (elem.nodeName.toLowerCase() === 'input' || elem.nodeName.toLowerCase() === 'textarea') {
                            if( $(elem).data('placeholder') ){ 
                                // has been polyfilled
                                return $( $(elem).data('placeholder') ).text();
                            }else{
                                // native / not yet polyfilled
                                return $(elem)[0].placeholder;
                            }
                            
                        }else{
                            return undefined;
                        }
                    },
                    set: function(elem, value){
                        return $( $(elem).data('placeholder') ).text(value);
                    }
                };
            }
        });

    

    };
    $(function(){
        var config = window.placeHolderConfig || {};
        if(config.autoInit === false){
            log('placeholder:abort because autoInit is off');
            return;
        }
        if(('placeholder' in $('<input>')[0] || 'placeHolder' in $('<input>')[0]) && !config.forceApply){ // don't run the polyfill when the browser has native support
            log('placeholder:abort because browser has native support');
            return;
        }
        $('input[placeholder], textarea[placeholder]').placeHolder(config);
    });
}(jQuery));
