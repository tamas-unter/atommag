/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	controls.js - client-side js actions, jquery-based mostly
**
**
*******************************************************************/

//	GLOBAL VARIABLES
var masterVolume=0;

/*********************************	cookies	**********************/
function getCookie(c_name){
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++){
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name){
		return unescape(y);
		}
	}
}

function setCookie(c_name,value,exdays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function checkCookie(){
	var username=getCookie("username");
	if (username!=null && username!=""){
		alert("Welcome again " + username);
	}
	else{
		username=prompt("Please enter your name:","");
		if (username!=null && username!=""){
			setCookie("username",username,365);
		}
	}
}
//	IE ajax workaround
function getRandomID(){return Math.round(Math.random()*Math.pow(2,30));}

//	tabs
function initTab(tabNumber){
	//console.log("initTab", _at);
	switch(parseInt(tabNumber)){
		case 0:
			//console.log("music0");
			initMusicplayer();
		break;
		case 1:
			initSeries();
		break;
		default:
			console.log("inittab default", tabNumber);
		break;
	}
}

/***********************************	music player globals	*********************/
var totalSec=0;
var currentSec=0;
var savedPos=0;
var savedTime=0;
var currentTime=0;
var tickerID=null;

/***********************************	volume slider	*****************************/
function getVolume(){
	//{ "Volume": {"Master": {"Mono": 50},"PCM": {"Left": 95,"Right": 95},"Front": {"Left": 92,"Right": 92}}}
	$.ajax({
		url: "commands/get_mixers.php",
		data: {r: getRandomID()},
		dataType: 'json',
		async: false,
		success: function(data){
			masterVolume=data.Volume.Master.Mono;
		}
	});
}

function colorize(selector,value){
	if(value<5){
		myColor="#ccc";
		myBGColor="#888";
	} else{
		myRed=Math.min(Math.floor(value*255/50),255);
		myGreen=Math.min(Math.floor((100-value)*255/50),255);
		myColor="rgb("+myRed+","+myGreen+",0)";
		myBGColor="rgb("+myRed+","+myGreen+",20)";
	}
	var cssObj={'border-color':myColor,'background-color': myBGColor};
	$(selector).css(cssObj);
}


/**************************	music player ***************************/
function initMusicplayer(){
	//	xmms-musicplayer
	//console.log("xmms init");
	updateTrackList();
	
	$( "#progressbar" ).progressbar({
		value: false, 
		min:0
	})
	.click(function(e,u){
		//	TODO: parent()...
		var x = e.pageX - $(this).position().left-55;//this.offsetLeft;
		var w = $(this).width();
		var r = x/w;
		
		var secondsToSeek = Math.round(totalSec * r - currentSec);
		//	plus sign needed!
		if(secondsToSeek>0)secondsToSeek="+"+secondsToSeek;
		$.ajax({
			dataType:'json',
			async: false,
			url:"include/xmms_actions.php",
			data:{c:"seek",v:secondsToSeek,r:getRandomID()},
			success:function(response){
				updateStatus(response);
			}
		});

	});
	
	var buttons=[
		["#rew10","ui-icon-circle-triangle-w"],
		["#beginning","ui-icon-seek-start"],
		["#rewind","ui-icon-seek-prev"],
		["#play","ui-icon-play"],
		["#stop","ui-icon-stop"],
		["#forward","ui-icon-seek-next"],
		["#end","ui-icon-seek-end"],
		["#fwd10","ui-icon-circle-triangle-e"]
	];
	for(i=0;i<buttons.length;i++){
		$(buttons[i][0]).button({text:false, icons:({primary: buttons[i][1]})})
			.click(function(){
				xmmsCommand(this.id);
			});
	}
	xmmsCommand("current");
	$( "#statusPosition" ).click(function(e){
		xmmsCommand("current");
	});
console.log("before updateplaylist");
	updatePlaylist();
	//	playlistSelector - on select, jump to playlist..
	//	search results positioning
	$("#searchResults").hide();
	
	$("#searchResults").css(
		"top",(
			($("#playlist").prop("offsetTop")+2)
		)+"px"
	);
	$("#searchResults").css(
		"left",(
			($("#playlist").prop("offsetLeft")-2)
		)+"px"
	);
	$("#searchResults").css(
		"width",(
			($("#playlist").prop("offsetWidth")+4)
		)+"px"
	);
	
	
	//	search button
	$("#go").click(function(){
		_q=$("#search").val();
		r=$("#searchResults");
		if(_q.length>0){
			//console.log("search for "+q);
			r.html("");
			r.show();
			r=$("<ul>").addClass("searchGroup").appendTo(r);
			//	types of elements to be found..
			keys=["artist","title","album","performer"];
			for(i in keys){
				k=keys[i];
				MAXLEN=31;
				$.ajax({
					url:"commands/xmms_db.php",
					type: "GET",
					data:{c:"search",key:k,q:_q},
					dataType:"json",
					async: false,
					success: function(response){
						if(response && response.length>0){
							
							g=$("<li>").appendTo(r);
							$("<h2>").html(k).appendTo(g);
							ul=$("<ul>").addClass("searchItem").appendTo(g);
							
							for(i=0;i<response.length;i++){
								$("<li>").html(
								/*	response[i].substring(0,MAXLEN)+
									(response[i].length>MAXLEN ? "..":"")
								*/
									response[i]
								).appendTo(ul);
							}
						}
						else{
							//wat?
						}
					}
				});
			}
			
			$("ul.searchItem>li").click(function(){
				//	TODO: xmms_db.php?c=list&key=...&value=...
				console.log($(this).closest("ul.searchGroup>li").find("h2").html());
				console.log($(this).html());
			});
			$("ul.searchItem>li").hover(function(){
				$(this).css("text-decoration","underline");
			},function(){
				$(this).css("text-decoration","none");
			});
		}
	});
}
function addZero(num){if (num<10) return "0"+num; else return num;}
function tick(){
	currentTime=new Date().valueOf();
	//console.log("C ",currentTime);
	currentSec=savedPos+Math.round((currentTime-savedTime)/1000);
	//console.log("C ",currentSec);
	M=Math.round(currentSec/60);
	S=currentSec % 60;
	$("#statusPosition").text(addZero(M)+":"+addZero(S));
	if(currentSec>totalSec){xmmsCommand("current")} else{
		tickerID=setTimeout(function(){tick()},1000);
		$( "#progressbar" ).progressbar({value:currentSec});
	}
}
function updateStatus(status){
	$( "#statusTitle" ).text(status.song);
	st=status.state;
	$( "title" ).text(st+": "+status.song);
	totalSec=parseInt(status.total);
	savedPos=parseInt(status.position);
	
	//console.log(savedTime%10000);
	$( "#progressbar" ).progressbar({max:totalSec, value:savedPos});
	if(tickerID) {clearTimeout(tickerID); tickerID=null;}
	switch (st.toLowerCase()){
		case "playing":
			tick();
			$( "#progressbar" ).progressbar("enable");
			$("#play").button("option","icons",{primary:"ui-icon-pause"});
			$( "#stop" ).button("enable");
			$( "#statusPosition" )
				.removeClass("statusPaused")
				.removeClass("statusStopped")
				.addClass("statusPlaying");
		break;
		case "paused":
			$("#play").button("option","icons",{primary:"ui-icon-play"});
			$( "#stop" ).button("enable");
			$( "#statusPosition" )
				.removeClass("statusPlaying")
				.removeClass("statusStopped")
				.addClass("statusPaused");
		break;
		case "stopped":
			$( "#progressbar" ).progressbar("disable");
			$( "#play" ).button("option","icons",{primary:"ui-icon-play"});
			$( "#stop" ).button("disable");
			$( "#statusPosition" )
				.removeClass("statusPlaying")
				.removeClass("statusPaused")
				.addClass("statusStopped");

		break;
	}
	$( "ul#playlist" ).addClass("waiting");
	setTimeout(updateTrackList,5000);
}

function updatePlaylist(){
	$.ajax({
		url: "commands/xmms_db.php",
		data: {c: "playlist", r: getRandomID()},
		dataType: 'json',
		async: true,
		success: function(data){
			for(i=0;i<data.length;i++){
				$( "#playlistSelector" ).append("<option"+(data[i][1] ? " SELECTED":"")+">"+data[i][0]+"</option>"); 
			}
		}
	});
}
function updateTrackList(){
	$.ajax({
		url: "commands/xmms_db.php",
		data: {c: "tracklist", r: getRandomID()},
		dataType: 'json',
		async: true,
		success: function(data){
			$( "ul#playlist" ).empty();
			for(var i in data){
				selected=(data[i].current ? " class=\"playlistCurrent\"" : "");
				trackno=(data[i].track_id ? data[i].track_id : (data[i].tracknr ? data[i].tracknr : ""));
				if (trackno.length>0) trackno="["+trackno+"] ";
				$( "ul#playlist" ).removeClass("waiting");
				sss=(data[i].current ? "playlistCurrent" : "playlistNormal");
				$("<li></li>").append("<a href=\"javascript:jumpTo("+i+");\">"+
				trackno+data[i].artist+": "+data[i].title+"</a>")
					.addClass(sss)
					.click(function(e){
						$( "ul#playlist li.playlistCurrent" ).removeClass("playlistCurrent");
						//console.log(e);
						if(e.target.parentNode.nodeName=="LI")
							$(e.target.parentNode).removeClass("playlistNormal").addClass("playlistCurrent");
					})
					.appendTo( "ul#playlist" );
				//$( "ul#playlist" ).append("<li"+selected+">"+"<a href=\"javascript:jumpTo("+i+");\">"+trackno+data[i].artist+": "+data[i].title+"</a></li>");
			}
		}
	});
}
function jumpTo(track){
	xmmsCommand("jump",track);
}

function xmmsCommand(caption,parameter){
	
	switch(caption)	{
		case "rew10":
			var d={c:"prev",v:"10"};
		break;
		case "beginning":
			var d={c:"prev"};
		break;
		case "rewind":
			var d={c:"seek",v:"-20"};
		break;
		case "play":
			var d={c:"toggle"};
		break;
		case "stop":
			var d={c:"stop"};
		break;
		case "forward":
			var d={c:"seek",v:"+30"};
		break;
		case "end":
			var d={c:"next"};
		break;
		case "fwd10":
			var d={c:"next",v:"10"};
		break;
		case "status":
			var d={c:"status"};
		break;
		case "current":
			var d={c:"current"};
		break;
		case "jump":
			//	NO IDEA WHY, but needed to fix this
			var d={c:"jump",v:parameter+1};
		break;
		case "switch":
			var d={c:"playlist switch",v:parameter};
		break;
	}	
	d.r=getRandomID();
	$.ajax({
		dataType:'json',
		async: false,
		url:"include/xmms_actions.php",
		data:d,
		success:function(response){
			//	try it here...
			savedTime=new Date().valueOf();
			updateStatus(response);
		}
	});
}

/***********************	series	**********************************/
function initSeries(){
	//	series
	$( "#s_rew" ).button()
	.click(function(e){
		$.ajax({
			url:"include/mplayer_actions.php",
			data:{c:"seek -60"},
			type:"GET",
			success:function(){console.log("sought 1 minute back");}
		});
	});
	$( "#s_fwd" ).button()
	.click(function(e){
		$.ajax({
			url:"include/mplayer_actions.php",
			data:{c:"seek 60"},
			type:"GET",
			success:function(){console.log("sought 1 minute back");}
		});
	});
	$( "#s_pause" ).button()
	.click(function(e){
		$.ajax({
			url:"include/mplayer_actions.php",
			data:{c:"pause"},
			type:"GET",
			success:function(){console.log("sought 1 minute back");}
		});
	});
	$( "#s_stop" ).button()
	.click(function(e){
		$.ajax({
			url:"include/mplayer_actions.php",
			data:{c:"quit"},
			type:"GET",
			success:function(){console.log("sought 1 minute back");}
		});
	});

	$( "#execute" ).button()
	.click(function(e){
		cmd=$( "#command" ).val();
		$.ajax({
			url: "include/mplayer_actions.php",
			data:{c:cmd},
			type:"GET",
			success:function(result){console.log(cmd, "issued");}
		});
	});
	$( "#start" ).button()
	.click(function(e){
		file=$("#playfile").val();
		console.log("started " + file);
		$.ajax({
			url: "include/mplayer_actions.php",
			data:{f:file},
			type:"POST",
			success:function(result){
				console.log("started");
				$.ajax({
					url: "commands/usage_db.php",
					type:"GET",
					dataType: "json",
					data:{
						title: $("#seriesTitle").val(),
						se: $("option:selected",$("#episodeNumber")).text()
					},
					success:function(data){
						//whatever...
						console.log("saved...");
					}
				});
			}
		});
	});

}


/***********************	MAIN  ************************************/
//	TODO: update volume values regularly... / song status as well
$(function() {
	//	general
	getVolume();
	$("#volume").addClass("slider").slider({
		value: masterVolume,
		orientation: "vertical",
		slide: function(e,u){
			$.ajax({
				url: "commands/set_mixers.php",
				data: {v: u.value,r: getRandomID()}
			}).done(function(){
				//	exactly...
			});
		},
		change: function(e,u){
			colorize("#volume",u.value);
		}
	});
	colorize("#volume",masterVolume);
	
	//	tabs
	activeTabIndex = getCookie("activeTab");
	if (!activeTabIndex) activeTabIndex=0;
	//console.log("init",activeTabIndex);
	$( "#tabs" ).tabs();
	$( "#tabs" ).tabs("option", "active", activeTabIndex);
	$( "#tabs" ).tabs({
		activate: function(e,u){
			_at=$("#tabs").tabs("option","active");
			setCookie("activeTab",_at,7);
			//	TODO: load content of the active tab, stop others, update title...
			initTab(_at);
		}
	});
	initTab(activeTabIndex);
	//	playlist
	$( "#playlistSelector" ).change(function(e,i){
		xmmsCommand("switch", e.target[e.target.selectedIndex].value);
		//console.log(e.target[e.target.selectedIndex].value);
		
	});
});

