<?

/*
 * Calculate the effective registered domain of a fully qualified domain name.
 *
 * <@LICENSE>
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at:
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * </@LICENSE>
 *
 * Florian Sager, 25.07.2008, sager@agitos.de
 */

/*
 * Remove subdomains from a signing domain to get the registered domain.
 *
 * dkim-reputation.org blocks signing domains on the level of registered domains
 * to rate senders who use e.g. a.spamdomain.tld, b.spamdomain.tld, ... under
 * the most common identifier - the registered domain - finally.
 *
 * This function returns NULL if $signingDomain is TLD itself
 */

function getRegisteredDomain($signingDomain) {

	global $tldTree;

	$signingDomainParts = split('\.', $signingDomain);

	$result = findRegisteredDomain($signingDomainParts, $tldTree);

	if ($result===NULL || $result=="") {
		// this is an invalid domain name
		return NULL;
	}

	// assure there is at least 1 TLD in the stripped signing domain
	if (!strpos($result, '.')) {
		$cnt = count($signingDomainParts);
		if ($cnt==1 || $signingDomainParts[$cnt-2]=="") return NULL;
		return $signingDomainParts[$cnt-2].'.'.$signingDomainParts[$cnt-1];
	}
	return $result;
}

// recursive helper method
function findRegisteredDomain($remainingSigningDomainParts, &$treeNode) {

	$sub = array_pop($remainingSigningDomainParts);

	$result = NULL;
	if (isset($treeNode['!'])) {
		return '#';
	} else if (is_array($treeNode) && array_key_exists($sub, $treeNode)) {
		$result = findRegisteredDomain($remainingSigningDomainParts, $treeNode[$sub]);
	} else if (is_array($treeNode) && array_key_exists('*', $treeNode)) {
		$result = findRegisteredDomain($remainingSigningDomainParts, $treeNode['*']);
	} else {
		return $sub;
	}

	// this is a hack 'cause PHP interpretes '' as NULL
	if ($result == '#') {
		return $sub;
	} else if (strlen($result)>0) {
		return $result.'.'.$sub;
	}
	return NULL;
}

?>