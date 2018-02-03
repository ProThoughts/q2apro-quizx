/*
	Plugin Name: Q2APRO Quizx
	Copyright: q2apro.com
*/

$(document).ready(function()
{

	// mobile site
	var ismobile = ($("#agentIsMobile").length != 0);
	
	// THEME CHANGES
	// var elem1 = $('.qam-logged-in-points').detach();
	// $('.qam-account-items-wrapper').prepend(elem1);
	// var elem1 = $('.qam-account-items').detach();
	
	if($('.qam-logged-out').length>0) {
		// if user logged out, hide the register form
		$('.qam-account-items').hide();
	
		// if( $('.icon-key.qam-auth-key').length>0) {
		// label field (css removes the key icon)
		// $('.icon-key.qam-auth-key').after('<span>Anmelden</span>');
		
		// bring register button to front
		var elem = $('.qa-nav-user-list').detach();
		$('.qam-account-items-wrapper').append(elem);
		$('.qam-logged-out').click( function() { 
			// hide register field if login fields are shown
			$('.qa-nav-user-list').toggle();
		});
		
		// always check the remember-me-box
		$('#qa-rememberme').prop('checked', true);
	}
	

	// if there is a h1>.qa-error message, put them into the beginning of h1
	if($('h1 + .qa-error').length>0) {
		var elem = $('h1 + .qa-error').detach();
		$('.qa-main').prepend(elem);
	}
	
	
	$(window).scroll(function(){
		// question page
		if($('.qa-template-question').length>0) {
			if($('.fixme').length==0) {
				// duplicate h1 with class copy
				$('.qa-main h1').clone().insertAfter('.qa-main h1').attr('class', 'fixme');
			}
			
			// stick title on top if scrolled down
			if ($(this).scrollTop() > 190) { // 135
				// $('.qa-main h1').addClass('fixme');
				$('.fixme').show();
			}
			else {
				$('.fixme').hide();
				// $('.qa-main h1').removeClass('fixme');
			}
		}
	});
	
	/* QUIZX */
	
	// now we can assign tipsy to question titles in q-list
	if(!ismobile) {
		$('.qa-q-item-title a span').tipsy( {gravity: 'n', fade: true, offset:10, delayIn:800, html:true } );
	}

	// lightbox effect for images (overlay)
	if(!ismobile) {
		// insert the lightbox in the end of the body
		$(".entry-content img").click( function(){
			$("#lightbox-popup").fadeIn("slow");
			$("#lightbox-img").attr("src", $(this).attr("src"));
			// center vertical
			$("#lightbox-center").css("margin-top", ($(window).height() - $("#lightbox-center").height())/2  + 'px');
		});
		$("#lightbox-popup").click( function(){
			$("#lightbox-popup").fadeOut("fast");
		});
		// keylistener ESC to close Lightbox
		$(document).keyup(function(e) {
		  if (e.keyCode == 27) { 
				if( $("#lightbox-popup").is(":visible") ) {
					$("#lightbox-popup").fadeOut("fast");
				}
			}
		});
	}
	else { 
		// open images in new window on mobiles, no lightbox effect
		$(".entry-content img").each(function() {
			var a = $('<a/>').attr('href', this.src);
			$(this).addClass('image').wrap(a);
			//$('.entry-content img').parent('a').attr('target', '_blank');
		});
	}

	// remove height attr from img for proportional display (max-width is set by css)
	$('.entry-content img').each(function(){
		$(this).removeAttr('height');
	});

	/*
	// PREVENT caps lock on question title field
	$('.qa-template-ask #title').keypress(function(e) { 
		var s = String.fromCharCode( e.which );
		if ( s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey ) {
			alert(unescape('Bitte richtig schreiben. FRAGEN IN GROSSSCHRIFT werden gel%F6scht.'));
		}
	});

	// add span-id below #title on ask page
	if($('.qa-template-ask #title').length > 0){
		$('<span id="requireMoreChars" style="display:block;color:#FF9000;"></span>').insertAfter('#title');
	}
	// display necessary number of characters
	$('.qa-template-ask #title').keyup(function(e) { 
	   nchars = (20 - $("#title").val().length);
	   if(nchars<=0) {
	       $('#requireMoreChars').html('');
	   }
	   else {
	       $('#requireMoreChars').html('Noch '+nchars+' Zeichen notwendig.');
	   }
	});
	*/
	
	// tipsy wont work on all devices
	if(!ismobile) {
		$('.tooltip, .tooltipS').tipsy( {gravity: 's', fade: false, offset: 5, html:true} );
		$('.tooltipW').tipsy( {gravity: 'w', fade: false, offset: 5, html:true} );
		$('.tooltipE').tipsy( {gravity: 'e', fade: false, offset: 5, html:true} );
		$('.tooltipN, qa-history-new-event-link').tipsy( {gravity: 'n', fade: false, offset: 5, html:true} );

		$('.qa-vote-one-button, .qa-favorite-button, .qa-unfavorite-button').tipsy( {gravity: 's', fade: false, offset:0 } );
		$('.qa-vote-first-button, .qa-vote-second-button').tipsy( {gravity: 'w', fade: false, offset:0 } );
		$('.qa-form-light-button').tipsy( {gravity: 's', fade: true, offset:3, delayIn:800 } );
		$('.qa-a-select-button').tipsy( {gravity: 's', fade: false, offset:5 } );
		
		$('.badge-title, .badge-bronze-medal, .badge-silver-medal, .badge-gold-medal, .badge-bronze, .badge-silver, .badge-gold').tipsy( {gravity: 's', fade: false, offset:0 } );
		
		$('.shareIT').tipsy( {gravity: 's', fade: false } );
		$('.sidebarBtn, .btngreen, .btnyellow').tipsy( {gravity: 's', offset:5 });

		$('.suggestVideoBtn, .oAQ_share').tipsy( {gravity: 's', offset:5 });

		// info on reward
		$('.rewardlist').tipsy( {gravity: 's', offset:5 });
		$('.qa-userlist-acceptrate').tipsy( {gravity: 's', offset:5 });
		$('.qa-userlist-upvotes').tipsy( {gravity: 's', offset:5 });
		
		// live-box widget
		$('.liveBox-events a').tipsy( {gravity: 'e', fade: true, offset:5 });
	}

	// parse soundcloud links in questions
	if ($('.qa-template-question').length > 0){		
		$('.qa-q-view-content a[href*="soundcloud.com"], .qa-a-item-content a[href*="soundcloud.com"], .qa-c-item-content a[href*="soundcloud.com"]').each(function(){
			var $link = $(this);
			$.getJSON('http://soundcloud.com/oembed?format=js&url=' + $link.attr('href') + '&iframe=true&callback=?', function(response){
			$link.replaceWith(response.html);
			});
		});
	}

	// if(!ismobile && $('.usergrading-knob').length>0) {
	if($('.usergrading-knob').length>0) {
		// rgb color changes (green, yellow, red)
		var knobcolor = getColorForPercentage( (Number)($('.usergrading-knob').val())/100 );
		// user grade in widget
		$('.usergrading-knob').knob({
			// 'readOnly ': true,
			'width': 100,
			'height': 100,
			'thickness': 0.3,
			'fgColor': knobcolor,
			'format' : function (value) { return value + ' %'; }
		});
	}
	
	// always add "richtig?" below Haken, but not if answer has been selected already
	if( $('.qa-a-selected-text').length == 0) {
		$('.qa-a-selection').append('<span class="selection_sublab">richtig?</span>');		
	}
	
}); // END READY


