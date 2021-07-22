import * as React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import {RateFeature} from '../rate-feature/RateFeature';

const { piwik, _pk_translate, $ } = window;

export class EnrichedHeadline extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showReportGenerated: false,
            showInlineHelp: false,
            showIcons: false,
            inlineHelp: props.inlineHelp,
            featureName: props.featureName,
        };

        this.root = React.createRef();
        this.title = React.createRef();
        this.titleLink = React.createRef();
    }

    componentDidMount() {
        // TODO: bad,bad,using jquery.
        let $root = $(this.root.current);

        if (!this.state.inlineHelp && !this.inlineHelpDeduced) {
            let $helpNode = $('.inlineHelp', this.title.current || this.titleLink.current);
            if ((!$helpNode || !$helpNode.length) && $root.next()) {
                // hack for reports :(
                $helpNode = $root.next().find('.reportDocumentation');
            }

            if ($helpNode && $helpNode.length) {
                // hackish solution to get binded html of p tag within the help node
                // at this point the ng-bind-html is not yet converted into html when report is not
                // initially loaded. Using $compile doesn't work. So get and set it manually
                if ($.trim($helpNode.text())) {
                    this.setState({ inlineHelp: $.trim($helpNode.html()) });
                }

                $helpNode.remove();
            }
        }

        if (!this.state.featureName && !this.featureNameDeduced) {
            let deducedFeatureName = $root.find('.title').first().text();
            if (deducedFeatureName) {
                this.setState({featureName: $.trim(deducedFeatureName)});
            }
        }

        if (this.props.reportGenerated && piwik.periods.parse(piwik.period, piwik.currentDateString).containsToday()) {
            $root.find('.report-generated').first().tooltip({
                track: true,
                content: this.props.reportGenerated,
                items: 'div',
                show: false,
                hide: false
            });

            this.setState({
                showReportGenerated: 1,
            });
        }
    }

    render() {
        return (
            <div
                ref={this.root}
                className="enrichedHeadline"
                onMouseEnter={() => this.setState({ showIcons: true })}
                onMouseLeave={() => this.setState({ showIcons: false })}
            >
                {!this.props.editUrl && <div ref={this.title} className="title" tabIndex="6">
                    {this.props.children}
                </div>}

                {this.props.editUrl && <a ref={this.titleLink} className="title" href={this.props.editUrl} title={_pk_translate('CoreHome_ClickToEditX', [this.state.featureName])}>
                    {this.props.children}
                </a>}

                <span hidden={!this.state.showIcons && !this.state.showInlineHelp} className={"iconsBar"}>
                    {this.props.helpUrl && !this.state.inlineHelp && <a
                        rel="noreferrer noopener"
                        target="_blank"
                        href={this.props.helpUrl}
                        title={_pk_translate('CoreHome_ExternalHelp')}
                        className="helpIcon"
                    >
                        <span className="icon-help"/>
                    </a>}

                    {this.state.inlineHelp && <a
                        title={_pk_translate('General_Help')}
                        onClick={() => this.setState({ showInlineHelp: !this.state.showInlineHelp })}
                        className={classNames("helpIcon", { active: this.state.showInlineHelp })}
                    >
                        <span className="icon-help"/>
                    </a>}

                    <div className="ratingIcons"
                         title={this.state.featureName}>
                        <RateFeature title={this.state.featureName}/>
                    </div>
                </span>

                {this.state.showReportGenerated && <div className="icon-clock report-generated"/>}

                {this.state.showInlineHelp && <div className="inlineHelp">
                    <div dangerouslySetInnerHTML={{__html: this.state.inlineHelp}}/>
                    {this.props.helpUrl && <a
                        rel={"noreferrer noopener"}
                        target={"_blank"}
                        href={this.props.helpUrl}
                        className={"readMore"}
                    >
                        {_pk_translate('General_MoreDetails')}
                    </a>}
                </div>}
            </div>
        );
    }

    static renderTo(element, props) {
        ReactDOM.render(<EnrichedHeadline {...props}/>, element);
    }
}
