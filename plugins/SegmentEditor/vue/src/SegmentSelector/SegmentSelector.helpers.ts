/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  SavedSegment,
  SegmentSelectorTranslations,
  SegmentSelectorUserContext,
} from '../types';

function getStarredByTitlePart(
  segment: SavedSegment,
  userContext: SegmentSelectorUserContext,
  translations: SegmentSelectorTranslations,
): string {
  const login = segment.starred_by || '';
  if (login === userContext.login) {
    return ` (${translations.General_StarredByYou})`;
  }

  return ` (${translations.General_StarredBy} ${login})`;
}

export function getCanUserEditSegment(
  segment: SavedSegment | null | undefined,
  segmentAccess: string,
  userContext: SegmentSelectorUserContext,
): boolean {
  if (!segment || userContext.isAnonymous) {
    return false;
  }
  if (segmentAccess !== 'write') {
    return false;
  }
  if (userContext.hasSuperUserAccess) {
    return true;
  }

  return segment.login === userContext.login;
}

export function getDeleteSegmentTitle(
  segment: SavedSegment,
  canEdit: boolean,
  translations: SegmentSelectorTranslations,
): string {
  if (segment.enable_only_idsite) {
    return canEdit
      ? translations.General_CanDeleteSiteSegment
      : translations.General_CanNotDeleteSiteSegment;
  }

  return canEdit
    ? translations.General_CanDeleteGlobalSegment
    : translations.General_CanNotDeleteGlobalSegment;
}

export function getEditSegmentTitle(
  segment: SavedSegment,
  canEdit: boolean,
  translations: SegmentSelectorTranslations,
): string {
  if (segment.enable_only_idsite) {
    return canEdit
      ? translations.General_CanEditSiteSegment
      : translations.General_CanNotEditSiteSegment;
  }

  return canEdit
    ? translations.General_CanEditGlobalSegment
    : translations.General_CanNotEditGlobalSegment;
}

export function getStarSegmentTitle(
  segment: SavedSegment,
  canEdit: boolean,
  translations: SegmentSelectorTranslations,
  userContext: SegmentSelectorUserContext,
): string {
  if (userContext.isAnonymous) {
    return '';
  }

  if (segment.enable_only_idsite) {
    if (canEdit) {
      if (segment.starred) {
        return `${translations.General_CanUnstarSiteSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
      }
      return translations.General_CanStarSiteSegment;
    }

    if (segment.starred) {
      return translations.General_CanNotUnstarSiteSegment;
    }
    return translations.General_CanNotStarSiteSegment;
  }

  if (canEdit) {
    if (segment.starred) {
      return `${translations.General_CanUnstarGlobalSegment} ${getStarredByTitlePart(segment, userContext, translations)}`;
    }
    return translations.General_CanStarGlobalSegment;
  }

  if (segment.starred) {
    return translations.General_CanNotUnstarGlobalSegment;
  }
  return translations.General_CanNotStarGlobalSegment;
}
