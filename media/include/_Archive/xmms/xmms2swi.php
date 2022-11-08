<?php
/*		ORIGINAL COPYRIGHT	*/
/*		modified oqnq		*/
/*
 * Copyright (c) 2008, Przemysław Grzywacz
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the XMMS2SWI nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Przemysław Grzywacz ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Przemysław Grzywacz BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

function two($x)
{
	if (strlen($x) == 1) return '0'.$x;
	else return $x;
}

interface  XMMS2SWIConfigInterface
{
	public function sectionExist($section);
	public function keyExist($section, $key);
	public function listSections();
	public function listSection($section);
	public function get($section, $key, $default = null);
}

interface XMMS2SWIDialogInterface
{
	public function init(XMMS2SWI $swi);
	public function run(XMMS2SWI $swi);
	public function show(XMMS2SWI $swi);
}

interface XMMS2SWIModuleInterface
{
	public function init(XMMS2SWI $swi);
	public function run(XMMS2SWI $swi);
}

class XMMS2SWI {
	/**
	 * XMMS2 client
	 *
	 * @var XMMS2
	 */
	public $xmms2;

	/**
	 * Configuration
	 *
	 * @var XMMS2SWIConfigInterface $config
	 */
	public $config;

	/**
	 * Current dialog
	 *
	 * @var XMMS2SWIDialogInterface
	 */
	private $dialog;

	private $modules;

	public function __construct(XMMS2SWIConfigInterface $config)
	{
		
		$this->config = $config;

		require_once('xmms2.php');
		$this->xmms2 = new XMMS2();
		$this->modules = array();

		$this->loadModules();

		if (!isset($_SESSION['dialog'])) {
			$_SESSION['dialog'] = 'Null';
			$this->setVisible(true);
			$new = true;
		} else {
			$new = false;
		}
		$this->changeDialog($_SESSION['dialog'], $new);
		
	}

	private function loadModules()
	{
		if (!isset($_SESSION['modules'])) $_SESSION['modules'] = array();

		$modules = $this->config->listSection('modules');
		foreach($modules as $module) {
			require_once($this->config->get('modules', $module));
			$this->modules[$module] = new $module();
			if (!($this->modules[$module] instanceOf XMMS2SWIModuleInterface)) {
				throw new Exception('Specified class is not a XMMS2SWI module');
			}

			if (!isset($_SESSION['modules'][$module])) {
				$this->modules[$module]->init($this);
			}

			$this->modules[$module]->run($this);
		}
	}

	public function toggleVisible()
	{
		if ($this->getVisible() == true) {
			$this->setVisible(false);
		} else {
			$this->setVisible(true);
		}
	}

	public function setVisible($bool)
	{
		$_SESSION['visible'] = $bool;
	}

	public function getVisible()
	{
		return $_SESSION['visible'];
	}

	public function call($action)
	{
		switch ($action) {
			case 'prev': 	$this->xmms2->prev(); break;
			case 'sprev':	$this->xmms2->seek('-15'); break;
			case 'play':	$this->xmms2->play(); break;
			case 'pause':	$this->xmms2->pause(); break;
			case 'stop':	$this->xmms2->stop(); break;
			case 'snext':	$this->xmms2->seek('+15'); break;
			case 'next':	$this->xmms2->next(); break;
		}
	}

	public function changeDialog($dialog, $new = false)
	{
	//oqnq20130215
		$file = __DIR__.'/dialogs/'.$dialog.'.php';

		if (!file_exists($file)) {
		echo"nincs";
			//$this->changeDialog('Null', true);
			return;
		}
		require_once($file);

		$class = 'XMMS2SWI'.$dialog.'Dialog';

		if (!class_exists($class)) {
			$this->changeDialog('Null', true);
			return;
		}

		$this->dialog = new $class();
		$_SESSION['dialog'] = $dialog;
		if ($new) {
			$this->dialog->init($this);
			$this->setVisible(true);
		}
	}

	public function showDialog()
	{
		if ($this->getVisible()) {
			$this->dialog->show($this);
		}
	}

	public function runDialog()
	{
		if ($this->getVisible()) {
			$this->dialog->run($this);
		}
	}

	public function showPlayer()
	{
	//	TODO:
	//		ajaxify
	//		icons -> jquery button
		//	TODO: e helyett egy status-ost!
		$now = $this->xmms2->nowPlaying();
		$min = floor($now['time'] / 60);
		$sec = $now['time'] % 60;
		if (!$now) $now = array('title'=>'', 'time'=>0);
		echo "\n\n";
		echo '<table class="player">'."\n";
		echo '<tr><td>'."\n";
		echo htmlspecialchars($now['title']).' <strong>('.two($min).':'.two($sec).')</strong>';;
		echo '</td></tr>'."\n";
		echo '<tr><td>'."\n";
		echo '<a href="index.php?p=prev" title="Previous"><img src="include/xmms/icons/media-skip-backward.png" alt="[prev]"/></a>';
		echo '<a href="index.php?p=sprev" title="-15sec"><img src="include/xmms/icons/media-seek-backward.png" alt="[-15]"/></a>';
		echo '<a href="index.php?p=play" title="Play"><img src="include/xmms/icons/media-playback-start.png" alt="[play]"/></a>';
		echo '<a href="index.php?p=pause" title="Pause"><img src="include/xmms/icons/media-playback-pause.png" alt="[pause]"/></a>';
		echo '<a href="index.php?p=stop" title="Stop"><img src="include/xmms/icons/media-playback-stop.png" alt="[stop]"/></a>';
		echo '<a href="index.php?p=snext" title="+15sec"><img src="include/xmms/icons/media-seek-forward.png" alt="[+15]"/></a>';
		echo '<a href="index.php?p=next" title="Next"><img src="include/xmms/icons/media-skip-forward.png" alt="[next]"/></a>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo '<a href="index.php?d=Playlist" title="Playlist"><img src="include/xmms/icons/playlist.png" alt="[pl]"/></a>';
		echo '<a href="index.php?d=Browser" title="Browser"><img src="include/xmms/icons/media-eject.png" alt="[...]"/></a>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo '<a href="index.php" title="Reload"><img src="include/xmms/icons/view-refresh.png" alt="[reload]"/></a>';
		echo '<a href="index.php?opt=toggleVisible" title="Toggle visible"><img src="include/xmms/icons/toggle-visible.png" alt="[...]"/></a>';
		echo '</td></tr>'."\n";
		echo '</table>'."\n";
		?>
<div id="playerControls" class="ui-widget-header ui-corner-all">		
	<button id="beginning">go to beginning</button>
	<button id="rewind">rewind</button>
	<button id="play">play</button>
	<button id="stop">stop</button>
	<button id="forward">fast forward</button>
	<button id="end">go to end</button>
	<script>
    $( "#beginning" ).button({
      text: false,
      icons: {
        primary: "ui-icon-seek-start"
      }
    });
    $( "#rewind" ).button({
      text: false,
      icons: {
        primary: "ui-icon-seek-prev"
      }
    });
    $( "#play" ).button({
      text: false,
      icons: {
        primary: "ui-icon-play"
      }
    })
    .click(function() {
      var options;
      if ( $( this ).text() === "play" ) {
        options = {
          label: "pause",
          icons: {
            primary: "ui-icon-pause"
          }
        };
      } else {
        options = {
          label: "play",
          icons: {
            primary: "ui-icon-play"
          }
        };
      }
      $( this ).button( "option", options );
    });
    $( "#stop" ).button({
      text: false,
      icons: {
        primary: "ui-icon-stop"
      }
    })
    .click(function() {
      $( "#play" ).button( "option", {
        label: "play",
        icons: {
          primary: "ui-icon-play"
        }
      });
    });
    $( "#forward" ).button({
      text: false,
      icons: {
        primary: "ui-icon-seek-next"
      }
    });
    $( "#end" ).button({
      text: false,
      icons: {
        primary: "ui-icon-seek-end"
      }
    });
	</script>
</div>
		<?
	}
}


?>
