$(function() {
	$(".imgs img").lazyload({
		effect: "fadeIn",
		threshold: 500
	});
	var config = ["selectcolor", "price_range", "selectshape","length","fabric","neckline","featrue"];
	$(".a>li>input").on("click", function() {
		var id = $(this).attr("id");
		if ($(this).attr("checked")) {
			var Request = new Object();
			Request = GetRequest();
			var price_range;
			price_range = Request["price_range"];
			price_range = price_range.split("-");
			console.log(price_range);
			var url = window.location.href;
			if (price_range.indexOf(id) > -1) {
				for (var i = 0; i < price_range.length; i++) {
					if (id == price_range[i]) {
						price_range.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("price_range", price_range.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var price_range;
		price_range_ = Request["price_range"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var price_range = [];
		if (price_range_ != undefined) {
			price_range = price_range_.split("-")
		}
		if (price_range.indexOf($(this).attr("id")) < 0) {
			price_range.push($(this).attr("id"))
		}
		// var searchUrl = setParam("price_range", price_range.join("-"));
		var price_rang=price_range.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		var test=window.location.href
		//console.log('test =' + test);
		window.location.href = changeUrlArg(test,'price_range',price_rang)
	});
	$(".b>li>label>input").on("click", function() {
		var id = $(this).attr("id");
		if ($(this).attr("checked")) {
			var Request = new Object();
			Request = GetRequest();
			var selectcolor;
			selectcolor = Request["selectcolor"];
			selectcolor = selectcolor.split("-");
			console.log(selectcolor);
			var url = window.location.href;
			if (selectcolor.indexOf(id) > -1) {
				for (var i = 0; i < selectcolor.length; i++) {
					if (id == selectcolor[i]) {
						selectcolor.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("selectcolor", selectcolor.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var selectcolor;
		selectcolor_ = Request["selectcolor"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var selectcolor = [];
		if (selectcolor_ != undefined) {
			selectcolor = selectcolor_.split("-")
		}
		if (selectcolor.indexOf($(this).attr("id")) < 0) {
			selectcolor.push($(this).attr("id"))
		}
		// var searchUrl = setParam("selectcolor", selectcolor.join("-"));
		var selectcolo=selectcolor.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		var test = window.location.href;
		window.location.href = changeUrlArg(test,'selectcolor',selectcolo)
	});

	$(".c>li>label>input").on("click", function() {
		var id = $(this).attr("id");
		if ($(this).attr("checked")) {
			var Request = new Object();
			Request = GetRequest();
			var selectshape;
			selectshape = Request["selectshape"];
			selectshape = selectshape.split("-");
			console.log(selectshape);
			var url = window.location.href;
			if (selectshape.indexOf(id) > -1) {
				for (var i = 0; i < selectshape.length; i++) {
					if (id == selectshape[i]) {
						selectshape.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("selectshape", selectshape.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var selectshape;
		selectshape_ = Request["selectshape"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var selectshape = [];
		if (selectshape_ != undefined) {
			selectshape = selectshape_.split("-")
		}
		if (selectshape.indexOf($(this).attr("id")) < 0) {
			selectshape.push($(this).attr("id"))
		}
		// var searchUrl = setParam("selectshape", selectshape.join("-"));
		var selectshap=selectshape.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		// window.location.href = url + searchUrl
		var test = window.location.href;
		window.location.href = changeUrlArg(test,'selectshape',selectshap)
	});
	$(".d>li>input").on("click", function() {
	
		var id = $(this).attr("id");
		
		if ($(this).attr("checked")) {
			
			var Request = new Object();
			Request = GetRequest();
			var length;
			length = Request["length"];
			length = length.split("-");
			console.log(length);
			var url = window.location.href;
			if (length.indexOf(id) > -1) {
				for (var i = 0; i < length.length; i++) {
					if (id == length[i]) {
						length.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("length", length.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var length;
		length_ = Request["length"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var length = [];
		console.log(length)
		if (length_ != undefined) {
			length = length_.split("-")
		}
		if (length.indexOf($(this).attr("id")) < 0) {
			length.push($(this).attr("id"))
		}
		// var searchUrl = setParam("price_range", price_range.join("-"));
		var leng=length.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		var test=window.location.href
		// window.location.href = url + searchUrl
		window.location.href = changeUrlArg(test,'length',leng)
	});
	$(".e>li>input").on("click", function() {
	
		var id = $(this).attr("id");
		
		if ($(this).attr("checked")) {
			
			var Request = new Object();
			Request = GetRequest();
			var fabric;
			fabric = Request["fabric"];
			fabric = fabric.split("-");
			console.log(fabric);
			var url = window.location.href;
			if (fabric.indexOf(id) > -1) {
				for (var i = 0; i < fabric.length; i++) {
					if (id == fabric[i]) {
						fabric.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("fabric", fabric.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var fabric;
		fabric_ = Request["fabric"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var fabric = [];
		console.log(fabric)
		if (fabric_ != undefined) {
			fabric = fabric_.split("-")
		}
		if (fabric.indexOf($(this).attr("id")) < 0) {
			fabric.push($(this).attr("id"))
		}
		// var searchUrl = setParam("price_range", price_range.join("-"));
		var leng=fabric.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		var test=window.location.href
		// window.location.href = url + searchUrl
		window.location.href = changeUrlArg(test,'fabric',leng)
	});
	$(".f>li>label>input").on("click", function() {
		var id = $(this).attr("id");
		if ($(this).attr("checked")) {
			var Request = new Object();
			Request = GetRequest();
			var neckline;
			neckline = Request["neckline"];
			neckline = neckline.split("-");
			console.log(neckline);
			var url = window.location.href;
			if (neckline.indexOf(id) > -1) {
				for (var i = 0; i < neckline.length; i++) {
					if (id == neckline[i]) {
						neckline.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("neckline", neckline.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var neckline;
		neckline_ = Request["neckline"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var neckline = [];
		if (neckline_ != undefined) {
			neckline = neckline_.split("-")
		}
		if (neckline.indexOf($(this).attr("id")) < 0) {
			neckline.push($(this).attr("id"))
		}
		// var searchUrl = setParam("selectshape", selectshape.join("-"));
		var selectshap=neckline.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		// window.location.href = url + searchUrl
		var test = window.location.href;
		window.location.href = changeUrlArg(test,'neckline',selectshap)
	});
	$(".g>li>input").on("click", function() {
	
		var id = $(this).attr("id");
		
		if ($(this).attr("checked")) {
			
			var Request = new Object();
			Request = GetRequest();
			var featrue;
			featrue = Request["featrue"];
			featrue = featrue.split("-");
			console.log(featrue);
			var url = window.location.href;
			if (featrue.indexOf(id) > -1) {
				for (var i = 0; i < featrue.length; i++) {
					if (id == featrue[i]) {
						featrue.splice(i, 1)
					}
				}
			}
			var searchUrl = setParam("featrue", featrue.join("-"));
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl;
			return false
		}
		var Request = new Object();
		Request = GetRequest();
		var featrue;
		featrue_ = Request["featrue"];
		if ($(this).attr("id") == "") {
			return false
		}
		var url = window.location.href;
		var featrue = [];
		console.log(featrue)
		if (featrue_ != undefined) {
			featrue = featrue_.split("-")
		}
		if (featrue.indexOf($(this).attr("id")) < 0) {
			featrue.push($(this).attr("id"))
		}
		// var searchUrl = setParam("price_range", price_range.join("-"));
		var leng=featrue.join("-")
		// if (url.indexOf("?") > 0) {
		// 	url = url.split("?")[0]
		// }
		var test=window.location.href
		// window.location.href = url + searchUrl
		window.location.href = changeUrlArg(test,'featrue',leng)
	});
	$(".c").on('mouseover','.same-two .bgc',function(){
		
		$(this).children('.tp').css("display","block")
	})
	$(".c").on('mouseout','.same-two .bgc',function(){
		
		$(this).children('.tp').css("display","none")
	})
	$(".f").on('mouseover','.same-two .bgc',function(){
		
		$(this).children('.tp').css("display","block")
	})
	$(".f").on('mouseout','.same-two .bgc',function(){
		
		$(this).children('.tp').css("display","none")
	})
	$("#b").on('mouseover','.same-one .bg',function(){
		
		$(this).children('.tp').css("display","block")
	})
	$("#b").on('mouseout','.same-one .bg',function(){
		
		$(this).children('.tp').css("display","none")
	})
	$(".collocation>ul").on("click", "i", function() {
		var id = $(this).attr("i_id");
		$("#" + id).attr("checked", false);
		var all = GetRequest();
		var select;
		for (var key in all) {
			select = all[key].split("-");
			if (select.indexOf(id) > -1) {
				for (var i = 0; i < select.length; i++) {
					if (id == select[i]) {
						select.splice(i, 1)
					}
				}
				var searchUrl = setParam(key, select.join("-"));
				var url = window.location.href;
				if (url.indexOf("?") > 0) {
					url = url.split("?")[0]
				}
				window.location.href = url + searchUrl
			}
		}
	});
	var urL = encodeURI(window.location.href);

	var all = GetRequest();
	var arr = [];
	for (var key in all) {
		if (config.indexOf(key) > -1) {
			if (all[key]) {
				arr = all[key].split("-");
				for (var i = 0; i < arr.length; i++) {
					if (arr[i]) {
						$("#" + arr[i]).attr("checked", true);
						$(".collocation").css("display", "block");
						if ($("#" + arr[i]).siblings("span").length > 0) {
							var txt = $("#" + arr[i]).siblings("span").text();
							var id = $("#" + arr[i]).attr("id");
							$(".collocation>ul").append("<li id_=" + arr[i] + "><span>" + txt + "</span><i i_id=" + arr[i] + " class='fa fa-times' aria-hidden='true'></i></li>")
						}
						if ($("#" + arr[i]).parent().parent().hasClass("same-one") == true) {
							var txt = $("#" + arr[i]).siblings("img").attr("alt");
							var id = $("#" + arr[i]).attr("id");
							$(".collocation>ul").append("<li id_=" + arr[i] + "><span>" + txt + "</span><i i_id=" + arr[i] + " class='fa fa-times' aria-hidden='true'></i></li>");
							$("#" + arr[i]).siblings("img").css("border", "1px solid #3a3939")
							$("." + arr[i]).siblings("img").css("border", "1px solid #3a3939")
						}
						if ($("#" + arr[i]).parent().parent().hasClass("same-two") == true) {
							var txt = $("#" + arr[i]).siblings("div").children("img").attr("alt");
							var id = $("#" + arr[i]).attr("id");
							$(".collocation>ul").append("<li id_=" + arr[i] + "><span>" + txt + "</span><i i_id=" + arr[i] + " class='fa fa-times' aria-hidden='true'></i></li>");
							// $("#" + arr[i]).siblings("div").css("background-color", "#fddcd1")
							$("#" + arr[i]).siblings("div").addClass("beijing");
						}
					}
				}
			}
			$(".subs>li").show()
		}
	}
	$(".clear").on("click", function() {
		var all = window.location.href;
		var url_new;
		var url;
		if (all.indexOf("?") > 0) {
			url = all.split("?")[1];
			url_new = all.split("?")[0]
		}
		url = url.split("&");
		var last = [];
		for (var i = 0; i < url.length; i++) {
			var arr = url[i].split("=");
			if (config.indexOf(arr[0]) == -1) {
				last.push(url[i])
			}
		}
		window.location.href = url_new + "?" + last.join("&")
	});

	function setParam(param, value) {
		var query = location.search.substring(1);
		var p = new RegExp("(^|)" + param + "=([^&]*)(|$)");
		if (p.test(query)) {
			console.log(query);
			var firstParam = query.split(param)[0];
			var secondParam = query.split(param)[1];
			console.log(firstParam);
			console.log(secondParam);
			if (secondParam.indexOf("&") > -1) {
				var lastPraam = secondParam.split("&");
				var lastPraam_str = "";
				for (var i = 1; i < lastPraam.length; i++) {
					lastPraam_str += "&" + lastPraam[i]
				}
				if (firstParam) {
					return "?" + firstParam + param + "=" + value + lastPraam_str
				} else {
					return "?" + param + "=" + value + lastPraam_str
				}
			} else {
				if (firstParam) {
					return "?" + firstParam + param + "=" + value
				} else {
					return "?" + param + "=" + value
				}
			}
		} else {
			if (query == "") {
				return "?" + param + "=" + value
			} else {
				return "?" + query + "&" + param + "=" + value
			}
		}
	}
	// 切换url后面参数
	function changeUrlArg(url, arg, val){
		//console.log('url = '+ url);
		if (url.indexOf("page=") != -1) {
			var uurl = url.split("?");
			url1 = uurl[0]
			url2 = uurl[1];
			var tempurl = '';
			if(url2){
				url2 = url2.split('&');
				if(url2)
				for (var key in url2) {
					if(url2[key])
					if(url2[key].indexOf("page=") != -1){
					}else{
						tempurl += url2[key]+'&';
					}
				}
				if(tempurl)
				url = url1 +'?'+ tempurl;
			}
				 
		}
		var pattern = arg+'=([^&]*)';
		var replaceText = arg+'='+val;
		return url.match(pattern) ? url.replace(eval('/('+ arg+'=)([^&]*)/gi'), replaceText) : (url.match('[\?]') ? url+'&'+replaceText : url+'?'+replaceText);
	}
	function GetRequest() {
		var url = location.search;
		var theRequest = new Object();
		if (url.indexOf("?") != -1) {
			var str = url.substr(1);
			var strs = str.split("&");
			for (var i = 0; i < strs.length; i++) {
				theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1])
			}
		}
		return theRequest
	}
	$(".load-center a").click(function() {
		var url = window.location.href;
		var searchUrl = setParam("page", parseInt($(this).attr("data-page")) + 1);
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});
	$(".cf-select-style").change(function() {
		var url = window.location.href;
		var searchUrl = setParam("order", $(this).val());
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});
	$(".cf-select-style-new").change(function() {
		var url = window.location.href;
 		 
		var searchUrl = setParam("order", $(this).val());
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		url = url + searchUrl;
		if (url.indexOf("page=") != -1) {
			var uurl = url.split("?");
			url1 = uurl[0]
			url2 = uurl[1];
			var tempurl = '';
			if(url2){
				url2 = url2.split('&');
				if(url2)
				for (var key in url2) {
					if(url2[key])
					if(url2[key].indexOf("page=") != -1){
					}else{
						tempurl += url2[key]+'&';
					}
				}
				if(tempurl)
				url = url1 +'?'+ tempurl;
			}
				 
		} 
		window.location.href = url;
	});
	$("#selectsize").click(function() {
		var url = window.location.href;
		var selectsize = [];
		$('input[name="selectsize[]"]:checked').each(function() {
			selectsize.push($(this).val())
		});
		var searchUrl = setParam("selectsize", selectsize.join("-"));
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});
	$("#selectcolor").click(function() {
		var url = window.location.href;
		var selectcolor = [];
		$('input[name="selectcolor[]"]:checked').each(function() {
			selectcolor.push($(this).val())
		});
		var searchUrl = setParam("selectcolor", selectcolor.join("-"));
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});
	$("#selectdesigner").click(function() {
		var url = window.location.href;
		var selectdesigner = [];
		$('input[name="selectdesigner[]"]:checked').each(function() {
			selectdesigner.push($(this).val())
		});
		var searchUrl = setParam("selectdesigner", selectdesigner.join("-"));
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});
	$("#search").click(function() {
		var url = window.location.href;
		var searchUrl = setParam("search_keyword", $('input[name="search_keyword"]').val());
		if (url.indexOf("?") > 0) {
			url = url.split("?")[0]
		}
		window.location.href = url + searchUrl
	});

	function setParam(param, value) {
		var query = location.search.substring(1);
		var p = new RegExp("(^|)" + param + "=([^&]*)(|$)");
		if (p.test(query)) {
			var firstParam = query.split(param)[0];
			var secondParam = query.split(param)[1];
			if (secondParam.indexOf("&") > -1) {
				var lastPraam = secondParam.split("&")[1];
				return "?" + firstParam + "&" + param + "=" + value + "&" + lastPraam
			} else {
				if (firstParam) {
					return "?" + firstParam + "&" + param + "=" + value
				} else {
					return "?" + param + "=" + value
				}
			}
		} else {
			if (query == "") {
				return "?" + param + "=" + value
			} else {
				return "?" + query + "&" + param + "=" + value
			}
		}
	}
	$(".input_text").bind("keypress", function(event) {
		if (event.keyCode == "13") {
			var url = window.location.href;
			var searchUrl = setParam("search_keyword", $('input[name="search_keyword"]').val());
			if (url.indexOf("?") > 0) {
				url = url.split("?")[0]
			}
			window.location.href = url + searchUrl
		}
	});
	$(function() {
		var w = 0;
		$(".size_a .sizeLista").each(function() {
			w += parseInt($(this).width())
		});
		$(".size-list").width(w + 20)
	});
	dataLayer.push({
		"event": "visit",
		"pagetype": "homepage",
		"Form URL": "<?=Yii::$app->request->referrer?>"
	});
	console.log("visit  dataLayer :");
	console.log(dataLayer)

	
});

function goPage(pno,pszie){
	var ul=document.getElementById("b")
	var lis=ul.getElementsByTagName("li")
	var num=lis.length;
	
	var totalpage=0;
	var pageSize = pszie;//每页显示个数
	  //总共分几页 
if(num/pageSize > parseInt(num/pageSize)){   
totalPage=parseInt(num/pageSize)+1;   
 }else{   
totalPage=parseInt(num/pageSize);   
}   
var currentPage = pno;//当前页数
var startRow = (currentPage - 1) * pageSize+1;//开始显示的行  31 
var endRow = currentPage * pageSize;//结束显示的行   40
endRow = (endRow > num)? num : endRow;    40
console.log(endRow);
for(var i=1;i<(num+1);i++){    
var irow = lis[i-1];
if(i>=startRow && i<=endRow){
irow.style.display = "block";    
}else{
irow.style.display = "none";
}
}

var tempStr = ''
if(totalPage>1){
	if(currentPage>1){
		// tempStr += "<a href=\"#\" onClick=\"goPage("+(1)+","+pszie+")\">首页</a>";
		tempStr += "<span onClick=\"goPage("+(currentPage-1)+","+pszie+")\"><i class='iconfont icon-arrowleft' style='color:pink'></i></span>"
		
		}else{
			tempStr += "<span><i class='iconfont icon-arrowleft'></i></span>";    
			// tempStr += "首页";
		}
		
			for(var i=0;i<totalPage;i++){
			
					tempStr +="<i class=\"iconfont icon-dian "+i+"\" style='color:#9e9e9e'></i>"	
				
			}
		
		if(currentPage<totalPage){
		tempStr += "<span onClick=\"goPage("+(currentPage+1)+","+pszie+")\"><i class='iconfont icon-arrowright' style='color:pink'></i></span>";
		// tempStr += "<a href=\"#\" onClick=\"goPage("+(totalPage)+","+pszie+")\">尾页</a>";
		
		}else{
			tempStr += "<span><i class='iconfont icon-arrowright'></i></span>";
			// tempStr += "尾页";    
		}
		
		document.getElementById("more_c").innerHTML = tempStr;
		
		$(".icon-dian").each(function(){
			if($(this).index()==(currentPage)){
				$(this).css("color","#5f5656")
			}
			
		})
		var url = window.location.href;
		
		
}else {
	tempStr +=''
	document.getElementById("more_c").innerHTML = tempStr;
}



}

goPage(1,28)
