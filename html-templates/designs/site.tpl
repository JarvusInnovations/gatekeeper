<!DOCTYPE html>
{load_templates designs/site.subtemplates.tpl}

{* page name/responseId => 'optional description' *}
{$navItems = array(
  'endpoints' => ''
       'keys' => ''
       'bans' => ''
)}

<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> {* disable IE compatibility mode, use Chrome Frame if available *}
	{block "viewport"}
	<meta name="viewport" content="width=device-width, initial-scale=1"> {* responsive viewport *}
	{/block}

	<title>{block "title"}{Site::getConfig(label)}{/block}</title>
	
	{block "css"}
		{cssmin main.css}
		{* <link rel="stylesheet" href="/css/prettify.css"> included in main.css via sass *}
	{/block}
	
	{block "js-top"}
		{jsmin modernizr.js} {* minimal build; shivs html5 and replaces no-js class *}
		<script>document.addEventListener("touchstart", function(){ },false);</script> {* enable :active styles on touch *}
	{/block}
</head>

<body class="{block 'body-class'}{str_replace('/', '_', $.responseId)}-tpl{/block}">
	<!--[if lt IE 9]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->
	
	<div class="wrapper site clearfix">
	
	<header class="header site clearfix">
		<div class="inner">
			{block "user-tools"}
			<section class="user-tools">
				<div class="inner">
				{if $.User}
					<a class="logged-in user-link" href="/profile">{avatar $.User size=16} {$.User->FullName}</a>
					<a class="log-out-link" href="/logout">Log Out</a>
				{else}
					<a href="/register" id="register-link">Register</a>
					<a href="/register/recover" id="recover-link">Forgot Password</a>
					<form action="/login" method="post" class="mini-login">
						<fieldset>
							<input type="text" class="text" name="_LOGIN[username]" placeholder="Username or email" id="minilogin-username" autocorrect="off" autocapitalize="off">
							<input type="password" class="text password" name="_LOGIN[password]" placeholder="Password" id="minilogin-password">
							<input type="submit" class="button submit" id="minilogin-submit" value="Log in">
						</fieldset>
					</form>
				{/if}
				</div>
			</section>
			{/block}

			{block "branding"}
			<h1 class="branding"><a href="/">
				{if Site::resolvePath('site-root/apple-touch-icon.css')}<img src="/apple-touch-icon.png" width=36>{/if}
				{Site::getConfig(label)}
			</a></h1>
			{/block}
			
			<div class="mobile-only nav-link-ct"><a href="#nav">Menu</a></div>
			{nav $navItems mobileHidden=true}

			<form class="search-form site-search" action="/search">
    			<input name="q" class="search-field" type="search" placeholder="Find something&hellip;">
			</form>
		</div>
	</header>
	
	<div class="content site clearfix" role="main"> {* !.site.content *}
		<div class="inner">
		{block "content"}
			<section class="main">
				<h2>To add a new page:</h2>
				<ol>
					<li>Create a .tpl in /html-templates for your new page (e.g., /html-templates/about.tpl).</li>
					<li>Add this line to your new .tpl file: <pre class="prettyprint"><code>{literal}{extends designs/site.tpl}{/literal}</code></pre> and save it.</li>
					<li>Create a .php file in /site-root that corresponds to the url you would like for your new page (e.g., /html-templates/about.php).</li>
					<li>Add this line to your new .php file: <pre class="prettyprint"><code>&lt;?php RequestHandler::respond('about');</code></pre> and save it. Make sure that the string inside the single quotes corresponds to the name of the template file you wish to load.</li>
					<li>If you want your page to show up in the main navigation, edit the configuration variable <code>$navitems</code> at the top of /html-templates/designs/site.tpl and add your new page name.</li>
				</ol>

				<h2>Site Section Name</h2>
				<p class="lead">This is a <code>.lead</code> paragraph, optionally used to introduce body text. <span class="muted">You can also apply a <code>.muted</code> class to any text to fade it out a bit.</span></p>
				
				<p>Nulla sodales, mi sit amet mollis tincidunt, dui velit ultrices felis, eu mattis sem enim pellentesque tellus. Maecenas vel magna enim. Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi.</p>

				<h3>A Sub-Section</h3>

				<div class="well">
					<h6>This is a <code>.well</code></h6>
					<p>Wells can be used to set off toolboxes, forms (<code>&lt;fieldset&gt;</code> gets the same styles), or special information from the rest of the body text.</p>
					<button>Button</button>
					<button>Another Button</button>
					<button class="primary">Primary/Submit Button</button>
					<button class="destructive">Delete</button>
				</div>

				<p>Fusce in ligula dolor. Sed pellentesque quam a odio sollicitudin molestie. Nulla vulputate congue elit id dapibus. Nulla sodales, mi sit amet mollis tincidunt, dui velit ultrices felis, eu mattis sem enim pellentesque tellus. Maecenas vel magna enim. Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi.</p>
				<ul>
					<li>List item</li>
					<li>Another list item</li>
					<li>A rather long list item suspendisse ultricies tempor purus, et eleifend leo porta sed. Phasellus sed sapien ac ipsum dignissim eleifend ut in urna. Sed pellentesque quam a odio sollicitudin molestie. Nulla vulputate congue elit id dapibus. Nulla sodales, mi sit amet mollis.
					</li>
				</ul>
				<p>Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed ac neque vitae metus rhoncus commodo eu at risus. Aenean quis auctor neque. Suspendisse ultricies tempor purus, et eleifend leo porta sed. Phasellus sed sapien ac ipsum dignissim eleifend ut in urna.</p>
				<blockquote>
					<p>
						<code>&lt;blockquote&gt;</code><br>
						Fusce in ligula dolor. Sed pellentesque quam a odio sollicitudin molestie. Nulla vulputate congue elit id dapibus. Nulla sodales, mi sit amet mollis tincidunt, dui velit ultrices felis, eu mattis sem enim pellentesque tellus. Maecenas vel magna enim. Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi. Praesent quis sodales nibh. Sed interdum sodales porttitor. Donec ante elit, venenatis non tempor ut, volutpat accumsan nulla.<br>
						<code>&lt;/blockquote&gt;</code>
					</p>
				</blockquote>
				<p>Nunc nunc nisl, vehicula sit amet pharetra non, lacinia at neque. Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed ac neque vitae metus rhoncus commodo eu at risus. Aenean quis auctor neque. Suspendisse ultricies tempor purus, et eleifend leo porta sed.</p>
			</section>

			<aside class="sidebar">
				<div class="inner">
					<h2>Sidebar</h2>
					<p>Nulla sodales, mi sit amet mollis tincidunt, dui velit ultrices felis, eu mattis sem enim pellentesque tellus. Maecenas vel magna enim. Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi. Praesent quis sodales nibh. Sed interdum sodales porttitor. Donec ante elit, venenatis non tempor ut, volutpat accumsan nulla.</p>
					<p>Proin commodo, magna in semper laoreet, nisl tellus dignissim odio, vel hendrerit arcu mauris vel mi. Praesent quis sodales nibh. Sed interdum sodales porttitor. Donec ante elit, venenatis non tempor ut, volutpat accumsan nulla.</p>
				</div>
			</aside>
		{/block}
		</div>
	</div> {* end .site.content *}
	
	<footer class="footer site clearfix">
		<div class="inner">

		<a name="nav"></a>

		<form class="mini-search">
			<label class="inline text">
				<input type="search" placeholder="Search This Site" required>
			</label>
		</form>

		{nav $navItems}

		{block "footer"}
			<address>
				<strong>Jarvus Innovations</strong><br>
				908A N 3rd St<br>
				Philadelphia, PA 19123
			</address>

			<small class="muted">Powered by <a target=_blank href="http://jarv.us" title="Jarvus Innovations, a web software development firm in Philadelphia">Jarvus</a></small>
		{/block}

		</div>
	</footer>

	</div> {* end .site.wrapper *}

	{block "js-bottom"}
		{jsmin prettify.js}
		<script>prettyPrint();</script>
	{/block}

	{block "js-analytics"}
		<script type="text/javascript">
		{if $.User}
			var clicky_custom = {
				session: {
					username: '{$.User->Username}'
					,email: '{$.User->Email}'
					,full_name: '{$.User->FullName}'
				}
			};
		{/if}
		
		var clicky_site_ids = clicky_site_ids || [];
		clicky_site_ids.push(100671073);
		(function() {
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = true;
			s.src = '//static.getclicky.com/js';
			( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s );
		})();
		</script>
		<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100671073ns.gif" /></p></noscript>
	{/block}
	
	{* enables site developers to dump the internal session log here by setting ?log_report=1 on any page *}
	{log_report}
</body>

</html>