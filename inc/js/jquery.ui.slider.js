/*!
 * jQuery UI Slider 1.9.2
 * http://jqueryui.com
 *
 * Copyright 2012 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/slider/
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.mouse.js
 *	jquery.ui.widget.js
 */
(function(a){var c=5;a.widget("ui.slider",a.ui.mouse,{version:"1.9.2",widgetEventPrefix:"slide",options:{animate:!1,distance:0,max:100,min:0,orientation:"horizontal",range:!1,step:1,value:0,values:null},_create:function(){var b,d,e=this.options,f=this.element.find(".ui-slider-handle").addClass("ui-state-default ui-corner-all"),g="<a class='ui-slider-handle ui-state-default ui-corner-all' href='#'></a>",h=[];for(this._keySliding=!1,this._mouseSliding=!1,this._animateOff=!0,this._handleIndex=null,this._detectOrientation(),this._mouseInit(),this.element.addClass("ui-slider ui-slider-"+this.orientation+" ui-widget"+" ui-widget-content"+" ui-corner-all"+(e.disabled?" ui-slider-disabled ui-disabled":"")),this.range=a([]),e.range&&(e.range===!0&&(e.values||(e.values=[this._valueMin(),this._valueMin()]),e.values.length&&2!==e.values.length&&(e.values=[e.values[0],e.values[0]])),this.range=a("<div></div>").appendTo(this.element).addClass("ui-slider-range ui-widget-header"+("min"===e.range||"max"===e.range?" ui-slider-range-"+e.range:""))),d=e.values&&e.values.length||1,b=f.length;d>b;b++)h.push(g);this.handles=f.add(a(h.join("")).appendTo(this.element)),this.handle=this.handles.eq(0),this.handles.add(this.range).filter("a").click(function(a){a.preventDefault()}).mouseenter(function(){e.disabled||a(this).addClass("ui-state-hover")}).mouseleave(function(){a(this).removeClass("ui-state-hover")}).focus(function(){e.disabled?a(this).blur():(a(".ui-slider .ui-state-focus").removeClass("ui-state-focus"),a(this).addClass("ui-state-focus"))}).blur(function(){a(this).removeClass("ui-state-focus")}),this.handles.each(function(b){a(this).data("ui-slider-handle-index",b)}),this._on(this.handles,{keydown:function(b){var d,e,f,g,h=a(b.target).data("ui-slider-handle-index");switch(b.keyCode){case a.ui.keyCode.HOME:case a.ui.keyCode.END:case a.ui.keyCode.PAGE_UP:case a.ui.keyCode.PAGE_DOWN:case a.ui.keyCode.UP:case a.ui.keyCode.RIGHT:case a.ui.keyCode.DOWN:case a.ui.keyCode.LEFT:if(b.preventDefault(),!this._keySliding&&(this._keySliding=!0,a(b.target).addClass("ui-state-active"),d=this._start(b,h),d===!1))return}switch(g=this.options.step,e=f=this.options.values&&this.options.values.length?this.values(h):this.value(),b.keyCode){case a.ui.keyCode.HOME:f=this._valueMin();break;case a.ui.keyCode.END:f=this._valueMax();break;case a.ui.keyCode.PAGE_UP:f=this._trimAlignValue(e+(this._valueMax()-this._valueMin())/c);break;case a.ui.keyCode.PAGE_DOWN:f=this._trimAlignValue(e-(this._valueMax()-this._valueMin())/c);break;case a.ui.keyCode.UP:case a.ui.keyCode.RIGHT:if(e===this._valueMax())return;f=this._trimAlignValue(e+g);break;case a.ui.keyCode.DOWN:case a.ui.keyCode.LEFT:if(e===this._valueMin())return;f=this._trimAlignValue(e-g)}this._slide(b,h,f)},keyup:function(b){var c=a(b.target).data("ui-slider-handle-index");this._keySliding&&(this._keySliding=!1,this._stop(b,c),this._change(b,c),a(b.target).removeClass("ui-state-active"))}}),this._refreshValue(),this._animateOff=!1},_destroy:function(){this.handles.remove(),this.range.remove(),this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all"),this._mouseDestroy()},_mouseCapture:function(b){var c,d,e,f,g,h,i,j,k=this,l=this.options;return l.disabled?!1:(this.elementSize={width:this.element.outerWidth(),height:this.element.outerHeight()},this.elementOffset=this.element.offset(),c={x:b.pageX,y:b.pageY},d=this._normValueFromMouse(c),e=this._valueMax()-this._valueMin()+1,this.handles.each(function(b){var c=Math.abs(d-k.values(b));e>c&&(e=c,f=a(this),g=b)}),l.range===!0&&this.values(1)===l.min&&(g+=1,f=a(this.handles[g])),h=this._start(b,g),h===!1?!1:(this._mouseSliding=!0,this._handleIndex=g,f.addClass("ui-state-active").focus(),i=f.offset(),j=!a(b.target).parents().andSelf().is(".ui-slider-handle"),this._clickOffset=j?{left:0,top:0}:{left:b.pageX-i.left-f.width()/2,top:b.pageY-i.top-f.height()/2-(parseInt(f.css("borderTopWidth"),10)||0)-(parseInt(f.css("borderBottomWidth"),10)||0)+(parseInt(f.css("marginTop"),10)||0)},this.handles.hasClass("ui-state-hover")||this._slide(b,g,d),this._animateOff=!0,!0))},_mouseStart:function(){return!0},_mouseDrag:function(a){var b={x:a.pageX,y:a.pageY},c=this._normValueFromMouse(b);return this._slide(a,this._handleIndex,c),!1},_mouseStop:function(a){return this.handles.removeClass("ui-state-active"),this._mouseSliding=!1,this._stop(a,this._handleIndex),this._change(a,this._handleIndex),this._handleIndex=null,this._clickOffset=null,this._animateOff=!1,!1},_detectOrientation:function(){this.orientation="vertical"===this.options.orientation?"vertical":"horizontal"},_normValueFromMouse:function(a){var b,c,d,e,f;return"horizontal"===this.orientation?(b=this.elementSize.width,c=a.x-this.elementOffset.left-(this._clickOffset?this._clickOffset.left:0)):(b=this.elementSize.height,c=a.y-this.elementOffset.top-(this._clickOffset?this._clickOffset.top:0)),d=c/b,d>1&&(d=1),0>d&&(d=0),"vertical"===this.orientation&&(d=1-d),e=this._valueMax()-this._valueMin(),f=this._valueMin()+d*e,this._trimAlignValue(f)},_start:function(a,b){var c={handle:this.handles[b],value:this.value()};return this.options.values&&this.options.values.length&&(c.value=this.values(b),c.values=this.values()),this._trigger("start",a,c)},_slide:function(a,b,c){var d,e,f;this.options.values&&this.options.values.length?(d=this.values(b?0:1),2===this.options.values.length&&this.options.range===!0&&(0===b&&c>d||1===b&&d>c)&&(c=d),c!==this.values(b)&&(e=this.values(),e[b]=c,f=this._trigger("slide",a,{handle:this.handles[b],value:c,values:e}),d=this.values(b?0:1),f!==!1&&this.values(b,c,!0))):c!==this.value()&&(f=this._trigger("slide",a,{handle:this.handles[b],value:c}),f!==!1&&this.value(c))},_stop:function(a,b){var c={handle:this.handles[b],value:this.value()};this.options.values&&this.options.values.length&&(c.value=this.values(b),c.values=this.values()),this._trigger("stop",a,c)},_change:function(a,b){if(!this._keySliding&&!this._mouseSliding){var c={handle:this.handles[b],value:this.value()};this.options.values&&this.options.values.length&&(c.value=this.values(b),c.values=this.values()),this._trigger("change",a,c)}},value:function(a){return arguments.length?(this.options.value=this._trimAlignValue(a),this._refreshValue(),this._change(null,0),b):this._value()},values:function(c,d){var e,f,g;if(arguments.length>1)return this.options.values[c]=this._trimAlignValue(d),this._refreshValue(),this._change(null,c),b;if(!arguments.length)return this._values();if(!a.isArray(arguments[0]))return this.options.values&&this.options.values.length?this._values(c):this.value();for(e=this.options.values,f=arguments[0],g=0;e.length>g;g+=1)e[g]=this._trimAlignValue(f[g]),this._change(null,g);this._refreshValue()},_setOption:function(b,c){var d,e=0;switch(a.isArray(this.options.values)&&(e=this.options.values.length),a.Widget.prototype._setOption.apply(this,arguments),b){case"disabled":c?(this.handles.filter(".ui-state-focus").blur(),this.handles.removeClass("ui-state-hover"),this.handles.prop("disabled",!0),this.element.addClass("ui-disabled")):(this.handles.prop("disabled",!1),this.element.removeClass("ui-disabled"));break;case"orientation":this._detectOrientation(),this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-"+this.orientation),this._refreshValue();break;case"value":this._animateOff=!0,this._refreshValue(),this._change(null,0),this._animateOff=!1;break;case"values":for(this._animateOff=!0,this._refreshValue(),d=0;e>d;d+=1)this._change(null,d);this._animateOff=!1;break;case"min":case"max":this._animateOff=!0,this._refreshValue(),this._animateOff=!1}},_value:function(){var a=this.options.value;return a=this._trimAlignValue(a)},_values:function(a){var b,c,d;if(arguments.length)return b=this.options.values[a],b=this._trimAlignValue(b);for(c=this.options.values.slice(),d=0;c.length>d;d+=1)c[d]=this._trimAlignValue(c[d]);return c},_trimAlignValue:function(a){if(this._valueMin()>=a)return this._valueMin();if(a>=this._valueMax())return this._valueMax();var b=this.options.step>0?this.options.step:1,c=(a-this._valueMin())%b,d=a-c;return 2*Math.abs(c)>=b&&(d+=c>0?b:-b),parseFloat(d.toFixed(5))},_valueMin:function(){return this.options.min},_valueMax:function(){return this.options.max},_refreshValue:function(){var b,c,d,e,f,g=this.options.range,h=this.options,i=this,j=this._animateOff?!1:h.animate,k={};this.options.values&&this.options.values.length?this.handles.each(function(d){c=100*((i.values(d)-i._valueMin())/(i._valueMax()-i._valueMin())),k["horizontal"===i.orientation?"left":"bottom"]=c+"%",a(this).stop(1,1)[j?"animate":"css"](k,h.animate),i.options.range===!0&&("horizontal"===i.orientation?(0===d&&i.range.stop(1,1)[j?"animate":"css"]({left:c+"%"},h.animate),1===d&&i.range[j?"animate":"css"]({width:c-b+"%"},{queue:!1,duration:h.animate})):(0===d&&i.range.stop(1,1)[j?"animate":"css"]({bottom:c+"%"},h.animate),1===d&&i.range[j?"animate":"css"]({height:c-b+"%"},{queue:!1,duration:h.animate}))),b=c}):(d=this.value(),e=this._valueMin(),f=this._valueMax(),c=f!==e?100*((d-e)/(f-e)):0,k["horizontal"===this.orientation?"left":"bottom"]=c+"%",this.handle.stop(1,1)[j?"animate":"css"](k,h.animate),"min"===g&&"horizontal"===this.orientation&&this.range.stop(1,1)[j?"animate":"css"]({width:c+"%"},h.animate),"max"===g&&"horizontal"===this.orientation&&this.range[j?"animate":"css"]({width:100-c+"%"},{queue:!1,duration:h.animate}),"min"===g&&"vertical"===this.orientation&&this.range.stop(1,1)[j?"animate":"css"]({height:c+"%"},h.animate),"max"===g&&"vertical"===this.orientation&&this.range[j?"animate":"css"]({height:100-c+"%"},{queue:!1,duration:h.animate}))}})})(jQuery);
