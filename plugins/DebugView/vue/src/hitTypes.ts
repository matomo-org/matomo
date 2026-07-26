/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface HitTypeInfo {
  icon: string;
  // image asset instead of an icon-font class; the same assets the visits log
  // shows for these actions, so both views look consistent
  iconSvg: string|null;
  cssClass: string;
  labelKey: string;
}

// the exact image assets the visits log shows for each action kind (see
// plugins/Actions/VisitorDetails.php and the other plugins' VisitorDetails),
// hardcoded on purpose so no API is needed. Types without a visits log
// counterpart (ping, session recordings, unknown) keep an icon-font class
// verified to exist in plugins/Morpheus/stylesheets/base/icons.css.
const HIT_TYPES: Record<string, { icon?: string; iconSvg?: string; labelKey: string }> = {
  pageview: { iconSvg: 'plugins/Morpheus/images/action.svg', labelKey: 'DebugView_TypePageview' },
  event: { iconSvg: 'plugins/Morpheus/images/event.svg', labelKey: 'DebugView_TypeEvent' },
  goal: { iconSvg: 'plugins/Morpheus/images/goal.svg', labelKey: 'DebugView_TypeGoal' },
  download: {
    iconSvg: 'plugins/Morpheus/images/download.svg',
    labelKey: 'DebugView_TypeDownload',
  },
  outlink: { iconSvg: 'plugins/Morpheus/images/link.svg', labelKey: 'DebugView_TypeOutlink' },
  search: { iconSvg: 'plugins/Morpheus/images/search.svg', labelKey: 'DebugView_TypeSearch' },
  ecommerceOrder: {
    iconSvg: 'plugins/Morpheus/images/ecommerceOrder.svg',
    labelKey: 'DebugView_TypeEcommerceOrder',
  },
  ecommerceAbandonedCart: {
    iconSvg: 'plugins/Morpheus/images/ecommerceAbandonedCart.svg',
    labelKey: 'DebugView_TypeEcommerceAbandonedCart',
  },
  content: {
    iconSvg: 'plugins/Morpheus/images/contentinteraction.svg',
    labelKey: 'DebugView_TypeContent',
  },
  ping: { icon: 'icon-heart', labelKey: 'DebugView_TypePing' },
  media: { iconSvg: 'plugins/MediaAnalytics/images/video.png', labelKey: 'DebugView_TypeMedia' },
  form: { iconSvg: 'plugins/FormAnalytics/images/form.png', labelKey: 'DebugView_TypeForm' },
  crash: { iconSvg: 'plugins/CrashAnalytics/images/crash.png', labelKey: 'DebugView_TypeCrash' },
  // the visits log links session recordings with the play icon
  sessionRecording: { icon: 'icon-play', labelKey: 'DebugView_TypeSessionRecording' },
  other: { icon: 'icon-help', labelKey: 'DebugView_TypeVendor' },
};

export default function getHitTypeInfo(
  type: string,
  trackingParams?: Record<string, unknown>|null,
): HitTypeInfo {
  const knownType = Object.prototype.hasOwnProperty.call(HIT_TYPES, type) ? type : 'other';
  const entry = HIT_TYPES[knownType];

  let iconSvg = entry.iconSvg || null;
  if (knownType === 'media' && trackingParams && trackingParams.ma_mt === 'audio') {
    // like the visits log: audio plays get the audio icon, everything else video
    iconSvg = 'plugins/MediaAnalytics/images/audio.png';
  }
  if (knownType === 'content' && trackingParams && !trackingParams.c_i) {
    // like the visits log: interactions and impressions have distinct icons —
    // a content request without an interaction name (c_i) is an impression
    iconSvg = 'plugins/Morpheus/images/contentimpression.svg';
  }

  return {
    icon: entry.icon || '',
    iconSvg,
    labelKey: entry.labelKey,
    cssClass: `debugViewHitIconCircle--${knownType}`,
  };
}
