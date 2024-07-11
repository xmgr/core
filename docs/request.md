# Request

```php
# Fetch data from get or post - first argument is the key, 2nd argument is the default value
Xmgr\Request::get('foo', 'nothing')
Xmgr\Request::post('foo', 'nothing')
Xmgr\Request::getOrPost('foo', 'nothing')
Xmgr\Request::postOrGet('foo', 'nothing')

# Fetch raw request body
Xmgr\Request::body()
# Or:
Xmgr\Request::body()

# Request method (if there is a "_method" value provided via post form, that value will be returned)
Xmgr\Request::method()
# Returns the actual request method
Xmgr\Request::httpMethod()

# JSON data as array
Xmgr\Request::jsonData()

# Request id (which has been submitted via post or get)
Xmgr\Request::id()

# Request headers
Xmgr\Request::headers()
```