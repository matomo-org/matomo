import * as React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import {SiteSelectorService} from "./SiteSelectorService";
import {FocusAnywhereButHere} from '../common/FocusAnywhereButHere';

// TODO: note not using prop-types for validation
// TODO: note not using immutable.js

let shortcutRegistered = false;

class AllSitesLink extends React.PureComponent {
    render() {
        return (
            <div
                className='custom_select_all'
                onClick={this.props.onClick}
            >
                <a
                    onClick={event => event.preventDefault()}
                    href={this.getUrlAllSites()}
                    tabindex="4"
                    dangerouslySetInnerHTML={{__html: this.props.allSitesText}}
                />
            </div>
        );
    }

    getUrlAllSites() {
        const newParameters = 'module=MultiSites&action=index';
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
    }
}

export class SiteSelector extends React.Component {
    constructor(props) {
        super(Object.assign({}, {
            autocompleteMinSites: piwik.config.autocomplete_min_sites,
            activeSiteId: piwik.idSite,
        }, props));

        this.state = {
            showSitesList: false,
            sites: [],
            selectedSite: {id: null, name: ''},
            isLoading: false,
            searchTerm: '',
        };

        this.searchInput = React.createRef();
        this.siteSelectorService = new SiteSelectorService({
            onlySitesWithAdminAccess: this.props.onlySitesWithAdminAccess,
        });
    }

    hasMultipleSites() {
        return this.sites.length > 1;
    }

    onClickSelectorLink() {
        if (!this.hasMultipleSites()) {
            return;
        }

        this.setState({
            showSitesList: !this.state.showSitesList,
        });

        if (!this.state.isLoading) {
            this.loadInitialSites();
        }
    }

    onKeyUpLink(event) {
        if (event.key.toLowerCase() === 'enter') {
            this.onClickSelectorLink();
        }
    }

    getLinkTitle() {
        if (!this.hasMultipleSites()) {
            return '';
        }

        return _pk_translate('CoreHome_ChangeCurrentWebsite', this.state.selectedSite.name || this.getFirstSiteName());
    }

    getFirstSiteName() {
        if (!this.state.sites.length) {
            return null;
        }

        return this.state.sites[0].name;
    }

    componentDidMount() {
        this.loadInitialSites().then(() => {
            if (!this.props.initialSelectedSite && !this.hasMultipleSites() && this.state.sites[0]) {
                this.setState({
                    selectedSite: {id: this.state.sites[0].idsite, name: this.state.sites[0].name},
                });
            }
        });

        this.registerShortcut();
    }

    registerShortcut() {
        if (shortcutRegistered) {
            return;
        }

        // done once per page
        piwikHelper.registerShortcut('w', _pk_translate('CoreHome_ShortcutWebsiteSelector'), function(event) {
            if (event.altKey) {
                return;
            }
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false; // IE
            }
            $('.siteSelector .title').trigger('click').focus();
        });

