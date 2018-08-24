
    alert(111)
    var lay =document.getElementById("lay"),
        smallImg = document.getElementById("smallImg"),
        bigImg =document.getElementById("bigImg");
    var imgB = bigImg.children[0]; //大图中的图片
    console.log(smallImg)
    var scale = 4;        //缩放倍数  可调整
    var w = smallImg.offsetWidth; //小图的宽高
    var h = smallImg.offsetHeight;
    lay.style.width = w / scale + "px";
    lay.style.height = h / scale + "px";

    imgB.style.width = w * scale + "px";
    imgB.style.height = h * scale + "px";
    smallImg.onmouseover = function(){
        lay.style.display = "block";
        bigImg.style.display = "block";
    }    
    smallImg.onmouseout = function(){
        lay.style.display = "none";
        bigImg.style.display = "none";
   
    }
    smallImg.onmousemove = function(e){
        e = e || event;
        var x = e.clientX - lay.offsetWidth/2;
        var y = e.clientY - lay.offsetHeight/2;
        if(x <= 0){            //左侧边界判断
            x = 0;
        }
        if(y <= 0){            //顶部边界判断
            y = 0;
        }
        if(x >= smallImg.offsetWidth - lay.offsetWidth ){
            x = smallImg.offsetWidth - lay.offsetWidth        //右侧边界判断
        }
        if(y >= smallImg.offsetHeight - lay.offsetHeight ){
            y = smallImg.offsetHeight - lay.offsetHeight        //底部边界判断
        }
        lay.style.left = x + "px";
        lay.style.top = y + "px";
        imgB.style.left = -x*scale + "px";    //图片默认位置为0 0左上角位置 需要反向才能两者相对显示
        imgB.style.top = -y*scale + "px";
    }    

