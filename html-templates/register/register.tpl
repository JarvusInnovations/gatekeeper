{extends "designs/site.tpl"}

{block "title"}Register &mdash; {$dwoo.parent}{/block}

{block "user-tools"}{/block} {* redundant *}

{block "content"}
    {$User = $data}
    {$errors = $User->validationErrors}
    
    <header class="page-header">
        <h1 class="header-title title-1">Register a New Account</h1>
    </header>
    
    <form method="POST" class="register-form">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}
    
        <fieldset class="shrink">
            <div class="inline-fields">
                {* field name label='' error='' type=text placeholder='' hint='' required=false attribs='' default=null class=null *}
                {field FirstName 'First Name' $errors.FirstName text '' '' true 'autofocus'}
                {field LastName  'Last Name'  $errors.LastName  text '' '' true}
            </div>

                {field Email    'Email Address' $errors.Email    email '' '' true}
                {field Username 'Username'      $errors.Username text  '' '' true 'autocapitalize="none" autocorrect="off"'}

            <div class="inline-fields">
                {field Password        'Password'  $errors.Password        password '' '' true}
                {field PasswordConfirm '(Confirm)' $errors.PasswordConfirm password '' '' true}
            </div>

            <div class="submit-area">
                <button class="submit" type="submit">Create Account</button>
                <span class="submit-text">or <a href="/login{tif $.request.return ? cat('?return=', escape($.request.return, url))}">Log In</a></span>
            </div>
        </fieldset>
    </form>
{/block}