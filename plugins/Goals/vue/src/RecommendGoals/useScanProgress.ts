/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { computed, onBeforeUnmount, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';

// fake, time-based progress: the backend reports no incremental scan status. The
// crawl phase eases to 60%, the (AI) ranking phase approaches but never reaches 100%.
const SCAN_CRAWL_PHASE_MS = 15000;
const SCAN_EXPECTED_TOTAL_MS = 30000;
const SCAN_CRAWL_PHASE_PROGRESS = 60;
const SCAN_RANKING_PHASE_PROGRESS = 93;
const SCAN_PROGRESS_TICK_MS = 250;

interface ScanProgress {
  progress: Ref<number>;
  isInRankingPhase: ComputedRef<boolean>;
  start: () => void;
  stop: () => void;
}

export default function useScanProgress(): ScanProgress {
  const progress = ref(0);
  const isInRankingPhase = computed(() => progress.value >= SCAN_CRAWL_PHASE_PROGRESS);

  let startedAt: number|null = null;
  let timer: number|null = null;

  function computeProgress(): number {
    if (startedAt === null) {
      return 0;
    }

    const elapsed = Date.now() - startedAt;
    if (elapsed <= SCAN_CRAWL_PHASE_MS) {
      return (elapsed / SCAN_CRAWL_PHASE_MS) * SCAN_CRAWL_PHASE_PROGRESS;
    }

    const rankingElapsed = elapsed - SCAN_CRAWL_PHASE_MS;
    const rankingDuration = SCAN_EXPECTED_TOTAL_MS - SCAN_CRAWL_PHASE_MS;
    if (rankingElapsed <= rankingDuration) {
      return SCAN_CRAWL_PHASE_PROGRESS
        + (rankingElapsed / rankingDuration)
          * (SCAN_RANKING_PHASE_PROGRESS - SCAN_CRAWL_PHASE_PROGRESS);
    }

    // past the expected duration: creep slowly towards (but never reach) 99%
    const overtimeSeconds = (rankingElapsed - rankingDuration) / 1000;
    return Math.min(99, SCAN_RANKING_PHASE_PROGRESS + (overtimeSeconds * 0.1));
  }

  function start() {
    startedAt = Date.now();
    progress.value = 0;
    timer = window.setInterval(() => {
      progress.value = computeProgress();
    }, SCAN_PROGRESS_TICK_MS);
  }

  function stop() {
    if (timer !== null) {
      window.clearInterval(timer);
      timer = null;
    }
    startedAt = null;
    progress.value = 0;
  }

  onBeforeUnmount(stop);

  return {
    progress,
    isInRankingPhase,
    start,
    stop,
  };
}