var percentColors = [
    { pct: 0.0, color: { r: 0xff, g: 0x00, b: 0 } },
    // { pct: 0.5, color: { r: 0xff, g: 0xff, b: 0 } },
    { pct: 0.5, color: { r: 0xcc, g: 0xbb, b: 0 } },
    // { pct: 1.0, color: { r: 0x00, g: 0xff, b: 0 } } ];
    { pct: 1.0, color: { r: 0x00, g: 0xaa, b: 0 } } ];

var getColorForPercentage = function(pct) {
    for (var i = 1; i < percentColors.length - 1; i++) {
        if (pct < percentColors[i].pct) {
            break;
        }
    }
    var lower = percentColors[i - 1];
    var upper = percentColors[i];
    var range = upper.pct - lower.pct;
    var rangePct = (pct - lower.pct) / range;
    var pctLower = 1 - rangePct;
    var pctUpper = rangePct;
    var color = {
        r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
        g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
        b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
    };
    return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
    // or output as hex if preferred
}  

/* URL parameter reader */
function getURLparameter( name ) {
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var results = regex.exec( window.location.href );
	if( results == null )
		return null;
	else
		return results[1];
}

/* bitly shortener */
function get_short_url(long_url, func) {
    $.getJSON(
        "http://api.bitly.com/v3/shorten?callback=?", 
        { 
            "format": "json",
            "apiKey": 'R_585094f3af186d9839cdef770864462e',
            "login": 'gutemathefragen',
            "longUrl": long_url
        },
        function(response) {
            func(response.data.url);
        }
    );
}

