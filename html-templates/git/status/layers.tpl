<table border="1">
	<tr>
		<th>Layer ID</th>
		<th>Working Branch</th>
		<th>Upstream Branch</th>
		<th colspan="2">Synchronize</th>
		<th colspan="2">Git</th>
		<th>Status</th>
	</tr>

	{foreach item=layer from=$layers}
		<tr>
			<td valign="top">{$layer.id}</td>
			<td valign="top">{$layer.workingBranch|escape}</td>
			<td valign="top">{$layer.upstreamBranch|escape}</td>
			<td valign="top">
				<form
					method="POST"
					action="/git/sync/to-disk?layer={$layer.id}"
					onsubmit="return confirm('Are you sure you want to push layer {$layer.id} from the VFS to its git working copy?\n\nThis may overwrite any uncommited changes in the working copy and they would be lost permanently.')"
				>
					<input type="submit" value="VFS &rarr; Disk">
				</form>
			</td>
			<td valign="top">
				<form
					method="POST"
					action="/git/sync/from-disk?layer={$layer.id}"
					onsubmit="return confirm('Are you sure you want to pull layer {$layer.id} from its git working copy to the VFS?')"
				>
					<input type="submit" value="Disk &rarr; VFS">
				</form>
			</td>
			<td valign="top">
				<form method="POST" action="/git/pull?layer={$layer.id}">
					<input type="submit" value="Pull (FF)">
				</form>
			</td>
			<td valign="top">
				<form method="POST" action="/git/push?layer={$layer.id}">
					<input type="submit" value="Push (FF)">
				</form>
			</td>
			<td valign="top">
				{if $layer.initialized}
					<pre>{$layer.status|escape}</pre>
				{else}
					<strong>Repository not initialized</strong>
					<form method="GET" action="/git/init" >
						<input type="hidden" name="layer" value="{$layer.id}">
						<input type="submit" value="Initialize">
					</form>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>