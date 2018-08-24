var $ul = $(".sur-show-info ul"),
    size = $ul.find("li").size();
         $ul.append($ul.html());

var $li = $ul.find('li'),
    $len = $li.size(),
    $width = $li.eq(0).width(),
    $prev = $(".sur-pre"),
    $next = $(".sur-next"),
    timer = null,
    bClick = true,
    $index = 1;

$li.eq(1).addClass("sur-img-cur");

// $ul.css("width",($width+20)*$len);
// timer = setInterval(function(){
//   $index++;
//   next();
// },2000);

// $(".sur-show-wrap").on("mouseover",function(){
//   clearInterval(timer);
// });

// $(".sur-show-wrap").on("mouseout",function(){
//   timer = setInterval(function(){
//     $index++;
//     next();
//   },2000);
// });

$(document).on("click",".sur-pre",function(){
    // if(!bClick) return;
    // bClick = false;
    $index--;
    if($index == 0){

        $li.removeClass("sur-img-cur");
        $index = $len/2;
        $ul.css({left:-($index)*($width+20)});
        $li.eq($index).addClass("sur-img-cur");
        $ul.animate({left:-($index-1)*($width+20)},{complete:function(){
            bClick = true;
        }});
    } else {
        next();
    }
});

$(document).on("click",".sur-next",function(){
    // if(!bClick) return;

    // bClick = false;
    $index++;
    next();

});

function next(){
    $li.removeClass("sur-img-cur");
    // $a.removeClass("i-sur");
    if($index == $len/2+2){
        $ul.css({left:0});
        $index = 2;
        $ul.animate({left:-($index-1)*($width+20)},{complete:function(){
            bClick = true;
        }});
    } else {
        $ul.animate({left:-($index-1)*($width+20)},{complete:function(){
            bClick = true;
        }});
    }


    $li.eq($index).addClass("sur-img-cur");
    // $a.eq($index).addClass("i-sur");
}

//news


$(document).ready(function(){
    $('.sur-c li').on('click',function(){
        $(this).find('.sur-contwrap').toggle();
    })
})

$(document).ready(function(){
    var ul = $('.cf-v-info ul');
    // ul.append(ul.html());
    var $lis = $('.cf-v-info li').size();
    var wL = 0;
    var num  = 0;
    var sWidth;
    var sTop = $('.cf-v-info li').eq(0).height();
    var wTop;
    var wInfo;
    var aa;

    function sGo(){
        $('.cf-v-info ul').animate({
            top:-(num*sTop)
        }, 500);
    }

    function wGo(){
        $('.cf-v-info ul').animate({
            left: -(wL*wTop)
        }, 500);
    }
    var wa = $('.cf-v-info').eq(0).width();
    wTop = $('.cf-v-info li').eq(0).width();
    wInfo = wa;
    aa = (wInfo*0.5)*$lis;
    ul.width(aa);
    $(".cf-v-info li").each(function(){
        $(this).width(wInfo*0.5 - 44)
    });

if($(".cf-v-info li").length > 2){
    $('.cf-v-info').find('.arrow-pre').on('click',function(){
        wL++;
        if(wL == $lis/2){
            ul.css({left:0});
            console.log(wL)
            // ul.animate({left:-(wL-1)*wTop},500)
            wL = 0;
        }else{
            wGo();
        }
    });
    $('.cf-v-info').find('.arrow-next').on('click',function(){
        wL--;
        if(wL < 0){
            wL = $lis/2 + 1;
            console.log(wL)
            ul.css({left:-(wL*wTop)})
        }else{
            wGo();
        }
    });
}


    $('.list-nav li').click(function(){
        $(this).closest('.list-nav').next('.list-info').children('div').eq($(this).index())
            .show().siblings().hide();
        $(this).addClass('c-cur').siblings().removeClass('c-cur');
        $('.vmcarousel-centered-infitine').vmcarousel({
            centered: true,
            start_item: 1,
            autoplay: false,
            infinite: true
        });
    });

    var sIndex = 0;
    var sUl = $('.cf-person ul');
    var sWw = $('.cf-person li').eq(0).width();
    // sUl.append(sUl.html());
    var sLi = sUl.find('li').size();
    sUl.width((sWw+8)*sLi);

    function hGo(){
        $('.cf-person ul').animate({
            left: -(sIndex*(sWw+8))
        }, 500);
    }

    $('.cf-style-s .prev').on('click',function(){
        sIndex++;
        if(sIndex == sLi/2){
            sUl.css({left:0});
            sIndex = 0;
        }else{
            hGo();
        }
    })

    $('.cf-style-s .next').on('click',function(){
        sIndex--;
        if(sIndex < 0){
            sIndex = sLi/2 + 1;
            sUl.css({left:-(sIndex*(sWw+8))})
        }else{
            hGo();
        }
    })
})




