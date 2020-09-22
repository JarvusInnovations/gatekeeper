{extends "task.tpl"}

{block content}
    <h4>
        <b>Skip Insert Transaction (Degradation Mode):</b> <u>{tif $enabled ? Active : Inactive}</u>
    </h4>

    <form method="post">
        <div class="checkbox">
            <label>Enable
                <input type="radio" name="status" value="enable" placeholder="Enabled" {tif $enabled ? checked} />
            </label>
            <label>Disable
                <input type="radio" name="status" value="disable" placeholder="Disabled" {tif $enabled == false ? checked} />
            </label>
        </div>

        <div class="form-group">
            <label>TTL
                <input type="number" min="60" name="ttl" placeholder="ttl seconds" value="{$ttl}">
            </label>
        </div>

        <button type="submit" class="btn btn-primary">{tif $enabled ? Dis : En}able Degradation Mode</button>
    </form>
{/block}