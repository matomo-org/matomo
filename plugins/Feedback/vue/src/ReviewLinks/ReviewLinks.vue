<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="requestReview">
    <p>{{ translate('Feedback_PleaseLeaveExternalReviewForMatomo') }}</p><br><br>
    <div class="review-links">
      <div class="review-link">
        <a
          href="https://www.capterra.com/p/182627/Matomo-Analytics/"
          target="_blank"
        >
          <div class="image">
            <img
              loading="lazy"
              src="plugins/Feedback/images/capterra.svg"
            />
          </div>
          <div class="link">Capterra</div>
        </a>
      </div>
      <div class="review-link">
        <a
          href="https://www.g2crowd.com/products/matomo-formerly-piwik/details"
          target="_blank"
        >
          <div class="image">
            <img
              loading="lazy"
              src="plugins/Feedback/images/g2crowd.svg"
            />
          </div>
          <div class="link">G2 Crowd</div>
        </a>
      </div>
      <div class="review-link">
        <a
          href="https://www.producthunt.com/posts/matomo-2"
          target="_blank"
        >
          <div class="image">
            <img
              loading="lazy"
              src="plugins/Feedback/images/producthunt.svg"
            />
          </div>
          <div class="link">Product Hunt</div>
        </a>
      </div>
      <div class="review-link">
        <a
          href="https://www.saasworthy.com/product/matomo"
          target="_blank"
        >
          <div class="image">
            <img
              loading="lazy"
              src="plugins/Feedback/images/saasworthy.png"
            />
          </div>
          <div class="link">SaaSworthy</div>
        </a>
      </div>
      <div class="review-link">
        <a
          href="https://www.trustradius.com/products/matomo/reviews"
          target="_blank"
        >
          <div class="image">
            <img
              loading="lazy"
              src="plugins/Feedback/images/trustradius.svg"
            />
          </div>
          <div class="link">TrustRadius</div>
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({});
</script>
