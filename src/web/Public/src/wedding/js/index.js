
$(function(){
    var items = $('#carousel').children();
    var len = items.length;
    var index = 0;
    var str = 0;
    var dots =  $('.dot').children();
    var timer;
    /*var dotsChild = $('.dot span');*/
    
    //自动播放函数autoPlay()
    
    // function autoPlay(){
        $(items[index]).fadeIn(1000);
        
        function play(){
            $(dots).removeClass("active");
            if(index >=0 & index < len-1){
                $(items[index]).fadeOut(1000);
                index++;
                $(items[index]).fadeIn(1000);
                $(dots[index]).addClass("active");
            }else{
                $(items[index]).fadeOut(1000);
                index=0;
                $(items[index]).fadeIn(1000);
                $(dots[index]).addClass("active");
            };
            str = index;
        }
        
        //  setInterval(play,7000);
        
    // }
    // autoPlay();
    timer=setInterval(function(){
      
        play()
    },7000);

    $('.carousel').mouseover(function(){
        clearInterval(timer);
    })
    $('.carousel').mouseout(function(){
        clearInterval(timer);
        timer=setInterval(function(){
            play()
        },7000);
    
        
    })
    
    //点击左侧按钮的函数
    $(".left").click(function(){
        $(dots).removeClass("active");
        if(str == 0){
            $(items[str]).fadeOut(1500);
            str = len-1;
            $(items[str]).fadeIn(1500);
            $(dots[str]).addClass("active");
            index = str;
            
        }else{
            $(items[str]).fadeOut(1500);
            str --;
            $(items[str]).fadeIn(1500);
            $(dots[str]).addClass("active");
            index = str;
        }
    });
    //点击右侧按钮的函数
    $(".right").click(function(){
        $(dots).removeClass("active");
        if(str == len-1){
            $(items[str]).fadeOut(1500);
            str = 0;
            $(items[str]).fadeIn(1500);
            $(dots[str]).addClass("active");
            index = str;
        }else{
            $(items[str]).fadeOut(1500);
            str ++;
            $(items[str]).fadeIn(1500);
            $(dots[str]).addClass("active");
            index = str;
        }
    })
    //小圆点
    $(".dot span").click(function(){
        var num = $(this).index();
        $(dots).removeClass("active");
        $(dots[num]).addClass("active");
        $(items).fadeOut(1500);
        $(items[num]).fadeIn(1500);
        index = num;
    })

$(".state-currency").on("mousemove",function(){
    $(".state-currency-list").show();
})
$(".state-currency").on("mouseleave",function(){
    $(".state-currency-list").hide();
})

$(".language").on("mousemove",function(){
    $(".language-list").show();
})
$(".language").on("mouseleave",function(){
    $(".language-list").hide();
})
//导航栏
    $(".nav-list-v").delegate(".name-list-title","mousemove",function(){
        $(this).find(".nav_a").css("border-bottom","2px solid #f5a279")
        $(this).find(".name-list").show();
        $(this).find(".dropdown-mask-list").show();
    });
    $(".nav-list-v").delegate(".name-list-title","mouseout",function(){
        $(this).find(".nav_a").css("border","none")
        $(this).find(".name-list").hide();
        $(this).find(".dropdown-mask-list").hide();
    });



    // 二二二二
    var item = $('#carouse').children();
    var le = item.length;
    var inde = 0;
    var st = 0;
    var dos =  $('.do').children();
    var time;
    /*var dotsChild = $('.dot span');*/
    
    //自动播放函数autoPlay()
    
    // function autoPlay(){
        console.log(item.length)
     if(item.length==1){
            $("Advertisements").children("div:first-child").removeClass("lef")
            $("Advertisements").children("div:last-child").removeClass("righ")
     }else{
        $(item[inde]).fadeIn(1000);
        
        function pay(){
            $(dos).removeClass("active");
            if(inde >=0 & inde < le-1){
                $(item[inde]).fadeOut(1000);
                inde++;
                $(item[inde]).fadeIn(1000);
                $(dos[inde]).addClass("active");
            }else{
                $(item[inde]).fadeOut(1000);
                inde=0;
                $(item[inde]).fadeIn(1000);
                $(dos[inde]).addClass("active");
            };
            st = inde;
        }
        
        //  setInterval(play,7000);
        
    // }
    // autoPlay();
    time=setInterval(function(){
      
        pay()
    },3000);

    
    
    
    //点击左侧按钮的函数
    $(".lef").click(function(){
        $(dos).removeClass("active");
        if(st == 0){
            $(item[st]).fadeOut(300);
            st = le-1;
            $(item[st]).fadeIn(300);
            $(dos[st]).addClass("active");
            inde = st;
            
        }else{
            $(item[st]).fadeOut(300);
            st --;
            $(item[st]).fadeIn(300);
            $(dos[st]).addClass("active");
            inde = st;
        }
    });
    //点击右侧按钮的函数
    $(".righ").click(function(){
        $(dos).removeClass("active");
        if(st == le-1){
            $(item[st]).fadeOut(300);
            st = 0;
            $(item[st]).fadeIn(300);
            $(dos[st]).addClass("active");
            inde = st;
        }else{
            $(item[st]).fadeOut(300);
            st ++;
            $(item[st]).fadeIn(300);
            $(dos[st]).addClass("active");
            inde = st;
        }
    })
    
     }
           
       
    //小圆点
    // $(".dot span").click(function(){
    //     var num = $(this).index();
    //     $(dos).removeClass("active");
    //     $(dos[num]).addClass("active");
    //     $(item).fadeOut(1500);
    //     $(item[num]).fadeIn(1500);
    //     inde = num;
    // })
});