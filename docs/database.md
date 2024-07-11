# xmgr

# Database helpers

## Conditions

You can build conditions using the `where(...$conditions)` function. That function accepts a variable
amount of arguments. If the argument is an array, the array items are connected with `AND`. The arguments
itself (the `...$conditions`) are then connected with `OR`.

The `where()` returns a string which then can be used in a WHERE clause. But in most cases you'll access the conditions
via the generic Record or the QueryBuilder.

There are a couple of rules for the values passed:

### Associative array

An associative array simply means "if key equals value".

```php
where(
    [
        "id" => 5,
        // AND
        "is_admin" => 1
    ],
    // OR
    [
        "id" => 9,
        // AND
        "total" => 15
    ],
)

// (`id` = 5 AND `is_admin` = 1) OR (`id` = 9 AND `total` = 15)
```

### Associative array with array values

If an associative array is passed whose values are also arrays, an sql `IN` is built:

```php
where(
    [
        "foo" => [123, "Hello World", null],
        // AND
        "id" => [3, 17,89]
    ],
    // OR
    [
        "firstname" => ["John", "Sarah"]
    ]
)

// (`foo` IN (123, 'Hello World', NULL) AND `id` IN (3, 17, 89)) OR (`firstname` IN ('John', 'Sarah'))
```

### Integers

If the value is an integer, it's assumed that the key is 'id'. For example `123 // id = 123`.

> This also applies for the case when that integer is a value in an array whose key is also an integer,
> so `[123] // id = 123`.

```php
where(
    123,
    [456 => 789]
)

// (`id` = 123) OR (`id` = 789)
```

### String value

If the value is a string, you can just pass a raw sql condition, for example `key = value // key = value`.

> This also applies for the case when you pass a string inside an array with a numeric key, so `["id = 5"] // id = 5`.

```php
where(
    "`foo` = 'bar'",
    // OR
    [
        "`key` = 'value'",
        "`id` >= 9"
    ]
)

// (`foo` = 'bar') OR (`key` = 'value' AND `id` >= 9)
```

### Column

If the value is an instance of the `System\Database\Column` class, that implicitely will be casted to a string.

```php
where(
    column("id")->equals(5),
    # OR
    [
        column("counter")->greaterThan(50),
        // AND
        column("is_admin")->isTrue(),
        // AND
        column("last_login")->isNotNull()
    ]
)

// ((`id` = 5)) OR ((`counter` > 50) AND (`is_admin` = 1) AND (`last_login` IS NOT NULL))
```

### ColumnName

If the value is a `System\Database\ColumnName` instance, the value of another column is used as value.

This is especially useful when using joins.

> You can use the shorthand `colname()` function for that.

```php
where(
    ['id' => column('post.user_id')->name()]
)

// (`id` = `post`.`user_id`)
```

### Some example with mixed values and types

Example:

```php
where(
        [
            "key" => "value",
            // AND:
            "foo" => "bar"
        ],
        // OR:
        123,
        // OR:
        column("total")->greaterThan(35),
        // OR:
        [
            column("is_admin")->isTrue(),
            // AND:
            "`user_id` = 15"
        ]
)
```

Result:

```sql
(`key` = 'value' AND `foo` = 'bar')
OR ((`total` > 35)) OR ((`is_admin` = 1) AND `user_id` = 15)
```

## Additional core helper functions

### dbkey()

Make a safe table or column name and automatically adds corresponding backticks.

