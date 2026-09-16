/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface RecommendedGoal {
  id?: string;
  name: string;
  category?: string;
  priority?: number;
  needsSetup?: boolean;
  matchAttribute: string;
  pattern: string;
  patternType: string;
  caseSensitive?: boolean;
  allowMultipleConversionsPerVisit?: boolean;
  revenue?: number;
  useEventValueAsRevenue?: boolean;
  description?: string;
  reason: string;
  source: string;
  implementationNote?: string;
  evidence?: string[];
  sourcePages?: string[];
}

export interface RecommendedManualGoal {
  name: string;
  howTo: string;
  category: string;
}

// TEMPORARY (ID-277 debugging): development-mode payload describing the crawl
export interface RecommendationDebug {
  url: string;
  platform?: string|null;
  technologies: string[];
  pagesCrawled: number;
  pages: { path: string; title: string; heading: string; types: string[]; hasAddToCart: boolean }[];
  links: { path: string; label: string; pages: number; button: number; hero: number }[];
  forms: { fieldTypes: string[]; submit: string; pages: number; firstPage: string }[];
  externalHosts: { host: string; label: string; example: string; pages: number }[];
  downloads: { href: string; label: string }[];
  candidates: {
    category: string;
    matchAttribute: string;
    pattern: string;
    label: string;
    confidence: number;
    prominence: number;
    score: number;
    source: string;
    offered: boolean;
  }[];
}

export interface RecommendationWarning {
  type: string;
  severity: 'info'|'warning';
  message: string;
}

export interface RecommendationsResponse {
  mode?: string|null;
  goals?: RecommendedGoal[];
  manualGoals?: RecommendedManualGoal[];
  warnings?: RecommendationWarning[];
  aiError?: string|null;
  useAi?: boolean;
  generatedAt?: number|null;
  remainingAiScans?: number|null;
  providerName?: string;
  aiAvailability?: string;
  privacyNote?: string;
  debug?: RecommendationDebug|null;
}
