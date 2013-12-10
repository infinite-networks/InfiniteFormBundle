InfiniteFormBundle's Twig Helper
================================

isValid test
------------

The helper provides a validity test that can be used in templates. It can be used for
something like the following:

```html+jinja
<div class="{% if form.field is invalid %}error{% endif %}">
    {{ form_row(form.field) }}
</div>
```
