# PDO Debug
A collection of small debugging classes.

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```bash
php composer.phar require sjoerdmaessen/pdodebug "dev-master"
```

Usage
------------
In a simple project which includes PDO the easiest way is set the class Sjoerdmaessen\PDODebug\Statement as the user-supplied statement class. For example:

```php
// Init db
$dbOptions = array(
	PDO::ATTR_EMULATE_PREPARES => true,
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

// Add additional database debugging methods
if ($app->isInDebugMode()) {
	$dbOptions[PDO::ATTR_STATEMENT_CLASS] = array('\Sjoerdmaessen\PDODebug\Statement', array());
}

$db = new PDO('mysql:host=localhost;dbname=' . DB_NAME, DB_USER, DB_PASSWORD, $dbOptions);
```

Now, when calling getQuery() you will see a parsed version of the query with its bounded parameters. Optionally the method getFormattedErrorInfo() sometimes give some usefull and quick (but basic) information what the cause of the query error could be.

For a project with Doctrine where you have the QueryBuilder object:

```php
$qb = $this->createQueryBuilder('t');
echo '<pre>';
var_dump(DoctrineQuery::getQuery($qb));
```

Or if you have a native query:

```php
$nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $result)->setParameter('user_id', $user->getId());
echo '<pre>';
var_dump(\Sjoerdmaessen\PDODebug\DoctrineQuery::getQuery($nativeQuery));
```

Or, just plain SQL with some parameters:

```php
echo '<pre>';
var_dump(\Sjoerdmaessen\PDODebug\DoctrineQuery::getQuery($sql, ['user_id' => $user->getId()]));
```

Contributing
------------

This package is an open source project. Contribution is highly appreciated.
