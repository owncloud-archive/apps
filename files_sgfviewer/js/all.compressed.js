Array.prototype.contains=function(_1){
if(Array.prototype.indexOf){
return this.indexOf(_1)!=-1;
}
for(var i in this){
if(this[i]==_1){
return true;
}
}
return false;
};
Array.prototype.setLength=function(_3,_4){
_4=typeof _4!="undefined"?_4:null;
for(var i=0;i<_3;i++){
this[i]=_4;
}
return this;
};
Array.prototype.addDimension=function(_6,_7){
_7=typeof _7!="undefined"?_7:null;
var _8=this.length;
for(var i=0;i<_8;i++){
this[i]=[].setLength(_6,_7);
}
return this;
};
Array.prototype.first=function(){
return this[0];
};
Array.prototype.last=function(){
return this[this.length-1];
};
Array.prototype.copy=function(){
var _a=[];
var _b=this.length;
for(var i=0;i<_b;i++){
if(this[i] instanceof Array){
_a[i]=this[i].copy();
}else{
_a[i]=this[i];
}
}
return _a;
};
if(!Array.prototype.map){
Array.prototype.map=function(_d){
var _e=this.length;
if(typeof _d!="function"){
throw new TypeError();
}
var _f=new Array(_e);
var _10=arguments[1];
for(var i=0;i<_e;i++){
if(i in this){
_f[i]=_d.call(_10,this[i],i,this);
}
}
return _f;
};
}
if(!Array.prototype.filter){
Array.prototype.filter=function(fun){
var len=this.length;
if(typeof fun!="function"){
throw new TypeError();
}
var res=new Array();
var _15=arguments[1];
for(var i=0;i<len;i++){
if(i in this){
var val=this[i];
if(fun.call(_15,val,i,this)){
res.push(val);
}
}
}
return res;
};
}
if(!Array.prototype.forEach){
Array.prototype.forEach=function(fun){
var len=this.length;
if(typeof fun!="function"){
throw new TypeError();
}
var _1a=arguments[1];
for(var i=0;i<len;i++){
if(i in this){
fun.call(_1a,this[i],i,this);
}
}
};
}
if(!Array.prototype.every){
Array.prototype.every=function(fun){
var len=this.length;
if(typeof fun!="function"){
throw new TypeError();
}
var _1e=arguments[1];
for(var i=0;i<len;i++){
if(i in this&&!fun.call(_1e,this[i],i,this)){
return false;
}
}
return true;
};
}
if(!Array.prototype.some){
Array.prototype.some=function(fun){
var len=this.length;
if(typeof fun!="function"){
throw new TypeError();
}
var _22=arguments[1];
for(var i=0;i<len;i++){
if(i in this&&fun.call(_22,this[i],i,this)){
return true;
}
}
return false;
};
}
Array.from=function(it){
var arr=[];
for(var i=0;i<it.length;i++){
arr[i]=it[i];
}
return arr;
};
Function.prototype.bind=function(_27){
var _28=this;
var _29=Array.from(arguments).slice(1);
return function(){
return _28.apply(_27,_29.concat(Array.from(arguments)));
};
};

eidogo=window.eidogo||{};

(function(){
var ua=navigator.userAgent.toLowerCase();
var _2=(ua.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[])[1];
eidogo.browser={ua:ua,ver:_2,ie:/msie/.test(ua)&&!/opera/.test(ua),moz:/mozilla/.test(ua)&&!/(compatible|webkit)/.test(ua),safari3:/webkit/.test(ua)&&parseInt(_2,10)>=420};
eidogo.util={byId:function(id){
return document.getElementById(id);
},makeQueryString:function(_4){
var qs="";
if(_4&&typeof _4=="object"){
var _6=[];
for(var _7 in _4){
if(_4[_7]&&_4[_7].constructor==Array){
for(var i=0;i<_4[_7].length;i++){
_6.push(encodeURIComponent(_7)+"="+encodeURIComponent(_4[_7]));
}
}else{
_6.push(encodeURIComponent(_7)+"="+encodeURIComponent(_4[_7]));
}
}
qs=_6.join("&").replace(/%20/g,"+");
}
return qs;
},ajax:function(_9,_a,_b,_c,_d,_e,_f){
_9=_9.toUpperCase();
var xhr=window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();
var qs=(_b&&typeof _b=="object"?eidogo.util.makeQueryString(_b):null);
if(qs&&_9=="GET"){
_a+=(_a.match(/\?/)?"&":"?")+qs;
qs=null;
}
xhr.open(_9,_a,true);
if(qs){
xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
}
var _12=false;
var _13=/webkit/.test(navigator.userAgent.toLowerCase());
function httpSuccess(r){
try{
return !r.status&&location.protocol=="file:"||(r.status>=200&&r.status<300)||r.status==304||_13&&r.status==undefined;
}
catch(e){
}
return false;
}
function handleReadyState(_15){
if(!_12&&xhr&&(xhr.readyState==4||_15=="timeout")){
_12=true;
if(_16){
clearInterval(_16);
_16=null;
}
var _17=_15=="timeout"&&"timeout"||!httpSuccess(xhr)&&"error"||"success";
if(_17=="success"){
_c.call(_e,xhr);
}else{
_d.call(_e);
}
xhr=null;
}
}
var _16=setInterval(handleReadyState,13);
if(_f){
setTimeout(function(){
if(xhr){
xhr.abort();
if(!_12){
handleReadyState("timeout");
}
}
},_f);
}
xhr.send(qs);
return xhr;
},addEventHelper:function(_18,_19,_1a){
if(_18.addEventListener){
_18.addEventListener(_19,_1a,false);
}else{
if(!eidogo.util.addEventId){
eidogo.util.addEventId=1;
}
if(!_1a.$$guid){
_1a.$$guid=eidogo.util.addEventId++;
}
if(!_18.events){
_18.events={};
}
var _1b=_18.events[_19];
if(!_1b){
_1b=_18.events[_19]={};
if(_18["on"+_19]){
_1b[0]=_18["on"+_19];
}
}
_1b[_1a.$$guid]=_1a;
_18["on"+_19]=eidogo.util.handleEvent;
}
},handleEvent:function(_1c){
var _1d=true;
_1c=_1c||((this.ownerDocument||this.document||this).parentWindow||window).event;
var _1e=this.events[_1c.type];
for(var i in _1e){
this.$$handleEvent=_1e[i];
if(this.$$handleEvent(_1c)===false){
_1d=false;
}
}
return _1d;
},addEvent:function(el,_21,_22,arg,_24){
if(!el){
return;
}
if(_24){
_22=_22.bind(arg);
}else{
if(arg){
var _25=_22;
_22=function(e){
_25(e,arg);
};
}
}
eidogo.util.addEventHelper(el,_21,_22);
},onClick:function(el,_28,_29){
eidogo.util.addEvent(el,"click",_28,_29,true);
},getElClickXY:function(e,el,_2c){
if(!e.pageX){
e.pageX=e.clientX+(document.documentElement.scrollLeft||document.body.scrollLeft);
e.pageY=e.clientY+(document.documentElement.scrollTop||document.body.scrollTop);
}
var _2d=eidogo.util.getElXY(el,_2c);
return [e.pageX-_2d[0],e.pageY-_2d[1]];
},stopEvent:function(e){
if(!e){
return;
}
if(e.stopPropagation){
e.stopPropagation();
}else{
e.cancelBubble=true;
}
if(e.preventDefault){
e.preventDefault();
}else{
e.returnValue=false;
}
},getTarget:function(ev){
var t=ev.target||ev.srcElement;
return (t&&t.nodeName&&t.nodeName.toUpperCase()=="#TEXT")?t.parentNode:t;
},addClass:function(el,cls){
if(!cls){
return;
}
var ca=cls.split(/\s+/);
for(var i=0;i<ca.length;i++){
if(!eidogo.util.hasClass(el,ca[i])){
el.className+=(el.className?" ":"")+ca[i];
}
}
},removeClass:function(el,cls){
var ca=el.className.split(/\s+/);
var nc=[];
for(var i=0;i<ca.length;i++){
if(ca[i]!=cls){
nc.push(ca[i]);
}
}
el.className=nc.join(" ");
},hasClass:function(el,cls){
var ca=el.className.split(/\s+/);
for(var i=0;i<ca.length;i++){
if(ca[i]==cls){
return true;
}
}
return false;
},show:function(el,_3f){
_3f=_3f||"block";
if(typeof el=="string"){
el=eidogo.util.byId(el);
}
if(!el){
return;
}
el.style.display=_3f;
},hide:function(el){
if(typeof el=="string"){
el=eidogo.util.byId(el);
}
if(!el){
return;
}
el.style.display="none";
},getElXY:function(el,_42){
var _43=el,elX=0,elY=0,_46=el.parentNode,sx=0,sy=0;
while(_43){
elX+=_43.offsetLeft;
elY+=_43.offsetTop;
_43=_43.offsetParent?_43.offsetParent:null;
}
while(!_42&&_46&&_46.tagName&&!/^body|html$/i.test(_46.tagName)){
sx+=_46.scrollLeft;
sy+=_46.scrollTop;
elX-=_46.scrollLeft;
elY-=_46.scrollTop;
_46=_46.parentNode;
}
return [elX,elY,sx,sy];
},getElX:function(el){
return this.getElXY(el)[0];
},getElY:function(el){
return this.getElXY(el)[1];
},addStyleSheet:function(_4b){
if(document.createStyleSheet){
document.createStyleSheet(_4b);
}else{
var _4c=document.createElement("link");
_4c.rel="stylesheet";
_4c.type="text/css";
_4c.href=_4b;
document.getElementsByTagName("head")[0].appendChild(_4c);
}
},getPlayerPath:function(){
var _4d=document.getElementsByTagName("script");
var _4e;
var _4f;
for(var i=0;_4f=_4d[i];i++){
if(/(all\.compressed\.js|eidogo\.js)/.test(_4f.src)){
_4e=_4f.src.replace(/\/js\/[^\/]+$/,"");
}
}
return _4e;
},numProperties:function(obj){
var _52=0;
for(var i in obj){
_52++;
}
return _52;
}};
})();

eidogo=window.eidogo||{};
eidogo.i18n=eidogo.i18n||{"move":"Move","loading":"Loading","passed":"passed","resigned":"resigned","variations":"Variations","no variations":"none","tool":"Tool","view":"Jump to Move","play":"Play","region":"Select Region","add_b":"Black Stone","add_w":"White Stone","edit comment":"Edit Comment","edit game info":"Edit Game Info","done":"Done","triangle":"Triangle","square":"Square","circle":"Circle","x":"X","letter":"Letter","number":"Number","dim":"Dim","clear":"Clear Marker","score":"Score","score est":"Score Estimate","search":"Search","search corner":"Corner Search","search center":"Center Search","region info":"Click and drag to select a region.","two stones":"Please select at least two stones to search for.","two edges":"For corner searches, your selection must touch two adjacent edges of the board.","no search url":"No search URL provided.","close search":"close search","matches found":"matches found.","show games":"Show pro games with this position","save to server":"Save to Server","download sgf":"Download SGF","next game":"Next Game","previous game":"Previous Game","end of variation":"End of variation","white":"White","white rank":"White rank","white team":"White team","black":"Black","black rank":"Black rank","black team":"Black team","captures":"captures","time left":"time left","you":"You","game":"Game","handicap":"Handicap","komi":"Komi","result":"Result","date":"Date","info":"Info","place":"Place","event":"Event","round":"Round","overtime":"Overtime","opening":"Openning","ruleset":"Ruleset","annotator":"Annotator","copyright":"Copyright","source":"Source","time limit":"Time limit","transcriber":"Transcriber","created with":"Created with","january":"January","february":"February","march":"March","april":"April","may":"May","june":"June","july":"July","august":"August","september":"September","october":"October","november":"November","december":"December","gw":"Good for White","vgw":"Very good for White","gb":"Good for Black","vgb":"Very good for Black","dm":"Even position","dmj":"Even position (joseki)","uc":"Unclear position","te":"Tesuji","bm":"Bad move","vbm":"Very bad move","do":"Doubtful move","it":"Interesting move","black to play":"Black to play","white to play":"White to play","ho":"Hotspot","confirm delete":"You've removed all properties from this position.\n\nDelete this position and all sub-positions?","position deleted":"Position deleted","dom error":"Error finding DOM container","error retrieving":"There was a problem retrieving the game data.","invalid data":"Received invalid game data","error board":"Error loading board container","unsaved changes":"There are unsaved changes in this game. You must save before you can permalink or download.","bad path":"Don't know how to get to path: ","gnugo thinking":"GNU Go is thinking..."};

