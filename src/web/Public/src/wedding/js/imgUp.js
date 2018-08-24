$(function(){
	var delParent;
	var defaults = {
		fileType         : ["jpg","png","bmp","jpeg"],   // 上传文件的类型
		fileSize         : 1024 * 1024 * 10                  // 上传文件的大小 10M
	};

		/*点击图片的文本框*/
	$(".file").change(function(){

		var idFile = $(this).attr("id");
		var file = document.getElementById(idFile);
		var imgContainer = $(this).parents(".z_photo"); //存放图片的父亲元素
		var fileList = file.files; //获取的图片文件
		console.log(fileList+"======filelist=====");
		var input = $(this).parent();//文本框的父亲元素
		var imgArr = [];
		//遍历得到的图片文件
		var numUp = imgContainer.find(".up-section").length;
		var totalNum = numUp + fileList.length;  //总的数量

		var $section = $("<section class='up-section fl loading'>");
		imgContainer.prepend($section);

        console.log(totalNum)
        console.log(numUp)

		
		if(fileList.length > 5 || totalNum > 5 ){
			alert("Cannot exceed 5 pictures. Please try again."); 
			 //一次选择上传超过5个 或者是已经上传和这次上传的到的总数也不可以超过5个
			// $(".pinglun").css("display","block")
		}
		else if(numUp < 5){
			fileList = validateUp(fileList);
            console.log(fileList)
			for(var i = 0;i<fileList.length;i++){
				var imgUrl = ''
				var xhr = new XMLHttpRequest();
				console.log(xhr)
				var formData = new FormData();
				formData.append('file', fileList[i]);
				xhr.open('POST', "upload");
				xhr.send(formData);
				xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && xhr.status == 200) {
						$(".loading").remove()
						console.log(xhr.responseText);
						var data = xhr.responseText;
						data = JSON.parse(data)
						if (data.code == 0) {
							imgUrl = data.data;


							imgArr.push(imgUrl);
							var $section = $("<section class='up-section fl loading'>");
							imgContainer.prepend($section);
							var $span = $("<span class='up-span fa fa-times-circle' aria-hidden='true'>");
							$span.appendTo($section);
							$(".z_photo section .up-span").each(function(){
								$(this).on("click",function(){
								
										$(this).parent().remove()
									
								})
							})
						
							
							var $img = $("<img class='up-img up-opcity'>");
							$img.attr("src", imgUrl);
							$img.appendTo($section);
							setTimeout(function(){
								$(".up-section").removeClass("loading");
								$(".up-img").removeClass("up-opcity");
							},450);
							numUp = imgContainer.find(".up-section").length;
							if(numUp >= 5){
								$(this).parent().hide();
							}
							$(this).val("");




						} else if (data.code == 1) {
							alert('The dimensions of the image you are trying to upload are invalid.');
							
							// $(".pinglun_a").css("display","block")
						} else if (data.code == 2) {
							alert('Upload failed');
							// $(".pinglun_b").css("display","block")
						} else if (data.code == 3) {
							alert('Picture size limit 2 MB');

							// $(".pinglun_c").css("display","block")
						}
					}
					xhr.timeout = 100000;
					xhr.ontimeout = function (event) {
						alert('TimeOut');
						// $(".pinglun_d").css("display","block")
					}
				}
		      
		   }
		}

	});
	
	
   
    $(".z_photo").delegate(".close-upimg","click",function(){
     	  $(".works-mask").show();
     	  delParent = $(this).parent();
	});
		
	$(".wsdel-ok").click(function(){
		$(".works-mask").hide();
		var numUp = delParent.siblings().length;
		if(numUp < 6){
			delParent.parent().find(".z_file").show();
		}
		 delParent.remove();
		
	});
	
	$(".wsdel-no").click(function(){
		$(".works-mask").hide();
	});
		
		function validateUp(files){
			var arrFiles = [];//替换的文件数组
			for(var i = 0, file; file = files[i]; i++){
				//获取文件上传的后缀名
				var newStr = file.name.split("").reverse().join("");
				if(newStr.split(".")[0] != null){
						var type = newStr.split(".")[0].split("").reverse().join("");
						console.log(type+"===type===");
						if(jQuery.inArray(type, defaults.fileType) > -1){
							// 类型符合，可以上传
							if (file.size >= defaults.fileSize) {
								alert(file.size);
								alert('file.name'+'size limit 2 MB');
									
								// $(".pinglun_e").css("display","block")
							} else {
								// 在这里需要判断当前所有文件中
								arrFiles.push(file);	
							}
						}else{
							alert('Wrong file format');	
							
							// $(".pinglun_f").css("display","block")
						}
					}else{
						alert('Wrong file format');
							
						// $(".pinglun_g").css("display","block")
					}
			}
			return arrFiles;
		}
		

	
	


	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
})
