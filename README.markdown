## Examples

**Simple usage**

    $db = new DB('./tmp');

    // write to DB
    $db->put('foo', 'id');

    // update in DB
    $db->update('id', 'bar');

    // read from DB
    $db->get('id');

    // delete in DB
    $db->delete('id');


**Usage with "extends"**

	class DB1 extends DB
	{
		public function __construct()
		{
			parent::__construct('./tmp1');
		}
	}

	class DB2 extends DB
	{
		public function __construct()
		{
			parent::__construct('./tmp2');
		}
	}

	$db1 = DB1::getInstance();
	var_dump($db1);

	$db2 = DB2::getInstance();
	var_dump($db2);

	// call methods "put", "update", "get", "delete"...
