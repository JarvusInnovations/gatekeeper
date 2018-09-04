<?php

{{#if cfg.site.holo.repo ~}}
    $gitDir = '{{ cfg.site.holo.repo }}';
    $siteRoot = '{{ pkg.svc_var_path }}/site';
{{else ~}}
    error_log('load.php can only be invoked if site.holo.repo is configured');
    exit(1);
{{/if}}


if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
    error_log('load.php method must be PUT');
    exit(1);
}


// read input tree-ish
$treeish = trim(file_get_contents('php://input'));

if (!$treeish) {
    error_log('load.php must have tree-ish provided via STDIN');
    exit(1);
}


// prepare git client
$git = '{{pkgPathFor "core/git"}}/bin/git';
putenv("GIT_DIR=${gitDir}");
putenv("GIT_WORK_TREE=${siteRoot}");
putenv("GIT_INDEX_FILE=${siteRoot}.INDEX");


// convert input to a hash
$inputHash = exec("$git rev-parse --verify ".escapeshellarg("$treeish"));

if (!$inputHash) {
    error_log('load.php could not read tree-ish "'.$treeish.'" from repository "'.$gitDir.'"');
    exit(1);
}


// ensure input is a tree hash
$treeHash = exec("$git rev-parse --verify ${inputHash}^{tree}");

echo "reading tree: '$treeHash'";
echo shell_exec("$git read-tree $treeHash 2>&1");


echo "checking out index";
echo shell_exec("$git checkout-index -af 2>&1");


echo "cleaning tree";
echo shell_exec("$git clean -df 2>&1");
