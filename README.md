Installation
============
This bundle provides a entity table generator for Symfony application running with doctrine.
#Dependencies

- Doctrine
- Fos JsRouter
- NodeJs
- Yarn

#Step 1: Enable the bundle
in : config/bundles.php

```php
return[
...
 Elparici\EntitySearchBundle\EntitySearchBundle::class => ['all' => true],
...
];

```
#Step 2: Enable routing:
in config/routes/entity_search.yaml

```yaml
_entity_search:
    resource: '@EntitySearchBundle/Resources/config/routes.xml'
    prefix: /entity-search/search
```
If you have not yet installed the lib, please add the FOS router vendor:

`composer require friendsofsymfony/jsrouting-bundle `

Full docs on https://symfony.com/doc/master/bundles/FOSJsRoutingBundle/usage.html

in assets/js/router.js
```javascript

import Routing from "../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js";
const routes = require("../../public/js/fos_js_routes.json");
Routing.setRoutingData(routes);

export default Routing

```
#Step 3: Configure the bundle

in: config/packages/entity_search.yaml
```yaml

entity_search:
    pager: pager_fanta # can be either pager_fanta or knp_paginator (not implemented yet)
    entities_mapping: 
        app.search.subscription:
            class: "App\\Entity\\Subscription"
            table_header: ['createdAt','guarantee', 'company', 'coveredValue', 'numInvoice','status', 'validTo'] # headers, must match with your entity properties. 
            header_trans:
                fr: ['Créé le','Type de guarantie', 'Société', 'Valeur assurée', 'référence','statut', 'début de validité', 'fin de validité']
                en: ['Created At','Guarantee type', 'Company', 'Covered value', 'Reference','status', 'start validity', 'end validity']
            likables: ['numInvoice', 'status']
            bool: []
            orderBy: ['createdAt'] # here we configure so the bundle will display reordering ASC|DESC buttons
            choices:
                status:
                    devis: quoted # label de la checkbox générée : valeur de la checkbox pour la recherche
                    payable: ready_to_pay #dito
                    souscrites: subscribed  #dito
            innerJoin: 
                company: name # entité enfant à pointer : right side = champ de recherche et d'affichage de l'entité enfant
                guarantee: code # dito
            actions_template: templates/actions.html.twig
            substitutes: # this value is optional, renders the template with the "value" and the entity. Make your own logic
                coveredValue: _widgets/table/coveredValue.html.twig
                status: _widgets/table/status.html.twig

```

#Step 4: Configure routes of the bundle:
##Register Controller:
in config/services.yaml, add:
```yaml
Elparici\EntitySearchBundle\Controller\EntitySearchController:
    public: true
    arguments:
        $fakeRepo: '@elparici_entity_search.utils.fake_repo'
        $pager: '@elparici_entity_search.utils.pager'
```

##Configure exposed routes
in config/routes/fos_js_routing.yaml
Your file should look like this:

```yaml
fos_js_routing:
    resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

entity_search_by_params:
    path: /elparici-entity-search/byParams
    defaults: { _controller: EntitySearchBundle:EntitySearch:byParams }
    options:
        expose: true

entity_search_get_headers:
    path: /elparici-entity-search/header
    defaults: { _controller: EntitySearchBundle:EntitySearch:header }
    options:
        expose: true

```

After you  made your changes, please update Json routes by running:

`php bin/console fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json `

#Step 5: Create your actions template
in templates/actions.html.twig (the template may be anywhere, name must just match with the entry `actions_template` of the config data mapping, cf. step 1)
```html
<div class="dropdown">
    <a aria-expanded="false" class="icon" data-target="#" data-toggle="dropdown" href="#">
        <i class="fe fe-more-vertical">
        </i>
    </a>
    <div class="dropdown-menu">
    	<a href="{{path('app_do_something', { id: entity.id })}}">Do something</a>
		etc...
        
    </div>
</div>

```
# step 6: Include the bundle template 

in ex: templates/subscriptions.html.twig
The name of the mapping must match the config file

```twig
	...
	{% include "@entity_search_bundle/search_panel.html.twig" with {
	       'data_mapping': 'app.search.subscription'
	 }%}
	 ...
```

#### You're all done!


#Options:

#Overriding templates:

##Example for substitute template

in templates/status.html.twig (the template may be anywhere, name must just match with the entry `substitutes:propertyName` of the config data mapping, cf. step 1)
Available data is `value` for the value of the cell, or `entity` to get back the object
```twig
{% if value == "foo" %}
{% set status = 'foofoo' %}
{% endif %}

<span class="{{status}}">{{entity.whatever}}</span>
```
