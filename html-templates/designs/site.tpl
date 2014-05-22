<!DOCTYPE html>
{load_templates designs/site.subtemplates.tpl}

<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> {* disable IE compatibility mode, use Chrome Frame if available *}

    {block "viewport"}
        <meta name="viewport" content="width=device-width, initial-scale=1"> {* responsive viewport *}
    {/block}

    <title>{block "title"}{Site::getConfig(label)}{/block}</title>

    {block "css"}
        {include includes/site.css.tpl}
    {/block}

    {block "js-top"}
        {include includes/site.js-top.tpl}
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
                {include includes/site.user-tools.tpl}
            {/block}

            {block "branding"}
                {include includes/site.branding.tpl}
            {/block}

            <div class="mobile-only nav-link-ct"><a href="#nav">Menu</a></div>
            {include includes/site.nav.tpl mobileHidden=true}

            <form class="search-form site-search mobile-hidden" action="/search">
                <input class="search-field" name="q" type="search" placeholder="Search This Site" required>
            </form>
        </div>
    </header>

    <div class="content site clearfix" role="main"> {* !.site.content *}
        <div class="inner">
            <header class="page-header">{block "page-header"}{/block}</header>

            <section class="main full-width">
                <div class="inner">
                    {block "content"}
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
                    {/block}
                </div>
            </section>

            <aside class="sidebar">
                <div class="inner">
                    {block "sidebar"}
                        {include includes/site.sidebar.tpl}
                    {/block}
                </div>
            </aside>
        </div>
    </div>

    <footer class="footer site clearfix">
        <div class="inner">

            <a name="nav"></a>

            <form class="site-search">
                <input class="search-field" type="search" placeholder="Search This Site" required>
            </form>

            {include includes/site.nav.tpl mobileOnly=true}

            {block "footer"}
                {include includes/site.footer.tpl}
            {/block}

        </div>
    </footer>

    </div> {* end .site.wrapper *}

    {block "js-bottom"}
        {include includes/site.js-bottom.tpl}
    {/block}

    {block "analytics"}
        {include includes/site.analytics.tpl}
    {/block}

    {* enables site developers to dump the internal session log here by setting ?log_report=1 on any page *}
    {log_report}
</body>

</html>