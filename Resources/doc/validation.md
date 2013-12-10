InfiniteFormBundle's Validation Constraints
===========================================

ABN Validator
-------------

The ABN Validator provided by InfiniteFormBundle will validate an Australia Business
Number.

```php
use Infinite\FormBundle\Validator\Constraint as InfiniteAssert;

class Company
{
    /**
     * @InfiniteAssert\Abn
     */
    protected $abn;
}
```
