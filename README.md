# ODM Document Maker

[![CI Status](https://github.com/constantable/odm-document-maker/workflows/CI/badge.svg)](https://github.com/constantable/odm-document-maker/actions?query=workflow%3ACI)
[![Latest Version](https://img.shields.io/packagist/v/constantable/odm-document-maker.svg)](https://packagist.org/packages/constantable/odm-document-maker)

The ODM Document Maker helps you create Symfony Doctrine ODM Documents

## Installation

```bash
composer require constantable/odm-document-maker
```

## Maker

The `constantable:make-document` command creates or updates a document and repository class.

```bash
php bin/console constantable:make-document BlogPost
```

If the argument is missing, the command will ask for the document class name interactively.

You can also mark this class as an API Platform resource. A hypermedia CRUD API will
automatically be available for this document class:

```bash
php bin/console constantable:make-document --api-resource
```

You can also generate all the getter/setter/adder/remover methods
for the properties of existing documents:

```bash
php bin/console constantable:make-document --regenerate
```

To *overwrite* any existing methods:

```bash
php bin/console constantable:make-document --regenerate --overwrite
```

You can create an EmbeddedDocument class:

```bash
php bin/console constantable:make-document --embedded
```
