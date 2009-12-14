function setRowData (idsite, visits, actions, unique, name, url, visitsSummaryValue, actionsSummaryValue, uniqueSummaryValue)
{
	this.idsite = idsite;
	this.visits = visits;
	this.unique = unique;
	this.name = name;
	this.url = url;
	this.actions = actions;
	this.visitsSummaryValue = parseFloat(visitsSummaryValue);
	this.actionsSummaryValue = parseFloat(actionsSummaryValue);
	this.uniqueSummaryValue = parseFloat(uniqueSummaryValue);
}

function setOrderBy(self, allSites, params, mOrderBy)
{
	if(params['mOrderBy'] == mOrderBy)
	{
		if(params['order'] == 'desc')
		{
			params['order'] = 'asc';
		}
		else
		{
			params['order'] = 'desc';
		}
	}
	params['mOrderBy'] = mOrderBy;
	prepareRows(allSites, params);

	$('#arrow_asc').hide();
	$('#arrow_desc').hide();
	$(self).append(params['arrow_'+params['order']]);
	return params;
}

function prepareRows(allUnsortedSites, params)
{
	var allSites;
	$("#tb").find("tr:not(:last)").remove();
	$("#next").html('');
	$("#prev").html('');
	$(".asc").hide();
	$(".desc").hide();
	var mOrderBy = params['mOrderBy'];

	allSites = orderBy(allUnsortedSites, params);
	if(allSites.length > params['limit'])
	{
		allSites = limitBy(allSites, params);
	}

	displayRows(allSites.reverse(), params);
	showPagination(allUnsortedSites, params);
	params['sitesVisible'] = allSites;
}

