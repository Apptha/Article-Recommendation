/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//avoid conflict with other script
$j=jQuery.noConflict();

$j(document).ready(function($) {

	//this is the floating content
	var $floatingbox = $('#floating-box');

        if($("#floatboxDirection").val() == "" || $("#floatboxDirection").val() =="0" || $("#floatboxDirection").val() =="2" ){
            $floatingbox.addClass("floatstyleright");
        }
        else if($("#floatboxDirection").val() =="1"){
            $floatingbox.addClass("floatstyleleft");
        }

        if($("#floatboxDirection").val() =="2"){
            $floatingbox.addClass("floatstyletop");
        }

        

	if($('#appthacontentWrap'+$("#articleCount").val() ).length > 0){
		//var bodyY =  parseInt($('#appthacontentWrap'+$("#articleCount").val()).offset().top) + parseInt($('#appthacontentWrap'+$("#articleCount").val()).scrollHeight)  ;
               
    		//var originalX = $floatingbox.css('margin-left');
                var elem =  $('#appthacontentWrap'+$("#articleCount").val());

                var divend;
                if( $.browser.webkit ){
                    divend = elem[0].clientHeight;

                }
                else{
                    divend = elem[0].scrollHeight + elem.offset().top;
                }
		$(window).scroll(function () {
			//var scrollY = $(window).scrollTop();
                        
                        var scrollY = $(this).scrollTop() + $(this).height() /2  ;

                        if( $.browser.msie){
                            scrollY +=200;
                        }
                        
                        //alert(scrollY)
                        
			var isfixed = $floatingbox.css('position') == 'fixed';
			if($floatingbox.length > 0){
				if ( scrollY >= divend && !isfixed ) {
                                    $floatingbox.removeClass("floatboxrelative");
                                    $floatingbox.addClass("floatboxfixed");
                                    if($("#floatboxDirection").val() == "" || $("#floatboxDirection").val() =="0" || $("#floatboxDirection").val() =="2" ){
                                        $floatingbox.animate({right:"0"},50,function(){});
                                    }
                                    else if($("#floatboxDirection").val() =="1"){
                                        $floatingbox.animate({left:"0"},50,function(){});
                                    }
                                    
				} else if ( scrollY < divend && isfixed ) {
                                    if($("#floatboxDirection").val() == "" || $("#floatboxDirection").val() =="0" || $("#floatboxDirection").val() =="2" ){
                                        $floatingbox.animate({right:"-263px"},50,function(){
                                            $floatingbox.removeClass("floatboxfixed");
                                            $floatingbox.addClass("floatboxrelative");
                                        });
                                    }
                                    if($("#floatboxDirection").val() =="1"){
                                        $floatingbox.animate({left:"-263px"},50,function(){
                                            $floatingbox.removeClass("floatboxfixed");
                                            $floatingbox.addClass("floatboxrelative");
                                        });
                                    }
                                        
				}
			}
		});
	}

});

function stopPopup(){
    var $floatingbox = jQuery('#floating-box');
    $floatingbox.remove();
}
function readMore(){
    window.location.href = document.getElementById("articleId").href;
}
