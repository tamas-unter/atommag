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


class XMMS2SWIBrowserDialog implements XMMS2SWIDialogInterface
{
	public function init(XMMS2SWI $swi)
	{
		if (!isset($_SESSION['browser'])) {
			// this prevents the reseting of screen after changing dialogs
			$_SESSION['browser'] = array();
			$_SESSION['browser']['mount'] = null;
			$_SESSION['browser']['dir'] = array();
			$_SESSION['browser']['files'] = array();
		}
	}
	public function run(XMMS2SWI $swi)
	{
		if (isset($_GET['mnt'])) {
			$_SESSION['browser']['dir'] = array();
			$_SESSION['browser']['files'] = array();
			$_SESSION['browser']['mount'] = $_GET['mnt'];
			if (!$swi->config->keyExist('browser/mount', $_GET['mnt'])) {
				$_SESSION['browser']['mount'] = null;
			}
		} else if (isset($_GET['cd'])) {
			if (strpos($_GET['cd'], '/') !== false) {
				// ignore
			} else if ($_GET['cd'] == '..') {
				if (count($_SESSION['browser']['dir'])) {
					array_pop($_SESSION['browser']['dir']);
				} else {
					$_SESSION['browser']['dir'] = array();
					$_SESSION['browser']['files'] = array();
					$_SESSION['browser']['mount'] = null;
				}
			} else {
				$this->cd($swi, $_GET['cd']);
			}
		} else if (isset($_GET['add']) && isset($_SESSION['browser']['files'][$_GET['add']])) {
			$this->add($swi, $_GET['add']);
		} else if (isset($_GET['addall'])) {
			$this->addAll($swi);
		}
	}

	public function show(XMMS2SWI $swi)
	{
		if ($_SESSION['browser']['mount'] == null) {
			$list = $this->showMounts($swi);
		} else {
			$mntPoint = $swi->config->get('browser/mount', $_SESSION['browser']['mount'], null);
			if ($mntPoint == null) {
				$list = $this->showMounts($swi);
			} else {
				$mnt = explode(',', $mntPoint, 3);
				$list = $this->browse($swi, $_SESSION['browser']['mount'], $mnt[0], $mnt[1], $mnt[2]);
			}
		}


		//var_dump($list);

		echo '<table class="playlist" cellspacing="0">'."\n";
		echo '<tr><th style="width: 20px"></th><th colspan="3">'.$list['title'].'</th></tr>'."\n";
		foreach($list['list'] as $item) {
			echo '<tr>'."\n";
			echo ' <td><img src="'.$item['icon'].'" alt=""/></td>'."\n";
			echo ' <td><a href="'.$item['link'].'">'.htmlspecialchars($item['title']).'</a></td>'."\n";
			echo ' <td class="t">'.(isset($item['opt'])?$item['opt']:'').'</td>'."\n";
			echo '</tr>'."\n";
		}
		if (isset($list['links'])) {
			echo '<tr><td></td><td colspan="2">';
			echo $list['links'];
			echo '</td></tr>'."\n";
		}
		echo '</table>'."\n";
	}


	private function showMounts(XMMS2SWI $swi)
	{
		$_SESSION['browser']['files'] = array();
		$mounts = $swi->config->listSection('browser/mount');
		$result = array(
			'title' => 'Browser: Sources',
			'list' => array()
		);
		foreach($mounts as $mount) {
			$row = $swi->config->get('browser/mount', $mount);
			$parts = explode(',', $row);
			$item = array();

			if ($parts[0] == 'disk') {
				$item['icon'] = 'icons/mount-harddisk.png';
			} else {
				throw new Exception('Unknown mount type');
			}

			$item['title'] = $parts[1];
			$item['link'] = 'index.php?mnt='.$mount;

			$result['list'][] = $item;
		}

		return $result;
	}

