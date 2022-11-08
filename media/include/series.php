<?php
/******************************************************************
**
**	ATOMMAG MEDIA SERVER (c) oqnq 2013
**	series.php - include for playing series
**
**
*******************************************************************/

/*
commands:
audio_delay <value> [abs]
    Set/adjust the audio delay.
    If [abs] is not given or is zero, adjust the delay by <value> seconds.
    If [abs] is nonzero, set the delay to <value> seconds.
get_file_name
    Print out the name of the current file.
get_time_pos
    Print out the current position in the file in seconds, as float.
get_time_length
    Print out the length of the current file in seconds.
?key_down_event <value>
    Inject <value> key code event into MPlayer.
loadfile <file|url> <append>
    Load the given file/URL, stopping playback of the current file/URL.
    If <append> is nonzero playback continues and the file/URL is
    appended to the current playlist instead.

pause
    Pause/unpause the playback.
quit [value]
    Quit MPlayer. The optional integer [value] is used as the return code
    for the mplayer process (default: 0).
seek <value> [type]
    Seek to some place in the movie.
        0 is a relative seek of +/- <value> seconds (default).
        1 is a seek to <value> % in the movie.
        2 is a seek to an absolute position of <value> seconds.

?seek_chapter <value> [type]
    Seek to the start of a chapter.
        0 is a relative seek of +/- <value> chapters (default).
        1 is a seek to chapter <value>.
stop
    Stop playback.
sub_delay <value> [abs]
    Adjust the subtitle delay by +/- <value> seconds or set it to <value>
    seconds when [abs] is nonzero.
???
vo_fullscreen [value]
    Toggle/set fullscreen mode.

vo_ontop [value]
    Toggle/set stay-on-top.

vo_rootwin [value]
    Toggle/set playback on the root window.
quit
!!!


*/
//$file="/universe/_SOROZATOK/mission_impossible/mission.impossible.S01E08.avi";
$file="/storage/___UNI/sorozatok/Star Trek/The-Original-Series/2/S02E01-Star-Trek-The-Original-Series-(DVDRIP).avi";
?>


