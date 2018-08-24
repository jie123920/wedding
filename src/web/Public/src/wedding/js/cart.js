//cart
jQuery(function() {

    // preview

    $('.spec-n2').delegate('li','click', function() {
        //获取图片的地址
        // var dataSrc = $(this).find('a').data('anchor-id');
        var dataSrc = $(this).find('img')[0].src;
     
        
        $('#spec-img').attr('src', dataSrc);
      
        if(document.getElementById("spec-img").naturalWidth>400){

            $("#ex3").addClass("mystyle")
    }
    if(document.getElementById("spec-img").naturalWidth==400){

        $("#ex3").removeClass("mystyle")
  
}
    $('#ex3').trigger('zoom.destroy');
   
      $('#ex3').zoom({ 
    url:   dataSrc,
    on: 'click'
});
        
        $('.magnifyingShow img').attr('src', dataSrc);
        $(this).addClass('tb-selected').siblings().removeClass('tb-selected');
    })


    //

    // $('#add-cart').on('click',function(event){
    // 	event.preventDefault()
    // 	$('.bone-box').eq(0).show();
    // })

    $('.box-del').on('click', function() {
        $(this).parents('.bone-box').hide();
    })

    var inpVal = $('#buy-num');
    $('.cart-compute .cf-reduce').on('click', function() {
        if (inpVal.val() > 1) {
            inpVal.val(parseInt(inpVal.val()) - 1)
        }
    })

    $('.cart-compute .cf-add').on('click', function() {
        inpVal.val(parseInt(inpVal.val()) + 1)
    })

    $('#buy-num').change(function() {

        var re = /^\\d+(\\.\\d+)$/; //浮点数

        if (isNaN(inpVal.val()) || inpVal.val() < 0 || inpVal.val() == '') {
            $('#buy-num').val(1)
        } else if (!re.test(inpVal.val())) {
            $('#buy-num').val(parseInt(inpVal.val()))
        } else if (inpVal.val() > 100) {
            alert('Can not be more than 100')
        }
    })


    // goods-news show

    $('.cart-info .goods-news-title').on('click', function() {

        $(this).siblings('.goods-intro').toggleClass('c-active');

        if ($(this).siblings('.goods-intro').hasClass('c-active')) {
            $(this).find('i').removeClass('icon-icon11').addClass(' icon-jian');
            $(this).css('border','none');
            $(this).parent().siblings().find('.goods-news-title').css('border-bottom','1px solid #af9e89');
        } else {
            $(this).find('i').removeClass(' icon-jian').addClass('icon-icon11');
            $(this).css('border-bottom','1px solid #af9e89');
        }

        $(this).parent('li').siblings().find('.goods-intro').removeClass('c-active');

        if ($(this).parent('li').siblings().children().hasClass('goods-intro')) {
            $(this).parent('li').siblings().find('i').removeClass(' icon-jian').addClass('icon-icon11')
        } else {
            $(this).find('i').removeClass('icon-icon11').addClass(' icon-jian')
        }
    })



    // 点击添加到购物车


    // 点击到购买


    // function show (){
    //     var pic=document.getElementById('pic');
    //     var bigpic=document.getElementById('bigpic');
    //     var box=document.getElementById('box');
    //     var drag=document.getElementById('drag');
    //     var leftImg=document.getElementById('leftImg');
    //     console.log(pic)
    //     pic.onmouseout=function(){
    //         //鼠标移出 滤镜和右边图隐藏
    //         drag.style.display='none';
    //         bigpic.style.display='none';
    //     }
    //     pic.onmousemove=function(event){
    //         var e=event||window.event;
    //         drag.style.display='block' //当鼠标移上并移动的时候，滤镜层是可以显示的
    //         bigpic.style.display='block' //当鼠标移上并移动的时候，右边大图层也是可以显示的
    //         //获取鼠标的位置，即鼠标距离窗口的值
    //         var x=e.clientX;
    //         var y=e.clientY;
    //         //x-pic.offsetleft的值是鼠标相对于外边图左边距的距离。（offsetleft是对象与窗口左边的距离，=父border+父padding+左margin）
    //         //因为想要鼠标位置位于滤镜的中心，所以在计算滤镜距外边图左边界的距离时还要减去滤镜宽度的一半
    //         //offsetWidth:对象的宽度，即offsetWidth=width+padding+border=clientWidth+border
    //         var lefts=x-pic.offsetLeft-drag.offsetWidth/2;
    //         var tops=y-165 -drag.offsetHeight/2;
    //         console.log(tops)
    //         //临界值判断 left需要在0和(pic.clientWidth-drag.offsetWidth)之间
    //         if (lefts<0){lefts=0;}
    //         if (lefts>pic.clientWidth-drag.offsetWidth) {lefts=pic.clientWidth-drag.offsetWidth};
    //
    //         if (tops<0){tops=0;}
    //         if (tops>pic.clientHeight-drag.offsetHeight) {tops=pic.clientHeight-drag.offsetHeight};
    //
    //         //现在可以确定滤镜相对于外边图的位置,注意drag.style.left需要有‘px’的单位。
    //         drag.style.left=lefts+'px';
    //         drag.style.top=tops+'px';
    //
    //         //现在控制右边大图的位置，即得到右边显示区域的scrollLeft和scrollTop.，
    //         //根据比例关系 倍数=左边图的宽高/原大图宽高=滤镜宽高/右边显示区域宽高=滤镜距左边界距离/右大图距原图左边界距离.
    //         var num=box.offsetWidth/leftImg.offsetWidth;
    //         bigpic.scrollLeft=drag.offsetLeft*num;
    //         bigpic.scrollTop=drag.offsetTop*num;
    //     }
    // }
    // 后端数据
    /**
     *  red yellow white blue ==> 10 11 12 13
     *  size ==> 20 21 22 23
     **/
    var data = window.goodDatas;

    //var startTime = new Date().getTime();

    //保存最后的组合结果信息
    window.SKUResult = {};
    //获得对象的key
    function getObjKeys(obj) {
        console.log(222222222222222222222222);
        console.log(obj);
        if (obj !== Object(obj)) {
            throw new TypeError('Invalid object');
        }
        var keys = [];
        for (var key in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, key)) {
                keys[keys.length] = key;
            }
        }
        return keys;
    }

    //把组合的key放入结果集SKUResult
    function add2SKUResult(combArrItem, sku) {
        var key = combArrItem.join(";");
        if (SKUResult[key]) { //SKU信息key属性·
            SKUResult[key].count += sku.count;
            SKUResult[key].prices.push(sku.price);
        } else {
            SKUResult[key] = {
                count: sku.count,
                prices: [sku.price],
                photos:sku.photos,
                id:sku.id
            };
        }
    }

    //初始化得到结果集
    function initSKU() {
        var i, j, skuKeys = getObjKeys(data);
        for (i = 0; i < skuKeys.length; i++) {
            var skuKey = skuKeys[i]; //一条SKU信息key
            var sku = data[skuKey]; //一条SKU信息value
            var skuKeyAttrs = skuKey.split(";"); //SKU信息key属性值数组
            skuKeyAttrs.sort(function(value1, value2) {
                return parseInt(value1) - parseInt(value2);
            });

            //对每个SKU信息key属性值进行拆分组合
            var combArr = combInArray(skuKeyAttrs);
            for (j = 0; j < combArr.length; j++) {
                add2SKUResult(combArr[j], sku);
            }

            //结果集接放入SKUResult
            SKUResult[skuKeyAttrs.join(";")] = {
                count: sku.count,
                prices: [sku.price],
                price_local: [sku.price_local],
                price_original: [sku.price_original],
                price_original_local: [sku.price_original_local],
                photos: sku.photos,
                id:sku.id
            }
        }
        var $skuWRAP = $('.choose-attrs');
        var $skuWRAPChild = $skuWRAP.find('.sku'); //所有sku对象
        //已经选择的节点
        var selectedObjs = $skuWRAP.find('.sku-selected');

        //获取所需要的元素
        ul = document.getElementById("bum-box");
        count = ul.children.length;
        index=0;
        //设置ul的宽度
        $("#bum-box").css({
            "height":count * 100+'%',
            /*					"left":"0"*/
        })
        if(count<=4){
            $(".bum-buttom").hide();
            $("preview_right_img").css("margin-top","0px");

        }else{
            $(".bum-buttom").show();
            $("preview_right_img").css("margin-top","37px");
        }
        animate(ul,-index*imgHeight);

        if (selectedObjs.length) {

            //获得组合key价格
            var selectedIds = [];
            selectedObjs.each(function() {
                selectedIds.push($(this).attr('data-attrid'));
            });

            //将结果排序
            selectedIds.sort(function(value1, value2) {
                return parseInt(value1) - parseInt(value2);
            });

            var len = selectedIds.length;
            var prices = SKUResult[selectedIds.join(';')].prices;
            var price_local = SKUResult[selectedIds.join(';')].price_local;
            var price_original = SKUResult[selectedIds.join(';')].price_original;
            var price_original_local = SKUResult[selectedIds.join(';')].price_original_local;
            var photos = SKUResult[selectedIds.join(';')].photos;

            var maxPrice = Math.max.apply(Math, prices);
            var minPrice = Math.min.apply(Math, prices);

            //  显示折扣价格
            var p, po;
            if (prices instanceof Array) {
                p = parseFloat( prices[0] );
                if (price_original === undefined) {
                    po = 0;
                } else {
                    po = parseFloat( price_original[0] );
                }
            } else {
                p = prices;
                po = price_original;
            }
            if ( p < po && po > 0 ) {
                var discount = ( po - p ) / po;
                discount = Math.round( discount * 100 )
                $('#discount').text("( " + discount + "% Off )");
            } else {
                $('#discount').text("");
            }

            // 显示原价
            if ( p < po ) {
                var node = $('#price_original_local');
                node.css("display", "inline");
                node.find(".price_original_local").text(price_original_local);

                if ( node_usd = $('#price_original') ) {
                    node_usd.find('.price_original').text(price_original);
                    node_usd.css( 'display', 'inline' );
                    // node_usd.find('.price_original').text(maxPrices > minPrices ? minPrices + "-" + maxPrices : maxPrices);
                }
            } else {
                var node = $('#price_original_local');
                node.css("display", "none");

                if ( node_usd = $('#price_original') ) {
                    node_usd.css( 'display', 'none' );
                }
            }

            $('.attr-price').text(maxPrice > minPrice ? minPrice + "-" + maxPrice : maxPrice); //获取价格
            $('.price_local').text(price_local);

            //selectedObjs.closest('dl').find('.attrs-name').text(selectedObjs.data('value'))
            //循环的把图片加入列表
            //alert(JSON.stringify(SKUResult[selectedIds.join(';')]));
            if(photos.length >0){
                var photos_str = '';
                for(var i=0;i<photos.length;i++){
                    photos_str = photos_str +'<li><a href="javascript:;"><img src ="'+photos[i]+'" title="'+goods_name+'" alt="'+goods_name+'"></a></li>';
                    if(i==0){
                        $('.spec-n1').html('<img  id="spec-img" src ="'+photos[i]+'" title="'+goods_name+'" alt="'+goods_name+'">')
                        $('#ex3').trigger('zoom.destroy');
                        $('#ex3').zoom({
                            url:   photos[i],
                            on: 'click'
                        });

                    }
                }


                $('.spec-n2').html(photos_str)
                $('.spec-n2 li').eq(0).addClass("tb-selected")

                //获取所需要的元素
                ul = document.getElementById("bum-box");
                index=0;
                count = ul.children.length;

                //设置ul的宽度
                $("#bum-box").css("height",count * 100+'%')
                if(count<=4){
                    $(".bum-buttom").hide();
                    $(".preview_right_img").css("margin-top","0px");

                }else{
                    $(".bum-buttom").show();
                    $(".preview_right_img").css("margin-top","37px");
                }

                //给购物车添加img图片
                $('.car-img').html('<img src="'+photos[0]+'" title="" alt="" data-pin-nopin="true">')
            }

            //用已选中的节点验证待测试节点 underTestObjs
            $skuWRAPChild.not(selectedObjs).not(self).each(function() {
                var siblingsSelectedObj = $(this).siblings('.sku-selected');
                var testAttrIds = []; //从选中节点中去掉选中的兄弟节点
                if (siblingsSelectedObj.length) {
                    var siblingsSelectedObjId = siblingsSelectedObj.attr('data-attrid');
                    for (var i = 0; i < len; i++) {
                        (selectedIds[i] != siblingsSelectedObjId) && testAttrIds.push(selectedIds[i]);
                    }
                } else {
                    testAttrIds = selectedIds.concat();
                }
                testAttrIds = testAttrIds.concat($(this).attr('data-attrid'));
                testAttrIds.sort(function(value1, value2) {
                    return parseInt(value1) - parseInt(value2);
                });

                if (!SKUResult[testAttrIds.join(';')]) {
                    $(this).addClass('attr-disabled').removeClass('sku-selected');
                } else {
                    if (SKUResult[testAttrIds.join(';')].count > 0) {
                        $(this).removeClass('attr-disabled');
                    } else {
                        $(this).addClass('attr-disabled').removeClass('sku-selected');
                    }
                }
            });
        } else {
            //设置默认价格
            //$('.attr-price').text('50');
            //设置属性状态
            $skuWRAPChild.each(function() {
                SKUResult[$(this).attr('data-attrid')] ? $(this).removeClass('attr-disabled') : $(this).addClass('attr-disabled').removeClass('sku-selected');
            });
        }


    }

    /**
     * 从数组中生成指定长度的组合
     * 方法: 先生成[0,1...]形式的数组, 然后根据0,1从原数组取元素，得到组合数组
     */
    function combInArray(aData) {
        if (!aData || !aData.length) {
            return [];
        }

        var len = aData.length;
        var aResult = [];

        for (var n = 1; n < len; n++) {
            var aaFlags = getCombFlags(len, n);
            while (aaFlags.length) {
                var aFlag = aaFlags.shift();
                var aComb = [];
                for (var i = 0; i < len; i++) {
                    aFlag[i] && aComb.push(aData[i]);
                }
                aResult.push(aComb);
            }
        }

        return aResult;
    }

    /**
     * 得到从 m 元素中取 n 元素的所有组合
     * 结果为[0,1...]形式的数组, 1表示选中，0表示不选
     */
    function getCombFlags(m, n) {
        if (!n || n < 1) {
            return [];
        }

        var aResult = [];
        var aFlag = [];
        var bNext = true;
        var i, j, iCnt1;

        for (i = 0; i < m; i++) {
            aFlag[i] = i < n ? 1 : 0;
        }

        aResult.push(aFlag.concat());

        while (bNext) {
            iCnt1 = 0;
            for (i = 0; i < m - 1; i++) {
                if (aFlag[i] == 1 && aFlag[i + 1] == 0) {
                    for (j = 0; j < i; j++) {
                        aFlag[j] = j < iCnt1 ? 1 : 0;
                    }
                    aFlag[i] = 0;
                    aFlag[i + 1] = 1;
                    var aTmp = aFlag.concat();
                    aResult.push(aTmp);
                    if (aTmp.slice(-n).join("").indexOf('0') == -1) {
                        bNext = false;
                    }
                    break;
                }
                aFlag[i] == 1 && iCnt1++;
            }
        }
        return aResult;
    }
    //获取所需要的元素
    var ul;
    //图片的大小
    var imgHeight=100;
    var index=0;
    var count;

    var app = {
        init: function() {
            initSKU();
            var $skuWRAP = $('.choose-attrs');
            var $skuWRAPChild = $skuWRAP.find('.sku'); //所有sku对象
            //$(".cart-list li:nth-child(1)").addClass("sku-selected")
            $skuWRAPChild.each(function() { 
                //加载页面遍历获取哪些应该变灰掉
                var self = $(this);

                var attr_id = self.attr('data-attrid');
                if (!SKUResult[attr_id]) {
                    self.addClass('attr-disabled');
                }
            }).on('click', function() {
                var self = $(this);
                if (self.hasClass('attr-disabled')) {
                    return false;
                }

                //选中自己，兄弟节点取消选中
                self.toggleClass('sku-selected').siblings().removeClass('sku-selected');
                if(!isFirstClick){
                    window.location.reload();
                }

                //已经选择的节点
                var selectedObjs = $skuWRAP.find('.sku-selected');

                //获取所需要的元素
                ul = document.getElementById("bum-box");
                count = ul.children.length;
                index=0;
                //设置ul的宽度
                $("#bum-box").css({
                    "height":count * 100+'%',
                    /*					"left":"0"*/
                })
                if(count<=4){
                    $(".bum-buttom").hide();
                    $("preview_right_img").css("margin-top","0px");

                }else{
                    $(".bum-buttom").show();
                    $("preview_right_img").css("margin-top","37px");
                }
                animate(ul,-index*imgHeight);

                if (selectedObjs.length) {

                    //获得组合key价格
                    var selectedIds = [];
                    selectedObjs.each(function () {
                        selectedIds.push($(this).attr('data-attrid'));
                    });

                    //将结果排序
                    selectedIds.sort(function (value1, value2) {
                        return parseInt(value1) - parseInt(value2);
                    });

                    var kid = SKUResult[selectedIds.join(';')].id;
                    var stateObject = {};
                    var title = "new";
                    var searchost = window.location.host;
                    
                    if(bsurl){
                         var newurl = bsurl + "/" + urltitle + "-g" + goods_id + "-k" + kid
                    }else{
                         var newurl = location.protocol + "//" + searchost + "/" + urltitle + "-g" + goods_id + "-k" + kid
                    }
                   
                    history.pushState(stateObject, title, newurl)
                }
            });
        }
    };

    app.init();

    //下一张
    $(".bum-next").on("click", function () {
        index++;
        if(index > count-4){
            index=0;
            animate(ul,-index*imgHeight);
            return false;
        }
        animate(ul,-index*imgHeight);
    })

    //上一张
    $(".bum-prev").on("click", function () {
        index --;
        if(index < 0){
            index=0;
            animate(ul,-index*imgHeight);
            return false;
        }
        animate(ul,-index*imgHeight);

    })
    //封装的函数
    function animate(element,target){
        console.log(element,target)
        if(element.timerId){
            clearInterval(element.timerId);
        }
        var step =100;
        element.timerId = setInterval(function () {
            //求出盒子所在的默认位置
            var current = element.offsetTop;
            console.log(current)
            //如果现在的位置大于要到达的位置
            if(current > target){
                //那么就需要减step
                step = -Math.abs(step);
            }
            //如果现在的位置减去要达到的位置还大于step，那么就还前进，反之停止前进
            if(Math.abs(current - target) > Math.abs(step)){
                current += step;
                element.style.top = current + "px";
            }else{
                clearInterval(element.timerId);
                //强制让盒子停在要达到的位置上
                element.style.top = target + "px";
            }
        },5);
    }


    /*============以下代码另作他用===============*/
    //获取 key的库存量
    var myData = {};
    //这个是获取数量的跟本次无关
    function getNum(key) {
        var result = 0,

            i, j, m,

            items, n = [];

        //检查是否已计算过
        if (typeof myData[key] != 'undefined') {
            return myData[key];
        }

        items = key.split(";");

        //已选择数据是最小路径，直接从后端数据获取
        if (items.length === keys.length) {
            return data[key] ? data[key].count : 0;
        }

        //拼接子串
        for (i = 0; i < keys.length; i++) {
            for (j = 0; j < keys[i].length && items.length > 0; j++) {
                if (keys[i][j] == items[0]) {
                    break;
                }
            }

            if (j < keys[i].length && items.length > 0) {
                //找到该项，跳过
                n.push(items.shift());
            } else {
                //分解求值
                for (m = 0; m < keys[i].length; m++) {
                    result += getNum(n.concat(keys[i][m], items).join(";"));
                }
                break;
            }
        }

        //缓存
        myData[key] = result;
        return result;
    }
 
   window.onload=function(){
    if(document.getElementById("spec-img").naturalWidth>400){

        $("#ex3").addClass("mystyle")
}
if(document.getElementById("spec-img").naturalWidth==400){

    $("#ex3").removeClass("mystyle")

}
   }
})