	private function addAll(XMMS2SWI $swi)
	{
		$mntPoint = $swi->config->get('browser/mount', $_SESSION['browser']['mount'], null);
		if ($mntPoint == null) {
			return;
		} else {
			$mnt = explode(',', $mntPoint, 3);
			$in = implode('/', $_SESSION['browser']['dir']);
			if ($in) $in = '/'.$in;
			$path = $mnt[2].$in.'/';
			$files = $_SESSION['browser']['files'];
			sort($files, SORT_STRING);
			foreach($files as $file) {
				$swi->xmms2->add('"'.addslashes($path.$file).'"');
			}
		}
	}

	private function add(XMMS2SWI $swi, $id)
	{
		$mntPoint = $swi->config->get('browser/mount', $_SESSION['browser']['mount'], null);
		if ($mntPoint == null) {
			return;
		} else {
			$mnt = explode(',', $mntPoint, 3);
			$in = implode('/', $_SESSION['browser']['dir']);
			if ($in) $in = '/'.$in;
			$path = $mnt[2].$in.'/'.$_SESSION['browser']['files'][$id];
			$swi->xmms2->add('"'.addslashes($path).'"');
		}
	}


	private function browse(XMMS2SWI $swi, $id, $type, $title, $base)
	{
		if ($type == 'disk') {
			$in = implode('/', $_SESSION['browser']['dir']);
			if ($in) $in = '/'.$in;

			$result = array(
				'title' => htmlspecialchars($title).'/'.$in,
				'list' => array(),
				'enableMass' => true,
				'massName' => 'f'
			);

			$path = $base.$in;
			$d = dir($path);
			$dirs = array(array(
				'title' => '(level up)',
				'icon' => 'icons/file-up.png',
				'link' => 'index.php?cd=..'
			));

			$files = array();
			$exts = explode(',', $swi->config->get('browser/core', 'types', 'mp3,ogg,mpc'));
			$_SESSION['browser']['files'] = array();
			$idx = 0;
			while(false !== ($entry = $d->read())) {
				if ($entry == '.') continue;
				if ($entry == '..') continue;

				$file = $path.'/'.$entry;
				//echo 'Checking: '.$file;
				if (is_dir($file)) {
					//echo ' - dir<br/>';

					$dirs[] = array(
						'icon' => 'icons/file-dir.png',
						'title' => htmlspecialchars($entry),
						'link' => 'index.php?cd='.$entry
					);
				} else if (is_file($file)) {
					//echo ' - file<br/>';
					$info = explode('.', $entry);
					$ext = strtolower(array_pop($info));
					if (in_array($ext, $exts)) {
						$files[] = array(
							'icon'=>'icons/file-audio.png',
							'title'=>htmlspecialchars($entry),
							'link'=>'index.php?add='.$idx,
							'mass'=>$idx
						);
						$_SESSION['browser']['files'][$idx] = $entry;
						$idx++;
					}
				}
			}
			usort($dirs, array($this, 'titleSort'));
			usort($files, array($this, 'titleSort'));
			$result['links'] = '<a href="index.php?addall"><img src="icons/item-add.png" alt=""/> Add all</a>';
			$result['list'] = array_merge($dirs, $files);
			return $result;
		}
	}

	private function cd(XMMS2SWI $swi, $dir)
	{
		$_SESSION['browser']['files'] = array();
		$row = $swi->config->get('browser/mount', $_SESSION['browser']['mount'], null);
		if ($row == null) {
			$_SESSION['browser']['mount'] = null;
			$_SESSION['browser']['dir'] = array();
			$_SESSION['browser']['files'] = array();
			return;
		}
		$parts = explode(',', $row);

		if ($parts[0] == 'disk') {
			$in = implode('/', $_SESSION['browser']['dir']);
			if ($in) $in = '/'.$in;

			$path = $parts[2].$in.'/'.$cd;
			if (file_exists($path)) {
				$_SESSION['browser']['dir'][] = $dir;
			}
		}
	}

	private function titleSort($a, $b)
	{
		return strcmp($a['title'], $b['title']);
	}
}




?>
