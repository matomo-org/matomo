<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<!-- template designed by Marco Von Ballmoos -->
			<title>{$title}</title>
			<link rel="stylesheet" href="{$subdir}media/stylesheet.css" />
			<link rel="stylesheet" href="{$subdir}media/banner.css" />
			<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
		</head>
		<body>
			<div class="banner">
				<div class="banner-title">{$package}</div>
				<div class="banner-menu">
					<form>
						<table cellpadding="0" cellspacing="0" style="width: 100%">
							<tr>
								<td>
									{if count($ric) >= 1}
										{assign var="last_ric_name" value=""}
										{section name=ric loop=$ric}
											{if $last_ric_name != ""} | {/if}
											<a href="{$ric[ric].file}" target="right">{$ric[ric].name}</a>
											{assign var="last_ric_name" value=$ric[ric].name}
										{/section}
									{/if}
								</td>
								<td style="width: 2em">&nbsp;</td>
								<td style="text-align: right">
									{if count($packages) > 1}
										<span class="field">Packages</span> 
										<select class="package-selector" onchange="window.parent.left_bottom.location=this[selectedIndex].value">
										{section name=p loop=$packages}
											<option value="{$packages[p].link}">{$packages[p].title}</option>
										{/section}
										</select>
									{/if}
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</body>
	</html>