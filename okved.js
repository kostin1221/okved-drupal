function printpage(){

$.fn.removeCol = function(col){
    // Make sure col has value
    if(!col){ col = 1; }
    $('tr td:nth-child('+col+'), tr th:nth-child('+col+')', this).remove();
    return this;
};

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
	
	old_cookie = getCookie("checked_okveds");
	if(old_cookie) {
	  arr = old_cookie.split(",");
	} else { arr = new Array(); }
	$("#okveds_list .okved_check").each(
		function() {
			if(arr.indexOf($(this).val()) > -1){
				$(this).attr("checked", "checked");
			};
		});
	
	$("#okveds_list .okved_check").change(function() {
		old_cookie = getCookie("checked_okveds");
		if(old_cookie) {
		  arr = old_cookie.split(",");
		} else { arr = new Array(); }
		if ($(this).attr("checked") == true){
			if(arr.indexOf($(this).val()) == -1){
				arr.push($(this).val());
			};
		} else {
			if(arr.indexOf($(this).val()) > -1){
				arr.splice( arr.indexOf($(this).val()) ,1);
			};
		};
		var new_cookie = arr.join();

		document.cookie = "checked_okveds=" + new_cookie;
	});
    $("#okveds_list .block").hover(function(){
		$(this).find("p").slideToggle("fast");
		var img_src = $(this).find("img").attr("src");
		var new_src = img_src.replace('up_arrow.jpg','down_arrow.jpg');
		$(this).find("img").attr("src", new_src);
		$(this).toggleClass("active");
     }, function() {
		$(this).find("p").slideToggle("fast");
                var img_src = $(this).find("img").attr("src");
                var new_src = img_src.replace('down_arrow.jpg','up_arrow.jpg');
                $(this).find("img").attr("src", new_src);
		$(this).removeClass("active");
	 });});
