/**
 * jQuery Editable Select
 * Indri Muska <indrimuska@gmail.com>
 *
 * Source on GitHub @ https://github.com/indrimuska/jquery-editable-select
 */

+(function ($) {
	// jQuery Editable Select
	EditableSelect = function (select, options) {
		var that     = this;
		
		this.options = options;
		this.$select = $(select);
		this.$input  = $('<input type="text" autocomplete="off" placeholder="Please select...">');
		
		this.$list   = $('<ul class="es-list">');
		this.utility = new EditableSelectUtility(this);
		
		if (['focus', 'manual'].indexOf(this.options.trigger) < 0) this.options.trigger = 'focus';
		if (['default', 'fade', 'slide'].indexOf(this.options.effects) < 0) this.options.effects = 'default';
		if (isNaN(this.options.duration) && ['fast', 'slow'].indexOf(this.options.duration) < 0) this.options.duration = 'fast';
		
		// create text input
		this.$select.replaceWith(this.$input);
		this.$list.appendTo(this.options.appendTo || this.$input.parent());
		
		// initalization
		this.utility.initialize();
		this.utility.initializeList();
		this.utility.initializeInput();
		this.utility.trigger('created');
	}
	EditableSelect.DEFAULTS = { filter: true, effects: 'default', duration: 'fast', trigger: 'focus' };
	EditableSelect.prototype.filter = function () {
		var hiddens = 0;
		var search  = this.$input.val().toLowerCase().trim();
		
		this.$list.find('li').addClass('es-visible').show();
		if (this.options.filter) {
			hiddens = this.$list.find('li').filter(function (i, li) { return $(li).text().toLowerCase().indexOf(search) < 0; }).removeClass('es-visible').length;
			// if (this.$list.find('li').length == hiddens) this.hide();
		}
	};
	EditableSelect.prototype.show = function () {
		this.$list.css({
			top:   this.$input.position().top + this.$input.outerHeight() - 1,
			left:  this.$input.position().left,
			width: this.$input.outerWidth()
		});
		
		if (!this.$list.is(':visible') && this.$list.find('li.es-visible').length > 0) {
			var fns = { default: 'show', fade: 'fadeIn', slide: 'slideDown' };
			var fn  = fns[this.options.effects];
			
			this.utility.trigger('show');
			this.$input.addClass('open');
			this.$list[fn](this.options.duration, $.proxy(this.utility.trigger, this.utility, 'shown'));
		}
	};
	EditableSelect.prototype.hide = function () {
		var fns = { default: 'hide', fade: 'fadeOut', slide: 'slideUp' };
		var fn  = fns[this.options.effects];
		
		this.utility.trigger('hide');
		this.$input.removeClass('open');
		this.$list[fn](this.options.duration, $.proxy(this.utility.trigger, this.utility, 'hidden'));
	};
	EditableSelect.prototype.select = function ($li) {
		if (!this.$list.has($li) || !$li.is('li.es-visible:not([disabled])')) return;
		this.$input.val($li.text());
		if (this.options.filter) this.hide();
		this.filter();
		this.utility.trigger('select', $li);
	};
	EditableSelect.prototype.add = function (text, index, attrs, data) {
		var $li     = $('<li>').html(text);
		var $option = $('<option>').text(text);
		var last    = this.$list.find('li').length;
		
		if (isNaN(index)) index = last;
		else index = Math.min(Math.max(0, index), last);
		if (index == 0) {
		  this.$list.prepend($li);
		  this.$select.prepend($option);
		} else {
		  this.$list.find('li').eq(index - 1).after($li);
		  this.$select.find('option').eq(index - 1).after($option);
		}
		this.utility.setAttributes($li, attrs, data);
		this.utility.setAttributes($option, attrs, data);
		this.filter();
	};
	EditableSelect.prototype.remove = function (index) {
		var last = this.$list.find('li').length;
		
		if (isNaN(index)) index = last;
		else index = Math.min(Math.max(0, index), last - 1);
		this.$list.find('li').eq(index).remove();
		this.$select.find('option').eq(index).remove();
		this.filter();
	};
	EditableSelect.prototype.clear = function () {
		this.$list.find('li').remove();
		this.$select.find('option').remove();
		this.filter();
	};
	EditableSelect.prototype.destroy = function () {
		this.$list.off('mousemove mousedown mouseup');
		this.$input.off('focus blur input keydown');
		this.$input.replaceWith(this.$select);
		this.$list.remove();
		this.$select.removeData('editable-select');
	};
	
	// Utility
	EditableSelectUtility = function (es) {
		this.es = es;
	}
	EditableSelectUtility.prototype.initialize = function () {
		var that = this;
		that.setAttributes(that.es.$input, that.es.$select[0].attributes, that.es.$select.data());
		that.es.$input.addClass('es-input').data('editable-select', that.es);
		that.es.$select.find('option').each(function (i, option) {
			var $option = $(option).remove();
			that.es.add($option.text(), i, option.attributes, $option.data());
			if ($option.attr('selected')) that.es.$input.val($option.text());
		});
		that.es.filter();
	};
	EditableSelectUtility.prototype.initializeList = function () {
		var that = this;
		that.es.$list
			.on('mousemove', 'li:not([disabled])', function () {
				that.es.$list.find('.selected').removeClass('selected');
				$(this).addClass('selected');
			})
			.on('mousedown', 'li', function (e) {
				if ($(this).is('[disabled]')) e.preventDefault();
				else that.es.select($(this));
			})
			.on('mouseup', function () {
				that.es.$list.find('li.selected').removeClass('selected');
			});
	};
	EditableSelectUtility.prototype.initializeInput = function () {
		var that = this;
		switch (this.es.options.trigger) {
			default:
			case 'focus':
				that.es.$input
					.on('focus', $.proxy(that.es.show, that.es))
					.on('blur', $.proxy(that.es.hide, that.es));
				break;
			case 'manual':
				break;
		}
		that.es.$input.on('input keydown', function (e) {
			switch (e.keyCode) {
				case 38: // Up
					var visibles = that.es.$list.find('li.es-visible:not([disabled])');
					var selectedIndex = visibles.index(visibles.filter('li.selected'));
					that.highlight(selectedIndex - 1);
					e.preventDefault();
					break;
				case 40: // Down
					var visibles = that.es.$list.find('li.es-visible:not([disabled])');
					var selectedIndex = visibles.index(visibles.filter('li.selected'));
					that.highlight(selectedIndex + 1);
					e.preventDefault();
					break;
				case 13: // Enter
					if (that.es.$list.is(':visible')) {
						that.es.select(that.es.$list.find('li.selected'));
						e.preventDefault();
					}
					break;
				case 9:  // Tab
				case 27: // Esc
					that.es.hide();
					break;
				default:
					that.es.filter();
					that.highlight(0);
					break;
			}
		});
	};
	EditableSelectUtility.prototype.highlight = function (index) {
		var that = this;
		that.es.show();
		setTimeout(function () {
			var visibles         = that.es.$list.find('li.es-visible');
			var oldSelected      = that.es.$list.find('li.selected').removeClass('selected');
			var oldSelectedIndex = visibles.index(oldSelected);
			
			if (visibles.length > 0) {
				var selectedIndex = (visibles.length + index) % visibles.length;
				var selected      = visibles.eq(selectedIndex);
				var top           = selected.position().top;
				
				selected.addClass('selected');
				if (selectedIndex < oldSelectedIndex && top < 0)
					that.es.$list.scrollTop(that.es.$list.scrollTop() + top);
				if (selectedIndex > oldSelectedIndex && top + selected.outerHeight() > that.es.$list.outerHeight())
					that.es.$list.scrollTop(that.es.$list.scrollTop() + selected.outerHeight() + 2 * (top - that.es.$list.outerHeight()));
			}
		});
	};
	EditableSelectUtility.prototype.setAttributes = function ($element, attrs, data) {
		$.each(attrs || {}, function (i, attr) { $element.attr(attr.name, attr.value); });
		$element.data(data);
	};
	EditableSelectUtility.prototype.trigger = function (event) {
		var params = Array.prototype.slice.call(arguments, 1);
		var args   = [event + '.editable-select'];
		args.push(params);
		this.es.$select.trigger.apply(this.es.$select, args);
		this.es.$input.trigger.apply(this.es.$input, args);
	};
	
	// Plugin
	Plugin = function (option) {
		var args = Array.prototype.slice.call(arguments, 1);
		return this.each(function () {
			var $this   = $(this);
			var data    = $this.data('editable-select');
			var options = $.extend({}, EditableSelect.DEFAULTS, $this.data(), typeof option == 'object' && option);
			
			if (!data) data = new EditableSelect(this, options);
			if (typeof option == 'string') data[option].apply(data, args);
		});
	}
	$.fn.editableSelect             = Plugin;
	$.fn.editableSelect.Constructor = EditableSelect;






	   //定制专题
           //    第一个联动下拉
    //    样式一
    var arr_t = [];
    for (var i = 21; i < 64;) {
        i = i + 0.5;
        arr_t.push(i)
    }
     //样式二
    var arr_tw = [];
    for (var i = 53; i < 161;) {
        i = i + 1;
        arr_tw.push(i)
    }
      var html_a = "<option value='' selected></option>"
            $("#bust_o").css("display","none")
              $("#bust_t").css("display","none")
              $("#bust_f").css("display","block")
            
             
              for (var i = 0; i < arr_t.length > 0; i++) {
                  html_a += '<option value="' + arr_t[i] + '">' + arr_t[i] + ' inch</option>'
                  

              }
              $('#bust_f').html(html_a)
              $('#bust_f').editableSelect();
              //    第二个联动下拉
    //    样式一
    var arr_f = [];
    for (var i = 20; i < 64;) {
        i = i + 0.5;
        arr_f.push(i)
    }
    //样式二
    var arr_fo = [];
    for (var i = 51; i < 161;) {
        i = i + 1;
        arr_fo.push(i)
    }
    var html_b = "<option value='' selected></option>"
    $("#waist_o").css("display","none")
      $("#waist_t").css("display","none")
      $("#waist_f").css("display","block")
    
     
      for (var i = 0; i < arr_f.length > 0; i++) {
          html_b += '<option value="' + arr_f[i] + '">' + arr_f[i] + ' inch</option>'
          

      }
      $('#waist_f').html(html_b)
      $('#waist_f').editableSelect();

 //第三个联动下拉
    //    样式一
    var arr_s = [];
    for (var i = 20; i < 64;) {
        i = i + 0.5;
        arr_s.push(i)
    }
    //样式二
    var arr_si = [];
    for (var i = 51; i < 161;) {
        i = i + 1;
        arr_si.push(i)
    }

    var html_c = "<option value='' selected></option>"
    $("#hips_o").css("display","none")
      $("#hips_t").css("display","none")
      $("#hips_f").css("display","block")
    
     
      for (var i = 0; i < arr_s.length > 0; i++) {
          html_c += '<option value="' + arr_s[i] + '">' + arr_s[i] + ' inch</option>'
          

      }
      $('#hips_f').html(html_c)
      $('#hips_f').editableSelect();
          //第四个联动下拉
        //    样式一
    var arr_n = [];
    for (var i = 22; i < 76;) {
        i = i + 0.5;
        arr_n.push(i)
    }
    //样式二
    var arr_ni = [];
    for (var i = 55; i < 191;) {
        i = i + 1;
        arr_ni.push(i)
    }
    var html_d = "<option value='' selected></option>"
    $("#hollow_o").css("display","none")
      $("#hollow_t").css("display","none")
      $("#hollow_f").css("display","block")
    
     
      for (var i = 0; i < arr_n.length > 0; i++) {
          html_d += '<option value="' + arr_n[i] + '">' + arr_n[i] + ' inch</option>'
          

      }
      $('#hollow_f').html(html_d)
	  $('#hollow_f').editableSelect();
	//   第五个联动下来
	// 样式一
	var arr_w=[];
	for(var i=2.5; i<15.5;){
		i=i+0.5;
		arr_w.push(i)
	}
	// 样式二
	var arr_wu=[];
	for(var i=9;i<40;){
		i=i+1;
		arr_wu.push(i)
	}

	var html_e = "<option value='' selected></option>"
    $("#bic_o").css("display","none")
      $("#bic_t").css("display","none")
      $("#bic_f").css("display","block")
    
     
      for (var i = 0; i < arr_w.length > 0; i++) {
          html_e += '<option value="' + arr_w[i] + '">' + arr_w[i] + ' inch</option>'
          

      }
      $('#bic_f').html(html_e)
	  $('#bic_f').editableSelect();
	  //   第六个联动下来
	// 样式一
	var arr_l=[];
	for(var i=-0.5; i<35.5;){
		i=i+0.5;
		arr_l.push(i)
	}
	// 样式二
	var arr_li=[];
	for(var i=-1;i<89;){
		i=i+1;
		arr_li.push(i)
	}

	var html_f = "<option value='' selected></option>"
    $("#sll_o").css("display","none")
      $("#sll_t").css("display","none")
      $("#sll_f").css("display","block")
    
     
      for (var i = 0; i < arr_l.length > 0; i++) {
          html_f += '<option value="' + arr_l[i] + '">' + arr_l[i] + ' inch</option>'
          

      }
      $('#sll_f').html(html_f)
	  $('#sll_f').editableSelect();
	    //   第七个联动下来
	// 样式一
	var arr_b=[];
	for(var i=-0.5; i<7.5;){
		i=i+0.5;
		arr_b.push(i)
	}
	// 样式二
	var arr_ba=[];
	for(var i=-1;i<19;){
		i=i+1;
		arr_ba.push(i)
	}

	var html_g = "<option value='' selected></option>"
    $("#heh_o").css("display","none")
      $("#heh_t").css("display","none")
      $("#heh_f").css("display","block")
    
     
      for (var i = 0; i < arr_b.length > 0; i++) {
          html_g += '<option value="' + arr_b[i] + '">' + arr_b[i] + ' inch</option>'
          

      }
      $('#heh_f').html(html_g)
	  $('#heh_f').editableSelect();
	      //   第八个联动下来
	// 样式一
	var arr_j=[];
	for(var i=21.5; i<55.5;){
		i=i+0.5;
		arr_j.push(i)
	}
	// 样式二
	var arr_ju=[];
	for(var i=54;i<149;){
		i=i+1;
		arr_ju.push(i)
	}

	var html_UU = "<option value='' selected></option>"
    $("#u_busto").css("display","none")
      $("#u_bustt").css("display","none")
      $("#u_bustf").css("display","block")
   
     
      for (var i = 0; i < arr_j.length > 0; i++) {
          html_UU += '<option value="' + arr_j[i] + '">' + arr_j[i] + ' inch</option>'
          

	  }
	 
      $('#u_bustf').html(html_UU)
	  $('#u_bustf').editableSelect();
	  	    //   第九个联动下来
	// 样式一
	var arr_ss=[];
	for(var i=15.5; i<27.5;){
		i=i+0.5;
		arr_ss.push(i)
	}
	// 样式二
	var arr_ssi=[];
	for(var i=39;i<69;){
		i=i+1;
		arr_ssi.push(i)
	}

	var html_ss = "<option value='' selected></option>"
    $("#to_waisto").css("display","none")
      $("#to_waistt").css("display","none")
      $("#to_waistf").css("display","block")
    
     
      for (var i = 0; i < arr_ss.length > 0; i++) {
          html_ss += '<option value="' + arr_ss[i] + '">' + arr_ss[i] + ' inch</option>'
          

      }
      $('#to_waistf').html(html_ss)
	  $('#to_waistf').editableSelect();
	    	    //   第十个联动下来
	// 样式一
	var arr_shi=[];
	for(var i=15.5; i<23.5;){
		i=i+0.5;
		arr_shi.push(i)
	}
	// 样式二
	var arr_shig=[];
	for(var i=39;i<59;){
		i=i+1;
		arr_shig.push(i)
	}

	var html_shi = "<option value='' selected></option>"
    $("#BackS_o").css("display","none")
      $("#BackS_t").css("display","none")
      $("#BackS_f").css("display","block")
    
     
      for (var i = 0; i < arr_shig.length > 0; i++) {
		html_shi += '<option value="' + arr_shig[i] + '">' + arr_shig[i] + ' inch</option>'
          

      }
      $('#BackS_f').html(html_shi)
	  $('#BackS_f').editableSelect();
	      	    //   第十一个联动下来
	// 样式一
	var arr_shiy=[];
	for(var i=15.5; i<23.5;){
		i=i+0.5;
		arr_shiy.push(i)
	}
	// 样式二
	var arr_shiyg=[];
	for(var i=39;i<59;){
		i=i+1;
		arr_shiyg.push(i)
	}

	var html_shiy = "<option value='' selected></option>"
    $("#Armu_o").css("display","none")
      $("#Armu_t").css("display","none")
      $("#Armu_f").css("display","block")
    
     
      for (var i = 0; i < arr_shiy.length > 0; i++) {
		html_shiy += '<option value="' + arr_shiy[i] + '">' + arr_shiy[i] + ' inch</option>'
          

      }
      $('#Armu_f').html(html_shiy)
	  $('#Armu_f').editableSelect();
	       	    //   第十二个联动下来
	// 样式一
	var arr_shiy=[];
	for(var i=15.5; i<23.5;){
		i=i+0.5;
		arr_shiy.push(i)
	}
	// 样式二
	var arr_shiyg=[];
	for(var i=39;i<59;){
		i=i+1;
		arr_shiyg.push(i)
	}

	var html_shie = "<option value='' selected></option>"
    $("#Armcu_o").css("display","none")
      $("#Armcu_t").css("display","none")
      $("#Armcu_f").css("display","block")
    
     
      for (var i = 0; i < arr_shiy.length > 0; i++) {
		html_shie += '<option value="' + arr_shiy[i] + '">' + arr_shiy[i] + ' inch</option>'
          

      }
      $('#Armcu_f').html(html_shie)
	  $('#Armcu_f').editableSelect();
// 事件

      $("#num_o").on("change", function () {
		  
       var _select=$("#num_o").val()
    
       var html_t = "<option value='' selected></option>"
          if (_select == 1) {
			  $("#bust_o").css("display","block")
			  $('#bust_o').attr('type','text')
			  $("#bust_t").css("display","none")
			  $("#bust_t").attr("type","hidden")
              $("#bust_f").css("display","none")
              $("#bust_f").attr("type","hidden")
             
              for (var i = 0; i < arr_t.length > 0; i++) {
                  html_t += '<option value="' + arr_t[i] + '">' + arr_t[i] + ' inch</option>'
                  $(".title_t").text("BUST(inch)")

              }
              $('#bust_o').html(html_t)
              $('#bust_o').editableSelect();
           
          } else {
             
			  $("#bust_t").css("display","block")
			  $('#bust_t').attr('type','text')
			  $("#bust_o").css("display","none")
			  $("#bust_o").attr("type","hidden")
			  $("#bust_f").css("display","none")
			  $("#bust_f").attr("type","hidden")
              for (var i = 0; i < arr_tw.length > 0; i++) {
                  html_t += '<option value="' + arr_tw[i] + '">' + arr_tw[i] + ' cm</option>'
                  $(".title_t").text("BUST(cm)")
              }
             $('#bust_t').html(html_t)
              $('#bust_t').editableSelect();
            
          }

        //   第二个
        var html_f = "<option value='' selected></option>"
        if (_select == 1) {
			$("#waist_o").css("display","block")
			$("#waist_o").attr("type","text")
			$("#waist_t").css("display","none")
			$("#waist_t").attr("type","hidden")
			$("#waist_f").css("display","none")
			$("#waist_f").attr("type","hidden")
            
           
            for (var i = 0; i < arr_f.length > 0; i++) {
                html_f += '<option value="' + arr_f[i] + '">' + arr_f[i] + ' inch</option>'
                $(".title_f").text("WAIST(inch)")

            }
            $('#waist_o').html(html_f)
            $('#waist_o').editableSelect();
         
        } else {
           
			$("#waist_t").css("display","block")
			$("#waist_t").attr("type","text")
			$("#waist_o").css("display","none")
			$("#waist_o").attr("type","hidden")
			$("#waist_f").css("display","none")
			$("#waist_f").attr("type","hidden")
            for (var i = 0; i < arr_fo.length > 0; i++) {
                html_f += '<option value="' + arr_fo[i] + '">' + arr_fo[i] + ' cm</option>'
                $(".title_f").text("WAIST(cm)")
            }
           $('#waist_t').html(html_f)
            $('#waist_t').editableSelect();
          
        }

    // 第三个
              
    var html_s = "<option value='' selected></option>"
    if (_select == 1) {
		$("#hips_o").css("display","block")
		$("#hips_o").attr("type","text")
		$("#hips_t").css("display","none")
		$("#hips_t").attr("type","hidden")
		$("#hips_f").css("display","none")
		$("#hips_f").attr("type","hidden")
        
       
        for (var i = 0; i < arr_s.length > 0; i++) {
            html_s += '<option value="' + arr_s[i] + '">' + arr_s[i] + ' inch</option>'
            $(".title_s").text("HIPS(inch)")

        }
        $('#hips_o').html(html_s)
        $('#hips_o').editableSelect();
     
    } else {
       
		$("#hips_t").css("display","block")
		$("#hips_t").attr("type","text")
		$("#hips_o").css("display","none")
		$("#hips_o").attr("type","hidden")
		$("#hips_f").css("display","none")
		$("#hips_f").attr("type","hidden")
        for (var i = 0; i < arr_si.length > 0; i++) {
            html_s += '<option value="' + arr_si[i] + '">' + arr_si[i] + ' cm</option>'
            $(".title_s").text("HIPS(cm)")
        }
       $('#hips_t').html(html_s)
        $('#hips_t').editableSelect();
      
    }
    // 第四个
    var html_n = "<option value='' selected></option>"
    if (_select == 1) {
		$("#hollow_o").css("display","block")
		$("#hollow_o").attr("type","text")
		$("#hollow_t").css("display","none")
		$("#hollow_t").attr("type","hidden")
		$("#hollow_f").css("display","none")
		$("#hollow_f").attr("type","hidden")
        
       
        for (var i = 0; i < arr_n.length > 0; i++) {
            html_n += '<option value="' + arr_n[i] + '">' + arr_n[i] + ' inch</option>'
            $(".title_n").text("HOLLOW TO FLOOR(without shoes)(inch)")

        }
        $('#hollow_o').html(html_n)
        $('#hollow_o').editableSelect();
     
    } else {
       
		$("#hollow_t").css("display","block")
		$("#hollow_t").attr("type","text")
		$("#hollow_o").css("display","none")
		$("#hollow_o").attr("type","hidden")
		$("#hollow_f").css("display","none")
		$("#hollow_f").attr("type","hidden")
        for (var i = 0; i < arr_si.length > 0; i++) {
            html_n += '<option value="' + arr_ni[i] + '">' + arr_ni[i] + ' cm</option>'
            $(".title_n").text("HOLLOW TO FLOOR(without shoes)(cm)")
        }
       $('#hollow_t').html(html_n)
        $('#hollow_t').editableSelect();
      
	}
	// 第五个
	var html_w = "<option value='' selected></option>"
    if (_select == 1) {
		$("#bic_o").css("display","block")
		$("#bic_o").attr("type","text")
		$("#bic_t").css("display","none")
		$("#bic_t").attr("type","hidden")
        $("#bic_f").css("display","none")
		$("#bic_f").attr("type","hidden")
       
        for (var i = 0; i < arr_w.length > 0; i++) {
            html_w += '<option value="' + arr_w[i] + '">' + arr_w[i] + ' inch</option>'
            $(".title_w").text("BICEPS CIRCUMFERENCE(inch)")

        }
        $('#bic_o').html(html_w)
        $('#bic_o').editableSelect();
     
    } else {
       
		$("#bic_t").css("display","block")
		$("#bic_t").attr("type","text")
		$("#bic_o").css("display","none")
		$("#bic_o").attr("type","hidden")
		$("#bic_f").css("display","none")
		$("#bic_f").attr("type","hidden")
        for (var i = 0; i < arr_wu.length > 0; i++) {
            html_w += '<option value="' + arr_wu[i] + '">' + arr_wu[i] + ' cm</option>'
            $(".title_w").text("BICEPS CIRCUMFERENCE(cm)")
        }
       $('#bic_t').html(html_w)
        $('#bic_t').editableSelect();
      
	}
	// 第六个
	var html_l = "<option value='' selected></option>"
    if (_select == 1) {
		$("#sll_o").css("display","block")
		$("#sll_o").attr("type","text")
		$("#sll_t").css("display","none")
		$("#sll_t").attr("type","hidden")
        $("#sll_f").css("display","none")
        $("#sll_f").attr("type","hidden")
       
        for (var i = 0; i < arr_l.length > 0; i++) {
            html_l += '<option value="' + arr_l[i] + '">' + arr_l[i] + ' inch</option>'
            $(".title_l").text("SLEEVE LENGTH(inch)")

        }
        $('#sll_o').html(html_l)
        $('#sll_o').editableSelect();
     
    } else {
       
		$("#sll_t").css("display","block")
		$("#sll_t").attr("type","text")
		$("#sll_o").css("display","none")
		$("#sll_o").attr("type","hidden")
		$("#sll_f").css("display","none")
		$("#sll_f").attr("type","hidden")
        for (var i = 0; i < arr_li.length > 0; i++) {
            html_l += '<option value="' + arr_li[i] + '">' + arr_li[i] + ' cm</option>'
            $(".title_l").text("SLEEVE LENGTH(cm)")
        }
       $('#sll_t').html(html_l)
        $('#sll_t').editableSelect();
	}
		// 第七个
		var html_q = "<option value='' selected></option>"
		if (_select == 1) {
			$("#heh_o").css("display","block")
			$("#heh_o").attr("type","text")
			$("#heh_t").css("display","none")
			$("#heh_t").attr("type","hidden")
			$("#heh_f").css("display","none")
			$("#heh_f").attr("type","hidden")
		   
			for (var i = 0; i < arr_b.length > 0; i++) {
				html_q += '<option value="' + arr_b[i] + '">' + arr_b[i] + ' inch</option>'
				$(".title_q").text("HEEL HEIGHT(inch)")
	
			}
			$('#heh_o').html(html_q)
			$('#heh_o').editableSelect();
		 
		} else {
		   
			$("#heh_t").css("display","block")
			$("#heh_t").attr("type","text")
			$("#heh_o").css("display","none")
			$("#heh_o").attr("type","hidden")
			$("#heh_f").css("display","none")
			$("#heh_f").attr("type","hidden")
			for (var i = 0; i < arr_ba.length > 0; i++) {
				html_q += '<option value="' + arr_ba[i] + '">' + arr_ba[i] + ' cm</option>'
				$(".title_q").text("HEEL HEIGHT(cm)")
			}
		   $('#heh_t').html(html_q)
			$('#heh_t').editableSelect();
		}
		// 第八个
		var html_b = "<option value='' selected></option>"
		if (_select == 1) {
			$("#u_busto").css("display","block")
			$("#u_busto").attr("type","text")
			$("#u_bustt").css("display","none")
			$("#u_bustt").attr("type","hidden")
			$("#u_bustf").css("display","none")
			$("#u_bustf").attr("type","hidden")
		   
			for (var i = 0; i < arr_j.length > 0; i++) {
				html_b += '<option value="' + arr_j[i] + '">' + arr_j[i] + ' inch</option>'
				$(".title_u").text("Under Bust(inch)")
	
			}
			$('#u_busto').html(html_b)
			$('#u_busto').editableSelect();
		 
		} else {
		   
			$("#u_bustt").css("display","block")
			$("#u_bustt").attr("type","text")
			$("#u_busto").css("display","none")
			$("#u_busto").attr("type","hidden")
			$("#u_bustf").css("display","none")
			$("#u_bustf").attr("type","hidden")
			for (var i = 0; i < arr_ju.length > 0; i++) {
				html_b += '<option value="' + arr_ju[i] + '">' + arr_ju[i] + ' cm</option>'
				$(".title_u").text("Under Bust(cm)")
			}
		   $('#u_bustt').html(html_b)
			$('#u_bustt').editableSelect();
		}
			// 第九个
			var html_j = "<option value='' selected></option>"
			if (_select == 1) {
				$("#to_waisto").css("display","block")
				$("#to_waisto").attr("type","text")
				$("#to_waistt").css("display","none")
				$("#to_waistt").attr("type","hidden")
				$("#to_waistf").css("display","none")
				$("#to_waistf").attr("type","hidden")
			   
				for (var i = 0; i < arr_ss.length > 0; i++) {
					html_j += '<option value="' + arr_ss[i] + '">' + arr_ss[i] + ' inch</option>'
					$(".title_to").text("Waist To Hem(inch)")
		
				}
				$('#to_waisto').html(html_j)
				$('#to_waisto').editableSelect();
			 
			} else {
			   
				$("#to_waistt").css("display","block")
				$("#to_waistt").attr("type","text")
				$("#to_waisto").css("display","none")
				$("#to_waisto").attr("type","hidden")
				$("#to_waistf").css("display","none")
				$("#to_waistf").attr("type","hidden")
				for (var i = 0; i < arr_ssi.length > 0; i++) {
					html_j += '<option value="' + arr_ssi[i] + '">' + arr_ssi[i] + ' cm</option>'
					$(".title_to").text("Waist To Hem(cm)")
				}
			   $('#to_waistt').html(html_j)
				$('#to_waistt').editableSelect();
			}
					// 第十个
					var html_sh = "<option value='' selected></option>"
					if (_select == 1) {
						$("#BackS_o").css("display","block")
						$("#BackS_o").attr("type","text")
						$("#BackS_t").css("display","none")
						$("#BackS_t").attr("type","hidden")
						$("#BackS_f").css("display","none")
						$("#BackS_f").attr("type","hidden")
					   
						for (var i = 0; i < arr_shi.length > 0; i++) {
							html_sh += '<option value="' + arr_shi[i] + '">' + arr_shi[i] + ' inch</option>'
							$(".title_bac").text("Back Shoulder Width(inch)")
				
						}
						$('#BackS_o').html(html_sh)
						$('#BackS_o').editableSelect();
					 
					} else {
					   
						$("#BackS_t").css("display","block")
						$("#BackS_t").attr("type","text")
						$("#BackS_o").css("display","none")
						$("#BackS_o").attr("type","hidden")
						$("#BackS_f").css("display","none")
						$("#BackS_f").attr("type","hidden")
						for (var i = 0; i < arr_shig.length > 0; i++) {
							html_sh += '<option value="' + arr_shig[i] + '">' + arr_shig[i] + ' cm</option>'
							$(".title_bac").text("Back Shoulder Width(cm)")
						}
					   $('#BackS_t').html(html_sh)
						$('#BackS_t').editableSelect();
					}
					// 第十一个个
					var html_sy = "<option value='' selected></option>"
					if (_select == 1) {
						$("#Armu_o").css("display","block")
						$("#Armu_o").attr("type","text")
						$("#Armu_t").css("display","none")
						$("#Armu_t").attr("type","hidden")
						$("#Armu_f").css("display","none")
						$("#Armu_f").attr("type","hidden")
					   
						for (var i = 0; i < arr_shiy.length > 0; i++) {
							html_sy += '<option value="' + arr_shiy[i] + '">' + arr_shiy[i] + ' inch</option>'
							$(".title_Armu").text("Arm Circumference(inch)")
				
						}
						$('#BackS_o').html(html_sy)
						$('#BackS_o').editableSelect();
					 
					} else {
					   
						$("#Armu_t").css("display","block")
						$("#Armu_t").attr("type","text")
						$("#Armu_o").css("display","none")
						$("#Armu_o").attr("type","hidden")
						$("#Armu_f").css("display","none")
						$("#Armu_f").attr("type","hidden")
						for (var i = 0; i < arr_shiyg.length > 0; i++) {
							html_sy += '<option value="' + arr_shiyg[i] + '">' + arr_shiyg[i] + ' cm</option>'
							$(".title_Armu").text("Arm Circumference(cm)")
						}
					   $('#Armu_t').html(html_sy)
						$('#Armu_t').editableSelect();
					}
							// 第十二个个
							var html_se = "<option value='' selected></option>"
							if (_select == 1) {
								$("#Armcu_o").css("display","block")
								$("#Armcu_o").attr("type","text")
								$("#Armcu_t").css("display","none")
								$("#Armcu_t").attr("type","hidden")
								$("#Armcu_f").css("display","none")
								$("#Armcu_f").attr("type","hidden")
							   
								for (var i = 0; i < arr_shiy.length > 0; i++) {
									html_se += '<option value="' + arr_shiy[i] + '">' + arr_shiy[i] + ' inch</option>'
									$(".title_Armcu").text("Arm Eye Circumference(inch)")
						
								}
								$('#BackS_o').html(htmhtml_sel_sy)
								$('#BackS_o').editableSelect();
							 
							} else {
							   
								$("#Armcu_t").css("display","block")
								$("#Armcu_t").attr("type","text")
								$("#Armcu_o").css("display","none")
								$("#Armcu_o").attr("type","hidden")
								$("#Armcu_f").css("display","none")
								$("#Armcu_f").attr("type","hidden")
								for (var i = 0; i < arr_shiyg.length > 0; i++) {
									html_se += '<option value="' + arr_shiyg[i] + '">' + arr_shiyg[i] + ' cm</option>'
									$(".title_Armcu").text("Arm Eye Circumference(cm)")
								}
							   $('#Armcu_t').html(html_se)
								$('#Armcu_t').editableSelect();
							}
		$(".bust input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/5778652cee0120c30d3497d2b67af1c1.png');
			$(".lo-img").css("top","80px")
		})
		$(".waist input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/847f053ce4290e96045e27fa745a00c0.png');
			$(".lo-img").css("top","80px")
		})
		$(".hips input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/b03624bbe91c6c3c550efb5aacdebbc1.png');
			$(".lo-img").css("top","80px")
		})
		$(".hollow input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/bc6d3fa3f6c937f7fcc93ec9da86dfb3.png');
			$(".lo-img").css("top","80px")
		})
		$(".bic input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/c4858813befedcf6dbe36efac00e6231.png');
			$(".lo-img").css("top","80px")
		})
		$(".sll input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/fad6917a7258bf4b187b388ca075199b.png');
			$(".lo-img").css("top","80px")
		})
		$(".heh input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/8d8a90decc5cfb85e7be364e311b739a.png');
			$(".lo-img").css("top","80px")
		})
		$(".u_bust input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/508e2dd4ab407655556f2ce639f7bf90.jpg');
			$(".lo-img").css("top","80px")
			$(".lo-img").css("width","80px")
		})
		$(".WaistTo input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/26af0f6d2e3f24ba506c7fd5e98e1e19.jpg');
			$(".lo-img").css("top","80px")
		})
		$(".BackS input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/6bb9a5c12d59f1b6b8ad9710dc440862.jpg');
			$(".lo-img").css("top","80px")
		})
		$(".Armu input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/fa6c66ed942ce6c9c22fc29c260dc8e5.jpg');
			$(".lo-img").css("top","80px")
		})
		$(".Armcu input").click(function(){
			$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/8480db817a11c5aef455926463c8f30e.jpg');
			$(".lo-img").css("top","80px")
		})
	  });
	  
	// 切换图片
	$("#num_o").on("click", function () {
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/73c0baaa1b0a24bf64fd60dd9937cc82.png');
		$(".lo-img").css("top","0px")
	})
	  $(".bust input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/5778652cee0120c30d3497d2b67af1c1.png');
		$(".lo-img").css("top","80px")
	})
	$(".waist input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/847f053ce4290e96045e27fa745a00c0.png');
		$(".lo-img").css("top","80px")
	})
	$(".hips input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/b03624bbe91c6c3c550efb5aacdebbc1.png');
		$(".lo-img").css("top","80px")
	})
	$(".hollow input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/bc6d3fa3f6c937f7fcc93ec9da86dfb3.png');
		$(".lo-img").css("top","80px")
	})
	$(".bic input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/c4858813befedcf6dbe36efac00e6231.png');
		$(".lo-img").css("top","80px")
	})
	$(".sll input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/fad6917a7258bf4b187b388ca075199b.png');
		$(".lo-img").css("top","80px")
	})
	$(".heh input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/8d8a90decc5cfb85e7be364e311b739a.png');
		$(".lo-img").css("top","80px")
	})
	$(".u_bust input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/508e2dd4ab407655556f2ce639f7bf90.jpg');
		$(".lo-img").css("top","80px")
	})
	$(".WaistTo input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/26af0f6d2e3f24ba506c7fd5e98e1e19.jpg');
		$(".lo-img").css("top","80px")
	})
	$(".BackS input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/6bb9a5c12d59f1b6b8ad9710dc440862.jpg');
		$(".lo-img").css("top","80px")
	})
	$(".Armu input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/fa6c66ed942ce6c9c22fc29c260dc8e5.jpg');
		$(".lo-img").css("top","80px")
	})
	$(".Armcu input").click(function(){
		$('.lo-img img').attr('src', 'https://cdn-image.mutantbox.com/201804/8480db817a11c5aef455926463c8f30e.jpg');
		$(".lo-img").css("top","80px")
	})
    $(".bust input").change(function(){
        var Bust = $(".bust input").val();
        
        if(Bust.indexOf('cm') ==-1 && Bust.indexOf('inch') ==-1){
            alert('Please input the unit of Bust : inch/cm .') ; return false;
        }
    })