<script>
function videoCommand(cmd){
	console.log(cmd);
}
//	part of $
///	NAMESPACING...
$(function(){
var SeriesApp={
	Models:{},
	Collections:{},
	Views:{},
	Templates:{}
};

SeriesApp.Models.OneFile=Backbone.Model.extend({
});
SeriesApp.Collections.AllFiles=Backbone.Collection.extend({
	model: SeriesApp.Models.OneFile,
	url: 'commands/find_series.py',
	initialize:function(){
		//console.log("coll init");
	}
});
//SeriesApp.Templates.AllTitles=_.template($( "#tp-titles" ).html());
SeriesApp.Views.TitlesView=Backbone.View.extend({
	//template: SeriesApp.Templates.AllTitles,
	/*events:{},*/
	el:$("#kaki"),
	events: {
		change: function(e){
			switch(e.target.id){
				case "seriesTitle":
					this.showEpisodes(e.target.selectedOptions[0].value);
					console.log("showEpisodes");
				break;
				case "episodeNumber":
					this.fillEpisode(e.target.selectedOptions[0].value);
				break;
			}
		}
	},
	initialize:	function(){
		//this.model.fetch();
		_.bindAll(this, "render", "addOne", "addAll"),
		this.collection.bind("reset", this.render);
		this.collection.bind("add", this.addOne);
	},
	render: function(){
		//console.log("view rendering");
		//console.log(this.collection.length);
		
		//$(this.el).html(/*this.template()*/"<ul></ul>");
		this.addAll();
		var dd=this;
		$.ajax({
			url:"commands/usage_db.php",
			type:"GET",
			dataType: "json",
			success:function(data){
				//console.log(data[0].title);
				$("#seriesTitle").val(data[0].title);
				dd.showEpisodes(data[0].title);
				//console.log(data[0].se);
				var _val="";
				$("option",$("#episodeNumber")).each(function(){
					//console.log($(this).text(),data[0].se);
					if($(this).text()==data[0].se) {
						_val=$(this).val();
						
					}
				});
				$("#episodeNumber").val(_val);
				$("option:selected",$("#episodeNumber")).next("option").prop('selected', true);
				dd.fillEpisode($("#episodeNumber").val());
			}
		});

	},
	addAll: function(){
		$("#seriesTitle", this.el).empty();
		_.each(_.sortBy(_.uniq(this.collection.pluck('title')),function(name){return name.toLowerCase();}),this.addOne);
		
	},
	addOne: function(item){
		//console.log("add one");
		//view=new SeriesApp.Views.TitleView({model: item});
		$("#seriesTitle", this.el).append(/*view.render()*/"<option>"+item+"</option>"); 
		
	},
	showEpisodes: function(title){
		$("#episodeNumber", this.el).empty();
		_.each(_.sortBy(this.collection.where({title: title}),function(item){
			_key= 100*parseInt(item.attributes.series)+parseInt(item.attributes.episode);
			//console.log(_key);
			return(_key);
		}),function(obj){
			_v=obj.attributes.filename;
			_s=addZero(obj.attributes.series);
			_e=addZero(obj.attributes.episode);
			$("#episodeNumber", this.el).append("<option value=\""+_v+"\">"+"S"+_s+"E"+_e+"</option>");
		});
	},
	fillEpisode: function(name){
		$("#playfile").val(name);
	}
});
//SeriesApp.Templates.OneTitle=_.template($( "#tp-title" ).html());
SeriesApp.Views.TitleView=Backbone.View.extend({
	tagName: "option",
//	template: SeriesApp.Templates.OneTitle,
	initialize: function(){
		_.bindAll(this, "render");
	},
	render: function(){
		return $( this.el ).append(/*this.template(this.model.toJSON())*/this.model);
	}
});

SeriesApp.Router=Backbone.Router.extend({
	routes: {
		"": "defaultRoute"
	},
	defaultRoute: function(){
		//console.log("default route");
		SeriesApp.files= new SeriesApp.Collections.AllFiles();
		new SeriesApp.Views.TitlesView({collection: SeriesApp.files});
		SeriesApp.files.fetch();
		//console.log(SeriesApp.files.length);
	}
});

var AppRouter=new SeriesApp.Router();
Backbone.history.start();
$("#s_rew").button({text:false,icons:{primary:"ui-icon-seek-prev"}})
	.click(function(){videoCommand("seek -20");});
$("#s_pause").button({text:false,icons:{primary:"ui-icon-pause"}})
	.click(function(e){
	videoCommand("pause");
	//	change || to |> .. better off doing it in the function?
	var _currentIcon=$(e.target.parentNode).button("option", "icons").primary;
	if(_currentIcon=="ui-icon-pause") $(e.target.parentNode).button("option", "icons", {primary: "ui-icon-play"});
	else $(e.target.parentNode).button("option", "icons", {primary: "ui-icon-pause"});
	//console.log(_currentIcon);
	
});
$("#s_fwd").button({text:false,icons:{primary:"ui-icon-seek-next"}})
	.click(function(){videoCommand("seek +20");});
$("#s_stop").button({text:false,icons:{primary:"ui-icon-stop"}})
	.click(function(){videoCommand("quit");});
});
</script>
<script type="text/template" id="tp-titles">
	<ul>
	</ul>
</script>
<script type="text/template" id="tp-title">
		<div><%= title %></div>
</script>


<div>
	<div id="kaki">
		<select id="seriesTitle"></select><select id="episodeNumber"></select>
		<button id="start">start player</button>
	</div>
	<input id="playfile" value="<?=$file?>" style="width:100%" />
	<br/>
	<div id="controls">
		<button id="s_rew">rewind</button>
		<button id="s_pause">pause</button>
		<button id="s_stop">stop</button>
		<button id="s_fwd">forward</button>
	</div>
	<input id="command" value="seek 200" />
	<button id="execute">go</button>
<pre>
<?include("commands/slave.txt");?>
</pre>
</div>
