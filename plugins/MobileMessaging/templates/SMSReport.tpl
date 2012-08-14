{strip}
	{$prettyDate}.{literal} {/literal}

	{if empty($reportRows)}
		{'CoreHome_ThereIsNoDataForThisReport'|translate}
	{/if}

	{foreach name=reportRows from=$reportRows item=row key=rowId}

		{assign var=rowMetrics value=$row->getColumns()}
		{assign var=rowMetadata value=$reportRowsMetadata[$rowId]->getColumns()}

		{if $displaySiteName}
			{$rowMetrics.label}:{literal} {/literal}
		{/if}

		{*visits*}
		{$rowMetrics.nb_visits} {'General_ColumnNbVisits'|translate}
		{if $rowMetrics.visits_evolution != 0}
			{literal} {/literal}({$rowMetrics.visits_evolution}%)
		{/if}

		{if $rowMetrics.nb_visits != 0}

			{*actions*}
			,{literal} {/literal}
			{$rowMetrics.nb_actions} {'General_ColumnNbActions'|translate}
			{if $rowMetrics.actions_evolution != 0}
				{literal} {/literal}({$rowMetrics.actions_evolution}%)
			{/if}

			{if $isGoalPluginEnabled}

				{*goal metrics*}
				{if $rowMetrics.nb_conversions != 0}

					,{literal} {/literal}
					{'Goals_ColumnRevenue'|translate}:{literal} {/literal}{$rowMetrics.revenue}
					{if $rowMetrics.revenue_evolution != 0}
						{literal} {/literal}({$rowMetrics.revenue_evolution}%)
					{/if}

					,{literal} {/literal}
					{$rowMetrics.nb_conversions} {'Goals_GoalConversions'|translate}
					{if $rowMetrics.nb_conversions_evolution != 0}
						{literal} {/literal}({$rowMetrics.nb_conversions_evolution}%)
					{/if}
				{/if}

				{*eCommerce metrics*}
				{if $siteHasECommerce[$rowMetadata.idsite]}

					,{literal} {/literal}
					{'General_ProductRevenue'|translate}:{literal} {/literal}{$rowMetrics.ecommerce_revenue}
					{if $rowMetrics.ecommerce_revenue_evolution != 0}
						{literal} {/literal}({$rowMetrics.ecommerce_revenue_evolution}%)
					{/if}

					,{literal} {/literal}
					{$rowMetrics.orders} {'General_EcommerceOrders'|translate}
					{if $rowMetrics.orders_evolution != 0}
						{literal} {/literal}({$rowMetrics.orders_evolution}%)
					{/if}
				{/if}
			{/if}

		{/if}

		{if !$smarty.foreach.reportRows.last}.{literal} {/literal}{/if}
	{/foreach}
{/strip}
