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

class XMMS2INIConfig implements XMMS2SWIConfigInterface
{
	private $config;

	public function __construct($configFile = 'config.ini')
	{
		$this->config = parse_ini_file($configFile, true);
	}

	public function sectionExist($section)
	{
		return isset($this->config[$section]);
	}

	public function keyExist($section, $key)
	{
		if (!isset($this->config[$section])) {
			return false;
		} else {
			return isset($this->config[$section][$key]);
		}
	}

	public function listSections()
	{
		return array_keys($this->config);
	}

	public function listSection($section)
	{
		if (isset($this->config[$section])) {
			return array_keys($this->config[$section]);
		} else {
			return array();
		}
	}

	public function get($section, $key, $default = null)
	{
		if (isset($this->config[$section]) && isset($this->config[$section][$key])) {
			return $this->config[$section][$key];
		} else {
			return $default;
		}
	}

}


?>