```php
dbkey("*")         // *
dbkey("name")      // `name`
dbkey("``name`")   // `name`
dbkey("foo.bar")   // `foo`.`bar`
dbkey("  test  ")  // `test`
```

### dbvalue()

Creates a save, native sql value

```php
dbvalue(123),                   // 123
dbvalue("Hello w'orld"),        // 'Hello w\'orld'
dbvalue("' OR 1; --"),          // '\' OR 1; --'
dbvalue(null),                  // NULL
dbvalue(true),                  // 1
dbvalue(false),                 // 0
dbvalue(["foo" => "bar"]),      // '{\"foo\":\"bar\"}'     (auto-cast to json)
```

### sql()

Instantiates a query builder object.

```php
sql("users")->where(...);
```

## Query builder

The query builder builds sql queries.

```php
$builder = new \Xmgr\Database\QueryBuilder("users");
// Alternatively:
// $builder = sql("users");
$builder->select(["id", "name"])->where(column("admin")->isTrue())->desc("id")->limit(5);
$builder->insert("users", ["name" => "John", "email" => "john.doe@example.com"]);
$builder->update("users", ["name" => "Sarah"])->where(123);
$builder->delete("users", 123);
```

### Methods to actually run the queries

#### get()

Fetch all rows for a SELECT query. This will return a 2-dimensional array, where the values are associative
key-value-pair arrays.

#### first()

Fetch first row for a SELECT query. This will return a 1-dimensional key-value-pair array.

#### collect()

Does the same as the `get()` method but returns a `App\ModelCollection` instance, containing the models.

```php
User::query()->collect();
```

Will return something like this:

```php
App\ModelCollection Object
(
    [items:protected] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [name] => Liam Lewis
                    [email] => liam-lewis@example.com
                    [password] => $2y$10$sB2dgeCms.w4z5jq17DpzO6Cfs.OPxlbxIrCvyzOPHo2U/U49cYW2
                )

            [1] => Array
                (
                    [id] => 2
                    [name] => Logan King
                    [email] => logan_king@example.com
                    [password] => $2y$10$AcFM37b4GxqBFLogWKa5GuotyDAT52NWIqFAFFCpGNqUuepQa91si
                )

            [2] => Array
                (
                    [id] => 3
                    [name] => Alexander Garcia
                    [email] => alexander.garcia@example.com
                    [password] => $2y$10$uwHb/Ydr6OAP78UTvVCnHO1wAgCt0ZVIXq2KEYgeu8e2/WajD2.TK
                )

        )

)
```

#### exec()

Execute a INSERT, UPDATE or DELETE query. This will then return an integer (the number of affected rows) or a
boolean false if the query failed for some reason.

### Methods to return the generated sql

#### getSelectStatement()

Returns the raw SELECT query.

#### getInsertStatement()

Returns the raw INSERT query.

#### getUpdateStatement()

Returns the raw UPDATE query.

#### getDeleteStatement()

Returns the raw DELETE query.

### Methods for filtering, conditions and joins

#### join()

Specify the table to join and a condition with `where()` to build the corresponding `ON` clause.

```php
sql('posts')->select('*')->join('users', ['posts.user_id' => column('user.id')->name()])->latest(10);
// SELECT * FROM `posts` JOIN `users` ON (`posts`.`user_id` = `user`.`id`) ORDER BY `id` DESC LIMIT 10
```

#### innerJoin()

#### leftJoin()

#### setData()

Set key-value-pair for INSERT or UPDATE queries.

#### from() or setTable()

Set the table

#### where()

Pass a variable amount of arguments as conditions.

#### having()

Builds HAVING clause.

#### orderBy()

Build ORDER BY clause (`orderBy('id', 'ASC')`).

#### asc() and desc()

Shorthand for `orderBy()`.

#### latest()

Shorthand for `desc('id')`. You can pass an integer as 1st argument to directly set a limit. You can also pass a string
as 2nd argument to choose another column (if 'id' does not make sense in your case).

```php
sql('posts')->latest(10);
// SELECT * FROM `posts` ORDER BY `id` DESC LIMIT 10
```

#### limit()

Limit rows (`limit(25)`).

#### find()

Shorthand condition to check for id field.

# Model

The `System\Record` class is a way to interact with the table data. The `App\Model` class extends the
Record, and classes in `app/Models` extend the Model class.

## Properties

### static $table

Defines the table name in the database.

### static $primary

Defines the primary column of the table (defaults to "id").

### $data

Holds the data. If you set new values, this property is updated.

### $initialData

Holds the initial data (in case the record already exists in the database). This value never changes.

### $modifiedData

Holds updated data. Each time you set a value, this property will be updated.

## Methods

### exists()

Check if a model exists (it checks if the primary key column has an integer value that is not zero).

### revert()

Resets the $data property to the $initialData value.

### static table()

Returns the table name.

### find(int $id)

To quickly get a record by the given id.

### first()

Returns the first record that matches the given ...$where conditions.

### where()

To specify some conditions.

### query()

This is to return a QueryBuilder object (the table name is already given) so you can handle flexible sql queries.

```php
\App\Models\User::all()->latest(25);
```

### has()

Pass a string (column name) to check.

### get()

Returns the value of the given column.

### save()

This will either create or update the record.

### reload()

Clears all data and freshly fetches them from the database.

> Works only if the record already exists.

### delete()

To delete a record.

### primaryValue()

Returns the value of the primary key column.

### random()

Returns one record from the table selected randomly.

### truncate()

Removes all records for the table.

### toArray()

This returns the plain data as array.

### hasMany()

Returns many models that belong to the current (1:n).

```php
$author->hasMany(Post::class, 'author_id'); // Returns 1 or more posts
```

### belongsTo()

Returns one model that is related to the current (n:1).

```php
$post->belongsTo(User::class, 'author_id'); // Returns 1 user
```