$(".waist input").change(function(){
    var Waist = $(".waist input").val();
   
    if(Waist.indexOf('cm') ==-1 && Waist.indexOf('inch') ==-1){
        alert('Please input the unit of Waist : inch/cm .') ; return false;
    }
})
$(".hips input").change(function(){
    var Hips = $(".hips input").val();
    

        if(Hips.indexOf('cm') ==-1 && Hips.indexOf('inch') ==-1){
            alert('Please input the unit of Hips : inch/cm .') ; return false;
        }
})
$(".hollow input").change(function(){
    var Hollow = $(".hollow input").val();
        

        if(Hollow.indexOf('cm') ==-1 && Hollow.indexOf('inch') ==-1){
            alert('Please input the unit of Hollow To Floor : inch/cm .') ; return false;
        }
    
	})
	$(".bic input").change(function(){
		var Hollow = $(".bic input").val();
			
	
			if(BIC.indexOf('cm') ==-1 && BIC.indexOf('inch') ==-1){
				alert('Please input the unit of Biceps Circumfereence: inch/cm .') ; return false;
			}
		
		})
		$(".sll input").change(function(){
			var SLL = $(".sll input").val();
				
		
				if(SLL.indexOf('cm') ==-1 && SLL.indexOf('inch') ==-1){
					alert('Please input the unit of Sleeve Length: inch/cm .') ; return false;
				}
			
			})
			$(".heh input").change(function(){
				var HEH= $(".heh input").val();
					
			
					if(HEH.indexOf('cm') ==-1 && HEH.indexOf('inch') ==-1){
						alert('Please input the unit of Heel Height: inch/cm .') ; return false;
					}
				
				})
				$(".u_bust input").change(function(){
					var ubust= $(".heh input").val();
						
				
						if(ubust.indexOf('cm') ==-1 && ubust.indexOf('inch') ==-1){
							alert('Please input the unit of Under Bust: inch/cm .') ; return false;
						}
					
					})
					$(".WaistTo input").change(function(){
						var waistt= $(".WaistTo input").val();
							
					
							if(waistt.indexOf('cm') ==-1 && waistt.indexOf('inch') ==-1){
								alert('Please input the unit of Waist To Hem: inch/cm .') ; return false;
							}
						
						})
					$(".BackS input").change(function(){
						var backs= $(".BackS input").val();
								
						
							if(backs.indexOf('cm') ==-1 && backs.indexOf('inch') ==-1){
								alert('Please input the unit of Back Shoulder Width: inch/cm .') ; return false;
							}
							
						})
					$(".Armu input").change(function(){
							var aremu= $(".Armu input").val();
									
							
							if(aremu.indexOf('cm') ==-1 && aremu.indexOf('inch') ==-1){
									alert('Please input the unit of Arm Circumference: inch/cm .') ; return false;
							}
								
						})
						$(".Armcu input").change(function(){
							var aremcu= $(".Armcu input").val();
									
							
							if(aremcu.indexOf('cm') ==-1 && aremcu.indexOf('inch') ==-1){
									alert('Please input the unit of Arm Eye Circumference: inch/cm .') ; return false;
							}
								
						})

})(jQuery);

