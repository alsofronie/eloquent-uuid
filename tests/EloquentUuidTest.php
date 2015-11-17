<?php

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;

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
			'password'=>'secret',
			'email'=>'alsofronie@gmail.com'
		]);

		// For some reason, the $creation has id = 1
		// But in database, everything is correct.
		// More, in a real world application, the id is correctly set upon creation

		$model = EloquentUserModel::first();

		$this->assertEquals(32, strlen($model->id));
		$this->assertRegExp('/^[0-9a-f]{32}$/',$model->id);
		

		// EloquentuserModel::guard();

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
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->unique('id');
        });

    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');
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