function orderBy(allSites, params)
{
	if(params['mOrderBy'] == 'names')
	{
		allSites.sort(function(a,b) {
			if (a['name'].toLowerCase() == b['name'].toLowerCase())
			{
				return 0;
			}
			return (a['name'].toLowerCase() < b['name'].toLowerCase()) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'visits')
	{
		allSites.sort(function(a,b) {
			if (a['visits'] == b['visits']) {
				return 0;
			}
			return (a['visits'] < b['visits']) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'actions')
	{
		allSites.sort(function (a,b) {
			if (a['actions'] == b['actions']) {
				return 0;
			}
			return (a['actions'] < b['actions']) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'unique')
	{
		allSites.sort(function (a,b) {
			if (a['unique'] == b['unique']) {
				return 0;
			}
			return (a['unique'] < b['unique']) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'uniqueSummary')
	{
		allSites.sort(function (a,b) {
			if (a['uniqueSummaryValue'] == b['uniqueSummaryValue']) {
				return 0;
			}
			return (a['uniqueSummaryValue'] - b['uniqueSummaryValue'] <= 0.01) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'actionsSummary')
	{
		allSites.sort(function (a,b) {
			if (a['actionsSummaryValue'] == b['actionsSummaryValue']) {
				return 0;
			}
			return (a['actionsSummaryValue'] - b['actionsSummaryValue'] <= 0.01) ? 1 : -1;
		});
	}
	else if(params['mOrderBy'] == 'visitsSummary')
	{
		allSites.sort(function (a,b) {
			if (a['visitsSummaryValue'] == b['visitsSummaryValue']) {
				return 0;
			}
			return (a['visitsSummaryValue'] - b['visitsSummaryValue'] <= 0.01) ? 1 : -1;
		});
	}

	if(params['order'] == 'desc')
	{
		allSites.reverse();
	}
	return allSites;
}

function limitBy(allSites, params)
{
	var begin  = (params['page'] - 1) * params['limit'];
	var end = (params['page'] * params['limit']);
	return	allSites.slice(begin, end);
}

function switchEvolution(params)
{
	$('.actions').hide();
	$('.unique').hide();
	$('.visits').hide();
	$('.' + params['evolutionBy']).show();
	sitesVisible = params['sitesVisible'];
	for(i  = 0;  i < allSites.length; i++)
	{
		$('#sparkline_' + allSites[i].idsite).html(getSparklineImg(allSites[i].idsite, params['evolutionBy'], params));
	}
}

function displayRows(allSites, params)
{
	var table = document.getElementById('tb');
	var trow = table.insertRow(0);
	for(var i  = 0;  i < allSites.length; i++)
	{
		var str = params['row'];
		str = str.replace(/%uniqueSummary%/g, getImageForSummary(allSites[i].uniqueSummaryValue));
		str = str.replace(/%actionsSummary%/g, getImageForSummary(allSites[i].actionsSummaryValue));
		str = str.replace(/%visitsSummary%/g, getImageForSummary(allSites[i].visitsSummaryValue));
		str = str.replace(/%sparkline%/g, getSparklineImg(allSites[i].idsite, params['evolutionBy'], params));
		str = str.replace(/%actions%/g, allSites[i].actions);
		str = str.replace(/%idsite%/g, allSites[i].idsite);
		str = str.replace(/%visits%/g, allSites[i].visits);
		str = str.replace(/%name%/g, allSites[i].name);
		str = str.replace(/%unique%/g, allSites[i].unique);
		str = str.replace(/%main_url%/g, allSites[i].url);
		str = str.replace(/%date%/g, params['date']);
		str = str.replace(/%period%/g, params['period']);
		trow = table.insertRow(0);
		trow.innerHTML = str;
		trow.setAttribute('id', 'row_' + allSites[i].idsite);
		trow.setAttribute('class', 'table_row');
	}

	$(".table_row").show();
	$('.actions').hide();
	$('.unique').hide();
	$('.visits').hide();
	$('#main_indicator').hide();
	$('.' + params['evolutionBy']).show();
	$("#main_indicator").hide();
}

function getSparklineImg(id, column, params)
{
    if(column == 'unique')
    {
        column = 'uniq_visitors';
    }
     return '<img class="sparkline" alt="" src="?module=MultiSites&action=getEvolutionGraph&period=' + params['period'] + '&date=' + params['dateToStr'] + '&evolutionBy=' + params['evolutionBy'] + '&columns[]=nb_' + column  + '&idSite=' + id + '&idsite=' + id + '&viewDataTable=sparkline" width="100" height="25"  />';
}



function showPagination(allSites, params)
{
	if ((params['page'] * params['limit']) < allSites.length)
	{
		var html = '<span style="cursor:pointer;" class="pointer" onClick="changePage(allSites, params, \'next\');">' + params['next'] + ' &#187;</span>';
		$("#next").html(html);
	}
	if(params['page'] > 1)
	{
		html = '<span style="cursor:pointer;" onClick="changePage(allSites, params, \'prev\');">&#171; ' + params['prev'] + '</span>'
		$("#prev").html(html);
	}
	var start = (params['page'] - 1) * params['limit'] + 1;
	var count = allSites.length;
	var end = parseInt(start) + parseInt(params['limit']);
	if(end > count) end = count;
	html = '<span>' + (start ) + ' - ' + end + ' of ' + count + '</span>';
	$("#counter").html(html);
}

function changePage(allSites, params, kind)
{
	if(kind == 'next')
	{
		params['page']++;
	}
	else
	{
		params['page']--;
	}
	prepareRows(allSites, params);
	return params;
}


function getImageForSummary(value)
{
	if(value > 0)
	{
		return '<img src="plugins/MultiSites/images/arrow_up.png"  alt="" /> <b style="color: green;">' + value + ' %</b>';
	}
	else if(value == 0)
	{
		return '<img src="plugins/MultiSites/images/stop.png"  alt="" /> <b>' + value + '%</b>';
	}
	else
	{
		return '<img src="plugins/MultiSites/images/arrow_down.png"  alt="" /> <b style="color: red;">' + value +' %</b>';
	}
}


