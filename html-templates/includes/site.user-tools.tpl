{load_templates subtemplates/personName.tpl}
{load_templates subtemplates/people.tpl}

<section class="user-tools">
    <div class="inner">
    {if $.User}
        <a class="logged-in user-link" href="/profile">{avatar $.User size=16} {personName $.User}</a>
        <a class="log-out-link" href="/logout">Log Out</a>
    {else}
        <a href="/register" id="register-link">Register</a>
        <a href="/login" id="log-in-link">Log In</a>
    {/if}
    </div>
</section>