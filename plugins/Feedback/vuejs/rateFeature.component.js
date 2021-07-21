
matomo.registerComponent('matomoRateFeature', {
    props: ['title'],
    data: function () { return {
        expanded: false,
        showFeedbackForm: false,
        like: false,
        feedbackMessage: '',
        ratingDone: false
    }},
    methods: {
        dislikeFeature() {
            this.like = false;
        },

        likeFeature() {
            this.like = true;
        },

        sendFeedback () {
            var piwikApi = piwikHelper.getAngularDependency('piwikApi');
            piwikApi.fetch({
                method: 'Feedback.sendFeedbackForFeature',
                featureName: this.title,
                like: this.like ? '1' : '0',
                message: this.feedbackMessage + ''
            });
            this.ratingDone = true;
        }
    },
    template: `
      <div :title="translate('Feedback_RateFeatureTitle', escape(title))" class="ratefeature">

          <div class="iconContainer"
               @mouseenter="expanded=true;"
               @mouseleave="expanded=false">
    
            <img @click="likeFeature();showFeedbackForm=true;"
                 class="like-icon"
                 src="plugins/Feedback/angularjs/ratefeature/thumbs-up.png"/>
    
            <img @click="dislikeFeature();showFeedbackForm=true;"
                 class="dislike-icon"
                 v-show="expanded"
                 src="plugins/Feedback/angularjs/ratefeature/thumbs-down.png"/>
          </div>
    
          <matomo-dialog class="ui-confirm ratefeatureDialog" :trigger="showFeedbackForm" @yes="sendFeedback()" @close="showFeedbackForm = false;">
            <h2>{{ translate('Feedback_RateFeatureThankYouTitle', title) }}</h2>
            <p v-if="like">{{ translate('Feedback_RateFeatureLeaveMessageLike') }}</p>
            <p v-else>{{ translate('Feedback_RateFeatureLeaveMessageDislike') }}</p>
            <br />
    
            <div class="messageContainer">
              <textarea v-model="feedbackMessage"></textarea>
            </div>
    
            <input type="button"
                   :title="translate('Feedback_RateFeatureSendFeedbackInformation')"
                   :value="translate('Feedback_SendFeedback')" role="yes"/>
            <input type="button" role="cancel" :value="translate('General_Cancel')"/>
          </matomo-dialog>
    
          <matomo-dialog class="ui-confirm ratefeatureDialog" :trigger="ratingDone" @close="ratingDone = false;">
            <h2>{{ translate('Feedback_ThankYou', title) }}</h2>
    
            <matomo-review-links ng-if="like"></matomo-review-links>
    
            <input type="button" :value="translate('General_Ok')" role="yes"/>
          </matomo-dialog>

      </div>`
});
