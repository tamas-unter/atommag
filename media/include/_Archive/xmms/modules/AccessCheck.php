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


class XMMS2SWIAccessCheckModule implements XMMS2SWIModuleInterface
{
	public function init(XMMS2SWI $swi)
	{

	}

	public function run(XMMS2SWI $swi)
	{
		// module must be enabled
		if (!$swi->config->get('access/core', 'enabled', false)) return;

		// get check mode
		$mode = $swi->config->get('access/core', 'mode', 'ip');
		switch ($mode)
		{
			case 'ip': $this->checkIp($swi); break;
			default:
				throw new Exception('Unknown access check mode');
		}
	}

	private function checkIp(XMMS2SWI $swi)
	{
		if (!$swi->config->sectionExist('access/ip')) return;
		$policy = $swi->config->get('access/ip', 'policy', 'any');

		if ($policy == 'any') return;

		$ips = explode(',', $swi->config->get('access/ip', 'filter', ''));
		$matched = false;
		$my_ip = explode('.', $_SERVER['REMOTE_ADDR']);
		foreach($ips as $ip) {
			$ip = explode('.', $ip);
			$done = true;
			foreach($ip as $idx => $byte) {
				if ($byte == '*') continue;
				else if ($byte != $my_ip[$idx]) {
					$done = false;
					break;
				}
			}

			if ($done) {
				// if IP was matched, the finish
				$matched = true;
				break;
			}
		}

		if ($matched) {
			// when matched
			if ($policy == 'deny') {
				// is in allow list
				return;
			} else if ($policy == 'allow') {
				// is in deny list
				header('status: 403 Forbidden');
				die('Your IP is blacklisted');
			} else {
				throw new Exception('Unknown access policy');
			}
		} else {
			// when not matched
			if ($policy == 'deny') {
				header('status: 403 Forbidden');
				die('You can not access this page');
			} else if ($policy == 'allow') {
				return;
			} else {
				throw new Exception('Unknown access policy');
			}
		}
	}
}








?>
