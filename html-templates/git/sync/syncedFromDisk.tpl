<h1>Synchronization from disk finished</h1>

<table border="1">
	<tr>
		<th rowspan="2">Path</th>
		<th colspan="2">Analyzed</th>
		<th rowspan="2">Updated Files</th>
		<th colspan="2">Deleted</th>
		<th rowspan="2">Excluded Paths</th>
	</tr>
	<tr>
		<th>Collections</th>
		<th>Files</th>
		<th>Collections</th>
		<th>Files</th>
	</tr>

	{foreach key=path item=result from=$results}
		<tr>
			<td>{$path}</td>
			<td>{$result.collectionsAnalyzed|number_format}</td>
			<td>{$result.filesAnalyzed|number_format}</td>
			<td>{$result.filesUpdated|number_format}</td>
			<td>{$result.collectionsDeleted|number_format}</td>
			<td>{$result.filesDeleted|number_format}</td>
			<td>{$result.pathsExcluded|number_format}</td>
		</tr>
	{/foreach}
</table>