eidogo.gameNodeIdCounter=100000;
eidogo.GameNode=function(){
this.init.apply(this,arguments);
};
eidogo.GameNode.prototype={init:function(_1,_2,id){
this._id=(typeof id!="undefined"?id:eidogo.gameNodeIdCounter++);
this._parent=_1||null;
this._children=[];
this._preferredChild=0;
if(_2){
this.loadJson(_2);
}
},pushProperty:function(_4,_5){
if(this[_4]){
if(!(this[_4] instanceof Array)){
this[_4]=[this[_4]];
}
if(!this[_4].contains(_5)){
this[_4].push(_5);
}
}else{
this[_4]=_5;
}
},hasPropertyValue:function(_6,_7){
if(!this[_6]){
return false;
}
var _8=(this[_6] instanceof Array?this[_6]:[this[_6]]);
return _8.contains(_7);
},deletePropertyValue:function(_9,_a){
var _b=(_a instanceof RegExp)?function(v){
return _a.test(v);
}:function(v){
return _a==v;
};
var _e=(_9 instanceof Array?_9:[_9]);
for(var i=0;_9=_e[i];i++){
if(this[_9] instanceof Array){
this[_9]=this[_9].filter(function(v){
return !_b(v);
});
if(!this[_9].length){
delete this[_9];
}
}else{
if(_b(this.prop)){
delete this[_9];
}
}
}
},loadJson:function(_11){
var _12=[_11],_13=[this];
var _14,_15;
var i,len;
while(_12.length){
_14=_12.pop();
_15=_13.pop();
_15.loadJsonNode(_14);
len=(_14._children?_14._children.length:0);
for(i=0;i<len;i++){
_12.push(_14._children[i]);
if(!_15._children[i]){
_15._children[i]=new eidogo.GameNode(_15);
}
_13.push(_15._children[i]);
}
}
},loadJsonNode:function(_18){
for(var _19 in _18){
if(_19=="_id"){
this[_19]=_18[_19].toString();
eidogo.gameNodeIdCounter=Math.max(eidogo.gameNodeIdCounter,parseInt(_18[_19],10));
continue;
}
if(_19.charAt(0)!="_"){
this[_19]=_18[_19];
}
}
},appendChild:function(_1a){
_1a._parent=this;
this._children.push(_1a);
},getProperties:function(){
var _1b={},_1c,_1d,_1e,_1f;
for(_1c in this){
isPrivate=(_1c.charAt(0)=="_");
_1e=(typeof this[_1c]=="string");
_1f=(this[_1c] instanceof Array);
if(!isPrivate&&(_1e||_1f)){
_1b[_1c]=this[_1c];
}
}
return _1b;
},walk:function(fn){
var _21=[this];
var _22;
var i,len;
while(_21.length){
_22=_21.pop();
fn(_22);
len=(_22._children?_22._children.length:0);
for(i=0;i<len;i++){
_21.push(_22._children[i]);
}
}
},getMove:function(){
if(typeof this.W!="undefined"){
return this.W;
}else{
if(typeof this.B!="undefined"){
return this.B;
}
}
return null;
},emptyPoint:function(_25){
var _26=this.getProperties();
var _27=null;
for(var _28 in _26){
if(_28=="AW"||_28=="AB"||_28=="AE"){
if(!(this[_28] instanceof Array)){
this[_28]=[this[_28]];
}
this[_28]=this[_28].filter(function(val){
if(val==_25){
_27=val;
return false;
}
return true;
});
if(!this[_28].length){
delete this[_28];
}
}else{
if((_28=="B"||_28=="W")&&this[_28]==_25){
_27=this[_28];
delete this[_28];
}
}
}
return _27;
},getPosition:function(){
if(!this._parent){
return null;
}
var _2a=this._parent._children;
for(var i=0;i<_2a.length;i++){
if(_2a[i]._id==this._id){
return i;
}
}
return null;
},toSgf:function(){
var sgf=(this._parent?"(":"");
var _2d=this;
function propsToSgf(_2e){
if(!_2e){
return "";
}
var sgf=";",key,val;
for(key in _2e){
if(_2e[key] instanceof Array){
val=_2e[key].map(function(val){
return val.toString().replace(/\]/g,"\\]");
}).join("][");
}else{
val=_2e[key].toString().replace(/\]/g,"\\]");
}
sgf+=key+"["+val+"]";
}
return sgf;
}
sgf+=propsToSgf(_2d.getProperties());
while(_2d._children.length==1){
_2d=_2d._children[0];
sgf+=propsToSgf(_2d.getProperties());
}
for(var i=0;i<_2d._children.length;i++){
sgf+=_2d._children[i].toSgf();
}
sgf+=(this._parent?")":"");
return sgf;
}};
eidogo.GameCursor=function(){
this.init.apply(this,arguments);
};
eidogo.GameCursor.prototype={init:function(_34){
this.node=_34;
},next:function(_35){
if(!this.hasNext()){
return false;
}
_35=(typeof _35=="undefined"||_35==null?this.node._preferredChild:_35);
this.node._preferredChild=_35;
this.node=this.node._children[_35];
return true;
},previous:function(){
if(!this.hasPrevious()){
return false;
}
this.node=this.node._parent;
return true;
},hasNext:function(){
return this.node&&this.node._children.length;
},hasPrevious:function(){
return this.node&&this.node._parent&&this.node._parent._parent;
},getNextMoves:function(){
if(!this.hasNext()){
return null;
}
var _36={};
var i,_38;
for(i=0;_38=this.node._children[i];i++){
_36[_38.getMove()]=i;
}
return _36;
},getNextColor:function(){
if(!this.hasNext()){
return null;
}
var i,_3a;
for(var i=0;_3a=this.node._children[i];i++){
if(_3a.W||_3a.B){
return _3a.W?"W":"B";
}
}
return null;
},getNextNodeWithVariations:function(){
var _3b=this.node;
while(_3b._children.length==1){
_3b=_3b._children[0];
}
return _3b;
},getPath:function(){
var _3c=[];
var cur=new eidogo.GameCursor(this.node);
var mn=(cur.node._parent&&cur.node._parent._parent?-1:null);
var _3f;
do{
_3f=cur.node;
cur.previous();
if(mn!=null){
mn++;
}
}while(cur.hasPrevious()&&cur.node._children.length==1);
if(mn!=null){
_3c.push(mn);
}
_3c.push(_3f.getPosition());
do{
if(cur.node._children.length>1||cur.node._parent._parent==null){
_3c.push(cur.node.getPosition());
}
}while(cur.previous());
return _3c.reverse();
},getPathMoves:function(){
var _40=[];
var cur=new eidogo.GameCursor(this.node);
_40.push(cur.node.getMove());
while(cur.previous()){
var _42=cur.node.getMove();
if(_42){
_40.push(_42);
}
}
return _40.reverse();
},getMoveNumber:function(){
var num=0,_44=this.node;
while(_44){
if(_44.W||_44.B){
num++;
}
_44=_44._parent;
}
return num;
},getGameRoot:function(){
if(!this.node){
return null;
}
var cur=new eidogo.GameCursor(this.node);
if(!this.node._parent&&this.node._children.length){
return this.node._children[0];
}
while(cur.previous()){
}
return cur.node;
}};

eidogo.SgfParser=function(){
this.init.apply(this,arguments);
};
eidogo.SgfParser.prototype={init:function(_1,_2){
_2=(typeof _2=="function")?_2:null;
this.sgf=_1;
this.index=0;
this.root={_children:[]};
this.parseTree(this.root);
_2&&_2.call(this);
},parseTree:function(_3){
while(this.index<this.sgf.length){
var c=this.curChar();
this.index++;
switch(c){
case ";":
_3=this.parseNode(_3);
break;
case "(":
this.parseTree(_3);
break;
case ")":
return;
break;
}
}
},parseNode:function(_5){
var _6={_children:[]};
if(_5){
_5._children.push(_6);
}else{
this.root=_6;
}
_6=this.parseProperties(_6);
return _6;
},parseProperties:function(_7){
var _8="";
var _9=[];
var i=0;
while(this.index<this.sgf.length){
var c=this.curChar();
if(c==";"||c=="("||c==")"){
break;
}
if(this.curChar()=="["){
while(this.curChar()=="["){
this.index++;
_9[i]="";
while(this.curChar()!="]"&&this.index<this.sgf.length){
if(this.curChar()=="\\"){
this.index++;
while(this.curChar()=="\r"||this.curChar()=="\n"){
this.index++;
}
}
_9[i]+=this.curChar();
this.index++;
}
i++;
while(this.curChar()=="]"||this.curChar()=="\n"||this.curChar()=="\r"){
this.index++;
}
}
if(_7[_8]){
if(!(_7[_8] instanceof Array)){
_7[_8]=[_7[_8]];
}
_7[_8]=_7[_8].concat(_9);
}else{
_7[_8]=_9.length>1?_9:_9[0];
}
_8="";
_9=[];
i=0;
continue;
}
if(c!=" "&&c!="\n"&&c!="\r"&&c!="\t"){
_8+=c;
}
this.index++;
}
return _7;
},curChar:function(){
return this.sgf.charAt(this.index);
}};

