Installing InfiniteFormBundle
=============================

The installation of this bundle is handled by the use of `composer`:

``` bash
$ php composer.phar require infinite-networks/form-bundle
```

Once composer has added InfiniteFormBundle to composer.json and downloaded
it, you will need to add the bundle to your kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Infinite\FormBundle\InfiniteFormBundle,
    );
}
```

For the default rendering of the checkbox grid, include the form theme in
your config.yml:

``` yaml
twig:
    form:
        resources:
            - 'InfiniteFormBundle::form_theme.html.twig'
```
