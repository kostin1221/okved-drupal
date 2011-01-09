function printpage(){

content='<table border=\"1\" cellspacing=\"1\">';

content+='<thead class=\"tableHeader-processed\"><tr><th>Номер</th><th>Наименование</tr></thead>';
content+='<tbody>';

oldtable=$('#okveds_list').html();

$(oldtable).find('tr').each(function(n, elem) {
	if ($(elem).find('td').eq(1).html() != null){
		content+='<tr>';
		content+='<td>' + $(elem).find('td').eq(1).html() + '</td>';
		info=$(elem).find('td').eq(2).html();
				
		info = info.replace(new RegExp(/<img.*?>/),'');
		info = info.replace(new RegExp(/<p>.*?<\/p>/),'');
		
		content+='<td>' + info + '</td>';
	};
});

content+='</table>';
w=window.open('about:blank');
w.document.open();
w.document.write( content );
w.document.close();

return false;
}
	
$(document).ready(function(){

	function getCookie(name) {
		var cookie = " " + document.cookie;
		var search = " " + name + "=";
		var setStr = null;
		var offset = 0;
		var end = 0;
		if (cookie.length > 0) {
			offset = cookie.indexOf(search);
			if (offset != -1) {
				offset += search.length;
				end = cookie.indexOf(";", offset)
				if (end == -1) {
					end = cookie.length;
				}
				setStr = unescape(cookie.substring(offset, end));
			}
		}
		return(setStr);
	};
	
	function get_okved_version() {
	  var vers = getCookie("okved_version");
	  if (vers == null) return '1';
	  return vers;
	};
	
	old_cookie = getCookie("checked_okveds_" + get_okved_version());
	if(old_cookie) {
	  arr = old_cookie.split(",");
	} else { arr = new Array(); }
	$("#okveds_list .okved_check").each(
		function() {
			if(arr.indexOf($(this).val()) > -1){
				$(this).attr("checked", "checked");
			};
		});
	
	function change_checked(val, status) {
	  	cookie_name = "checked_okveds_" + get_okved_version();
		old_cookie = getCookie( cookie_name );
		if(old_cookie) {
		  arr = old_cookie.split(",");
		} else { arr = new Array(); }
		if (status == true){
			if(arr.indexOf(val) == -1){
				arr.push(val);
			};
		} else {
			if(arr.indexOf(val) > -1){
				arr.splice( arr.indexOf(val) ,1);
			};
		};
		var new_cookie = arr.join();

		document.cookie = cookie_name + "=" + new_cookie;
	};
	
	$("#okveds_list .okved_check").change(function() {

		change_checked($(this).val(), $(this).attr("checked"));
		
	});
	  var checkInFocus = false;
	  $("#okveds_list .okved_check").focus(function() {
	    checkInFocus = true;
	  });

	  $("#okveds_list .okved_check").blur(function() {
	    checkInFocus = false;
	  });

	$("#okveds_list .okved_allcheck").change(function() {
	    var status = $(this).attr("checked");
	     $("#okveds_list .okved_check").each(function()
	      {
		change_checked($(this).val(), status);
		this.checked = status;
	      });		
		
	});
    $("#okveds_list .block").click(function(){
		if (checkInFocus == true) return true;
		$(this).find("p").slideToggle("fast");
		var img_src = $(this).find("img").attr("src");
		if ($(this).hasClass("active")) {
		  var new_src = img_src.replace('down_arrow.jpg','up_arrow.jpg');
		} else {
		  var new_src = img_src.replace('up_arrow.jpg','down_arrow.jpg');
		}
		$(this).find("img").attr("src", new_src);
		$(this).toggleClass("active");
     });});
