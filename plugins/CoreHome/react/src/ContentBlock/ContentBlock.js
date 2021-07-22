import * as React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import {RateFeature} from '../rate-feature/RateFeature';
import {EnrichedHeadline} from "../enriched-headline/EnrichedHeadline";

const { piwik, _pk_translate, $ } = window;

let adminContent = null;

export class ContentBlock extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            helpText: this.props.helpText,
            feature: this.props.feature,
        }

        this.childContainer = React.createRef();
    }

    componentDidMount() {
        const $root = $(this.childContainer.current);

        const inlineHelp = $root.find('.contentHelp');
        if (inlineHelp.length) {
            this.setState({ helpText: inlineHelp.html() });
            inlineHelp.remove();
        }

        if (this.state.feature && (this.state.feature===true || this.state.feature ==='true')) {
            this.state.feature = this.props.contentTitle;
        }

        if (adminContent === null) {
            // cache admin node for further content blocks
            adminContent = $('#content.admin');
        }

        let contentTopPosition = false;

        if (adminContent.length) {
            contentTopPosition = adminContent.offset().top;
        }

        if (contentTopPosition || contentTopPosition === 0) {
            let parents = $root.parentsUntil('.col', '[piwik-widget-loader]');
            let topThis;
            if (parents.length) {
                // when shown within the widget loader, we need to get the offset of that element
                // as the widget loader might be still shown. Would otherwise not position correctly
                // the widgets on the admin home page
                topThis = parents.offset().top;
            } else {
                topThis = $root.offset().top;
            }

            if ((topThis - contentTopPosition) < 17) {
                // we make sure to display the first card with no margin-top to have it on same as line as
                // navigation
                $root.css('marginTop', '0');
            }
        }
    }

    render() {
        return [
            this.props.anchor && <a key={0} id={this.props.anchor}/>,
            <div key={1} className="card">
                <div className="card-content">
                    {(this.props.contentTitle && !this.state.feature && !this.props.helpUrl && !this.state.helpText) &&
                        <h2 className="card-title">{this.props.contentTitle}</h2>}

                    {this.props.contentTitle && (this.state.feature || this.props.helpUrl || this.state.helpText) &&
                        <h2 className="card-title">
                            <EnrichedHeadline
                                featureName={this.state.feature}
                                helpUrl={this.props.helpUrl}
                                inlineHelp={this.state.helpText}
                            >
                                {this.props.contentTitle}
                            </EnrichedHeadline>
                        </h2>
                    }
                    <div ref={this.childContainer}>
                        {this.props.children}
                    </div>
                </div>
            </div>
        ];
    }

    static renderTo(element, props) {
        ReactDOM.render(<ContentBlock {...props}/>, element);
    }
}
