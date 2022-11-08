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

class XMMS2
{
	public function play()
	{
		$this->call('play');
	}

	public function next()
	{
		$this->call('next');
	}

	public function prev()
	{
		$this->call('prev');
	}

	public function stop()
	{
		$this->call('stop');
	}

	public function pause()
	{
		$this->call('pause');
	}

	public function togglePlay()
	{
		$this->call('toggleplay');
	}

	public function jump($n)
	{
		$this->call('jump '.((int)$n));
	}

	public function add($files)
	{
		$this->call('add '.$files);
	}

	public function seek($pos)
	{
		$this->call('seek '.$pos);
	}

	public function remove($pos)
	{
		$this->call('remove '.(int)$pos);
	}

	public function move($what, $where)
	{
		$this->call('move '.(int)$what.' '.(int)$where);
	}

	public function clear()
	{
		$this->call('clear');
	}

	public function shuffle()
	{
		$this->call('shuffle');
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function nowPlaying()
	{
		//TODO: status parancs eredményét kirakni...
		$null = null;
		$pl = $this->playlist($null);

		foreach($pl as $item) {
			if ($item['current']) {
				return $item;
			}
		}

		return null;
	}


	/**
	 * Returns current playlist
	 *
	 * @return array
	 */
	public function playlist(&$total)
	{
		//oqnq20130218
		//	ez így nem lesz jó! sqlite kéne inkább!
		//$data = $this->read('list');
		$lines = explode("\n", $data);
		$result = array();
		//oqnq20130217
		$count=0;
		foreach($lines as $line) {
			if($count++ >10) break;
			if ($line == '') continue;
			$item = array();
			if (substr($line, 0, 2) == '->') {
				$item['current'] = true;
			} else if (substr($line, 0, 2) == '  ') {
				$item['current'] = false;
			} else {
				$h = explode(':', $line, 2);
				$h = explode(':', $h[1]);
				$total = $h[0]*60*60 + $h[1]*60 + $h[2];
				continue;
			}
			$line = substr($line, 2);

			$parts = explode(' ', $line, 2);
			$x = strrpos($parts[1], ' ');
			$parts = array($parts[0], substr($parts[1], 0, $x), substr($parts[1], $x+1));

			// index/id
			$ii = explode('/', substr($parts[0], 1, strlen($parts[0]) - 2));
			$item['index'] = (int)$ii[0];
			$item['id'] = (int)$ii[1];
			if (strpos($parts[1], '%') === false && strpos($parts[1], '+') === false) {
				$item['title'] = $parts[1];
			} else {
				$item['title'] = urldecode($parts[1]);
			}
			$time = explode(':', substr($parts[2], 1, strlen($parts[2]) - 2));
			$item['time'] = $time[0]*60+$time[1];
			$result[] = $item;

		}

		return $result;
	}

	/**
	 * Returns info on currently playing file.
	 *
	 * @return array
	 */
	protected function info()
	{
		$data = $this->read('info');
		$lines = explode("\n", $data);
		$result = array();
		foreach($lines as $line) {
			$lr = explode('=', $line, 2);
			if (count($lr) != 2) continue;

			$left = explode(' ', trim($lr[0]));
			if (count($left) != 2) continue;

			$section = substr($left[0], 1, strlen($left[0]) - 2);
			$key = $left[1];

			if (!isset($result[$section])) $result[$section] = array();
			$result[$section][$key] = trim($lr[1]);
		}

		return $result;
	}


	protected function call($func)
	{
	//oqnq20130215 - from xmms2
		system('nyxmms2 '.$func.' > /dev/null');
	}

	protected function read($func)
	{
		//oqnq20130215
		$f = popen('nyxmms2 '.$func.' ', 'r');
		$out = '';
		while ($d = fread($f, 1024*512)) {
			$out.= $d;
		}
		pclose($f);
		return $out;
	}
}

?>