eidogo.Board=function(){
this.init.apply(this,arguments);
};
eidogo.Board.prototype={WHITE:1,BLACK:-1,EMPTY:0,init:function(_1,_2){
this.boardSize=_2||19;
this.stones=this.makeBoardArray(this.EMPTY);
this.markers=this.makeBoardArray(this.EMPTY);
this.captures={};
this.captures.W=0;
this.captures.B=0;
this.cache=[];
this.renderer=_1||new eidogo.BoardRendererHtml();
this.lastRender={stones:this.makeBoardArray(null),markers:this.makeBoardArray(null)};
},reset:function(){
this.init(this.renderer,this.boardSize);
},clear:function(){
this.clearStones();
this.clearMarkers();
this.clearCaptures();
},clearStones:function(){
for(var i=0;i<this.stones.length;i++){
this.stones[i]=this.EMPTY;
}
},clearMarkers:function(){
for(var i=0;i<this.markers.length;i++){
this.markers[i]=this.EMPTY;
}
},clearCaptures:function(){
this.captures.W=0;
this.captures.B=0;
},makeBoardArray:function(_5){
return [].setLength(this.boardSize*this.boardSize,_5);
},commit:function(){
this.cache.push({stones:this.stones.concat(),captures:{W:this.captures.W,B:this.captures.B}});
},rollback:function(){
if(this.cache.last()){
this.stones=this.cache.last().stones.concat();
this.captures.W=this.cache.last().captures.W;
this.captures.B=this.cache.last().captures.B;
}else{
this.clear();
}
},revert:function(_6){
_6=_6||1;
this.rollback();
for(var i=0;i<_6;i++){
this.cache.pop();
}
this.rollback();
},addStone:function(pt,_9){
this.stones[pt.y*this.boardSize+pt.x]=_9;
},getStone:function(pt){
return this.stones[pt.y*this.boardSize+pt.x];
},getRegion:function(t,l,w,h){
var _f=[].setLength(w*h,this.EMPTY);
var _10;
for(var y=t;y<t+h;y++){
for(var x=l;x<l+w;x++){
_10=(y-t)*w+(x-l);
_f[_10]=this.getStone({x:x,y:y});
}
}
return _f;
},addMarker:function(pt,_14){
this.markers[pt.y*this.boardSize+pt.x]=_14;
},getMarker:function(pt){
return this.markers[pt.y*this.boardSize+pt.x];
},render:function(_16){
var _17=this.makeBoardArray(null);
var _18=this.makeBoardArray(null);
var _19,_1a;
var len;
if(!_16&&this.cache.last()){
var _1c=this.cache.last();
len=this.stones.length;
for(var i=0;i<len;i++){
if(_1c.stones[i]!=this.lastRender.stones[i]){
_17[i]=_1c.stones[i];
}
}
_18=this.markers;
}else{
_17=this.stones;
_18=this.markers;
}
var _1e;
for(var x=0;x<this.boardSize;x++){
for(var y=0;y<this.boardSize;y++){
_1e=y*this.boardSize+x;
if(_18[_1e]!=null){
this.renderer.renderMarker({x:x,y:y},_18[_1e]);
this.lastRender.markers[_1e]=_18[_1e];
}
if(_17[_1e]==null){
continue;
}else{
if(_17[_1e]==this.EMPTY){
_19="empty";
}else{
_19=(_17[_1e]==this.WHITE?"white":"black");
}
}
this.renderer.renderStone({x:x,y:y},_19);
this.lastRender.stones[_1e]=_17[_1e];
}
}
}};
eidogo.BoardRendererHtml=function(){
this.init.apply(this,arguments);
};
eidogo.BoardRendererHtml.prototype={init:function(_21,_22,_23,_24){
if(!_21){
throw "No DOM container";
return;
}
this.boardSize=_22||19;
var _25=document.createElement("div");
_25.className="board-gutter"+(this.boardSize==19?" with-coords":"");
_21.appendChild(_25);
var _26=document.createElement("div");
_26.className="board size"+this.boardSize;
_26.style.position=(_24&&eidogo.browser.ie?"static":"relative");
_25.appendChild(_26);
this.domNode=_26;
this.domGutter=_25;
this.domContainer=_21;
this.player=_23;
this.uniq=_21.id+"-";
this.renderCache={stones:[].setLength(this.boardSize,0).addDimension(this.boardSize,0),markers:[].setLength(this.boardSize,0).addDimension(this.boardSize,0)};
this.pointWidth=0;
this.pointHeight=0;
this.margin=0;
var _27=this.renderStone({x:0,y:0},"black");
this.pointWidth=this.pointHeight=_27.offsetWidth;
this.renderStone({x:0,y:0},"white");
this.renderMarker({x:0,y:0},"current");
this.clear();
this.margin=(this.domNode.offsetWidth-(this.boardSize*this.pointWidth))/2;
this.scrollX=0;
this.scrollY=0;
if(_24){
this.crop(_24);
if(eidogo.browser.ie){
var _28=this.domNode.parentNode;
while(_28&&_28.tagName&&!/^body|html$/i.test(_28.tagName)){
this.scrollX+=_28.scrollLeft;
this.scrollY+=_28.scrollTop;
_28=_28.parentNode;
}
}
}
this.dom={};
this.dom.searchRegion=document.createElement("div");
this.dom.searchRegion.id=this.uniq+"search-region";
this.dom.searchRegion.className="search-region";
this.domNode.appendChild(this.dom.searchRegion);
eidogo.util.addEvent(this.domNode,"mousemove",this.handleHover,this,true);
eidogo.util.addEvent(this.domNode,"mousedown",this.handleMouseDown,this,true);
eidogo.util.addEvent(this.domNode,"mouseup",this.handleMouseUp,this,true);
},showRegion:function(_29){
this.dom.searchRegion.style.top=(this.margin+this.pointHeight*_29[0])+"px";
this.dom.searchRegion.style.left=(this.margin+this.pointWidth*_29[1])+"px";
this.dom.searchRegion.style.width=this.pointWidth*_29[2]+"px";
this.dom.searchRegion.style.height=this.pointHeight*_29[3]+"px";
eidogo.util.show(this.dom.searchRegion);
},hideRegion:function(){
eidogo.util.hide(this.dom.searchRegion);
},clear:function(){
this.domNode.innerHTML="";
},renderStone:function(pt,_2b){
var _2c=document.getElementById(this.uniq+"stone-"+pt.x+"-"+pt.y);
if(_2c){
_2c.parentNode.removeChild(_2c);
}
if(_2b!="empty"){
var div=document.createElement("div");
div.id=this.uniq+"stone-"+pt.x+"-"+pt.y;
div.className="point stone "+_2b;
try{
div.style.left=(pt.x*this.pointWidth+this.margin-this.scrollX)+"px";
div.style.top=(pt.y*this.pointHeight+this.margin-this.scrollY)+"px";
}
catch(e){
}
this.domNode.appendChild(div);
return div;
}
return null;
},renderMarker:function(pt,_2f){
if(this.renderCache.markers[pt.x][pt.y]){
var _30=document.getElementById(this.uniq+"marker-"+pt.x+"-"+pt.y);
if(_30){
_30.parentNode.removeChild(_30);
}
}
if(_2f=="empty"||!_2f){
this.renderCache.markers[pt.x][pt.y]=0;
return null;
}
this.renderCache.markers[pt.x][pt.y]=1;
if(_2f){
var _31="";
switch(_2f){
case "triangle":
case "square":
case "circle":
case "ex":
case "territory-white":
case "territory-black":
case "dim":
case "current":
break;
default:
if(_2f.indexOf("var:")==0){
_31=_2f.substring(4);
_2f="variation";
}else{
_31=_2f;
_2f="label";
}
break;
}
var div=document.createElement("div");
div.id=this.uniq+"marker-"+pt.x+"-"+pt.y;
div.className="point marker "+_2f;
try{
div.style.left=(pt.x*this.pointWidth+this.margin-this.scrollX)+"px";
div.style.top=(pt.y*this.pointHeight+this.margin-this.scrollY)+"px";
}
catch(e){
}
div.appendChild(document.createTextNode(_31));
this.domNode.appendChild(div);
return div;
}
return null;
},setCursor:function(_33){
this.domNode.style.cursor=_33;
},handleHover:function(e){
var xy=this.getXY(e);
this.player.handleBoardHover(xy[0],xy[1]);
},handleMouseDown:function(e){
var xy=this.getXY(e);
this.player.handleBoardMouseDown(xy[0],xy[1]);
},handleMouseUp:function(e){
var xy=this.getXY(e);
this.player.handleBoardMouseUp(xy[0],xy[1]);
},getXY:function(e){
var _3b=eidogo.util.getElClickXY(e,this.domNode);
var m=this.margin;
var pw=this.pointWidth;
var ph=this.pointHeight;
var x=Math.round((_3b[0]-m-(pw/2))/pw);
var y=Math.round((_3b[1]-m-(ph/2))/ph);
return [x,y];
},crop:function(_41){
eidogo.util.addClass(this.domContainer,"shrunk");
this.domGutter.style.overflow="hidden";
var _42=_41.width*this.pointWidth+this.margin;
var _43=_41.height*this.pointHeight+this.margin;
this.domGutter.style.width=_42+"px";
this.domGutter.style.height=_43+"px";
this.player.dom.player.style.width=_42+"px";
this.domGutter.scrollLeft=_41.left*this.pointWidth;
this.domGutter.scrollTop=_41.top*this.pointHeight;
}};
eidogo.BoardRendererFlash=function(){
this.init.apply(this,arguments);
};
eidogo.BoardRendererFlash.prototype={init:function(_44,_45,_46,_47){
if(!_44){
throw "No DOM container";
return;
}
this.ready=false;
this.swf=null;
this.unrendered=[];
var _48=_44.id+"-board";
var so=new SWFObject(eidogo.playerPath+"/swf/board.swf",_48,"421","421","8","#665544");
so.addParam("allowScriptAccess","sameDomain");
so.write(_44);
var _4a=0;
var _4b=function(){
swf=eidogo.util.byId(_48);
if(!swf||!swf.init){
if(_4a>2000){
throw "Error initializing board";
return;
}
setTimeout(arguments.callee.bind(this),10);
_4a+=10;
return;
}
this.swf=swf;
this.swf.init(_46.uniq,_45);
this.ready=true;
}.bind(this);
_4b();
},showRegion:function(_4c){
},hideRegion:function(){
},clear:function(){
if(!this.swf){
return;
}
this.swf.clear();
},renderStone:function(pt,_4e){
if(!this.swf){
this.unrendered.push(["stone",pt,_4e]);
return;
}
for(var i=0;i<this.unrendered.length;i++){
if(this.unrendered[i][0]=="stone"){
this.swf.renderStone(this.unrendered[i][1],this.unrendered[i][2]);
}
}
this.unrendered=[];
this.swf.renderStone(pt,_4e);
},renderMarker:function(pt,_51){
if(!_51){
return;
}
if(!this.swf){
this.unrendered.push(["marker",pt,_51]);
return;
}
for(var i=0;i<this.unrendered.length;i++){
if(this.unrendered[i][0]=="marker"){
this.swf.renderMarker(this.unrendered[i][1],this.unrendered[i][2]);
}
}
this.unrendered=[];
this.swf.renderMarker(pt,_51);
},setCursor:function(_53){
},crop:function(){
}};
eidogo.BoardRendererAscii=function(_54,_55){
this.init(_54,_55);
};
eidogo.BoardRendererAscii.prototype={pointWidth:2,pointHeight:1,margin:1,blankBoard:"+-------------------------------------+\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"|. . . . . . . . . . . . . . . . . . .|\n"+"+-------------------------------------+",init:function(_56,_57){
this.domNode=_56||null;
this.boardSize=_57||19;
this.content=this.blankBoard;
},clear:function(){
this.content=this.blankBoard;
this.domNode.innerHTML="<pre>"+this.content+"</pre>";
},renderStone:function(pt,_59){
var _5a=(this.pointWidth*this.boardSize+this.margin*2)*(pt.y*this.pointHeight+1)+(pt.x*this.pointWidth)+2;
this.content=this.content.substring(0,_5a-1)+"."+this.content.substring(_5a);
if(_59!="empty"){
this.content=this.content.substring(0,_5a-1)+(_59=="white"?"O":"#")+this.content.substring(_5a);
}
this.domNode.innerHTML="<pre>"+this.content+"</pre>";
},renderMarker:function(pt,_5c){
}};

eidogo.Rules=function(_1){
this.init(_1);
};
eidogo.Rules.prototype={init:function(_2){
this.board=_2;
this.pendingCaptures=[];
},check:function(pt,_4){
if(this.board.getStone(pt)!=this.board.EMPTY){
return false;
}
return true;
},apply:function(pt,_6){
this.doCaptures(pt,_6);
},doCaptures:function(pt,_8){
var _9=0;
_9+=this.doCapture({x:pt.x-1,y:pt.y},_8);
_9+=this.doCapture({x:pt.x+1,y:pt.y},_8);
_9+=this.doCapture({x:pt.x,y:pt.y-1},_8);
_9+=this.doCapture({x:pt.x,y:pt.y+1},_8);
_9-=this.doCapture(pt,-_8);
if(_9<0){
_8=-_8;
_9=-_9;
}
_8=_8==this.board.WHITE?"W":"B";
this.board.captures[_8]+=_9;
},doCapture:function(pt,_b){
this.pendingCaptures=[];
if(this.findCaptures(pt,_b)){
return 0;
}
var _c=this.pendingCaptures.length;
while(this.pendingCaptures.length){
this.board.addStone(this.pendingCaptures.pop(),this.board.EMPTY);
}
return _c;
},findCaptures:function(pt,_e){
if(pt.x<0||pt.y<0||pt.x>=this.board.boardSize||pt.y>=this.board.boardSize){
return 0;
}
if(this.board.getStone(pt)==_e){
return 0;
}
if(this.board.getStone(pt)==this.board.EMPTY){
return 1;
}
for(var i=0;i<this.pendingCaptures.length;i++){
if(this.pendingCaptures[i].x==pt.x&&this.pendingCaptures[i].y==pt.y){
return 0;
}
}
this.pendingCaptures.push(pt);
if(this.findCaptures({x:pt.x-1,y:pt.y},_e)){
return 1;
}
if(this.findCaptures({x:pt.x+1,y:pt.y},_e)){
return 1;
}
if(this.findCaptures({x:pt.x,y:pt.y-1},_e)){
return 1;
}
if(this.findCaptures({x:pt.x,y:pt.y+1},_e)){
return 1;
}
return 0;
}};

