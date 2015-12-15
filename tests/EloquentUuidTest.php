<?php

use Alsofronie\Uuid\UuidModelTrait;
use Alsofronie\Uuid\Uuid32ModelTrait;
use Alsofronie\Uuid\UuidBinaryModelTrait;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\DB;

class EloquentUuidTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests the creation of model with uuid as primary key
     *
     * @return void
     */
    public function testCreation()
    {
        // EloquentUserModel::unguard();
        $creation = EloquentUserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $this->assertEquals(36, strlen($creation->id));

        $model = EloquentUserModel::first();

        $this->assertEquals(36, strlen($model->id));
        $this->assertRegExp('/^[0-9a-f-]{36}$/', $model->id);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $model->id);

        $this->assertEquals($creation->id, $model->id);

        // EloquentuserModel::guard();

    }

    public function test32Creation()
    {
        $creation = Eloquent32UserModel::create([
            'username'=>'alsofronie',
            'password'=>'secret'
        ]);

        $this->assertEquals(32, strlen($creation->id));

        $model = Eloquent32UserModel::first();

        $this->assertEquals(32, strlen($model->id));
        $this->assertRegExp('/^[0-9a-f]{32}$/', $model->id);
        $this->assertRegExp('/^[0-9a-f]{32}$/', $model->id);

        $this->assertEquals($creation->id, $model->id);
    }

    public function testBinaryCreation()
    {
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

        $this->assertEquals($creation->id, $model->id);

        $hexUuid = bin2hex($binUuid);
        // This is to be expected, but just to show...
        $this->assertEquals(32, strlen($hexUuid));

        $this->assertEquals($hexUuid, $model->id_string);

    }

    public function testBinaryFind()
    {

        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        $this->assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($binUuid);
        $this->assertEquals($found, $model);

    }

    public function testBinaryFindFromStringUuid()
    {
        $creation = EloquentBinUserModel::create([
            'username'=>'alsofronie-binary',
            'password'=>'secret'
        ]);

        $model = EloquentBinUserModel::first();

        $binUuid = $model->id;
        $hexUuid = bin2hex($binUuid);

        $this->assertEquals($hexUuid, $model->id_string);

        $found = EloquentBinUserModel::find($hexUuid);
        $this->assertEquals($found, $model);
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
            $table->char('id', 36);
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('users32', function ($table) {
            $table->char('id', 36); // this is not a mistake, we need to be sure the field is not stripped down by the DB
            $table->string('username');
            $table->string('password');
            $table->timestamps();
            $table->primary('id');
        });

        $this->schema()->create('usersb', function ($table) {
            $table->string('username');
            $table->string('password');
            $table->timestamps();
        });

        // unfortunately, we need to do this:
        // DB::statement (...)
        $this->connection()->statement('ALTER TABLE `usersb` ADD `id` BINARY(16); ALTER TABLE `usersb` ADD PRIMARY KEY (`id`);');

    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema()->drop('users');
        $this->schema()->drop('users32');
        $this->schema()->drop('usersb');
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



class EloquentUserModel extends Eloquent
{
    use UuidModelTrait;
    protected $table = 'users';

    protected $guarded = [];
}

class Eloquent32UserModel extends Eloquent
{
    use Uuid32ModelTrait;
    protected $table = 'users32';

    protected $guarded = [];
}

class EloquentBinUserModel extends Eloquent
{
    use UuidBinaryModelTrait;
    protected $table = 'usersb';
    
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
