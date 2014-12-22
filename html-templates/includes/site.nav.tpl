<nav class="nav site {if $mobileHidden}mobile-hidden{/if} {if $mobileOnly}mobile-only{/if}">
	<ul>
        <li><a href="/endpoints" class="{tif $.responseId == endpoints ? current}">Endpoints</a></li>
        <li><a href="/alerts" class="{tif $.responseId == alerts ? current}">Alerts</a></li>
        <li><a href="/keys" class="{tif $.responseId == keys ? current}">Keys</a></li>
        <li><a href="/bans" class="{tif $.responseId == bans ? current}">Bans</a></li>
        <li><a href="/transactions" class="{tif $.responseId == transactions ? current}">Transactions Log</a></li>
        <li><a href="/reports/top-users" class="{tif $.responseId == topUsers ? current}">Top Users</a></li>
	</ul>
</nav>