        shortcutRegistered = true;
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        this.focusInputIfNeeded();
    }

    focusInputIfNeeded() {
        if (this.state.showSitesList && (this.props.auto <= this.sites.length || this.state.searchTerm)) {
            this.searchInput.current.focus();
        }
    }

    onClickAllSitesLink(event) {
        this.switchSite({idsite: 'all', name: this.allSitesText}, event);
        this.showSitesList = false;
    }

    getUrlForSiteId(idSite) {
        const idSiteParam   = 'idSite=' + idSite;
        const newParameters = 'segment=&' + idSiteParam;
        const hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
        return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
            '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
    }

    async loadInitialSites() {
        this.setState({ isLoading: true });

        const sites = await this.siteSelectorService.loadInitialSites();

        this.setState({
            sites,
            isLoading: false,
        });
    }

    async searchSite() {
        this.setState({ isLoading: true });

        const sites = await this.siteSelectorService.searchSite(this.state.searchTerm);
        this.setState({
            sites: sites,
            isLoading: false,
        });
    }

    switchSite(site, event) {
        // for Mac OS cmd key needs to be pressed, ctrl key on other systems
        const controlKey = navigator.userAgent.indexOf("Mac OS X") !== -1 ? event.metaKey : event.ctrlKey;
        if (event && controlKey && event.target && event.target.href) {
            window.open(event.target.href, "_blank");
            return;
        }

        this.setState({
            selectedSite: {id: site.idsite, name: site.name},
        });

        if (!this.props.switchSiteOnSelect || this.props.activeSiteId === site.idsite) {
            return;
        }

        this.loadSite(site.idsite);
    }

    loadSite(idSite) {
        if (idSite === 'all') {
            document.location.href = piwikHelper.getCurrentQueryStringWithParametersModified(piwikHelper.getQueryStringFromParameters({
                module: 'MultiSites',
                action: 'index',
                date: piwik.currentDateString,
                period: piwik.period,
            }));
        } else {
            piwik.broadcast.propagateNewPage('segment=&idSite=' + idSite, false);
        }
    }

    render() {
        return (
            <FocusAnywhereButHere onLoseFocus={() => this.setState({ showSitesList: false })}>
                <div
                    className={classNames("siteSelector", "piwikSelector", "borderedControl", {
                        expanded: this.state.showSitesList,
                        disabled: !this.hasMultipleSites()
                    })}
                >
                    {this.renderSelectedSiteInput()}
                </div>

                <a
                    onClick={this.onClickSelectorLink.bind(this)}
                    onKeyUp={this.onKeyUpLink.bind(this)}
                    href="javascript:void(0)"
                    title={this.getLinkTitle()}
                    className={classNames({title: true, loading: this.state.isLoading})}
                    tabIndex={4}
                >
                    <span className={classNames('icon', 'icon-arrow-bottom', {iconHidden: this.state.isLoading, collapsed: !this.state.showSitesList})}/>
                    <span>
                        {(this.state.selectedSite.name || !this.props.placeholder) &&
                            <span>
                                {this.state.selectedSite.name || this.getFirstSiteName()}
                            </span>
                        }
                        {(!this.state.selectedSite.name && this.props.placeholder) &&
                            <span className="placeholder">
                                {this.props.placeholder}
                            </span>
                        }
                    </span>
                </a>

                <div hidden={this.props.showSitesList} className="dropdown">
                    <div className={"custom_select_search"} hidden={this.props.autocompleteMinSites <= this.state.sites.length || this.state.searchTerm}>
                        <input
                            type="text"
                            ref={this.searchInput}
                            onClick={() => this.setState({searchTerm: ''})}
                            onChange={(value) => {
                                this.setState({searchTerm: value});
                                this.searchSite();
                            }}
                            placeholder={_pk_translate('General_Search')}
                            tabIndex={4}
                            className={"websiteSearch inp browser-default"}
                        />
                        <img
                            title={_pk_translate("General_Clear")}
                            hidden={!!this.state.searchTerm}
                            onClick={() => {
                                this.setState({ searchTerm: '' });
                                this.loadInitialSites();
                            }}
                            className={"reset"}
                            src={"plugins/CoreHome/images/reset_search.png"}
                        />
                    </div>

                    {(this.props.allSitesLocation === 'top' && this.props.showAllSitesItem) &&
                        <AllSitesLink onClick={this.onClickAllSitesLink.bind(this)} allSitesText={this.props.allSitesText} />}

                    <div className={"custom_select_container"}>
                        <ul className="custom_select_ul_list" onClick={() => this.setState({ showSitesList: false })}>
                            {this.state.sites.map(site => this.renderSiteRow(site))}
                        </ul>
                        <ul
                            hidden={!this.state.sites.length && this.state.searchTerm}
                            className={"ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect"}
                        >
                            <li className="ui-menu-item">
                                <a className="ui-corner-all" tabIndex={-1}>
                                    {`${_pk_translate('SitesManager_NotFound')} ${this.state.searchTerm}`}
                                </a>
                            </li>
                        </ul>
                    </div>

                    {(this.props.allSitesLocation === 'bottom' && this.props.showAllSitesItem) &&
                        <AllSitesLink onClick={this.onClickAllSitesLink.bind(this)} allSitesText={this.props.allSitesText} />}
                </div>
            </FocusAnywhereButHere>
        );
    }

    renderSelectedSiteInput() {
        if (!this.props.inputName) {
            return null;
        }

        return <input type="hidden" name={this.props.inputName} value={this.state.selectedSite.id}/>;
    }

    renderSiteRow(site) {
        const parts = !this.state.searchTerm ? [site.name] : site.name.split(this.state.searchTerm);

        return (
            <li
                key={site.idsite}
                onClick={(event) => this.props.switchSite(site, event)}
                hidden={!this.state.showSelectedSite && this.props.activeSiteId === site.idsite}
            >
                <a
                    onClick={event => event.preventDefault()}
                    href={this.getUrlForSiteId(site.idsite)}
                    title={site.name}
                    tabIndex={4}
                >
                    {parts.map((w, i) => {
                        if (i === 0) {
                            return w;
                        }

                        return [
                            <span key={i} className="autocompleteMatched">{this.state.searchTerm}</span>,
                            w,
                        ];
                    })}
                </a>
            </li>
        );
    }

    static renderTo(element, props) {
        ReactDOM.render(<SiteSelector {...props}/>, element);
    }
}
