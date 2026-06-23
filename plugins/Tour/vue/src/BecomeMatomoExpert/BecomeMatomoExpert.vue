<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="widgetBody tourEngagement">
    <ActivityIndicator
      v-if="loading"
      :loading="true"
    />
    <template v-else-if="level">
      <p aria-hidden="true">
        <template
          v-for="i in level.numLevelsTotal"
          :key="i"
        >
          <span
            class="icon-star"
            :class="level.currentLevel >= i
              ? 'successStar' : 'upgradeStar'"
          />
          <!-- keep the space the server-rendered template had between stars -->
          {{ ' ' }}
        </template>
      </p>

      <div v-if="isCompleted">
        <p>
          <strong class="completed">
            {{ translate('Tour_CompletionTitle') }}
          </strong>
          {{ translate('Tour_CompletionMessage') }}
          <br><br>
          <span
            v-html="$sanitize(youCanCallYourselfHtml)"
          />
          <br><br>
          <span v-html="$sanitize(shareHtml)" />
        </p>
      </div>

      <div v-else>
        <p v-if="level.description">
          {{ level.description }}
        </p>
        <p v-html="$sanitize(statusLevelHtml)" />

        <ul>
          <li
            v-for="challenge in pagedChallenges"
            :key="challenge.id"
            class="tourChallenge"
            :class="challenge.id"
            :title="challenge.description"
          >
            <span
              v-if="challenge.isCompleted
                || challenge.isSkipped"
              class="icon-ok"
              :title="translate('Tour_ChallengeCompleted')"
            />
            <a
              v-else
              href="javascript:void 0;"
              class="skip-challenge"
              :title="translate('Tour_SkipThisChallenge')"
              @click="skipChallenge(challenge.id)"
            >
              <span class="icon-hide" />
            </a>
            <!-- keep the space the server-rendered template had between
              the status icon and the challenge link -->
            {{ ' ' }}
            <a
              v-if="$sanitizeUrl(challenge.url)"
              :href="$sanitizeUrl(challenge.url)"
              target="_blank"
              rel="noreferrer noopener"
            >
              {{ challenge.name }}
            </a>
            <template v-else>
              {{ challenge.name }}
            </template>
          </li>
        </ul>

        <hr>
        <p style="text-align:center;padding-bottom:0;">
          <a
            v-if="hasPrevPage"
            class="previousChallenges"
            @click="currentPage -= 1"
          >
            &lsaquo;
            {{ hasNextPage
              ? translate('General_Previous')
              : translate('Tour_PreviousChallenges')
            }}
          </a>
          <template v-if="hasPrevPage && hasNextPage">
            |
          </template>
          <a
            v-if="hasNextPage"
            class="nextChallenges"
            @click="currentPage += 1"
          >
            {{ hasPrevPage
              ? translate('General_Next')
              : translate('Tour_NextChallenges')
            }}
            &rsaquo;
          </a>
        </p>
        <hr>
        <p
          class="tourSuperUserNote"
          v-html="$sanitize(superUserNoteHtml)"
        />
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  ActivityIndicator,
  translate,
  externalLink,
} from 'CoreHome';

const PER_PAGE = 5;

interface Challenge {
  id: string;
  name: string;
  description: string;
  isCompleted: boolean;
  isSkipped: boolean;
  url: string;
}

interface Level {
  description: string;
  currentLevel: number;
  currentLevelName: string;
  nextLevelName: string;
  numLevelsTotal: number;
  challengesNeededForNextLevel: number;
}

export default defineComponent({
  components: {
    ActivityIndicator,
  },
  data() {
    return {
      loading: true,
      challenges: [] as Challenge[],
      level: null as Level | null,
      currentPage: 0,
    };
  },
  computed: {
    isCompleted(): boolean {
      return this.challenges.every(
        (c) => c.isCompleted || c.isSkipped,
      );
    },
    pagedChallenges(): Challenge[] {
      const start = this.currentPage * PER_PAGE;
      return this.challenges.slice(start, start + PER_PAGE);
    },
    totalPages(): number {
      return Math.ceil(
        this.challenges.length / PER_PAGE,
      );
    },
    hasPrevPage(): boolean {
      return this.currentPage > 0;
    },
    hasNextPage(): boolean {
      return this.currentPage < this.totalPages - 1;
    },
    statusLevelHtml(): string {
      if (!this.level) {
        return '';
      }
      return translate(
        'Tour_StatusLevel',
        `<strong>${this.level.currentLevelName}</strong>`,
        String(this.level.challengesNeededForNextLevel),
        `<strong>${this.level.nextLevelName}</strong>`,
      );
    },
    youCanCallYourselfHtml(): string {
      return translate(
        'Tour_YouCanCallYourselfExpert',
        '<strong class="successStar">',
        '</strong>',
      );
    },
    shareHtml(): string {
      if (!this.level) {
        return '';
      }
      const shareText = encodeURIComponent(
        translate(
          'Tour_ShareAllChallengesCompleted',
          this.level.currentLevelName,
        ),
      );
      const url = encodeURIComponent('https://matomo.org');
      const shareUrl = `http://twitter.com/share?text=${shareText}&url=${url}`;
      return translate(
        'Tour_ShareYourAchievementOn',
        `<a target="_blank" rel="noreferrer noopener" href="${shareUrl}">Twitter</a>`,
      );
    },
    superUserNoteHtml(): string {
      const faqUrl = 'https://matomo.org/faq/'
        + 'general/faq_35/';
      return translate(
        'Tour_OnlyVisibleToSuperUser',
        externalLink(faqUrl),
        '</a>',
      );
    },
  },
  mounted() {
    this.fetchData();
    window.addEventListener('focus', this.onFocus);
  },
  beforeUnmount() {
    window.removeEventListener('focus', this.onFocus);
  },
  methods: {
    translate,

    onFocus() {
      this.fetchData();
    },

    async fetchData() {
      try {
        const [challenges, level] = await Promise.all([
          AjaxHelper.fetch<Challenge[]>({
            method: 'Tour.getChallenges',
          }),
          AjaxHelper.fetch<Level>({
            method: 'Tour.getLevel',
          }),
        ]);

        this.challenges = challenges;
        this.level = level;

        if (!this.loading) {
          return;
        }

        // set initial page to first uncompleted
        const firstIncomplete = challenges.findIndex(
          (c) => !c.isCompleted && !c.isSkipped,
        );
        const done = firstIncomplete === -1
          ? challenges.length : firstIncomplete;
        this.currentPage = Math.floor(done / PER_PAGE);
      } catch {
        // silently keep current state on error
      } finally {
        this.loading = false;
      }
    },

    async skipChallenge(id: string) {
      const challenge = this.challenges.find(
        (c) => c.id === id,
      );
      if (challenge) {
        challenge.isSkipped = true;
      }

      try {
        await AjaxHelper.post({
          method: 'Tour.skipChallenge',
          id,
        });
      } catch {
        if (challenge) {
          challenge.isSkipped = false;
        }
      }
    },
  },
});
</script>
