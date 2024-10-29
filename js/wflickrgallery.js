var wfg = new Array;

function oWFG(userid,feed,nbphotos,dsptype,nbpicsline) {
	this.userid = userid;
	this.feed = feed;
	this.nbphotos = nbphotos;
	this.dsptype = dsptype;
	this.nbpicsline = nbpicsline;
}

$(document).ready(function() {
	
	$.each(wfg, function(i,widget){
		if( typeof(widget)!='undefined' ) {
			
			if( widget.userid == null ) return;
			if( widget.nbphotos == null ) widget.nbphotos = 6;
			if( widget.nbpicsline == null || widget.nbpicsline < 2) widget.nbpicsline = 2;
			if( widget.dsptype == null ) widget.dsptype = 1;
			
			var feedJson = "http://api.flickr.com/services/feeds/photos_faves.gne?nsid=" + widget.userid + "&lang=fr-fr&format=json&jsoncallback=?"
			if( widget.feed == "gal" ) {
					feedJson = "http://api.flickr.com/services/feeds/photos_public.gne?id=" + widget.userid + "&lang=fr-fr&format=json&jsoncallback=?";
			}
				
			if( widget.feed == "fav" ) {
					feedJson = "http://api.flickr.com/services/feeds/photos_faves.gne?nsid=" + widget.userid + "&lang=fr-fr&format=json&jsoncallback=?";
			}
			
			$.getJSON(feedJson, function(data){
				
				var vWidth = $("#wflickrgallery-"+i).width();
				if( vWidth > 240 ) {
					$("#wflickrgallery-"+i).css("width","240px");
					vWidth = 240;
				}

				$.each(data.items, function(j,item){
					if(j < widget.nbphotos) { 
						$("<img/>").attr("src", item.media.m)
						.attr("alt",item.title)
						.attr("width",vWidth)
						.attr("class","wfg_pic1")
						.appendTo("#wflickrgallery-"+i)
						.wrap("<div class=\"imgholder cWfg-"+i+"\"><a href=\"" + item.link + "\" title=\"" + item.title + "\" target=\"_blank\"></a></div>");
					} else {
						return false;
					}
				});
					
				if( widget.dsptype == 1 ) {
					// Set up the image container divs to collapse
					$(".cWfg-"+i).not(":first").css("height","25px");
					// Add the container mouseover event function
					$(".cWfg-"+i).mouseover(function(){
						// Make every image other than the one being mousedover small
						$(".cWfg-"+i).stop().not(this).animate({height:"25px"});
						// Make the currently active image big (set width to image width)
						$(this).animate({height:$(this).find("img").height()+"px"});
					});
				}
				
				if( widget.dsptype == 2 ) {
					var picWidth = (vWidth / widget.nbpicsline) - 4;
					
					$(".cWfg-"+i).css({'border':'1px solid gray'});
					$.each($(".cWfg-"+i+" a img.wfg_pic1"), function(k,img){
						var wfg_pic = img;
						$(wfg_pic).css({'position':'relative','z-index':'8'})
							.attr("src",wfg_pic.src.replace("_m.jpg","_s.jpg"))
							.after('<img class="'+wfg_pic.className+'">')
							.attr("width",picWidth);
						
						wfg_pic.z = wfg_pic.nextSibling;
						$(wfg_pic.z)
							.removeClass("wfg_pic1")
							.addClass("wfg_pic2")
							.attr("width",vWidth)
							.attr("src",wfg_pic.src.replace("_s.jpg","_m.jpg"))
							.css({'position':'absolute','z-index':'10','border':'1px solid black'})
							.hide();
    
						$(wfg_pic.z).css({left:$(wfg_pic).offsetLeft()-(wfg_pic.z.width-wfg_pic.scrollWidth)/2+'px',
						top:$(wfg_pic).offsetTop()-(wfg_pic.z.height-wfg_pic.scrollHeight)/2+'px'});
						
					});
					
					$(".cWfg-"+i).css("width",picWidth);
					$(".cWfg-"+i).css("height",picWidth);
					$(".cWfg-"+i).css("margin","1px");
					$(".cWfg-"+i).css("float","left");
					$(".imageholderend-"+i).css("clear","both");
					
					$(".cWfg-"+i).mouseover(function(){
						
						$("img.wfg_pic2").hide();
						$(this).find("img.wfg_pic2")
						.css({left:$(this).offsetLeft()-(this.width-this.scrollWidth)/2+'px',
						top:$(this).offsetTop()+'px'});
						//.fadeIn("fast");
						$(this).find("img.wfg_pic2").show();
					});
					
					$(".cWfg-"+i).mouseout(function(){
						$(this).find("img.wfg_pic2").hide();
					});
				}
				
			});
			
		}
	});
	
	$.fn.offsetLeft = function() {
		var e = this[0];
		if(!e.offsetParent) return e.offsetLeft;
		return e.offsetLeft + $(e.offsetParent).offsetLeft(); 
	}

	$.fn.offsetTop = function() {
		var e = this[0];
		if(!e.offsetParent) return e.offsetTop;
		return e.offsetTop + $(e.offsetParent).offsetTop(); 
	}


});	