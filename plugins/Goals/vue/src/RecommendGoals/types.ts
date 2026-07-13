/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface RecommendedGoal {
  id?: string;
  name: string;
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

export interface RecommendationsResponse {
  mode?: string|null;
  goals?: RecommendedGoal[];
  manualGoals?: RecommendedManualGoal[];
  aiError?: string|null;
  useAi?: boolean;
  generatedAt?: number|null;
  remainingAiScans?: number|null;
}