(function(){
var t=eidogo.i18n,_2=eidogo.util.byId,_3=eidogo.util.ajax,_4=eidogo.util.addEvent,_5=eidogo.util.onClick,_6=eidogo.util.getElClickXY,_7=eidogo.util.stopEvent,_8=eidogo.util.addClass,_9=eidogo.util.removeClass,_a=eidogo.util.show,_b=eidogo.util.hide,_c=eidogo.browser.moz,_d=eidogo.util.getPlayerPath();
eidogo.players=eidogo.players||{};
eidogo.delegate=function(_e,fn){
var _10=eidogo.players[_e];
_10[fn].apply(_10,Array.from(arguments).slice(2));
};
eidogo.Player=function(){
this.init.apply(this,arguments);
};
eidogo.Player.prototype={init:function(cfg){
cfg=cfg||{};
this.mode=cfg.mode?cfg.mode:"play";
this.dom={};
this.dom.container=(typeof cfg.container=="string"?_2(cfg.container):cfg.container);
if(!this.dom.container){
alert(t["dom error"]);
return;
}
this.uniq=(new Date()).getTime();
eidogo.players[this.uniq]=this;
this.sgfPath=cfg.sgfPath;
this.searchUrl=cfg.searchUrl;
this.showingSearch=false;
this.saveUrl=cfg.saveUrl;
this.downloadUrl=cfg.downloadUrl;
this.scoreEstUrl=cfg.scoreEstUrl;
this.hooks=cfg.hooks||{};
this.permalinkable=!!this.hooks.setPermalink;
this.propertyHandlers={W:this.playMove,B:this.playMove,KO:this.playMove,MN:this.setMoveNumber,AW:this.addStone,AB:this.addStone,AE:this.addStone,CR:this.addMarker,LB:this.addMarker,TR:this.addMarker,MA:this.addMarker,SQ:this.addMarker,TW:this.addMarker,TB:this.addMarker,DD:this.addMarker,PL:this.setColor,C:this.showComments,N:this.showAnnotation,GB:this.showAnnotation,GW:this.showAnnotation,DM:this.showAnnotation,HO:this.showAnnotation,UC:this.showAnnotation,V:this.showAnnotation,BM:this.showAnnotation,DO:this.showAnnotation,IT:this.showAnnotation,TE:this.showAnnotation,BL:this.showTime,OB:this.showTime,WL:this.showTime,OW:this.showTime};
this.infoLabels={GN:t["game"],PW:t["white"],WR:t["white rank"],WT:t["white team"],PB:t["black"],BR:t["black rank"],BT:t["black team"],HA:t["handicap"],KM:t["komi"],RE:t["result"],DT:t["date"],GC:t["info"],PC:t["place"],EV:t["event"],RO:t["round"],OT:t["overtime"],ON:t["opening"],RU:t["ruleset"],AN:t["annotator"],CP:t["copyright"],SO:t["source"],TM:t["time limit"],US:t["transcriber"],AP:t["created with"]};
this.months=[t["january"],t["february"],t["march"],t["april"],t["may"],t["june"],t["july"],t["august"],t["september"],t["october"],t["november"],t["december"]];
this.theme=cfg.theme;
this.reset(cfg);
this.renderer=cfg.renderer||"html";
this.cropParams=null;
this.shrinkToFit=cfg.shrinkToFit;
if(this.shrinkToFit||cfg.cropWidth||cfg.cropHeight){
this.cropParams={};
this.cropParams.width=cfg.cropWidth;
this.cropParams.height=cfg.cropHeight;
this.cropParams.left=cfg.cropLeft;
this.cropParams.top=cfg.cropTop;
this.cropParams.padding=cfg.cropPadding||1;
}
this.constructDom();
if(cfg.enableShortcuts){
_4(document,_c?"keypress":"keydown",this.handleKeypress,this,true);
}
_4(document,"mouseup",this.handleDocMouseUp,this,true);
if(cfg.sgf||cfg.sgfUrl||(cfg.sgfPath&&cfg.gameName)){
this.loadSgf(cfg);
}
this.hook("initDone");
},hook:function(_12,_13){
if(_12 in this.hooks){
return this.hooks[_12].bind(this)(_13);
}
},reset:function(cfg){
this.gameName="";
this.collectionRoot=new eidogo.GameNode();
this.cursor=new eidogo.GameCursor();
this.progressiveLoad=cfg.progressiveLoad?true:false;
this.progressiveLoads=null;
this.progressiveUrl=null;
this.progressiveMode=cfg.progressiveLoad&&cfg.progressiveMode||"id";
this.opponentUrl=null;
this.opponentColor=null;
this.opponentLevel=null;
this.board=null;
this.rules=null;
this.currentColor=null;
this.moveNumber=null;
this.totalMoves=null;
this.variations=null;
this.timeB="";
this.timeW="";
this.regionTop=null;
this.regionLeft=null;
this.regionWidth=null;
this.regionHeight=null;
this.regionBegun=null;
this.regionClickSelect=null;
this.mouseDown=null;
this.mouseDownX=null;
this.mouseDownY=null;
this.labelLastLetter=null;
this.labelLastNumber=null;
this.resetLastLabels();
this.unsavedChanges=false;
this.updatedNavTree=false;
this.searching=false;
this.editingText=false;
this.goingBack=false;
this.problemMode=cfg.problemMode;
this.problemColor=cfg.problemColor||"W";
this.prefs={};
this.prefs.markCurrent=typeof cfg.markCurrent!="undefined"?!!cfg.markCurrent:true;
this.prefs.markNext=typeof cfg.markNext!="undefined"?cfg.markNext:false;
this.prefs.markVariations=typeof cfg.markVariations!="undefined"?!!cfg.markVariations:true;
this.prefs.showGameInfo=!!cfg.showGameInfo;
this.prefs.showPlayerInfo=!!cfg.showPlayerInfo;
this.prefs.showTools=!!cfg.showTools;
this.prefs.showComments=typeof cfg.showComments!="undefined"?!!cfg.showComments:true;
this.prefs.showOptions=!!cfg.showOptions;
this.prefs.showNavTree=!this.progressiveLoad&&typeof cfg.showNavTree!="undefined"?!!cfg.showNavTree:false;
if(this.prefs.showNavTree&&!(eidogo.browser.moz||eidogo.browser.safari3)){
this.prefs.showNavTree=false;
}
},loadSgf:function(cfg,_16){
cfg=cfg||{};
this.nowLoading();
this.reset(cfg);
this.sgfPath=cfg.sgfPath||this.sgfPath;
this.loadPath=cfg.loadPath&&cfg.loadPath.length>1?cfg.loadPath:[0,0];
this.gameName=cfg.gameName||"";
var _17=false;
if(typeof cfg.sgf=="string"){
var sgf=new eidogo.SgfParser(cfg.sgf);
this.load(sgf.root);
}else{
if(typeof cfg.sgf=="object"){
this.load(cfg.sgf);
}else{
if(cfg.progressiveLoad&&cfg.progressiveUrl){
this.progressiveLoads=0;
this.progressiveUrl=cfg.progressiveUrl;
this.fetchProgressiveData(_16);
_17=true;
}else{
if(typeof cfg.sgfUrl=="string"||this.gameName){
if(!cfg.sgfUrl){
cfg.sgfUrl=this.sgfPath+this.gameName+".sgf";
}
this.remoteLoad(cfg.sgfUrl,null,false,null,_16);
_17=true;
if(cfg.progressiveLoad){
this.progressiveLoads=0;
this.progressiveUrl=cfg.progressiveUrl||cfg.sgfUrl.replace(/\?.+$/,"");
}
}else{
var _19=cfg.boardSize||"19";
var _1a={19:6.5,13:4.5,9:3.5};
var _1b={_children:[{SZ:_19,KM:cfg.komi||_1a[_19]||6.5,_children:[]}]};
if(cfg.opponentUrl){
this.gameName="gnugo";
this.opponentUrl=cfg.opponentUrl;
this.opponentColor=cfg.opponentColor=="B"?cfg.opponentColor:"W";
this.opponentLevel=cfg.opponentLevel||7;
var _1c=_1b._children[0];
_1c.PW=this.opponentColor=="B"?t["you"]:"GNU Go";
_1c.PB=this.opponentColor=="B"?"GNU Go":t["you"];
_1c.HA=parseInt(cfg.handicap,10)||0;
if(_1c.HA){
var _1d={19:[["pd","dp"],["pd","dp","pp"],["pd","dp","pp","dd"],["pd","dp","pp","dd","jj"],["pd","dp","pp","dd","dj","pj"],["pd","dp","pp","dd","dj","pj","jj"],["pd","dp","pp","dd","dj","pj","jd","jp"],["pd","dp","pp","dd","dj","pj","jd","jp","jj"]],13:[["jd","dj"],["jd","dj","jj"],["jd","dj","jj","dd"],["jd","dj","jj","dd","gg"],["jd","dj","jj","dd","dg","jg"],["jd","dj","jj","dd","dg","jg","gg"],["jd","dj","jj","dd","dg","jg","gd","gj"],["jd","dj","jj","dd","dg","jg","gd","gj","gg"]],9:[["cg","gc"],["cg","gc","gg"],["cg","gc","gg","cc"],["cg","gc","gg","cc","ee"],["cg","gc","gg","cc","ce","ge"],["cg","gc","gg","cc","ce","ge","ee"],["cg","gc","gg","cc","ce","ge","ec","eg"],["cg","gc","gg","cc","ce","ge","ec","eg","ee"]]};
_1c.KM=0.5;
if(_1c.HA>1){
_1c.AB=_1d[_19][_1c.HA-2];
}
}
}
this.load(_1b);
}
}
}
}
if(!_17&&typeof _16=="function"){
_16();
}
},load:function(_1e,_1f){
if(!_1f){
_1f=new eidogo.GameNode();
this.collectionRoot=_1f;
}
_1f.loadJson(_1e);
_1f._cached=true;
this.doneLoading();
this.progressiveLoads--;
if(!_1f._parent){
var _20=this.loadPath.length?parseInt(this.loadPath[0],10):0;
this.initGame(_1f._children[_20||0]);
}
if(this.loadPath.length){
this.goTo(this.loadPath,false);
if(!this.progressiveLoad){
this.loadPath=[0,0];
}
}else{
this.refresh();
}
if(!_1f._parent&&this.problemMode){
this.currentColor=this.problemColor=this.cursor.getNextColor();
}
},remoteLoad:function(url,_22,_23,_24,_25){
_23=_23=="undefined"?true:_23;
_25=(typeof _25=="function")?_25:null;
if(_23){
if(!_22){
this.gameName=url;
}
url=this.sgfPath+url+".sgf";
}
if(_24){
this.loadPath=_24;
}
var _26=function(req){
var _28=req.responseText.replace(/^( |\t|\r|\n)*/,"");
if(_28.charAt(0)=="("){
var me=this;
var sgf=new eidogo.SgfParser(_28,function(){
me.load(this.root,_22);
_25&&_25();
});
}else{
if(_28.charAt(0)=="{"){
_28=eval("("+_28+")");
this.load(_28,_22);
_25&&_25();
}else{
this.croak(t["invalid data"]);
}
}
};
var _2b=function(req){
this.croak(t["error retrieving"]);
};
_3("get",url,null,_26,_2b,this,30000);
},initGame:function(_2d){
_2d=_2d||{};
this.handleDisplayPrefs();
var _2e=_2d.SZ||19;
if(_2e!=9&&_2e!=13&&_2e!=19){
_2e=19;
}
if(this.shrinkToFit){
this.calcShrinkToFit(_2d,_2e);
}
if(!this.board){
this.createBoard(_2e);
this.rules=new eidogo.Rules(this.board);
}
this.unsavedChanges=false;
this.resetCursor(true);
this.totalMoves=0;
var _2f=new eidogo.GameCursor(this.cursor.node);
while(_2f.next()){
this.totalMoves++;
}
this.totalMoves--;
this.showGameInfo(_2d);
this.enableNavSlider();
this.selectTool(this.mode=="view"?"view":"play");
this.hook("initGame");
},handleDisplayPrefs:function(){
(this.prefs.showGameInfo||this.prefs.showPlayerInfo?_a:_b)(this.dom.info);
(this.prefs.showGameInfo?_a:_b)(this.dom.infoGame);
(this.prefs.showPlayerInfo?_a:_b)(this.dom.infoPlayers);
(this.prefs.showTools?_a:_b)(this.dom.toolsContainer);
if(!this.showingSearch){
(this.prefs.showComments?_a:_b)(this.dom.comments);
}
(this.prefs.showOptions?_a:_b)(this.dom.options);
(this.prefs.showNavTree?_a:_b)(this.dom.navTreeContainer);
},createBoard:function(_30){
_30=_30||19;
if(this.board&&this.board.renderer&&this.board.boardSize==_30){
return;
}
try{
this.dom.boardContainer.innerHTML="";
var _31;
if(this.renderer=="flash"){
_31=eidogo.BoardRendererFlash;
}else{
_31=eidogo.BoardRendererHtml;
}
var _32=new _31(this.dom.boardContainer,_30,this,this.cropParams);
this.board=new eidogo.Board(_32,_30);
}
catch(e){
if(e=="No DOM container"){
this.croak(t["error board"]);
return;
}
}
},calcShrinkToFit:function(_33,_34){
var l=null,t=null,r=null,b=null;
var _38={};
var me=this;
_33.walk(function(_3a){
var _3b,i,_3d;
for(_3b in _3a){
if(/^(W|B|AW|AB|LB)$/.test(_3b)){
_3d=_3a[_3b];
if(!(_3d instanceof Array)){
_3d=[_3d];
}
if(_3b!="LB"){
_3d=me.expandCompressedPoints(_3d);
}else{
_3d=[_3d[0].split(/:/)[0]];
}
for(i=0;i<_3d.length;i++){
_38[_3d[i]]="";
}
}
}
});
for(var key in _38){
var pt=this.sgfCoordToPoint(key);
if(l==null||pt.x<l){
l=pt.x;
}
if(r==null||pt.x>r){
r=pt.x;
}
if(t==null||pt.y<t){
t=pt.y;
}
if(b==null||pt.y>b){
b=pt.y;
}
}
this.cropParams.width=r-l+1;
this.cropParams.height=b-t+1;
this.cropParams.left=l;
this.cropParams.top=t;
var pad=this.cropParams.padding;
for(var _41=pad;l-_41<0;_41--){
}
if(_41){
this.cropParams.width+=_41;
this.cropParams.left-=_41;
}
for(var _42=pad;t-_42<0;_42--){
}
if(_42){
this.cropParams.height+=_42;
this.cropParams.top-=_42;
}
for(var _43=pad;r+_43>_34;_43--){
}
if(_43){
this.cropParams.width+=_43;
}
for(var _44=pad;b+_44>_34;_44--){
}
if(_44){
this.cropParams.height+=_44;
}
},fetchOpponentMove:function(){
this.nowLoading(t["gnugo thinking"]);
var _45=function(req){
this.doneLoading();
this.createMove(req.responseText);
};
var _47=function(req){
this.croak(t["error retrieving"]);
};
var _49=this.cursor.getGameRoot();
var _4a={sgf:_49.toSgf(),move:this.currentColor,size:_49.SZ,level:this.opponentLevel};
_3("post",this.opponentUrl,_4a,_45,_47,this,45000);
},fetchScoreEstimate:function(){
this.nowLoading(t["gnugo thinking"]);
var _4b=function(req){
this.doneLoading();
var _4d=req.responseText.split("\n");
var _4e,_4f=_4d[1].split(" ");
for(var i=0;i<_4f.length;i++){
_4e=_4f[i].split(":");
if(_4e[1]){
this.addMarker(_4e[1],_4e[0]);
}
}
this.board.render();
this.prependComment(_4d[0]);
};
var _51=function(req){
this.croak(t["error retrieving"]);
};
var _53=this.cursor.getGameRoot();
var _54={sgf:_53.toSgf(),move:"est",size:_53.SZ||19,komi:_53.KM||0,mn:this.moveNumber+1};
_3("post",this.scoreEstUrl,_54,_4b,_51,this,45000);
},playProblemResponse:function(_55){
setTimeout(function(){
this.variation(null,_55);
if(this.hooks.playProblemResponse){
this.hook("playProblemResponse");
}else{
if(!this.cursor.hasNext()){
this.prependComment(t["end of variation"]);
}
}
}.bind(this),200);
},goTo:function(_56,_57){
_57=typeof _57!="undefined"?_57:true;
if(_57){
this.resetCursor(true);
}
var _58=parseInt(_56,10);
if(!(_56 instanceof Array)&&!isNaN(_58)){
if(_57){
_58++;
}
for(var i=0;i<_58;i++){
this.variation(null,true);
}
this.refresh();
return;
}
if(!(_56 instanceof Array)||!_56.length){
alert(t["bad path"]+" "+_56);
return;
}
var _5a;
var _5b;
if(isNaN(parseInt(_56[0],10))){
if(!this.cursor.node._parent){
this.variation(0,true);
}
while(_56.length){
if(this.progressiveLoads>0){
this.loadPath.push(_5a);
return;
}
_5a=_56.shift();
_5b=this.getVariations();
for(var i=0;i<_5b.length;i++){
if(_5b[i].move==_5a){
this.variation(_5b[i].varNum,true);
break;
}
}
}
this.refresh();
return;
}
var _5c=true;
while(_56.length){
_5a=parseInt(_56.shift(),10);
if(!_56.length){
for(var i=0;i<_5a;i++){
this.variation(0,true);
}
}else{
if(_56.length){
if(!_5c&&this.cursor.node._parent._parent){
while(this.cursor.node._children.length==1){
this.variation(0,true);
}
}
this.variation(_5a,true);
}
}
_5c=false;
}
this.refresh();
},resetCursor:function(_5d,_5e){
this.board.reset();
this.resetCurrentColor();
if(_5e){
this.cursor.node=this.cursor.getGameRoot();
}else{
this.cursor.node=this.collectionRoot;
}
this.refresh(_5d);
},resetCurrentColor:function(){
this.currentColor=(this.problemMode?this.problemColor:"B");
var _5f=this.cursor.getGameRoot();
if(_5f&&_5f.HA>1){
this.currentColor="W";
}
},refresh:function(_60){
if(this.progressiveLoads>0){
var me=this;
setTimeout(function(){
me.refresh.call(me);
},10);
return;
}
this.board.revert(1);
this.execNode(_60);
},variation:function(_62,_63){
if(this.cursor.next(_62)){
this.execNode(_63);
this.resetLastLabels();
if(this.progressiveLoads>0){
return false;
}
return true;
}
return false;
},execNode:function(_64,_65){
if(!_65&&this.progressiveLoads>0){
var me=this;
setTimeout(function(){
me.execNode.call(me,_64);
},10);
return;
}
if(!this.cursor.node){
return;
}
if(!_64){
this.dom.comments.innerHTML="";
this.board.clearMarkers();
this.moveNumber=this.cursor.getMoveNumber();
}
if(this.moveNumber<1){
this.resetCurrentColor();
}
var _67=this.cursor.node.getProperties();
for(var _68 in _67){
if(this.propertyHandlers[_68]){
(this.propertyHandlers[_68]).apply(this,[this.cursor.node[_68],_68,_64]);
}
}
if(_64){
this.board.commit();
}else{
if(this.opponentUrl&&this.opponentColor==this.currentColor&&this.moveNumber==this.totalMoves){
this.fetchOpponentMove();
}
this.findVariations();
this.updateControls();
this.board.commit();
this.board.render();
}
if(!_65&&this.progressiveUrl){
this.fetchProgressiveData();
}
if(this.problemMode&&this.currentColor&&this.currentColor!=this.problemColor&&!this.goingBack){
this.playProblemResponse(_64);
}
this.goingBack=false;
},fetchProgressiveData:function(_69){
var _6a=this.cursor.node||null;
if(_6a&&_6a._cached){
return;
}
if(this.progressiveMode=="pattern"){
if(_6a&&!_6a._parent._parent){
return;
}
this.fetchProgressiveContinuations(_69);
}else{
var _6b=(_6a&&_6a._id)||0;
this.nowLoading();
this.progressiveLoads++;
this.updatedNavTree=false;
var _6c=function(){
var _6d=this.cursor.getMoveNumber();
if(_6d>1){
this.cursor.node.C="<a id='cont-search' href='#'>"+t["show games"]+"</a>"+(this.cursor.node.C||"");
}
this.refresh();
if(_69&&typeof _69=="function"){
_69();
}
_4(_2("cont-search"),"click",function(e){
var _6f=8;
var _70=this.convertRegionPattern(this.board.getRegion(0,19-_6f,_6f,_6f));
this.loadSearch("ne",_6f+"x"+_6f,this.compressPattern(_70));
_7(e);
}.bind(this));
}.bind(this);
this.remoteLoad(this.progressiveUrl+"?id="+_6b,_6a,false,null,_6c);
}
},fetchProgressiveContinuations:function(_71){
this.nowLoading();
this.progressiveLoads++;
this.updatedNavTree=false;
var _72=this.cursor.getMoveNumber();
var _73=(_72>1?11:7);
var _74=19-_73-1;
var _75=this.board?this.convertRegionPattern(this.board.getRegion(0,_74+1,_73,_73)):".................................................";
var _76={q:"ne",w:_73,h:_73,p:_75,a:"continuations",t:(new Date()).getTime()};
var _77=function(req){
this.croak(t["error retrieving"]);
};
var _79=function(req){
if(!req.responseText||req.responseText=="NONE"){
this.progressiveLoads--;
this.doneLoading();
this.cursor.node._cached=true;
this.refresh();
return;
}
var _7b={LB:[],_children:[]},_7c;
_7b.C=_72>1?"<a id='cont-search' href='#'>"+t["show games"]+"</a>":"";
var _7d,_7e=eval("("+req.responseText+")");
if(_7e.length){
_7e.sort(function(a,b){
return parseInt(b.count,10)-parseInt(a.count,10);
});
var _81=parseInt(_7e[0].count,10);
var x,y,_84,_85;
_7b.C+="<div class='continuations'>";
for(var i=0;_7d=_7e[i];i++){
_85=parseInt(_7d.count/_81*150);
if(_81>20&&parseInt(_7d.count,10)<10){
continue;
}
_7c={};
x=_74+parseInt(_7d.x,10)+1;
y=parseInt(_7d.y,10);
_84=this.pointToSgfCoord({x:x,y:y});
_7c[this.currentColor||"B"]=_84;
_7b.LB.push(_84+":"+_7d.label);
if(_85){
_7b.C+="<div class='continuation'>"+"<div class='cont-label'>"+_7d.label+"</div>"+"<div class='cont-bar' style='width: "+_85+"px'></div>"+"<div class='cont-count'>"+_7d.count+"</div>"+"</div>";
}
_7b._children.push(_7c);
}
_7b.C+="</div>";
if(!this.cursor.node){
_7b={_children:[_7b]};
}
}
this.load(_7b,this.cursor.node);
_4(_2("cont-search"),"click",function(e){
this.loadSearch("ne",_73+"x"+_73,this.compressPattern(_75));
_7(e);
}.bind(this));
if(_71&&typeof _71=="function"){
_71();
}
}.bind(this);
_3("get",this.progressiveUrl,_76,_79,_77,this,45000);
},findVariations:function(){
this.variations=this.getVariations();
},getVariations:function(){
var _88=[],_89=this.cursor.node._children;
for(var i=0;i<_89.length;i++){
_88.push({move:_89[i].getMove(),varNum:i});
}
return _88;
},back:function(e,obj,_8d){
if(this.cursor.previous()){
this.board.revert(1);
this.goingBack=true;
this.refresh(_8d);
this.resetLastLabels();
}
},forward:function(e,obj,_90){
this.variation(null,_90);
},first:function(){
if(!this.cursor.hasPrevious()){
return;
}
this.resetCursor(false,true);
},last:function(){
if(!this.cursor.hasNext()){
return;
}
while(this.variation(null,true)){
}
this.refresh();
},pass:function(){
if(!this.variations){
return;
}
for(var i=0;i<this.variations.length;i++){
if(!this.variations[i].move||this.variations[i].move=="tt"){
this.variation(this.variations[i].varNum);
return;
}
}
this.createMove("tt");
},handleBoardMouseDown:function(x,y,e){
if(this.domLoading){
return;
}
if(!this.boundsCheck(x,y,[0,this.board.boardSize-1])){
return;
}
this.mouseDown=true;
this.mouseDownX=x;
this.mouseDownY=y;
if(this.mode=="region"&&x>=0&&y>=0&&!this.regionBegun){
this.regionTop=y;
this.regionLeft=x;
this.regionBegun=true;
}
},handleBoardHover:function(x,y,e){
if(this.domLoading){
return;
}
if(this.mouseDown||this.regionBegun){
if(!this.boundsCheck(x,y,[0,this.board.boardSize-1])){
return;
}
if(this.searchUrl&&!this.regionBegun&&(x!=this.mouseDownX||y!=this.mouseDownY)){
this.selectTool("region");
this.regionBegun=true;
this.regionTop=this.mouseDownY;
this.regionLeft=this.mouseDownX;
}
if(this.regionBegun){
this.regionRight=x+(x>=this.regionLeft?1:0);
this.regionBottom=y+(y>=this.regionTop?1:0);
this.showRegion();
}
_7(e);
}
},handleBoardMouseUp:function(x,y,e){
if(this.domLoading){
return;
}
this.mouseDown=false;
var _9b=this.pointToSgfCoord({x:x,y:y});
if(this.mode=="view"||this.mode=="play"){
for(var i=0;i<this.variations.length;i++){
var _9d=this.sgfCoordToPoint(this.variations[i].move);
if(_9d.x==x&&_9d.y==y){
this.variation(this.variations[i].varNum);
_7(e);
return;
}
}
}
if(this.mode=="view"){
var _9e=this.cursor.getGameRoot(),_9f=[0,_9e.getPosition()],mn=0,_a1=_9e._children[0];
while(_a1){
if(_a1.getMove()==_9b){
_9f.push(mn);
this.goTo(_9f);
break;
}
mn++;
_a1=_a1._children[0];
}
return;
}
if(this.mode=="play"){
if(!this.rules.check({x:x,y:y},this.currentColor)){
return;
}
if(_9b){
var _a2=this.cursor.getNextMoves();
if(_a2&&_9b in _a2){
this.variation(_a2[_9b]);
}else{
this.createMove(_9b);
}
}
}else{
if(this.mode=="region"&&x>=-1&&y>=-1&&this.regionBegun){
if(this.regionTop==y&&this.regionLeft==x&&!this.regionClickSelect){
this.regionClickSelect=true;
this.regionRight=x+1;
this.regionBottom=y+1;
this.showRegion();
}else{
this.regionBegun=false;
this.regionClickSelect=false;
this.regionBottom=(y<0?0:(y>=this.board.boardSize)?y:y+(y>this.regionTop?1:0));
this.regionRight=(x<0?0:(x>=this.board.boardSize)?x:x+(x>this.regionLeft?1:0));
this.showRegion();
_a(this.dom.searchButton,"inline");
_7(e);
}
}else{
var _a3;
var _a4=this.board.getStone({x:x,y:y});
if(this.mode=="add_b"||this.mode=="add_w"){
var _a5=this.cursor.node.emptyPoint(this.pointToSgfCoord({x:x,y:y}));
if(_a4!=this.board.BLACK&&this.mode=="add_b"){
_a3="AB";
}else{
if(_a4!=this.board.WHITE&&this.mode=="add_w"){
_a3="AW";
}else{
if(this.board.getStone({x:x,y:y})!=this.board.EMPTY&&!_a5){
_a3="AE";
}
}
}
}else{
switch(this.mode){
case "tr":
_a3="TR";
break;
case "sq":
_a3="SQ";
break;
case "cr":
_a3="CR";
break;
case "x":
_a3="MA";
break;
case "dim":
_a3="DD";
break;
case "number":
_a3="LB";
_9b=_9b+":"+this.labelLastNumber;
this.labelLastNumber++;
break;
case "letter":
_a3="LB";
_9b=_9b+":"+this.labelLastLetter;
this.labelLastLetter=String.fromCharCode(this.labelLastLetter.charCodeAt(0)+1);
break;
case "clear":
this.cursor.node.deletePropertyValue(["TR","SQ","CR","MA","DD","LB"],new RegExp("^"+_9b));
break;
}
if(this.cursor.node.hasPropertyValue(_a3,_9b)){
this.cursor.node.deletePropertyValue(_a3,_9b);
_a3=null;
}
}
if(_a3){
this.cursor.node.pushProperty(_a3,_9b);
}
this.unsavedChanges=true;
var _a5=this.checkForEmptyNode();
this.refresh();
if(_a5){
this.prependComment(t["position deleted"]);
}
}
}
},checkForEmptyNode:function(){
if(!eidogo.util.numProperties(this.cursor.node.getProperties())){
var _a6=window.confirm(t["confirm delete"]);
if(_a6){
var id=this.cursor.node._id;
var _a8=0;
this.back();
this.cursor.node._children=this.cursor.node._children.filter(function(_a9,i){
if(_a9._id==id){
_a8=i;
return false;
}else{
return true;
}
});
if(_a8&&this.cursor.node._preferredChild==_a8){
this.cursor.node._preferredChild--;
}
return true;
}
}
return false;
},handleDocMouseUp:function(evt){
if(this.domLoading){
return true;
}
if(this.mode=="region"&&this.regionBegun&&!this.regionClickSelect){
this.mouseDown=false;
this.regionBegun=false;
_a(this.dom.searchButton,"inline");
}
return true;
},boundsCheck:function(x,y,_ae){
if(_ae.length==2){
_ae[3]=_ae[2]=_ae[1];
_ae[1]=_ae[0];
}
return (x>=_ae[0]&&y>=_ae[1]&&x<=_ae[2]&&y<=_ae[3]);
},getRegionBounds:function(){
var l=this.regionLeft;
var w=this.regionRight-this.regionLeft;
if(w<0){
l=this.regionRight;
w=-w+1;
}
var t=this.regionTop;
var h=this.regionBottom-this.regionTop;
if(h<0){
t=this.regionBottom;
h=-h+1;
}
return [t,l,w,h];
},showRegion:function(){
var _b3=this.getRegionBounds();
this.board.renderer.showRegion(_b3);
},hideRegion:function(){
this.board.renderer.hideRegion();
},convertRegionPattern:function(_b4){
return _b4.join("").replace(new RegExp(this.board.EMPTY,"g"),".").replace(new RegExp(this.board.BLACK,"g"),"x").replace(new RegExp(this.board.WHITE,"g"),"o");
},loadSearch:function(q,dim,p,a,o){
var _ba={_children:[{SZ:this.board.boardSize,_children:[]}]};
this.load(_ba);
a=a||"corner";
this.dom.searchAlgo.value=a;
p=this.uncompressPattern(p);
dim=dim.split("x");
var w=dim[0];
var h=dim[1];
var bs=this.board.boardSize;
var l;
var t;
switch(q){
case "nw":
l=0;
t=0;
break;
case "ne":
l=bs-w;
t=0;
break;
case "se":
l=bs-w;
t=bs-h;
break;
case "sw":
l=0;
t=bs-h;
break;
}
var c;
var x;
var y;
for(y=0;y<h;y++){
for(x=0;x<w;x++){
c=p.charAt(y*w+x);
if(c=="o"){
c="AW";
}else{
if(c=="x"){
c="AB";
}else{
c="";
}
}
this.cursor.node.pushProperty(c,this.pointToSgfCoord({x:l+x,y:t+y}));
}
}
this.refresh();
this.regionLeft=l;
this.regionTop=t;
this.regionRight=l+x;
this.regionBottom=t+y;
var b=this.getRegionBounds();
var r=[b[1],b[0],b[1]+b[2],b[0]+b[3]-1];
for(y=0;y<this.board.boardSize;y++){
for(x=0;x<this.board.boardSize;x++){
if(!this.boundsCheck(x,y,r)){
this.board.renderer.renderMarker({x:x,y:y},"dim");
}
}
}
this.searchRegion(o);
},searchRegion:function(_c5){
if(this.searching){
return;
}
this.searching=true;
if(!this.searchUrl){
_a(this.dom.comments);
_b(this.dom.searchContainer);
this.prependComment(t["no search url"]);
return;
}
var _c5=parseInt(_c5,10)||0;
var _c6=this.dom.searchAlgo.value;
var _c7=this.getRegionBounds();
var _c8=this.board.getRegion(_c7[0],_c7[1],_c7[2],_c7[3]);
var _c9=this.convertRegionPattern(_c8);
var _ca=/^\.*$/.test(_c9);
var _cb=/^\.*o\.*$/.test(_c9);
var _cc=/^\.*x\.*$/.test(_c9);
if(_ca||_cb||_cc){
this.searching=false;
_a(this.dom.comments);
_b(this.dom.searchContainer);
this.prependComment(t["two stones"]);
return;
}
var _cd=[];
if(_c7[0]==0){
_cd.push("n");
}
if(_c7[1]==0){
_cd.push("w");
}
if(_c7[0]+_c7[3]==this.board.boardSize){
_cd.push("s");
}
if(_c7[1]+_c7[2]==this.board.boardSize){
_cd.push("e");
}
if(_c6=="corner"&&!(_cd.length==2&&((_cd.contains("n")&&_cd.contains("e"))||(_cd.contains("n")&&_cd.contains("w"))||(_cd.contains("s")&&_cd.contains("e"))||(_cd.contains("s")&&_cd.contains("w"))))){
this.searching=false;
_a(this.dom.comments);
_b(this.dom.searchContainer);
this.prependComment(t["two edges"]);
return;
}
var _ce=(_cd.contains("n")?"n":"s");
_ce+=(_cd.contains("w")?"w":"e");
this.showComments("");
this.gameName="search";
var _cf=function(req){
this.searching=false;
this.doneLoading();
_b(this.dom.comments);
_a(this.dom.searchContainer);
this.showingSearch=true;
if(req.responseText=="ERROR"){
this.croak(t["error retrieving"]);
return;
}else{
if(req.responseText=="NONE"){
_b(this.dom.searchResultsContainer);
this.dom.searchCount.innerHTML="No";
return;
}
}
var ret=eval("("+req.responseText+")");
var _d2=ret.results,_d3,_d4="",odd,_d6=parseInt(ret.total,10),_d7=parseInt(ret.offset,10)+1,_d8=parseInt(ret.offset,10)+50;
for(var i=0;_d3=_d2[i];i++){
odd=odd?false:true;
_d4+="<a class='search-result"+(odd?" odd":"")+"' href='#'>                    <span class='id'>"+_d3.id+"</span>                    <span class='mv'>"+_d3.mv+"</span>                    <span class='pw'>"+_d3.pw+" "+_d3.wr+"</span>                    <span class='pb'>"+_d3.pb+" "+_d3.br+"</span>                    <span class='re'>"+_d3.re+"</span>                    <span class='dt'>"+_d3.dt+"</span>                    <div class='clear'>&nbsp;</div>                    </a>";
}
if(_d6>_d8){
_d4+="<div class='search-more'><a href='#' id='search-more'>Show more...</a></div>";
}
_a(this.dom.searchResultsContainer);
this.dom.searchResults.innerHTML=_d4+"<br>";
this.dom.searchCount.innerHTML=_d6;
this.dom.searchOffsetStart.innerHTML=_d7;
this.dom.searchOffsetEnd.innerHTML=(_d6<_d8?_d6:_d8);
this.dom.searchContainer.scrollTop=0;
if(_d6>_d8){
setTimeout(function(){
_4(_2("search-more"),"click",function(e){
this.loadSearch(_ce,_c7[2]+"x"+_c7[3],_c9,"corner",ret.offset+51);
_7(e);
}.bind(this));
}.bind(this),0);
}
};
var _db=function(req){
this.croak(t["error retrieving"]);
};
var _dd={q:_ce,w:_c7[2],h:_c7[3],p:_c9,a:_c6,o:_c5,t:(new Date()).getTime()};
this.progressiveLoad=false;
this.progressiveUrl=null;
this.prefs.markNext=false;
this.prefs.showPlayerInfo=true;
this.hook("searchRegion",_dd);
this.nowLoading();
_3("get",this.searchUrl,_dd,_cf,_db,this,45000);
},loadSearchResult:function(e){
this.nowLoading();
var _df=e.target||e.srcElement;
if(_df.nodeName=="SPAN"){
_df=_df.parentNode;
}
if(_df.nodeName=="A"){
var _e0;
var id;
var mv;
for(var i=0;_e0=_df.childNodes[i];i++){
if(_e0.className=="id"){
id=_e0.innerHTML;
}
if(_e0.className=="mv"){
mv=parseInt(_e0.innerHTML,10);
}
}
}
this.remoteLoad(id,null,true,[0,mv],function(){
this.doneLoading();
this.setPermalink();
this.prefs.showOptions=true;
this.handleDisplayPrefs();
}.bind(this));
_7(e);
},closeSearch:function(){
this.showingSearch=false;
_b(this.dom.searchContainer);
_a(this.dom.comments);
},compressPattern:function(_e4){
var c=null;
var pc="";
var n=1;
var ret="";
for(var i=0;i<_e4.length;i++){
c=_e4.charAt(i);
if(c==pc){
n++;
}else{
ret=ret+pc+(n>1?n:"");
n=1;
pc=c;
}
}
ret=ret+pc+(n>1?n:"");
return ret;
},uncompressPattern:function(_ea){
var c=null;
var s=null;
var n="";
var ret="";
for(var i=0;i<_ea.length;i++){
c=_ea.charAt(i);
if(c=="."||c=="x"||c=="o"){
if(s!=null){
n=parseInt(n,10);
n=isNaN(n)?1:n;
for(var j=0;j<n;j++){
ret+=s;
}
n="";
}
s=c;
}else{
n+=c;
}
}
n=parseInt(n,10);
n=isNaN(n)?1:n;
for(var j=0;j<n;j++){
ret+=s;
}
return ret;
},createMove:function(_f1){
var _f2={};
_f2[this.currentColor]=_f1;
var _f3=new eidogo.GameNode(null,_f2);
_f3._cached=true;
this.totalMoves++;
this.cursor.node.appendChild(_f3);
this.unsavedChanges=true;
this.variation(this.cursor.node._children.length-1);
},handleKeypress:function(e){
if(this.editingText){
return true;
}
var _f5=e.keyCode||e.charCode;
if(!_f5||e.ctrlKey||e.altKey||e.metaKey){
return true;
}
var _f6=String.fromCharCode(_f5).toLowerCase();
for(var i=0;i<this.variations.length;i++){
var _f8=this.sgfCoordToPoint(this.variations[i].move);
var _f9=""+(i+1);
if(_f8.x!=null&&this.board.getMarker(_f8)!=this.board.EMPTY&&typeof this.board.getMarker(_f8)=="string"){
_f9=this.board.getMarker(_f8).toLowerCase();
}
_f9=_f9.replace(/^var:/,"");
if(_f6==_f9.charAt(0)){
this.variation(this.variations[i].varNum);
_7(e);
return;
}
}
if(_f5==112||_f5==27){
this.selectTool("play");
}
var _fa=true;
switch(_f5){
case 39:
if(e.shiftKey){
var _fb=this.totalMoves-this.moveNumber;
var _fc=(_fb>9?9:_fb-1);
for(var i=0;i<_fc;i++){
this.forward(null,null,true);
}
}
this.forward();
break;
case 37:
if(e.shiftKey){
var _fc=(this.moveNumber>9?9:this.moveNumber-1);
for(var i=0;i<_fc;i++){
this.back(null,null,true);
}
}
this.back();
break;
case 40:
this.last();
break;
case 38:
this.first();
break;
case 192:
this.pass();
break;
default:
_fa=false;
break;
}
if(_fa){
_7(e);
}
},showGameInfo:function(_fd){
this.hook("showGameInfo",_fd);
if(!_fd){
return;
}
this.dom.infoGame.innerHTML="";
this.dom.whiteName.innerHTML="";
this.dom.blackName.innerHTML="";
var dl=document.createElement("dl"),val;
for(var _100 in this.infoLabels){
if(_fd[_100] instanceof Array){
_fd[_100]=_fd[_100][0];
}
if(_fd[_100]){
if(_100=="PW"){
this.dom.whiteName.innerHTML=_fd[_100]+(_fd["WR"]?", "+_fd["WR"]:"");
continue;
}else{
if(_100=="PB"){
this.dom.blackName.innerHTML=_fd[_100]+(_fd["BR"]?", "+_fd["BR"]:"");
continue;
}
}
if(_100=="WR"||_100=="BR"){
continue;
}
val=_fd[_100];
if(_100=="DT"){
var _101=_fd[_100].split(/[\.-]/);
if(_101.length==3){
val=_101[2].replace(/^0+/,"")+" "+this.months[_101[1]-1]+" "+_101[0];
}
}
var dt=document.createElement("dt");
dt.innerHTML=this.infoLabels[_100]+":";
var dd=document.createElement("dd");
dd.innerHTML=val;
dl.appendChild(dt);
dl.appendChild(dd);
}
}
this.dom.infoGame.appendChild(dl);
},selectTool:function(tool){
var _105;
_b(this.dom.scoreEst);
if(tool=="region"){
_105="crosshair";
}else{
if(tool=="comment"){
this.startEditComment();
}else{
if(tool=="gameinfo"){
this.startEditGameInfo();
}else{
_105="default";
this.regionBegun=false;
this.hideRegion();
_b(this.dom.searchButton);
_b(this.dom.searchAlgo);
if(this.searchUrl){
_a(this.dom.scoreEst,"inline");
}
}
}
}
this.board.renderer.setCursor(_105);
this.mode=tool;
this.dom.toolsSelect.value=tool;
},startEditComment:function(){
this.closeSearch();
var div=this.dom.commentsEdit;
div.style.position="absolute";
div.style.top=this.dom.comments.offsetTop+"px";
div.style.left=this.dom.comments.offsetLeft+"px";
_a(this.dom.shade);
this.dom.comments.innerHTML="";
_a(div);
_a(this.dom.commentsEditDone);
this.dom.commentsEditTa.value=this.cursor.node.C||"";
this.dom.commentsEditTa.focus();
this.editingText=true;
},finishEditComment:function(){
this.editingText=false;
var oldC=this.cursor.node.C;
var newC=this.dom.commentsEditTa.value;
if(oldC!=newC){
this.unsavedChanges=true;
this.cursor.node.C=newC;
}
if(!this.cursor.node.C){
delete this.cursor.node.C;
}
_b(this.dom.shade);
_b(this.dom.commentsEdit);
_a(this.dom.comments);
this.selectTool("play");
var _109=this.checkForEmptyNode();
this.refresh();
if(_109){
this.prependComment(t["position deleted"]);
}
},startEditGameInfo:function(){
this.closeSearch();
var div=this.dom.gameInfoEdit;
div.style.position="absolute";
div.style.top=this.dom.comments.offsetTop+"px";
div.style.left=this.dom.comments.offsetLeft+"px";
_a(this.dom.shade);
this.dom.comments.innerHTML="";
_a(div);
_a(this.dom.gameInfoEditDone);
var root=this.cursor.getGameRoot();
var html=["<table>"];
for(var prop in this.infoLabels){
html.push("<tr><td>"+this.infoLabels[prop]+":"+"</td><td>"+"<input type=\"text\" id=\"game-info-edit-field-"+prop+"\""+" value=\""+(root[prop]||"")+"\">"+"</td></tr>");
}
html.push("</table>");
this.dom.gameInfoEditForm.innerHTML=html.join("");
setTimeout(function(){
_2("game-info-edit-field-GN").focus();
},0);
this.editingText=true;
},finishEditGameInfo:function(){
this.editingText=false;
_b(this.dom.shade);
_b(this.dom.gameInfoEdit);
_a(this.dom.comments);
var root=this.cursor.getGameRoot();
var _10f=null;
for(var prop in this.infoLabels){
_10f=_2("game-info-edit-field-"+prop).value;
if((root[prop]||"")!=_10f){
root[prop]=_10f;
this.unsavedChanges=true;
}
}
this.showGameInfo(root);
this.dom.gameInfoEditForm.innerHTML="";
this.selectTool("play");
this.refresh();
},updateControls:function(){
this.dom.moveNumber.innerHTML=(this.moveNumber?(t["move"]+" "+this.moveNumber):(this.permalinkable?"permalink":""));
this.dom.whiteCaptures.innerHTML=t["captures"]+": <span>"+this.board.captures.W+"</span>";
this.dom.blackCaptures.innerHTML=t["captures"]+": <span>"+this.board.captures.B+"</span>";
this.dom.whiteTime.innerHTML=t["time left"]+": <span>"+(this.timeW?this.timeW:"--")+"</span>";
this.dom.blackTime.innerHTML=t["time left"]+": <span>"+(this.timeB?this.timeB:"--")+"</span>";
_9(this.dom.controlPass,"pass-on");
this.dom.variations.innerHTML="";
for(var i=0;i<this.variations.length;i++){
var _112=i+1;
if(!this.variations[i].move||this.variations[i].move=="tt"){
_8(this.dom.controlPass,"pass-on");
}else{
if(this.prefs.markNext||this.variations.length>1){
var _113=this.sgfCoordToPoint(this.variations[i].move);
if(this.board.getMarker(_113)!=this.board.EMPTY){
_112=this.board.getMarker(_113);
}
if(this.prefs.markVariations){
this.board.addMarker(_113,"var:"+_112);
}
}
}
var _114=document.createElement("div");
_114.className="variation-nav";
_114.innerHTML=_112;
_4(_114,"click",function(e,arg){
arg.me.variation(arg.varNum);
},{me:this,varNum:this.variations[i].varNum});
this.dom.variations.appendChild(_114);
}
if(this.variations.length<2){
this.dom.variations.innerHTML="<div class='variation-nav none'>"+t["no variations"]+"</div>";
}
if(this.cursor.hasNext()){
_8(this.dom.controlForward,"forward-on");
_8(this.dom.controlLast,"last-on");
}else{
_9(this.dom.controlForward,"forward-on");
_9(this.dom.controlLast,"last-on");
}
if(this.cursor.hasPrevious()){
_8(this.dom.controlBack,"back-on");
_8(this.dom.controlFirst,"first-on");
}else{
_9(this.dom.controlBack,"back-on");
_9(this.dom.controlFirst,"first-on");
var info="";
if(!this.prefs.showPlayerInfo){
info+=this.getGameDescription(true);
}
if(!this.prefs.showGameInfo){
info+=this.dom.infoGame.innerHTML;
}
if(info.length&&this.theme!="problem"){
this.prependComment(info,"comment-info");
}
}
if(!this.progressiveLoad){
this.updateNavSlider();
}
if(this.prefs.showNavTree){
this.updateNavTree();
}
},setColor:function(_118){
this.prependComment(_118=="B"?t["black to play"]:t["white to play"]);
this.currentColor=_118;
},setMoveNumber:function(num){
this.moveNumber=num;
},playMove:function(_11a,_11b,_11c){
_11b=_11b||this.currentColor;
this.currentColor=(_11b=="B"?"W":"B");
_11b=_11b=="W"?this.board.WHITE:this.board.BLACK;
var pt=this.sgfCoordToPoint(_11a);
if((!_11a||_11a=="tt"||_11a=="")&&!_11c){
this.prependComment((_11b==this.board.WHITE?t["white"]:t["black"])+" "+t["passed"],"comment-pass");
}else{
if(_11a=="resign"){
this.prependComment((_11b==this.board.WHITE?t["white"]:t["black"])+" "+t["resigned"],"comment-resign");
}else{
if(_11a&&_11a!="tt"){
this.board.addStone(pt,_11b);
this.rules.apply(pt,_11b);
if(this.prefs.markCurrent&&!_11c){
this.addMarker(_11a,"current");
}
}
}
}
},addStone:function(_11e,_11f){
if(!(_11e instanceof Array)){
_11e=[_11e];
}
_11e=this.expandCompressedPoints(_11e);
for(var i=0;i<_11e.length;i++){
this.board.addStone(this.sgfCoordToPoint(_11e[i]),_11f=="AW"?this.board.WHITE:_11f=="AB"?this.board.BLACK:this.board.EMPTY);
}
},addMarker:function(_121,type){
if(!(_121 instanceof Array)){
_121=[_121];
}
_121=this.expandCompressedPoints(_121);
var _123;
for(var i=0;i<_121.length;i++){
switch(type){
case "TR":
_123="triangle";
break;
case "SQ":
_123="square";
break;
case "CR":
_123="circle";
break;
case "MA":
_123="ex";
break;
case "TW":
_123="territory-white";
break;
case "TB":
_123="territory-black";
break;
case "DD":
_123="dim";
break;
case "LB":
_123=(_121[i].split(":"))[1];
break;
default:
_123=type;
break;
}
this.board.addMarker(this.sgfCoordToPoint((_121[i].split(":"))[0]),_123);
}
},showTime:function(_125,type){
var tp=(type=="BL"||type=="OB"?"timeB":"timeW");
if(type=="BL"||type=="WL"){
var mins=Math.floor(_125/60);
var secs=(_125%60).toFixed(0);
secs=(secs<10?"0":"")+secs;
this[tp]=mins+":"+secs;
}else{
this[tp]+=" ("+_125+")";
}
},showAnnotation:function(_12a,type){
var msg;
switch(type){
case "N":
msg=_12a;
break;
case "GB":
msg=(_12a>1?t["vgb"]:t["gb"]);
break;
case "GW":
msg=(_12a>1?t["vgw"]:t["gw"]);
break;
case "DM":
msg=(_12a>1?t["dmj"]:t["dm"]);
break;
case "UC":
msg=t["uc"];
break;
case "TE":
msg=t["te"];
break;
case "BM":
msg=(_12a>1?t["vbm"]:t["bm"]);
break;
case "DO":
msg=t["do"];
break;
case "IT":
msg=t["it"];
break;
case "HO":
msg=t["ho"];
break;
}
this.prependComment(msg);
},showComments:function(_12d,junk,_12f){
if(!_12d||_12f){
return;
}
this.dom.comments.innerHTML+=_12d.replace(/^(\n|\r|\t|\s)+/,"").replace(/\n/g,"<br />");
},prependComment:function(_130,cls){
cls=cls||"comment-status";
this.dom.comments.innerHTML="<div class='"+cls+"'>"+_130+"</div>"+this.dom.comments.innerHTML;
},downloadSgf:function(evt){
_7(evt);
if(this.downloadUrl){
if(this.unsavedChanges){
alert(t["unsaved changes"]);
return;
}
location.href=this.downloadUrl+this.gameName;
}else{
if(_c){
location.href="data:text/plain,"+encodeURIComponent(this.cursor.getGameRoot().toSgf());
}
}
},save:function(evt){
_7(evt);
var _134=function(req){
this.hook("saved",[req.responseText]);
};
var _136=function(req){
this.croak(t["error retrieving"]);
};
var sgf=this.cursor.getGameRoot().toSgf();
_3("POST",this.saveUrl,{sgf:sgf},_134,_136,this,30000);
},constructDom:function(){
this.dom.player=document.createElement("div");
this.dom.player.className="eidogo-player"+(this.theme?" theme-"+this.theme:"");
this.dom.player.id="player-"+this.uniq;
this.dom.container.innerHTML="";
eidogo.util.show(this.dom.container);
this.dom.container.appendChild(this.dom.player);
var _139="            <div id='board-container' class='board-container'></div>            <div id='controls-container' class='controls-container'>                <ul id='controls' class='controls'>                    <li id='control-first' class='control first'>First</li>                    <li id='control-back' class='control back'>Back</li>                    <li id='control-forward' class='control forward'>Forward</li>                    <li id='control-last' class='control last'>Last</li>                    <li id='control-pass' class='control pass'>Pass</li>                </ul>                <div id='move-number' class='move-number"+(this.permalinkable?" permalink":"")+"'></div>                <div id='nav-slider' class='nav-slider'>                    <div id='nav-slider-thumb' class='nav-slider-thumb'></div>                </div>                <div id='variations-container' class='variations-container'>                    <div id='variations-label' class='variations-label'>"+t["variations"]+":</div>                    <div id='variations' class='variations'></div>                </div>                <div class='controls-stop'></div>            </div>            <div id='tools-container' class='tools-container'"+(this.prefs.showTools?"":" style='display: none'")+">                <div id='tools-label' class='tools-label'>"+t["tool"]+":</div>                <select id='tools-select' class='tools-select'>                    <option value='play'>&#9658; "+t["play"]+"</option>                    <option value='view'>&#8594; "+t["view"]+"</option>                    <option value='add_b'>&#9679; "+t["add_b"]+"</option>                    <option value='add_w'>&#9675; "+t["add_w"]+"</option>                    "+(this.searchUrl?("<option value='region'>&#9618; "+t["region"]+"</option>"):"")+"                    "+(this.saveUrl&&!this.progressiveLoad?("<option value='comment'>&para; "+t["edit comment"]+"</option>"):"")+"                    "+(this.saveUrl?("<option value='gameinfo'>&#8962; "+t["edit game info"]+"</option>"):"")+"                    <option value='tr'>&#9650; "+t["triangle"]+"</option>                    <option value='sq'>&#9632; "+t["square"]+"</option>                    <option value='cr'>&#9679; "+t["circle"]+"</option>                    <option value='x'>&times; "+t["x"]+"</option>                    <option value='letter'>A "+t["letter"]+"</option>                    <option value='number'>5 "+t["number"]+"</option>                    <option value='dim'>&#9619; "+t["dim"]+"</option>                    <option value='clear'>&#9617; "+t["clear"]+"</option>                </select>                <input type='button' id='score-est' class='score-est-button' value='"+t["score est"]+"' />                <select id='search-algo' class='search-algo'>                    <option value='corner'>"+t["search corner"]+"</option>                    <option value='center'>"+t["search center"]+"</option>                </select>                <input type='button' id='search-button' class='search-button' value='"+t["search"]+"' />            </div>            <div id='comments' class='comments'></div>            <div id='comments-edit' class='comments-edit'>                <textarea id='comments-edit-ta' class='comments-edit-ta'></textarea>                <div id='comments-edit-done' class='comments-edit-done'>"+t["done"]+"</div>            </div>            <div id='game-info-edit' class='game-info-edit'>                <div id='game-info-edit-form' class='game-info-edit-form'></div>                <div id='game-info-edit-done' class='game-info-edit-done'>"+t["done"]+"</div>            </div>            <div id='search-container' class='search-container'>                <div id='search-close' class='search-close'>"+t["close search"]+"</div>                <p class='search-count'><span id='search-count'></span>&nbsp;"+t["matches found"]+"                    Showing <span id='search-offset-start'></span>-<span id='search-offset-end'></span></p>                <div id='search-results-container' class='search-results-container'>                    <div class='search-result'>                        <span class='pw'><b>"+t["white"]+"</b></span>                        <span class='pb'><b>"+t["black"]+"</b></span>                        <span class='re'><b>"+t["result"]+"</b></span>                        <span class='dt'><b>"+t["date"]+"</b></span>                        <div class='clear'></div>                    </div>                    <div id='search-results' class='search-results'></div>                </div>            </div>            <div id='info' class='info'>                <div id='info-players' class='players'>                    <div id='white' class='player white'>                        <div id='white-name' class='name'></div>                        <div id='white-captures' class='captures'></div>                        <div id='white-time' class='time'></div>                    </div>                    <div id='black' class='player black'>                        <div id='black-name' class='name'></div>                        <div id='black-captures' class='captures'></div>                        <div id='black-time' class='time'></div>                    </div>                </div>                <div id='info-game' class='game'></div>            </div>            <div id='nav-tree-container' class='nav-tree-container'>                <div id='nav-tree' class='nav-tree'></div>            </div>            <div id='options' class='options'>                "+(this.saveUrl?"<a id='option-save' class='option-save' href='#'>"+t["save to server"]+"</a>":"")+"                "+(this.downloadUrl||_c?"<a id='option-download' class='option-download' href='#'>"+t["download sgf"]+"</a>":"")+"                <div class='options-stop'></div>            </div>            <div id='preferences' class='preferences'>                <div><input type='checkbox'> Show variations on board</div>                <div><input type='checkbox'> Mark current move</div>            </div>            <div id='footer' class='footer'></div>            <div id='shade' class='shade'></div>        ";
_139=_139.replace(/ id='([^']+)'/g," id='$1-"+this.uniq+"'");
this.dom.player.innerHTML=_139;
var re=/ id='([^']+)-\d+'/g;
var _13b;
var id;
var _13d;
while(_13b=re.exec(_139)){
id=_13b[0].replace(/'/g,"").replace(/ id=/,"");
_13d="";
_13b[1].split("-").forEach(function(word,i){
word=i?word.charAt(0).toUpperCase()+word.substring(1):word;
_13d+=word;
});
this.dom[_13d]=_2(id);
}
[["moveNumber","setPermalink"],["controlFirst","first"],["controlBack","back"],["controlForward","forward"],["controlLast","last"],["controlPass","pass"],["scoreEst","fetchScoreEstimate"],["searchButton","searchRegion"],["searchResults","loadSearchResult"],["searchClose","closeSearch"],["optionDownload","downloadSgf"],["optionSave","save"],["commentsEditDone","finishEditComment"],["gameInfoEditDone","finishEditGameInfo"],["navTree","navTreeClick"]].forEach(function(eh){
if(this.dom[eh[0]]){
_5(this.dom[eh[0]],this[eh[1]],this);
}
}.bind(this));
_4(this.dom.toolsSelect,"change",function(e){
this.selectTool.apply(this,[(e.target||e.srcElement).value]);
},this,true);
},enableNavSlider:function(){
if(this.progressiveLoad){
_b(this.dom.navSliderThumb);
return;
}
this.dom.navSlider.style.cursor="pointer";
var _142=false;
var _143=null;
_4(this.dom.navSlider,"mousedown",function(e){
_142=true;
_7(e);
},this,true);
_4(document,"mousemove",function(e){
if(!_142){
return;
}
var xy=_6(e,this.dom.navSlider);
clearTimeout(_143);
_143=setTimeout(function(){
this.updateNavSlider(xy[0]);
}.bind(this),10);
_7(e);
},this,true);
_4(document,"mouseup",function(e){
if(!_142){
return true;
}
_142=false;
var xy=_6(e,this.dom.navSlider);
this.updateNavSlider(xy[0]);
return true;
},this,true);
},updateNavSlider:function(_149){
var _14a=this.dom.navSlider.offsetWidth-this.dom.navSliderThumb.offsetHeight;
var _14b=this.totalMoves;
var _14c=!!_149;
_149=_149||(this.moveNumber/_14b*_14a);
_149=_149>_14a?_14a:_149;
_149=_149<0?0:_149;
var _14d=parseInt(_149/_14a*_14b,10);
if(_14c){
this.nowLoading();
var _14e=_14d-this.cursor.getMoveNumber();
for(var i=0;i<Math.abs(_14e);i++){
if(_14e>0){
this.variation(null,true);
}else{
if(_14e<0){
this.cursor.previous();
}
}
}
if(_14e<0){
this.board.revert(Math.abs(_14e));
}
this.doneLoading();
this.refresh();
}
_149=parseInt(_14d/_14b*_14a,10)||0;
this.dom.navSliderThumb.style.left=_149+"px";
},updateNavTree:function(){
if(!this.prefs.showNavTree){
return;
}
if(!this.unsavedChanges&&this.updatedNavTree){
this.showNavTreeCurrent();
return;
}
this.updatedNavTree=true;
var html="",_151=this.cursor.node._id,_152=this.board.renderer.pointWidth+5,path=[this.cursor.getGameRoot().getPosition()],_154=this;
var _155=function(node,_157,_158){
var _159=0,_15a=0,_15b=_157,_15c;
html+="<li"+(_158==0?" class='first'":"")+"><div class='mainline'>";
do{
_15c=path.join("-")+"-"+_15a;
html+="<a href='#' id='navtree-node-"+_15c+"' class='"+(typeof node.W!="undefined"?"w":(typeof node.B!="undefined"?"b":"x"))+"'>"+(_15b)+"</a>";
_15b++;
if(node._children.length!=1){
break;
}
if(node._parent._parent==null){
path.push(node.getPosition());
}else{
_15a++;
}
node=node._children[0];
_159++;
}while(node);
html+="</div>";
if(node._children.length>1){
html+="<ul style='margin-left: "+(_159*_152)+"px'>";
}
for(var i=0;i<node._children.length;i++){
if(node._children.length>1){
path.push(i);
}
_155(node._children[i],_15b,i);
if(node._children.length>1){
path.pop();
}
}
if(node._children.length>1){
html+="</ul>";
}
html+="</li>";
};
_155(this.cursor.getGameRoot(),0,0);
this.dom.navTree.style.width=((this.totalMoves+2)*_152)+"px";
this.dom.navTree.innerHTML="<ul class='root'>"+html+"</ul>";
setTimeout(function(){
this.showNavTreeCurrent();
}.bind(this),0);
},showNavTreeCurrent:function(){
var _15e=_2("navtree-node-"+this.cursor.getPath().join("-"));
if(!_15e){
return;
}
if(this.prevNavTreeCurrent){
this.prevNavTreeCurrent.className=this.prevNavTreeCurrentClass;
}
this.prevNavTreeCurrent=_15e;
this.prevNavTreeCurrentClass=_15e.className;
_15e.className="current";
},navTreeClick:function(e){
var _160=e.target||e.srcElement;
if(_160.nodeName.toLowerCase()=="li"&&_160.className=="first"){
_160=_160.parentNode.previousSibling.lastChild;
}
if(!_160||!_160.id){
return;
}
var path=_160.id.replace(/^navtree-node-/,"").split("-");
this.goTo(path,true);
_7(e);
},resetLastLabels:function(){
this.labelLastNumber=1;
this.labelLastLetter="A";
},getGameDescription:function(_162){
var root=this.cursor.getGameRoot();
if(!root){
return;
}
var desc=(_162?"":root.GN||this.gameName);
if(root.PW&&root.PB){
var wr=root.WR?" "+root.WR:"";
var br=root.BR?" "+root.BR:"";
desc+=(desc.length?" - ":"")+root.PW+wr+" vs "+root.PB+br;
}
return desc;
},sgfCoordToPoint:function(_167){
if(!_167||_167=="tt"){
return {x:null,y:null};
}
var _168={a:0,b:1,c:2,d:3,e:4,f:5,g:6,h:7,i:8,j:9,k:10,l:11,m:12,n:13,o:14,p:15,q:16,r:17,s:18};
return {x:_168[_167.charAt(0)],y:_168[_167.charAt(1)]};
},pointToSgfCoord:function(pt){
if(!pt||(this.board&&!this.boundsCheck(pt.x,pt.y,[0,this.board.boardSize-1]))){
return null;
}
var pts={0:"a",1:"b",2:"c",3:"d",4:"e",5:"f",6:"g",7:"h",8:"i",9:"j",10:"k",11:"l",12:"m",13:"n",14:"o",15:"p",16:"q",17:"r",18:"s"};
return pts[pt.x]+pts[pt.y];
},expandCompressedPoints:function(_16b){
var _16c;
var ul,lr;
var x,y;
var _171=[];
var hits=[];
for(var i=0;i<_16b.length;i++){
_16c=_16b[i].split(/:/);
if(_16c.length>1){
ul=this.sgfCoordToPoint(_16c[0]);
lr=this.sgfCoordToPoint(_16c[1]);
for(x=ul.x;x<=lr.x;x++){
for(y=ul.y;y<=lr.y;y++){
_171.push(this.pointToSgfCoord({x:x,y:y}));
}
}
hits.push(i);
}
}
_16b=_16b.concat(_171);
return _16b;
},setPermalink:function(){
if(!this.permalinkable){
return true;
}
if(this.unsavedChanges){
alert(t["unsaved changes"]);
return;
}
this.hook("setPermalink");
},nowLoading:function(msg){
if(this.croaked||this.problemMode){
return;
}
msg=msg||t["loading"]+"...";
if(_2("eidogo-loading-"+this.uniq)){
return;
}
this.domLoading=document.createElement("div");
this.domLoading.id="eidogo-loading-"+this.uniq;
this.domLoading.className="eidogo-loading"+(this.theme?" theme-"+this.theme:"");
this.domLoading.innerHTML=msg;
this.dom.player.appendChild(this.domLoading);
},doneLoading:function(){
if(this.domLoading&&this.domLoading!=null&&this.domLoading.parentNode){
this.domLoading.parentNode.removeChild(this.domLoading);
this.domLoading=null;
}
},croak:function(msg){
this.doneLoading();
if(this.board){
alert(msg);
}else{
if(this.problemMode){
this.prependComment(msg);
}else{
this.dom.player.innerHTML+="<div class='eidogo-error "+(this.theme?" theme-"+this.theme:"")+"'>"+msg.replace(/\n/g,"<br />")+"</div>";
this.croaked=true;
}
}
}};
})();

