/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function setRowData(idsite, visits, pageviews, revenue, name, url, visitsSummaryValue, pageviewsSummaryValue, revenueSummaryValue) {
    this.idsite = idsite;
    this.visits = visits;
    this.revenue = revenue;
    this.name = name;
    this.url = url;
    this.pageviews = pageviews;
    this.visitsSummaryValue = parseFloat(visitsSummaryValue);
    this.pageviewsSummaryValue = parseFloat(pageviewsSummaryValue);
    this.revenueSummaryValue = parseFloat(revenueSummaryValue) || 0;
}

function setOrderBy(self, allSites, params, mOrderBy) {
    if (params['mOrderBy'] == mOrderBy) {
        if (params['order'] == 'desc') {
            params['order'] = 'asc';
        } else {
            params['order'] = 'desc';
        }
    }
    params['mOrderBy'] = mOrderBy;
    prepareRows(allSites, params);

    $('.arrow').removeClass('multisites_desc multisites_asc');
    if ($(self).attr('class') == 'evolution') {
        mOrderBy = 'evolution';
    }
    $('#' + mOrderBy + '  .arrow').addClass('multisites_' + params['order']);

    return params;
}

function prepareRows(allUnsortedSites, params) {
    var allSites;
    $("#tb").find("tr").remove();
    $("#next").html('');
    $("#prev").html('');
    var mOrderBy = params['mOrderBy'];

    allSites = orderBy(allUnsortedSites, params);

    if (allSites.length > params['limit']) {
        allSites = limitBy(allSites, params);
    }

    displayRows(allSites, params);

    showPagination(allUnsortedSites, params);
    params['sitesVisible'] = allSites;
}

function orderBy(allSites, params) {
    if (params['mOrderBy'] == 'names') {
        allSites.sort(function (a, b) {
            if (a['name'].toLowerCase() == b['name'].toLowerCase()) {
                return 0;
            }
            return (a['name'].toLowerCase() < b['name'].toLowerCase()) ? -1 : 1;
        });
    }
    else if (params['mOrderBy'] == 'visits') {
        allSites.sort(function (a, b) {
            if (a['visits'] == b['visits']) {
                return 0;
            }
            return (a['visits'] < b['visits']) ? -1 : 1;
        });
    }
    else if (params['mOrderBy'] == 'pageviews') {
        allSites.sort(function (a, b) {
            if (a['pageviews'] == b['pageviews']) {
                return 0;
            }
            return (a['pageviews'] < b['pageviews']) ? -1 : 1;
        });
    }
    else if (params['mOrderBy'] == 'revenue') {
        allSites.sort(function (a, b) {
            var lhs = parseFloat(a['revenue'].replace(/[^0-9\.]+/g, "")) || 0,
                rhs = parseFloat(b['revenue'].replace(/[^0-9\.]+/g, "")) || 0;

            return lhs === rhs ? 0 : ((lhs < rhs) ? -1 : 1);
        });
    }
    else if (params['mOrderBy'] == 'revenueSummary') {
        allSites.sort(function (a, b) {
            if (a['revenueSummaryValue'] == b['revenueSummaryValue']) {
                return 0;
            }
            return (a['revenueSummaryValue'] - b['revenueSummaryValue'] <= 0.01) ? -1 : 1;
        });
    }
    else if (params['mOrderBy'] == 'pageviewsSummary') {
        allSites.sort(function (a, b) {
            if (a['pageviewsSummaryValue'] == b['pageviewsSummaryValue']) {
                return 0;
            }
            return (a['pageviewsSummaryValue'] - b['pageviewsSummaryValue'] <= 0.01) ? -1 : 1;
        });
    }
    else if (params['mOrderBy'] == 'visitsSummary') {
        allSites.sort(function (a, b) {
            if (a['visitsSummaryValue'] == b['visitsSummaryValue']) {
                return 0;
            }
            return (a['visitsSummaryValue'] - b['visitsSummaryValue'] <= 0.01) ? -1 : 1;
        });
    }

    if (params['order'] == 'desc') {
        allSites.reverse();
    }
    return allSites;
}

function limitBy(allSites, params) {
    var begin = (params['page'] - 1) * params['limit'];
    var end = (params['page'] * params['limit']);
    return    allSites.slice(begin, end);
}

