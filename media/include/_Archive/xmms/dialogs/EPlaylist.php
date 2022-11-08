<?php
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


class XMMS2SWIEPlaylistDialog implements XMMS2SWIDialogInterface
{
	public function init(XMMS2SWI $swi)
	{

	}
	public function run(XMMS2SWI $swi)
	{
		if (isset($_GET['pl'])) {
			$swi->xmms2->jump($_GET['pl']);
		} else if (isset($_GET['rm'])) {
			$swi->xmms2->remove($_GET['rm']);
		} else if (isset($_GET['up'])) {
			$swi->xmms2->move($_GET['up'], $_GET['up']-1);
		} else if (isset($_GET['down'])) {
			$swi->xmms2->move($_GET['down'], $_GET['down']+1);
		} else if (isset($_GET['clear'])) {
			$swi->xmms2->clear();
		}
	}
	public function show(XMMS2SWI $swi)
	{
		$total = 0;
		$list = $swi->xmms2->playlist($total);

		echo '<table class="playlist" cellspacing="0">'."\n";
		echo '<tr><th style="width: 20px"></th>';
		echo '<th colspan="3">Playlist editor ';
		echo '[<a class="yellow" href="index.php?d=Playlist" title="Playlist">playlist</a>]';
		echo '</th></tr>'."\n";
		foreach($list as $idx => $item) {
			$min = floor($item['time'] / 60);
			$sec = $item['time'] % 60;
			if ($item['current']) {
				$class = ' class="pl_cur"';
				$classt = ' class="pl_curt"';
			} else {
				$class = '';
				$classt = ' class="t"';
			}
			echo '<tr>'."\n";
			echo ' <td'.$class.'>'.($idx+1).'</td>'."\n";
			echo ' <td'.$class.'><a href="index.php?pl='.$item['index'].'">'.htmlspecialchars($item['title']).'</a></td>'."\n";
			echo ' <td'.$class.'>';
			echo '<a href="index.php?up='.$item['index'].'"><img src="icons/move-up.png" alt="[up]"/></a>';
			echo '<a href="index.php?down='.$item['index'].'"><img src="icons/move-down.png" alt="[down]"/></a>';
			echo '<a href="index.php?rm='.$item['index'].'"><img src="icons/remove.png" alt="[rm]"/></a>';
			echo '</td>'."\n";
			echo ' <td'.$classt.'>'.$min.':'.two($sec).'</td>'."\n";
			echo '</tr>'."\n";
		}
		$hour = floor($total / 60 / 60);
		$min = floor($total / 60) - $hour * 60;
		$sec = $total % 60;
		echo '<tr><td></td><td colspan="2">';
		echo '<a href="index.php?clear"><img src="icons/item-remove.png" alt=""/> Clear playlist</a>';
		echo '</td></tr>'."\n";
		echo '</table>'."\n";
	}

}




?>
