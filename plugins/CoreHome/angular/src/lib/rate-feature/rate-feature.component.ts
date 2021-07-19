import {Component, EventEmitter, Input, Output} from "@angular/core";
import {MatomoApiService} from "../matomo-api/matomo-api.service";

@Component({
    selector: 'review-links',
    template: `
        <div class="requestReview">
            <p>{{ 'Feedback_PleaseLeaveExternalReviewForMatomo'|translate }}</p><br/><br/>

            <div class="review-links">
                <div class="review-link">
                    <a href="https://www.capterra.com/p/182627/Matomo-Analytics/" target="_blank">
                        <div class="image"><img loading="lazy" src="plugins/Feedback/images/capterra.svg"></div>
                        <div class="link">Capterra</div>
                    </a>
                </div>

                <div class="review-link">
                    <a href="https://www.g2crowd.com/products/matomo-formerly-piwik/details" target="_blank">
                        <div class="image"><img loading="lazy" src="plugins/Feedback/images/g2crowd.svg"></div>
                        <div class="link">G2 Crowd</div>
                    </a>
                </div>

                <div class="review-link">
                    <a href="https://www.producthunt.com/posts/matomo-2" target="_blank">
                        <div class="image"><img loading="lazy" src="plugins/Feedback/images/producthunt.svg"></div>
                        <div class="link">Product Hunt</div>
                    </a>
                </div>

                <div class="review-link">
                    <a href="https://www.saasworthy.com/product/matomo" target="_blank">
                        <div class="image"><img loading="lazy" src="plugins/Feedback/images/saasworthy.png"></div>
                        <div class="link">SaaSworthy</div>
                    </a>
                </div>

                <div class="review-link">
                    <a href="https://www.trustradius.com/products/matomo/reviews" target="_blank">
                        <div class="image"><img loading="lazy" src="plugins/Feedback/images/trustradius.svg"></div>
                        <div class="link">TrustRadius</div>
                    </a>
                </div>
            </div>
        </div>
    `,
})
export class ReviewLinksComponent {
    // empty
}

@Component({
    selector: 'rate-feature',
    template: `
        <div title="{{ 'Feedback_RateFeatureTitle'|translate:(title) }}" class="ratefeature">
            <div
                class="iconContainer"
                (mouseenter)="expanded = true"
                (mouseleave)="expanded = false"
            >
                <img (click)="likeFeature()" class="like-icon" src="plugins/Feedback/angularjs/ratefeature/thumbs-up.png"/>
                <img *ngIf="expanded" (click)="dislikeFeature()" class="dislike-icon"
                     src="plugins/Feedback/angularjs/ratefeature/thumbs-down.png"/>
            </div>
            
            <div matomoDialog class="ui-confirm ratefeatureDialog" [showModal]="showFeedbackForm" (onYesClick)="sendFeedback()" (onClose)="showFeedbackForm = false">
                <h2>{{ 'Feedback_RateFeatureThankYouTitle'|translate:title }}</h2>
                <p *ngIf="like">{{ 'Feedback_RateFeatureLeaveMessageLike'|translate }}</p>
                <p *ngif="!like">{{ 'Feedback_RateFeatureLeaveMessageDislike'|translate }}</p>
                <br />

                <div class="messageContainer">
                    <textarea [(ngModel)]="feedbackMessage"></textarea>
                </div>

                <input
                    type="button"
                    title="{{ 'Feedback_RateFeatureSendFeedbackInformation'|translate }}"
                    value="{{ 'Feedback_SendFeedback'|translate }}"
                    role="yes"
                />
                <input type="button" role="cancel" value="{{ 'General_Cancel'|translate }}"/>
            </div>

            <div matomoDialog class="ui-confirm ratefeatureDialog" [showModal]="ratingDone" (onClose)="showFeedbackForm = false">
                <h2>{{ 'Feedback_ThankYou'|translate:title }}</h2>

                <review-links *ngIf="like" src="'plugins/Feedback/angularjs/feedback-popup/review-links.directive.html'"></review-links>

                <input type="button" value="{{ 'General_Ok'|translate }}" role="yes"/>
            </div>
        </div>
    `,
})
export class RateFeatureComponent {
    constructor(private matomoApi: MatomoApiService) {}

    @Input() title: string = '';

    expanded: boolean = false;
    showFeedbackForm: boolean = false;
    feedbackMessage: string = '';
    like: boolean = false;
    ratingDone: boolean = false;

    likeFeature() {
        this.like = true;
        this.showFeedbackForm = true;
    }

    dislikeFeature() {
        this.like = false;
        this.showFeedbackForm = true;
    }

    async sendFeedback() {
        await this.sendFeedbackForFeature(this.title, this.like, this.feedbackMessage);
        this.ratingDone = true;
    }

    sendFeedbackForFeature(featureName: string, like: boolean, message: string) {
        return this.matomoApi.fetch({
            method: 'Feedback.sendFeedbackForFeature',
            featureName: featureName,
            like: like ? '1' : '0',
            message: message + '',
        }).toPromise();
    }
}
