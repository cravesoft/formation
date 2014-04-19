<?php

$app['ldap'] = $app->share(function()
{
    $ldap = new Ldap(array(
        $app['config']['ldap']
    ));
    return $ldap->bind();
});
