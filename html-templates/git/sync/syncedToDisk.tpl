<h1>Synchronization to disk finished</h1>

<table border="1">
    <tr>
        <th rowspan="2">Path</th>
		<th colspan="2">Collections</th>
		<th colspan="4">Files</th>
	</tr>
    <tr>
        <th>Excluded</th>
    	<th>Analyzed</th>
        <th>Excluded</th>
    	<th>Analyzed</th>
		<th>Written</th>
		<th>Deleted</th>
	</tr>

	{foreach key=path item=result from=$results}
		<tr>
			<td>{$path}</td>
    		<td>{$result.collectionsExcluded|number_format}</td>
    		<td>{$result.collectionsAnalyzed|number_format}</td>
    		<td>{$result.filesExcluded|number_format}</td>
			<td>{$result.filesAnalyzed|number_format}</td>
    		<td>{$result.filesWritten|number_format}</td>
			<td>{$result.filesDeleted|number_format}</td>
		</tr>
	{/foreach}
</table>