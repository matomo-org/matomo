===============================================
Detection of registered domains by reg-dom libs
===============================================

The reg-dom libs are available in C, Perl and PHP so far.

They include recent representations of the effective TLD list available at
http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/src/effective_tld_names.dat?raw=1
and help to convert an arbitrary domain name to the registered domain name.

Pseudo code:
registeredDomain = getRegisteredDomain(ingoingDomain);

Return values:
1) NULL if ingoingDomain is a TLD
2) the registered domain name if TLD is known
3) just <domain>.<tld> if <tld> is unknown
   This case was added to support new TLDs in outdated reg-dom libs
   by a certain likelihood. This fallback method is implemented in the
   last conversion step and can be simply commented out.
   

   
# Licensed to the Apache Software Foundation (ASF) under one or more
# contributor license agreements.  See the NOTICE file distributed with
# this work for additional information regarding copyright ownership.
# The ASF licenses this file to you under the Apache License, Version 2.0
# (the "License"); you may not use this file except in compliance with
# the License.  You may obtain a copy of the License at:
# 
#     http://www.apache.org/licenses/LICENSE-2.0
# 
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
# </@LICENSE>


Florian Sager, 2009-02-05, sager@agitos.de
