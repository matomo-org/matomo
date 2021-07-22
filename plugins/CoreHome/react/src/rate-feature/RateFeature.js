import * as React from 'react';
import ReactDOM from 'react-dom';
import {MatomoModal} from '../common/MatomoModal';
import matomoApiService from "../common/MatomoApi";

const { _pk_translate } = window;

export class RateFeature extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            expanded: false,
            showFeedbackForm: false,
            feedbackMessage: '',
            ratingDone: false,
        };
    }

    dislikeFeature () {
        this.setState({
            like: false,
            showFeedbackForm: true,
        });
    }

    likeFeature () {
        this.setState({
            like: true,
            showFeedbackForm: true,
        });
    }

    sendFeedback(message) {
        matomoApiService.fetch({
            method: 'Feedback.sendFeedbackForFeature',
            featureName: this.props.title,
            like: this.state.like ? '1' : '0',
            message: message + ''
        });

        this.setState({ ratingDone: true });
    }

    render() {
        return (
            <div title={_pk_translate('Feedback_RateFeatureTitle', [this.props.title])} className={"ratefeature"}>
                <div
                    className={"iconContainer"}
                    onMouseEnter={() => this.setState({expanded: true})}
                    onMouseLeave={() => this.setState({expanded: false})}
                >
                    <img onClick={() => this.likeFeature()} className={"like-icon"} src={"plugins/Feedback/angularjs/ratefeature/thumbs-up.png"} alt={"thumbs up"}/>

                    {this.state.expanded && <img
                        onClick={() => this.dislikeFeature()}
                        className={"dislike-icon"}
                        src={"plugins/Feedback/angularjs/ratefeature/thumbs-down.png"}
                        alt={"thumbs down"}
                    />}
                </div>

                <MatomoModal
                    showModal={this.state.showFeedbackForm}
                    className="ui-confirm ratefeatureDialog"
                    onYes={() => this.sendFeedback()}
                    onCloseEnd={() => this.setState({ showFeedbackForm: false })}
                >
                    <h2>{_pk_translate('Feedback_RateFeatureThankYouTitle', [this.props.title])}</h2>
                    {this.props.like && <p>{_pk_translate('Feedback_RateFeatureLeaveMessageLike')}</p>}
                    {!this.props.like && <p>{_pk_translate('Feedback_RateFeatureLeaveMessageDislike')}</p>}

                    <br/>

                    <div className="messageContainer">
                        <textarea
                            onChange={(v) => this.setState({ feedbackMessage: v })}
                            value={this.state.feedbackMessage}
                        />
                    </div>

                    <input
                        type="button"
                        title={_pk_translate('Feedback_RateFeatureSendFeedbackInformation')}
                        value={_pk_translate('Feedback_SendFeedback')}
                        role="yes"
                    />
                    <input type="button" role="cancel" value={_pk_translate('General_Cancel')}/>
                </MatomoModal>

                <MatomoModal className={"ui-confirm ratefeatureDialog"} showModal={this.state.ratingDone} onCloseEnd={() => this.setState({ ratingDone: false })}>
                    <h2>{_pk_translate('Feedback_ThankYou', [this.props.title])}</h2>

                    {this.state.like && this.renderReviewLinks()}

                    <input type={"button"} value={_pk_translate('General_Ok')} role={"yes"}/>
                </MatomoModal>
            </div>
        );
    }

    renderReviewLinks() {
        return (
            <div className="requestReview">
                <p>{_pk_translate('Feedback_PleaseLeaveExternalReviewForMatomo')}</p><br/><br/>

                <div className="review-links">
                    <div className="review-link">
                        <a href="https://www.capterra.com/p/182627/Matomo-Analytics/" target="_blank" rel={"noopener noreferrer"}>
                            <div className="image"><img loading="lazy" src="plugins/Feedback/images/capterra.svg"/></div>
                            <div className="link">Capterra</div>
                        </a>
                    </div>

                    <div className="review-link">
                        <a href="https://www.g2crowd.com/products/matomo-formerly-piwik/details" target="_blank" rel={"noopener noreferrer"}>
                            <div className="image"><img loading="lazy" src="plugins/Feedback/images/g2crowd.svg"/></div>
                            <div className="link">G2 Crowd</div>
                        </a>
                    </div>

                    <div className="review-link">
                        <a href="https://www.producthunt.com/posts/matomo-2" target="_blank" rel={"noopener noreferrer"}>
                            <div className="image"><img loading="lazy" src="plugins/Feedback/images/producthunt.svg"/>
                            </div>
                            <div className="link">Product Hunt</div>
                        </a>
                    </div>

                    <div className="review-link">
                        <a href="https://www.saasworthy.com/product/matomo" target="_blank" rel={"noopener noreferrer"}>
                            <div className="image"><img loading="lazy" src="plugins/Feedback/images/saasworthy.png"/>
                            </div>
                            <div className="link">SaaSworthy</div>
                        </a>
                    </div>

                    <div className="review-link">
                        <a href="https://www.trustradius.com/products/matomo/reviews" target="_blank" rel={"noopener noreferrer"}>
                            <div className="image"><img loading="lazy" src="plugins/Feedback/images/trustradius.svg"/>
                            </div>
                            <div className="link">TrustRadius</div>
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    static renderTo(element, props) {
        ReactDOM.render(<RateFeature {...props}/>, element);
    }
}
