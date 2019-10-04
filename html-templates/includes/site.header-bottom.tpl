{if $.User->hasAccountLevel('Staff')}
    <div class="mobile-only nav-link-ct"><a href="#nav">Menu</a></div>
    {include includes/site.nav.tpl mobileHidden=true}
    
    <form class="search-form site-search mobile-hidden" action="/search">
        <input class="search-field" name="q" type="search" placeholder="Search This Site" required>
    </form>
{/if}