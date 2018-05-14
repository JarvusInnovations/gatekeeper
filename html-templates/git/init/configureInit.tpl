<h1>Initialize repository {$layer->getId()}</h1>
<pre>{$layer->getConfig()|var_export:true|escape}</pre>
<form method="POST">
    <p><input type="submit" value="Create repo"></p>

	{if !$layer->isRemoteHttp()}
        <fieldset>
            <legend>Deploy key (optional)</legend>

            <p>Add the below generated public key to your git server before continuing, or paste your key public/private key pair.</p>
            <p><a href="https://developer.github.com/guides/managing-deploy-keys/#deploy-keys">See this guide for adding a deploy key to a GitHub repository</a></p>

            <p>
            	<label>
        			Public key:<br>
        			<textarea name="publicKey" rows="10" cols="65" onfocus="this.select()" placeholder="ssh-rsa ... {Site::getConfig('primary_hostname')}">{$publicKey}</textarea>
    		    </label>
            </p>
            <p>
            	<label>
        			Private key:<br>
        			<textarea name="privateKey" rows="30" cols="65" onfocus="this.select()" placeholder="-----BEGIN RSA PRIVATE KEY-----
-----END RSA PRIVATE KEY-----">{$privateKey}</textarea>
		        </label>
            </p>
        </fieldset>
	{/if}
</form>