(function(){
var _1=window.eidogoConfig||{};
var _2={theme:"problem",problemMode:true,markVariations:false,markNext:false,shrinkToFit:true};
var _3=eidogo.util.getPlayerPath();
var _4=eidogo.playerPath=(_1.playerPath||_3||"player").replace(/\/$/,"");
if(!_1.skipCss){
eidogo.util.addStyleSheet(_4+"/css/player.css");
if(eidogo.browser.ie&&parseInt(eidogo.browser.ver,10)<=6){
eidogo.util.addStyleSheet(_4+"/css/player-ie6.css");
}
}
eidogo.util.addEvent(window,"load",function(){
eidogo.autoPlayers=[];
var _5=[];
var _6=document.getElementsByTagName("div");
var _7=_6.length;
for(var i=0;i<_7;i++){
if(eidogo.util.hasClass(_6[i],"eidogo-player-auto")||eidogo.util.hasClass(_6[i],"eidogo-player-problem")){
_5.push(_6[i]);
}
}
var el;
for(var i=0;el=_5[i];i++){
var _a={container:el,disableShortcuts:true,theme:"compact"};
for(var _b in _1){
_a[_b]=_1[_b];
}
if(eidogo.util.hasClass(el,"eidogo-player-problem")){
for(var _b in _2){
_a[_b]=_2[_b];
}
}
var _c=el.getAttribute("sgf");
if(_c){
_a.sgfUrl=_c;
}else{
if(el.innerHTML){
_a.sgf=el.innerHTML;
}
}
el.innerHTML="";
eidogo.util.show(el);
var _d=new eidogo.Player(_a);
eidogo.autoPlayers.push(_d);
}
});
})();

