jQuery(function($){

class Polkadot_palette {

 constructor() {
  this.is_fullscreen = PolkadotPalette.is_fullscreen==1;
  this.dir = PolkadotPalette.dir;
  this.url = PolkadotPalette.url;
  this.cid = Math.floor(Math.random()*1000000);
  this.body = null;
  this.list = null;
  this.mode_name = ['Grid','Polkadot'];
  this.mode_index = 0;
  this.menu_mode = null;
  this.menu_palette = null;
  this.menu_tags = null;
  this.docs = [];
  this.k = 0;
  this.n = 50;
  this.timer_id = 0;
  this.rows = 0;
  this.cols = 0;
  this.unit_h = 150;
  this.unit_h0 = 150;
  this.unit_w = 150;
  this.unit_w0 = 150;
  this.space = 6;
  this.cell = [];
  this.audio_format = ['mp3','ogg'];
 }

 init() {
  var o = null;
  if (this.is_fullscreen) {
   o = $('body').empty().attr({id:'polkadot_palette'});
   var menu = $('<div>').attr({id:'polkadot_palette_menu'}).append($('<span>').attr({id:'polkadot_palette_menu_palette'})).append($('<span>').attr({id:'polkadot_palette_menu_tags'})).append($('<span>').attr({id:'polkadot_palette_menu_mode'}));
   o.append(menu).append($('<div>').attr({id:'polkadot_palette_list'}));
   for (var i in this.mode_name) {
    var mode = this.mode_name[i].toLowerCase();
    var audio = $('<audio>').attr({id:'bgm_'+mode,loop:'loop',preload:'auto'});
    o.append(audio);
    for (var j in this.audio_format) {
     var audio_format = this.audio_format[j];
     var audio_url = PolkadotPalette['bgm_'+mode+'_'+audio_format];
     if (audio_url!='') audio.append($('<source>').attr({src:audio_url,type:'audio/'+audio_format}));
    }
   }
  }
  else {
   o = $('footer:last');
   var menu = $('<div>').attr({id:'polkadot_palette_menu'}).append($('<span>').attr({id:'polkadot_palette_menu_palette'})).append($('<span>').attr({id:'polkadot_palette_menu_tags'}));
   var option = $.ajax({async:false,dataType:'json',url:this.url+'?action=polkadot_palette_get_option'}).responseJSON;
   var text = '';
   if (option.is_bgm) text += '🔊';
   text += option.link_text;
   menu.append($('<a>').attr({href:'/?polkadot_palette'}).text(text));
   o.prepend($('<div>').attr({id:'polkadot_palette'}).append($('<div>').attr({id:'polkadot_palette_list'})).append(menu));
  }
  if ($('#polkadot_palette').length>0) this.body = $('#polkadot_palette');
  if ($('#polkadot_palette_list').length>0) this.list = $('#polkadot_palette_list');
  if ($('#polkadot_palette_menu_palette').length>0) this.menu_palette = $('#polkadot_palette_menu_palette');
  if ($('#polkadot_palette_menu_tags').length>0) this.menu_tags = $('#polkadot_palette_menu_tags');
  if ($('#polkadot_palette_menu_mode').length>0) this.menu_mode = $('#polkadot_palette_menu_mode');
 }

 set_menu_mode_text() {
   polkadot_palette.menu_mode.text('>'+polkadot_palette.mode_name[(polkadot_palette.mode_index+1)%polkadot_palette.mode_name.length]);
 }

 execute() {
  if (this.menu_mode!=null) {
   this.set_menu_mode_text();
   this.menu_mode.click(this.change_mode);
  }
  if (this.menu_tags!=null) this.get_tags();
  if (this.menu_palette!=null) this.get_palette();
  this.get_docs();
  this.run();
  this.play_bgm();
 }

 set_next_mode_index() {
  this.mode_index = ++this.mode_index % this.mode_name.length;
 }

 run() {
  this.shuffle();
  if (this.menu_mode!=null) this.set_menu_mode_text();
  $(window).unbind('resize');
  clearInterval(this.timer_id);
  this.list.empty();
  if (this.mode_name[this.mode_index]=='Polkadot') {
   this.body.css({background:'#222'});
   this.set_number();
   $(window).resize(function(){
    polkadot_palette.set_number();
    polkadot_palette.list.empty();
   });
   if (this.docs.length>0) this.timer_id = setInterval(this.put_images,500);
  } else {
   this.body.css({background:'#FFF'});
   $(window).resize(function(){
    polkadot_palette.list.empty();
    polkadot_palette.init_grid();
    if (polkadot_palette.docs.length>0) polkadot_palette.set();
   });
   this.init_grid();
   if (this.docs.length>0) this.set();
  }
 }

 change_mode() {
  polkadot_palette.set_next_mode_index();
  polkadot_palette.run();
  polkadot_palette.play_bgm();
 }

 shuffle() {
  var n = this.docs.length, doc, i;
  while (n) {
   i = Math.floor(Math.random()*n--);
   doc = this.docs[n];
   this.docs[n] = this.docs[i];
   this.docs[i] = doc;
  }
 }

 set_number() {
  this.n = Math.floor(this.list.height()*this.list.width()/36000);
 }

 put_images() {
  var i = 0;
  while ($('>div',this.list).length<polkadot_palette.n&&i<polkadot_palette.n/20) {
   polkadot_palette.put_image();
   i++;
  }
 }

 put_image() {
  this.k = ++this.k % this.docs.length;
  var doc = this.docs[this.k];
  var p = this.get_position(doc.meta);
  var div = $('<div>').addClass('p id-'+this.k).attr({title:doc.title}).css({background:"url('"+this.dir+doc.id+".jpg?cid="+this.cid+"')",backgroundPosition:'center',height:p.r+'px',left:p.x+'px',transform:'scale('+p.s+')',top:p.y+'px',width:p.r+'px'});
  if (doc.doc_id!=null) div.css({cursor:'pointer'}).click(function(){polkadot_palette.go_to($(this).attr('class'))});
  div.hide().delay(Math.random()*5000).fadeIn(500).delay(5000+Math.random()*15000).fadeOut(500,function(){$(this).remove()});
  this.list.append(div);
}

 get_position(meta) {
  var meta = $.parseJSON(meta);
  var s = Math.random() * .6 + .4;
  var r = meta.width<meta.height ? meta.width : meta.height;
  var x = this.list.width() * Math.random() - r * s / 2;
  var y = this.list.height() * Math.random() - r * s / 2;
  return {x:x,y:y,r:r,s:s};
 }

 get_id(str) {
  if (str==null) return '';
  return str.replace(/^.+-/,'');
 }

 go_to(id) {
  if (id!=null) {
   var doc = this.docs[this.get_id(id)];
   window.open('/?p='+doc.doc_id);
  }
 }

 init_grid() {
  var height = this.list.height() - this.space, width = this.list.width() - this.space;
  this.rows = Math.floor(height/this.unit_h0), this.cols = Math.floor(width/this.unit_w0);
  if (this.cols<1) this.cols = 1;
  if (this.rows<1) this.rows = 1;
  if (height/this.rows>width/this.cols) this.rows++;
  this.unit_h = height / this.rows;
  this.unit_w = width / this.cols;
  this.cell = new Array(this.rows*this.cols);
  this.set_square();
  this.set_hlong();
  this.set_vlong();
 }

 set() {
  var l = 0;
  var t = 0;
  var w = 0;
  var h = 0;
  for (var i=0; i<this.cell.length; i++) {
   if (this.cell[i]=='o') continue;
   l = this.space + ( i % this.cols ) * this.unit_w;
   t = this.space + Math.floor( i / this.cols ) * this.unit_h;
   h = this.unit_h - this.space, w = this.unit_w - this.space;
   if (this.cell[i]=='s') {
    h += this.unit_h, w += this.unit_w;
   }
   else if (this.cell[i]=='h') w += this.unit_w;
   else if (this.cell[i]=='v') h += this.unit_h;
   var div = $('<div>').css({height:h,left:l,top:t,width:w});
   this.list.append(div);
   div.append(this.set_image().delay(Math.random()*3000));
  }
 }

 set_image() {
  this.k = ++this.k % this.docs.length;
  var doc = this.docs[this.k];
  var image = $('<img>').addClass('id-'+this.k).attr({src:this.dir+doc.id+'.jpg?cid='+this.cid,title:doc.title});
  if (doc.doc_id!=null) image.css({cursor:'pointer'}).click(function(){polkadot_palette.go_to($(this).attr('class'))});
  image.hide().on('load',function(){
   var div = $(this).parent();
   var h0 = div.height(), w0 = div.width();
   var h = $(this).height(), w = $(this).width();
   var r = Math.min(h/h0,w/w0);
   h /= r, w /= r;
   $(this).css({left:((w0-w)/2)+'px',maxHeight:h+'px',maxWidth:w+'px',minHeight:h+'px',minWidth:w+'px',top:((h0-h)/2)+'px'});
//     $(this).polkadot_palette_fade(300); // polkadot_palette_fade in/out version
   $(this).polkadot_palette_appear(1000).delay(5000+polkadot_palette.cell.length*Math.random()*2000).polkadot_palette_disappear(1000); // slide version
  });
  return image;
 }

 set_square() {
  for (var i=0; i<this.cell.length-this.cols; i++) {
   if (i%this.cols<this.cols-1) {
    if ( this.cell[i]==null && this.cell[i+1]==null && this.cell[i+this.cols]==null && this.cell[i+this.cols+1]==null && Math.random()<0.1 ) {
     this.cell[i] = 's';
     this.cell[i+1] = 'o';
     this.cell[i+this.cols] = 'o';
     this.cell[i+this.cols+1] = 'o';
    }
   }
  }
 }

 set_hlong() {
  for (var i=0; i<this.cell.length; i++) {
   if (i%this.cols<this.cols-1) {
    if ( this.cell[i]==null && this.cell[i+1]==null && Math.random()<0.2 ) {
     this.cell[i] = 'h';
     this.cell[i+1] = 'o';
    }
   }
  }
 }

 set_vlong() {
  for (var i=0; i<this.cell.length-this.cols; i++) {
   if ( this.cell[i]==null && this.cell[i+this.cols]==null && Math.random()<0.1 ) {
    this.cell[i] = 'v';
    this.cell[i+this.cols] = 'o';
   }
  }
 }

 play_bgm() {
  if (this.bgm!=null) this.bgm.pause();
  var id = 'bgm_' + this.mode_name[this.mode_index].toLowerCase();
  var au = $('#'+id);
  if (au.length==0) return;
  this.bgm = au[0];
  this.bgm.currentTime = 0;
  this.bgm.play();
 }

 get_tags() {
  var json = $.ajax({async:false,dataType:'json',url:this.url+'?action=polkadot_palette_get_tags'}).responseJSON;
  if ( json==null || json.length==0 ) return;
  var select = $('<select>').attr({id:'polkadot_palette_menu_tags'});
  select.change(function(){
   polkadot_palette.tag_ids = $(this).val();
   polkadot_palette.get_docs();
   polkadot_palette.run();
  });
  for (var i in json) {
   var a = json[i];
   var option = $('<option>').val(a.id).text(a.name);
   select.append(option);
  }
  if (select.children().length>1) this.menu_tags.replaceWith(select);
 }

 get_palette() {
  var json = $.ajax({async:false,dataType:'json',url:this.url+'?action=polkadot_palette_get_palette'}).responseJSON;
  if ( json==null || json.length==0 ) return;
  for (var i in json) {
   var a = json[i];
   var span = $('<span>');
   if (a.class!=null) span.addClass(a.class);
   if (a.id!='') span.attr({id:'polkadot_palette_menu-'+a.id}).css({background:a.id});
   if (a.name!=null) span.text(a.name);
   this.set_color(span);
  }
 }

 set_color(span) {
  span.click(function(){
   $('>span',$(this).parent()).removeClass('on');
   $(this).addClass('on');
   polkadot_palette.color = polkadot_palette.get_id($(this).attr('id'));
   polkadot_palette.get_docs();
   polkadot_palette.run();
  });
  this.menu_palette.append(span);
 }

 get_docs() {
  var json = $.ajax({async:false,data:{tag_ids:this.tag_ids,color:this.color},dataType:'json',url:this.url+'?action=polkadot_palette_get_docs'}).responseJSON;
  this.docs = json.docs;
 }

 is_mobile() {
  return window.navigator.userAgent.toLowerCase().indexOf('mobile')!=-1;
 }

}

$.fn.polkadot_palette_fade = function(dur) {
 $(this).fadeIn(300).delay(3000+Math.random()*120000).fadeOut(300,function(){$(this).parent().empty().append(polkadot_palette.set_image())});
 return $(this);
}

$.fn.polkadot_palette_appear = function(dur) {
 var l0 = parseInt($(this).css('left')), t0 = parseInt($(this).css('top'));
 var l = l0, t = t0;
 var r = Math.random();
 if (r>0.75) t -= 100;
 else if (r>0.5) l += 100;
 else if (r>0.25) t += 100;
 else l -= 100;
 $(this).css({left:l,top:t,zIndex:0}).animate({left:l0,opacity:'toggle',top:t0,zIndex:1},dur,'linear');
 return $(this);
}

$.fn.polkadot_palette_disappear = function(dur) {
 var l = t = '+=0px';
 var r = Math.random();
 if (r>0.75) t = '-=100px';
 else if (r>0.5) l = '+=100px';
 else if (r>0.25) t = '+=100px';
 else l = '-=100px';
 $(this).animate({left:l,opacity:'toggle',top:t,zIndex:0},dur,'linear',function(){
  var div = $(this).parent();
  div.empty().append(polkadot_palette.set_image());
 });
 return $(this);
}

var polkadot_palette = new Polkadot_palette();
polkadot_palette.init();
polkadot_palette.execute();

});
