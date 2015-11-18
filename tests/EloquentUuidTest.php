<?php

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

class EloquentUuidTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests the creation of model with uuid as primary key
	 *
	 * @return void
	 */
	public function testCreation() {

		// EloquentUserModel::unguard();

		$creation = EloquentUserModel::create([
			'username'=>'alsofronie',
			'password'=>'secret'
		]);

        // TODO: explore this, identify whether is from Laravel, factory or sqlite (:memory:)

		// For some reason, the $creation has id = 1
		// But in database, everything is correct.
		// More, in a real world application, the id is correctly set upon creation

		$model = EloquentUserModel::first();

		$this->assertEquals(32, strlen($model->id));
		$this->assertRegExp('/^[0-9a-f]{32}$/',$model->id);
		

		// EloquentuserModel::guard();

	}

    public function testBinaryCreation() {

        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;

        // We should be good with strlen because
        // in PHP the strings are not delimited by \0 like in C
        // but they are storing the length, also
        $this->assertEquals(16, strlen($binUuid));

        $hexUuid = bin2hex($binUuid);
        // This is to be expected, but just to show...
        $this->assertEquals(32, strlen($hexUuid));
    }



	/**
     * Bootstrap Eloquent.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {

        Eloquent::setConnectionResolver(
            new DatabaseIntegrationTestConnectionResolver
        );
        
        Eloquent::setEventDispatcher(
            new Illuminate\Events\Dispatcher
        );
        
    }

    /**
     * Tear down Eloquent.
     */
    public static function tearDownAfterClass()
    {
        Eloquent::unsetEventDispatcher();
        Eloquent::unsetConnectionResolver();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function setUp()
    {
        $this->schema()->create('users', function ($table) {
            $table->char('id',32);
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->unique('id');
        });

        $this->schema()->create('binusers', function($table) {
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });

        // unfortunately, we need to do this:
        // DB::statement (...)

        // This is not a mistake, We are testing if the binary we're save it's actually 16 bytes and
        // not being cut by DBS

        $this->connection()->statement('ALTER TABLE binusers ADD COLUMN id BINARY(18)');

    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');
        $this->schema()->drop('binusers');
    }

	/**
     * Helpers...
     */

    /**
     * Get a database connection instance.
     *
     * @return Connection
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }
}



class EloquentUserModel extends Eloquent {

	use UuidModelTrait;
	protected $table = 'users';

	protected $guarded = [];

}

class EloquentBinUserModel extends Eloquent {
    use UuidModelTrait;
    protected $table = 'binusers';
    protected $guarded = [];
    protected $uuidBinary = true;
}

class DatabaseIntegrationTestConnectionResolver implements Illuminate\Database\ConnectionResolverInterface
{
    protected $connection;

    public function connection($name = null)
    {
        if (isset($this->connection)) {
            return $this->connection;
        }

        return $this->connection = new Illuminate\Database\SQLiteConnection(new PDO('sqlite::memory:'));
    }

    public function getDefaultConnection()
    {
        return 'default';
    }

    public function setDefaultConnection($name)
    {
        //
    }
}