/* COOKIE for teaser-dialog-Box */
function setCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}




/**** SCRIPTS ****/

// tipsy tooltips for jquery, version 1.0.0a, (c) 2008-2010 jason frame [jason@onehackoranother.com], released under the MIT license
(function($){function maybeCall(thing,ctx){return typeof thing=="function"?thing.call(ctx):thing}function Tipsy(element,options){this.$element=$(element);this.options=options;this.enabled=true;this.fixTitle()}Tipsy.prototype={show:function(){var title=this.getTitle();if(title&&this.enabled){var $tip=this.tip();$tip.find(".tipsy-inner")[this.options.html?"html":"text"](title);$tip[0].className="tipsy";$tip.remove().css({top:0,left:0,visibility:"hidden",display:"block"}).prependTo(document.body);var pos=
$.extend({},this.$element.offset(),{width:this.$element[0].offsetWidth,height:this.$element[0].offsetHeight});var actualWidth=$tip[0].offsetWidth,actualHeight=$tip[0].offsetHeight,gravity=maybeCall(this.options.gravity,this.$element[0]);var tp;switch(gravity.charAt(0)){case "n":tp={top:pos.top+pos.height+this.options.offset,left:pos.left+pos.width/2-actualWidth/2};break;case "s":tp={top:pos.top-actualHeight-this.options.offset,left:pos.left+pos.width/2-actualWidth/2};break;case "e":tp={top:pos.top+
pos.height/2-actualHeight/2,left:pos.left-actualWidth-this.options.offset};break;case "w":tp={top:pos.top+pos.height/2-actualHeight/2,left:pos.left+pos.width+this.options.offset};break}if(gravity.length==2)if(gravity.charAt(1)=="w")tp.left=pos.left+pos.width/2-15;else tp.left=pos.left+pos.width/2-actualWidth+15;$tip.css(tp).addClass("tipsy-"+gravity);$tip.find(".tipsy-arrow")[0].className="tipsy-arrow tipsy-arrow-"+gravity.charAt(0);if(this.options.className)$tip.addClass(maybeCall(this.options.className,
this.$element[0]));if(this.options.fade)$tip.stop().css({opacity:0,display:"block",visibility:"visible"}).animate({opacity:this.options.opacity});else $tip.css({visibility:"visible",opacity:this.options.opacity})}},hide:function(){if(this.options.fade)this.tip().stop().fadeOut(function(){$(this).remove()});else this.tip().remove()},fixTitle:function(){var $e=this.$element;if($e.attr("title")||typeof $e.attr("original-title")!="string")$e.attr("original-title",$e.attr("title")||"").removeAttr("title")},
getTitle:function(){var title,$e=this.$element,o=this.options;this.fixTitle();var title,o=this.options;if(typeof o.title=="string")title=$e.attr(o.title=="title"?"original-title":o.title);else if(typeof o.title=="function")title=o.title.call($e[0]);title=(""+title).replace(/(^\s*|\s*$)/,"");return title||o.fallback},tip:function(){if(!this.$tip)this.$tip=$('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"></div>');return this.$tip},validate:function(){if(!this.$element[0].parentNode){this.hide();
this.$element=null;this.options=null}},enable:function(){this.enabled=true},disable:function(){this.enabled=false},toggleEnabled:function(){this.enabled=!this.enabled}};$.fn.tipsy=function(options){if(options===true)return this.data("tipsy");else if(typeof options=="string"){var tipsy=this.data("tipsy");if(tipsy)tipsy[options]();return this}options=$.extend({},$.fn.tipsy.defaults,options);function get(ele){var tipsy=$.data(ele,"tipsy");if(!tipsy){tipsy=new Tipsy(ele,$.fn.tipsy.elementOptions(ele,
options));$.data(ele,"tipsy",tipsy)}return tipsy}function enter(){var tipsy=get(this);tipsy.hoverState="in";if(options.delayIn==0)tipsy.show();else{tipsy.fixTitle();setTimeout(function(){if(tipsy.hoverState=="in")tipsy.show()},options.delayIn)}}function leave(){var tipsy=get(this);tipsy.hoverState="out";if(options.delayOut==0)tipsy.hide();else setTimeout(function(){if(tipsy.hoverState=="out")tipsy.hide()},options.delayOut)}if(!options.live)this.each(function(){get(this)});if(options.trigger!="manual"){var binder=
options.live?"live":"bind",eventIn=options.trigger=="hover"?"mouseenter":"focus",eventOut=options.trigger=="hover"?"mouseleave":"blur";this[binder](eventIn,enter)[binder](eventOut,leave)}return this};$.fn.tipsy.defaults={className:null,delayIn:0,delayOut:0,fade:false,fallback:"",gravity:"n",html:false,live:false,offset:0,opacity:0.8,title:"title",trigger:"hover"};$.fn.tipsy.elementOptions=function(ele,options){return $.metadata?$.extend({},options,$(ele).metadata()):options};$.fn.tipsy.autoNS=function(){return $(this).offset().top>
$(document).scrollTop()+$(window).height()/2?"s":"n"};$.fn.tipsy.autoWE=function(){return $(this).offset().left>$(document).scrollLeft()+$(window).width()/2?"e":"w"};$.fn.tipsy.autoBounds=function(margin,prefer){return function(){var dir={ns:prefer[0],ew:prefer.length>1?prefer[1]:false},boundTop=$(document).scrollTop()+margin,boundLeft=$(document).scrollLeft()+margin,$this=$(this);if($this.offset().top<boundTop)dir.ns="n";if($this.offset().left<boundLeft)dir.ew="w";if($(window).width()+$(document).scrollLeft()-
$this.offset().left<margin)dir.ew="e";if($(window).height()+$(document).scrollTop()-$this.offset().top<margin)dir.ns="s";return dir.ns+(dir.ew?dir.ew:"")}}})(jQuery);


