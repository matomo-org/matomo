/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface Hit {
  // monotonic raw request id; used as the incremental polling cursor (minId)
  idRawRequest: string;
  // visit the request belongs to; feeds the lazy Live.getLastVisitsDetails
  // call (segment visitId==...) when the details pane opens
  idVisit: string|null;
  // log_link_visit_action id, to pick this request's action(s) out of the
  // lazily loaded visit
  idLinkVa: number|null;
  timestamp: number;
  timePretty: string;
  type: string;
  title: string;
  subtitle: string;
  // raw tracking request parameters as received by matomo.php
  trackingParams: Record<string, unknown>;
  // passively received request data (user agent, browser language, client
  // hints, server receive time) captured alongside the raw parameters
  trackingParamsDefaults: Record<string, unknown>|null;
  // other request state derived while tracking, e.g. whether the request was
  // authenticated via token_auth
  trackingParamsOther: Record<string, unknown>|null;
  // true for requests captured on the tracker's bot path; they record no
  // visit, so idVisit/idLinkVa are null and no processed details exist
  isBot: boolean;
  // name of the detected bot (e.g. "Googlebot"), if known
  botName: string|null;
}

export interface ApiResponse {
  hits: Hit[];
  serverTime: number;
  timezone: string;
}

export interface MinuteBucket {
  minuteStart: number;
  count: number;
  label: string;
  showLabel: boolean;
}

export interface StreamItem {
  hit: Hit;
  gapLabel: string|null;
}
