InfiniteFormBundle's Choice Tree Form Type
=============================================

Introduction
------------

The choice tree form type allow you to display a tree of choice, a simple
choice list give you the possibility to generate group but just for one level.

Installation
------------

[Installation of InfiniteFormBundle](installation.md) is covered in a separate
document. The Choice Tree type is automatically enabled when the bundle is
installed.

Object Structure
----------------

The Choice Tree type work as a choice but choices must implement a specific pattern:
```php
        $tree = {
            0 => [
                "value" => 1,
                "label" => Object/string,
                "choice_list" =>{
                    0 => [
                        "value" => 3,
                        "label" => Object/string,
                        "choice_list" => []
                    ]
                }
            ],
            1 => [
                "value" => 2,
                "label" => Object/string,
                "choice_list" =>{
                    0 => [
                        "value" => 1,
                        "label" => Object/string,
                        "choice_list" => []
                    ]
                }
            ]
        }
        $builder
            ->add(
                'category', 'infinite_form_choice_tree', [
                    'choices'     => $tree,
                ]
            );

```
For example when you use the Nested Tree Repository from Gedmo :

```php
    class MyFormType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $tree = $myRepo->buildTree($myRepo->getNodesHierarchy());
            // then we need recursively parse this tree for the form with a recursive function
            $builder
                ->add(
                    'category', 'infinite_form_choice_tree', [
                        'choices'     => $this->rebuildTree($tree),
                    ]
                );

        }


        public function rebuildTree($tree)
        {
            $hierarchy = [];
            foreach ($tree as $children) {
                $node = [];
                $node['label'] = $category['title'];
                $node['value'] = $category['id'];
                $node['choice_list'] = $this->rebuildTree($children);
                $hierarchy[] = $node;

            }
            return $hierarchy;
        }
    }
```

#TODO
----------------

L'utilisation de paramètres dans le form type devraient permettre de parser ce qui est passer par "choices",
pour éviter ainsi d'avoir à implémenter une méthode de parsing