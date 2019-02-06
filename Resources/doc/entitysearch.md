InfiniteFormBundle's EntitySearch Form Type
===========================================

Synopsis
--------

```php
    $builder->add('customer', EntitySearchType::class, array(
        'class' => Customer::class,
        'name' => 'fullName',
        'attr' => ['placeholder' => 'Start typing to search for a customer ...'],
        'search_route' => 'customer_search_json',
    ));
```

This appears as a normal text field with autocompletion.

You must add js-autocomplete as a dependency and include
bundles/infiniteform/js/entity-search.js to enable the autocomplete feature.

You must define the search_route yourself:

```php
    /**
     * @Route("/customer/search.json", name="customer_search_json")
     *
     * @return JsonResponse
     */
    public function customerSearchJson(Request $request)
    {
        $query = $request->query->get('query');

        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Customer::class);

        $qb = $repo->createQueryBuilder('c');
        $qb->andWhere('c.name LIKE :match');
        $qb->setParameter('match', '%' . strtr($query, [' ' => '%']) . '%');
        $qb->setMaxResults(20);

        $results = [];

        foreach ($qb->getQuery()->getResult() as $customer) {
            /** @var Customer $customer */
            $results[] = [
                'id'   => $customer->getId(),
                'name' => $customer->getName(),
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
```

Additional notes
----------------

EntitySearchType field includes a visible text field and a hidden ID field.
The hidden ID field is set whenever the user selects an item from the
autocomplete dropdown, and is unset whenever the user types in the text field.

If the user types in some text and submits the form without clicking on an
item, EntitySearchType will search for an exact match using the field
specified in the 'name' option.

entity-search.js is intentionally very simple. For more advanced uses it will
be necessary to write custom integration code. See entity-search.js for ideas.
