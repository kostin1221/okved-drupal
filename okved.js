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
	
	old_cookie = getCookie("ckecked_okveds");
	arr = old_cookie.split(",");
	$("#okveds_list .okved_check").each(
		function() {
			if(arr.indexOf($(this).val()) > -1){
				$(this).attr("checked", "checked");
			};
		});
	
	$("#okveds_list .okved_check").change(function() {
		old_cookie = getCookie("ckecked_okveds");
		arr = old_cookie.split(",");
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

		document.cookie = "ckecked_okveds=" + new_cookie;
	});
    $("#okveds_list .block").hover(function(){
		$(this).find("p").slideToggle("fast");
     }, function() {
		$(this).find("p").slideToggle("fast");
	 });});