function switchEvolution(params) {
    $('.pageviews').hide();
    $('.revenue').hide();
    $('.visits').hide();
    $('.' + params['evolutionBy']).show();
    sitesVisible = params['sitesVisible'];
    for (var i = 0; i < allSites.length; i++) {
        $('#sparkline_' + allSites[i].idsite).html(getSparklineImg(allSites[i].idsite, params['evolutionBy'], params));
    }
}

function displayRows(allSites, params) {
    for (var i = 0; i < allSites.length; i++) {
        var str = params['row'];
        str = str.replace(/%revenueSummary%/g, getImageForSummary(allSites[i].revenueSummaryValue));
        str = str.replace(/%pageviewsSummary%/g, getImageForSummary(allSites[i].pageviewsSummaryValue));
        str = str.replace(/%visitsSummary%/g, getImageForSummary(allSites[i].visitsSummaryValue));
        str = str.replace(/%sparkline%/g, getSparklineImg(allSites[i].idsite, params['evolutionBy'], params));
        str = str.replace(/%pageviews%/g, allSites[i].pageviews);
        str = str.replace(/%idsite%/g, allSites[i].idsite);
        str = str.replace(/%visits%/g, allSites[i].visits);
        str = str.replace(/%name%/g, allSites[i].name);
        str = str.replace(/%revenue%/g, allSites[i].revenue);
        str = str.replace(/%main_url%/g, allSites[i].url);
        str = str.replace(/%date%/g, params['date'] || params['dateSparkline']); // For period=range, dateSparkline only is set
        str = str.replace(/%period%/g, params['period']);

        $('#tb').append('<tr class="tables_row" id="row_' + allSites[i].idsite + '">' + str + '</tr>');
    }

    $(".table_row").show();
    $('.pageviews').hide();
    $('.revenue').hide();
    $('.visits').hide();
    $('#main_indicator').hide();
    $('.' + params['evolutionBy']).show();
    $("#main_indicator").hide();
}

function getSparklineImg(id, column, params) {
    if (column != 'revenue') {
        column = 'nb_' + column;
    }
    var append = '';
    var token_auth = broadcast.getValueFromUrl('token_auth');
    if (token_auth.length) {
        append = '&token_auth=' + token_auth;
    }
    return '<img alt="" src="?module=MultiSites&action=getEvolutionGraph&period=' + params['period'] + '&date=' + params['dateSparkline'] + '&evolutionBy=' + params['evolutionBy'] + '&columns=' + column + '&idSite=' + id + '&idsite=' + id + '&viewDataTable=sparkline' + append + '&colors=' + encodeURIComponent(JSON.stringify(piwik.getSparklineColors())) + '" width="100" height="25" />';
}

function showPagination(allSites, params) {
    if ((params['page'] * params['limit']) < allSites.length) {
        var html = '<span style="cursor:pointer;" class="pointer" onClick="changePage(allSites, params, \'next\');">' + params['next'] + ' &#187;</span>';
        $("#next").html(html);
    }
    if (params['page'] > 1) {
        html = '<span style="cursor:pointer;" onClick="changePage(allSites, params, \'prev\');">&#171; ' + params['prev'] + '</span>';
        $("#prev").html(html);
    }
    var start = (params['page'] - 1) * params['limit'] + 1;
    var count = allSites.length;
    var end = parseInt(start) + parseInt(params['limit']) - 1;
    if (end > count) end = count;
    html = '<span>' + (start ) + ' - ' + end + ' of ' + count + '</span>';
    $("#counter").html(html);
}

function changePage(allSites, params, kind) {
    if (kind == 'next') {
        params['page']++;
    }
    else {
        params['page']--;
    }
    prepareRows(allSites, params);
    return params;
}


function getImageForSummary(value) {
    if (value > 0) {
        return '<img src="plugins/MultiSites/images/arrow_up.png" alt="" /> <strong style="color: green;">' + value + '&nbsp;%</strong>';
    }
    else if (value == 0) {
        return '<img src="plugins/MultiSites/images/stop.png" alt="" /> <strong>' + value + '%</strong>';
    }
    else {
        return '<img src="plugins/MultiSites/images/arrow_down.png" alt="" /> <strong style="color: red;">' + value + '&nbsp;%</strong>';
    }
}