/*!jQuery Knob*/
/**
 * Downward compatible, touchable dial
 *
 * Version: 1.2.11
 * Requires: jQuery v1.7+
 *
 * Copyright (c) 2012 Anthony Terrien
 * Under MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * Thanks to vor, eskimoblood, spiffistan, FabrizioC
 */
!function(t){"object"==typeof exports?module.exports=t(require("jquery")):"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)}(function(t){"use strict";var i={},s=Math.max,h=Math.min;i.c={},i.c.d=t(document),i.c.t=function(t){return t.originalEvent.touches.length-1},i.o=function(){var s=this;this.o=null,this.$=null,this.i=null,this.g=null,this.v=null,this.cv=null,this.x=0,this.y=0,this.w=0,this.h=0,this.$c=null,this.c=null,this.t=0,this.isInit=!1,this.fgColor=null,this.pColor=null,this.dH=null,this.cH=null,this.eH=null,this.rH=null,this.scale=1,this.relative=!1,this.relativeWidth=!1,this.relativeHeight=!1,this.$div=null,this.run=function(){var i=function(t,i){var h;for(h in i)s.o[h]=i[h];s._carve().init(),s._configure()._draw()};if(!this.$.data("kontroled")){if(this.$.data("kontroled",!0),this.extend(),this.o=t.extend({min:void 0!==this.$.data("min")?this.$.data("min"):0,max:void 0!==this.$.data("max")?this.$.data("max"):100,stopper:!0,readOnly:this.$.data("readonly")||"readonly"===this.$.attr("readonly"),cursor:this.$.data("cursor")===!0&&30||this.$.data("cursor")||0,thickness:this.$.data("thickness")&&Math.max(Math.min(this.$.data("thickness"),1),.01)||.35,lineCap:this.$.data("linecap")||"butt",width:this.$.data("width")||200,height:this.$.data("height")||200,displayInput:null==this.$.data("displayinput")||this.$.data("displayinput"),displayPrevious:this.$.data("displayprevious"),fgColor:this.$.data("fgcolor")||"#87CEEB",inputColor:this.$.data("inputcolor"),font:this.$.data("font")||"Arial",fontWeight:this.$.data("font-weight")||"bold",inline:!1,step:this.$.data("step")||1,rotation:this.$.data("rotation"),draw:null,change:null,cancel:null,release:null,format:function(t){return t},parse:function(t){return parseFloat(t)}},this.o),this.o.flip="anticlockwise"===this.o.rotation||"acw"===this.o.rotation,this.o.inputColor||(this.o.inputColor=this.o.fgColor),this.$.is("fieldset")?(this.v={},this.i=this.$.find("input"),this.i.each(function(i){var h=t(this);s.i[i]=h,s.v[i]=s.o.parse(h.val()),h.bind("change blur",function(){var t={};t[i]=h.val(),s.val(s._validate(t))})}),this.$.find("legend").remove()):(this.i=this.$,this.v=this.o.parse(this.$.val()),""===this.v&&(this.v=this.o.min),this.$.bind("change blur",function(){s.val(s._validate(s.o.parse(s.$.val())))})),!this.o.displayInput&&this.$.hide(),this.$c=t(document.createElement("canvas")).attr({width:this.o.width,height:this.o.height}),this.$div=t('<div style="'+(this.o.inline?"display:inline;":"")+"width:"+this.o.width+"px;height:"+this.o.height+'px;"></div>'),this.$.wrap(this.$div).before(this.$c),this.$div=this.$.parent(),"undefined"!=typeof G_vmlCanvasManager&&G_vmlCanvasManager.initElement(this.$c[0]),this.c=this.$c[0].getContext?this.$c[0].getContext("2d"):null,!this.c)throw{name:"CanvasNotSupportedException",message:"Canvas not supported. Please use excanvas on IE8.0.",toString:function(){return this.name+": "+this.message}};return this.scale=(window.devicePixelRatio||1)/(this.c.webkitBackingStorePixelRatio||this.c.mozBackingStorePixelRatio||this.c.msBackingStorePixelRatio||this.c.oBackingStorePixelRatio||this.c.backingStorePixelRatio||1),this.relativeWidth=this.o.width%1!==0&&this.o.width.indexOf("%"),this.relativeHeight=this.o.height%1!==0&&this.o.height.indexOf("%"),this.relative=this.relativeWidth||this.relativeHeight,this._carve(),this.v instanceof Object?(this.cv={},this.copy(this.v,this.cv)):this.cv=this.v,this.$.bind("configure",i).parent().bind("configure",i),this._listen()._configure()._xy().init(),this.isInit=!0,this.$.val(this.o.format(this.v)),this._draw(),this}},this._carve=function(){if(this.relative){var t=this.relativeWidth?this.$div.parent().width()*parseInt(this.o.width)/100:this.$div.parent().width(),i=this.relativeHeight?this.$div.parent().height()*parseInt(this.o.height)/100:this.$div.parent().height();this.w=this.h=Math.min(t,i)}else this.w=this.o.width,this.h=this.o.height;return this.$div.css({width:this.w+"px",height:this.h+"px"}),this.$c.attr({width:this.w,height:this.h}),1!==this.scale&&(this.$c[0].width=this.$c[0].width*this.scale,this.$c[0].height=this.$c[0].height*this.scale,this.$c.width(this.w),this.$c.height(this.h)),this},this._draw=function(){var t=!0;s.g=s.c,s.clear(),s.dH&&(t=s.dH()),t!==!1&&s.draw()},this._touch=function(t){var h=function(t){var i=s.xy2val(t.originalEvent.touches[s.t].pageX,t.originalEvent.touches[s.t].pageY);i!=s.cv&&(s.cH&&s.cH(i)===!1||(s.change(s._validate(i)),s._draw()))};return this.t=i.c.t(t),h(t),i.c.d.bind("touchmove.k",h).bind("touchend.k",function(){i.c.d.unbind("touchmove.k touchend.k"),s.val(s.cv)}),this},this._mouse=function(t){var h=function(t){var i=s.xy2val(t.pageX,t.pageY);i!=s.cv&&(s.cH&&s.cH(i)===!1||(s.change(s._validate(i)),s._draw()))};return h(t),i.c.d.bind("mousemove.k",h).bind("keyup.k",function(t){if(27===t.keyCode){if(i.c.d.unbind("mouseup.k mousemove.k keyup.k"),s.eH&&s.eH()===!1)return;s.cancel()}}).bind("mouseup.k",function(){i.c.d.unbind("mousemove.k mouseup.k keyup.k"),s.val(s.cv)}),this},this._xy=function(){var t=this.$c.offset();return this.x=t.left,this.y=t.top,this},this._listen=function(){return this.o.readOnly?this.$.attr("readonly","readonly"):(this.$c.bind("mousedown",function(t){t.preventDefault(),s._xy()._mouse(t)}).bind("touchstart",function(t){t.preventDefault(),s._xy()._touch(t)}),this.listen()),this.relative&&t(window).resize(function(){s._carve().init(),s._draw()}),this},this._configure=function(){return this.o.draw&&(this.dH=this.o.draw),this.o.change&&(this.cH=this.o.change),this.o.cancel&&(this.eH=this.o.cancel),this.o.release&&(this.rH=this.o.release),this.o.displayPrevious?(this.pColor=this.h2rgba(this.o.fgColor,"0.4"),this.fgColor=this.h2rgba(this.o.fgColor,"0.6")):this.fgColor=this.o.fgColor,this},this._clear=function(){this.$c[0].width=this.$c[0].width},this._validate=function(t){var i=~~((0>t?-.5:.5)+t/this.o.step)*this.o.step;return Math.round(100*i)/100},this.listen=function(){},this.extend=function(){},this.init=function(){},this.change=function(){},this.val=function(){},this.xy2val=function(){},this.draw=function(){},this.clear=function(){this._clear()},this.h2rgba=function(t,i){var s;return t=t.substring(1,7),s=[parseInt(t.substring(0,2),16),parseInt(t.substring(2,4),16),parseInt(t.substring(4,6),16)],"rgba("+s[0]+","+s[1]+","+s[2]+","+i+")"},this.copy=function(t,i){for(var s in t)i[s]=t[s]}},i.Dial=function(){i.o.call(this),this.startAngle=null,this.xy=null,this.radius=null,this.lineWidth=null,this.cursorExt=null,this.w2=null,this.PI2=2*Math.PI,this.extend=function(){this.o=t.extend({bgColor:this.$.data("bgcolor")||"#EEEEEE",angleOffset:this.$.data("angleoffset")||0,angleArc:this.$.data("anglearc")||360,inline:!0},this.o)},this.val=function(t,i){return null==t?this.v:(t=this.o.parse(t),void(i!==!1&&t!=this.v&&this.rH&&this.rH(t)===!1||(this.cv=this.o.stopper?s(h(t,this.o.max),this.o.min):t,this.v=this.cv,this.$.val(this.o.format(this.v)),this._draw())))},this.xy2val=function(t,i){var e,n;return e=Math.atan2(t-(this.x+this.w2),-(i-this.y-this.w2))-this.angleOffset,this.o.flip&&(e=this.angleArc-e-this.PI2),this.angleArc!=this.PI2&&0>e&&e>-.5?e=0:0>e&&(e+=this.PI2),n=e*(this.o.max-this.o.min)/this.angleArc+this.o.min,this.o.stopper&&(n=s(h(n,this.o.max),this.o.min)),n},this.listen=function(){var i,e,n,a,o=this,r=function(t){t.preventDefault();var n=t.originalEvent,a=n.detail||n.wheelDeltaX,r=n.detail||n.wheelDeltaY,l=o._validate(o.o.parse(o.$.val()))+(a>0||r>0?o.o.step:0>a||0>r?-o.o.step:0);l=s(h(l,o.o.max),o.o.min),o.val(l,!1),o.rH&&(clearTimeout(i),i=setTimeout(function(){o.rH(l),i=null},100),e||(e=setTimeout(function(){i&&o.rH(l),e=null},200)))},l=1,c={37:-o.o.step,38:o.o.step,39:o.o.step,40:-o.o.step};this.$.bind("keydown",function(i){var e=i.keyCode;if(e>=96&&105>=e&&(e=i.keyCode=e-48),n=parseInt(String.fromCharCode(e)),isNaN(n)&&(13!==e&&8!==e&&9!==e&&189!==e&&(190!==e||o.$.val().match(/\./))&&i.preventDefault(),t.inArray(e,[37,38,39,40])>-1)){i.preventDefault();var r=o.o.parse(o.$.val())+c[e]*l;o.o.stopper&&(r=s(h(r,o.o.max),o.o.min)),o.change(o._validate(r)),o._draw(),a=window.setTimeout(function(){l*=2},30)}}).bind("keyup",function(){isNaN(n)?a&&(window.clearTimeout(a),a=null,l=1,o.val(o.$.val())):o.$.val()>o.o.max&&o.$.val(o.o.max)||o.$.val()<o.o.min&&o.$.val(o.o.min)}),this.$c.bind("mousewheel DOMMouseScroll",r),this.$.bind("mousewheel DOMMouseScroll",r)},this.init=function(){(this.v<this.o.min||this.v>this.o.max)&&(this.v=this.o.min),this.$.val(this.v),this.w2=this.w/2,this.cursorExt=this.o.cursor/100,this.xy=this.w2*this.scale,this.lineWidth=this.xy*this.o.thickness,this.lineCap=this.o.lineCap,this.radius=this.xy-this.lineWidth/2,this.o.angleOffset&&(this.o.angleOffset=isNaN(this.o.angleOffset)?0:this.o.angleOffset),this.o.angleArc&&(this.o.angleArc=isNaN(this.o.angleArc)?this.PI2:this.o.angleArc),this.angleOffset=this.o.angleOffset*Math.PI/180,this.angleArc=this.o.angleArc*Math.PI/180,this.startAngle=1.5*Math.PI+this.angleOffset,this.endAngle=1.5*Math.PI+this.angleOffset+this.angleArc;var t=s(String(Math.abs(this.o.max)).length,String(Math.abs(this.o.min)).length,2)+2;this.o.displayInput&&this.i.css({width:(this.w/2+4>>0)+"px",height:(this.w/3>>0)+"px",position:"absolute","vertical-align":"middle","margin-top":(this.w/3>>0)+"px","margin-left":"-"+(3*this.w/4+2>>0)+"px",border:0,background:"none",font:this.o.fontWeight+" "+(this.w/t>>0)+"px "+this.o.font,"text-align":"center",color:this.o.inputColor||this.o.fgColor,padding:"0px","-webkit-appearance":"none"})||this.i.css({width:"0px",visibility:"hidden"})},this.change=function(t){this.cv=t,this.$.val(this.o.format(t))},this.angle=function(t){return(t-this.o.min)*this.angleArc/(this.o.max-this.o.min)},this.arc=function(t){var i,s;return t=this.angle(t),this.o.flip?(i=this.endAngle+1e-5,s=i-t-1e-5):(i=this.startAngle-1e-5,s=i+t+1e-5),this.o.cursor&&(i=s-this.cursorExt)&&(s+=this.cursorExt),{s:i,e:s,d:this.o.flip&&!this.o.cursor}},this.draw=function(){var t,i=this.g,s=this.arc(this.cv),h=1;i.lineWidth=this.lineWidth,i.lineCap=this.lineCap,"none"!==this.o.bgColor&&(i.beginPath(),i.strokeStyle=this.o.bgColor,i.arc(this.xy,this.xy,this.radius,this.endAngle-1e-5,this.startAngle+1e-5,!0),i.stroke()),this.o.displayPrevious&&(t=this.arc(this.v),i.beginPath(),i.strokeStyle=this.pColor,i.arc(this.xy,this.xy,this.radius,t.s,t.e,t.d),i.stroke(),h=this.cv==this.v),i.beginPath(),i.strokeStyle=h?this.o.fgColor:this.fgColor,i.arc(this.xy,this.xy,this.radius,s.s,s.e,s.d),i.stroke()},this.cancel=function(){this.val(this.v)}},t.fn.dial=t.fn.knob=function(s){return this.each(function(){var h=new i.Dial;h.o=s,h.$=t(this),h.run()}).parent()}});