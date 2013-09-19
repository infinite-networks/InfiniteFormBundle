InfiniteFormBundle's Collection Javascript
==========================================

Introduction
------------

The InfiniteFormBundle provides some helper javascript that allows you to easily implement
prototype handling and adding and removing rows from the collection. The javascript
library also implements support for our PolyCollection formtype.

Requirements
------------

Our collections.js helper requires jQuery 1.8 or greater.

Installation
------------

Include the source file into your javascript includes. This can be achieved by using
assetic or any other method supported by Symfony2 or your chosen templating method.

```html+jinja
    {% javascripts
        'components/jquery/jquery.js'
        'bundles/infiniteform/js/collections.js'
        output='js/application.js'
    %}
    <script src="{{ asset_url }}"></script>
    {% endjavascripts %}
```

Basic Use
---------

To use the library, you must initialise it for each collection you wish to enhance. The
easiest method to achieve this is to run a snippet of code on DOMReady, but you can
customise the initialisation to suit your own environment.

At this time, there is no simple method to activate this helper without adding additional
markup to your forms. You will need to customise the templates for each of your collection
types to add additional classes to target.

Add our `collection_theme.html.twig` template to your twig form theme templates: (note
that we do slightly modify the collection block to add an 'add' link instead of adding
the prototype to the collection div. Be aware this might break other collections in your
application!)

```yaml
# app/config/config.yml
twig:
    form:
        resources:
            - 'InfiniteFormBundle::collection_theme.html.twig'
            - '::form_theme.html.twig'
```

Once you've made this change, you can add something like the example below to initialise
the collection helper. This snippet is limited by the default template scheme and will
only find prototype buttons or links that are siblings to the element with the attribute
`data-form-widget`. For a real implementation, it makes more sense to customise each
collection's theme blocks to cater for specific requirements of each collection's needs.

```js
    $(function () {
        $('[data-form-widget=collection]').each(function () {
            new window.infinite.Collection(this, $(this).siblings('[data-prototype]'));
        });
    });
```

Full Example
------------

Consider a Customer object that contains an array of email addresses. We would define the
form type to look something like:

```php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->add('emails', 'collection', array(
            'type' => 'email',
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
        ));
        // ...
    }
```

Provided we've added the collection_theme.html.twig template to our twig form
configuration our template could potentially look like:

```html+jinja
    {% form_theme form.emails _self %}

    {% block body %}
    {# ... #}
    {{ form_row(form.emails) }}
    {# ... #}
    {% endblock body %}

    {% block _form_name_emails_entry_row %}
    <div class="item input-append">
        {{ form_widget(form) }}
        <a class="btn btn-danger remove_item"><i class="icon-minus"></i></a>
    </div>
    {% endblock _form_name_emails_entry_row %}
```

With the javascript included and the snippet supplied above added to your application
javascript, the add and remove buttons will automatically add or remove email input fields
to the collection when pressed.

Options
-------

There are options provided by the helper that let you configure how it finds information
or behaves in certain circumstances. These options are passed to the 3rd argument of the
collection helper's constructor.

| *Option*           | *Default Value* | *Description*                                   |
| ------------------ | --------------- | ----------------------------------------------- |
| allowAdd           | true            | Used to indicate to the collection if rows can be added. If false, the helper will not add new rows to the collection. This will not hide any prototype buttons already present in the DOM. |
| allowDelete        | true            | Used to indicate to the collection if rows can be removed. If false, the helper will not remove rows from the collection. Note that this will not remove (or add) any DOM elements to trigger deletion of rows. |
| itemSelector       | .item           | The selector used to indicate a single row in the collection. In your templates, each item of the collection must have a selector to identify it as a collection item. |
| prototypeAttribute | data-prototype  | The attribute where the prototype html is stored on each prototype button/link. |
| prototypeName      | \_\_name\_\_    | A Symfony2 Form component specific option: the name used for each prototype. It defaults to \_\_name\_\_ and unless changed in the form framework, does not need to be modified. |
| removeSelector     | .remove_item    | The selector to target a remove item button against each row. When clicked, the collection helper will remove the row from the DOM. |

Events
------

### infinite_collection_add

This event is fired when a new item is about to be added to a collection. It provides
a way to modify the prototype to pre-fill information, stop the item from being added,
change where in the collection the item should be added or otherwise modify how the row
should be handled. This event does not fire if the collection's allowAdd option is false.

If you prevent the default action from occuring, you will need to manually handle adding
the row to the collection.

Properties provided on the event object:

- `collection`: The window.infinite.Collection instance
- `$triggeredPrototype`: this is the dom element that triggered the adding of an item to
                         the collection. In the case of a normal collection type, the
                         prototype will be the add button. In the case of the
                         Polycollection, the prototype will be one of the prototype
                         buttons.
- `$row`: the jQuery wrapped DOM elements that will end up being added to the collection
          once the event finishes.
- `insertBefore`: if set by an event listener, the row will be inserted before this dom
                  element.

### infinite_collection_remove

This event is fired before a row is to be removed from the DOM. This event does not fire
if the collections allowDelete option is false.

If the default action is prevented, the row will not be removed and you will have to
manually handle removal from the DOM.

Properties provided on the event object:

- `collection`: The window.infinite.Collection instance

### infinite_collection_prototype

The prototype event is fired when building the prototype HTML from the data attribute that
stores the prototype HTML in string form.

This event allows custom behavior or modification to the prototype if required. It mostly
useful for modifying the label values when adding new items.

If the default action is prevented, the prototype name and label are not replaced
automatically and must be handled manually.

The HTML string set at `event.html` will be used for generating the new item. If you wish
to change it, modifying the value on the event will change the HTML used.

Properties provided on the event object:

- `collection`: The window.infinite.Collection instance
- `$triggeredPrototype`: this is the dom element that triggered the adding of an item to
                         the collection. In the case of a normal collection type, the
                         prototype will be the add button. In the case of the
                         PolyCollection, the prototype will be one of the prototype
                         buttons.
- `html`: The raw HTML to be used for generating the prototype. It should remain as a
          string of HTML. The helper will process this HTML in a later stage into the DOM.
- `replacement`: READ ONLY. What the prototype name should be replaced with. The helper
                 will generate the next integer based on its internal count of items in
                 the collection. If you wish to do a custom replacement it will need to
                 be applied directly to `event.html`.
